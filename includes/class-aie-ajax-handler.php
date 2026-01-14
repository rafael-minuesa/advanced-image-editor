<?php
/**
 * AJAX Handler class for Advanced Image Editor
 *
 * Handles all AJAX requests for image processing
 *
 * @package AdvancedImageEditor
 * @author Rafael Minuesa
 * @license GPL-2.0+
 * @link https://github.com/rafael-minuesa/advanced-image-editor
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIE_Ajax_Handler {

    /**
     * Constructor - Register AJAX hooks
     */
    public function __construct() {
        add_action('wp_ajax_aie_preview', [$this, 'ajax_preview']);
        add_action('wp_ajax_aie_save', [$this, 'ajax_save']);
    }

    /**
     * AJAX handler for previewing image filters
     */
    public function ajax_preview() {
        // Check user capability
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'advanced-image-editor'));
        }

        // Check rate limiting
        if ($this->check_rate_limit('preview')) {
            wp_send_json_error(__('Too many requests. Please wait a moment before trying again.', 'advanced-image-editor'));
        }

        // Validate nonce
        if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce(wp_unslash($_POST['_ajax_nonce']), 'aie_nonce')) {
            wp_send_json_error(__('Security check failed.', 'advanced-image-editor'));
        }

        // Validate required parameters
        if (!isset($_POST['image_id']) || empty($_POST['image_id'])) {
            wp_send_json_error(__('No image selected.', 'advanced-image-editor'));
        }

        $attachment_id = absint($_POST['image_id']);
        $contrast      = isset($_POST['contrast']) ? floatval($_POST['contrast']) : 0;
        $amount        = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $radius        = isset($_POST['radius']) ? floatval($_POST['radius']) : 1;
        $threshold     = isset($_POST['threshold']) ? floatval($_POST['threshold']) : 0;

        // Validate parameter ranges
        $contrast = max(-1, min(1, $contrast)); // Clamp between -1 and 1
        $amount = max(0, min(5, $amount)); // Clamp between 0 and 5
        $radius = max(0, min(5, $radius)); // Clamp between 0 and 5
        $threshold = max(0, min(1, $threshold)); // Clamp between 0 and 1

        // Check if attachment exists
        if (!wp_attachment_is_image($attachment_id)) {
            wp_send_json_error(__('Invalid image attachment.', 'advanced-image-editor'));
        }

        $path = get_attached_file($attachment_id);

        if (!file_exists($path)) {
            wp_send_json_error(__("Image file not found on server.", 'advanced-image-editor'));
        }

        // Check file size
        $file_size = filesize($path);
        if ($file_size === false || $file_size > Advanced_Image_Filters::MAX_FILE_SIZE) {
            wp_send_json_error(__('Image file is too large to process.', 'advanced-image-editor'));
        }

        // Check image dimensions
        $image_info = @getimagesize($path);
        if ($image_info === false) {
            wp_send_json_error(__('Unable to read image dimensions.', 'advanced-image-editor'));
        }

        $width = $image_info[0];
        $height = $image_info[1];

        if ($width > Advanced_Image_Filters::MAX_IMAGE_WIDTH || $height > Advanced_Image_Filters::MAX_IMAGE_HEIGHT) {
            /* translators: 1: Current image width, 2: Current image height, 3: Maximum allowed width, 4: Maximum allowed height */
            wp_send_json_error(
                sprintf(
                    __('Image dimensions (%1$dx%2$d) exceed maximum allowed size (%3$dx%4$d).', 'advanced-image-editor'),
                    $width, $height, Advanced_Image_Editor::MAX_IMAGE_WIDTH, Advanced_Image_Editor::MAX_IMAGE_HEIGHT
                )
            );
        }

        // Estimate memory usage (rough calculation: width * height * 4 bytes per pixel * 3 for processing)
        $estimated_memory = $width * $height * 4 * 3;
        $memory_limit = $this->get_memory_limit_bytes();

        if ($estimated_memory > $memory_limit) {
            wp_send_json_error(__('Image is too large to process with current memory limits.', 'advanced-image-editor'));
        }

        $img = null;
        try {
            $img = new Imagick($path);

            // Store original format for output
            $original_format = $img->getImageFormat();

            // Apply Contrast
            if ($contrast !== 0) {
                // Convert to correct range for contrastImage (boolean parameter)
                // Positive contrast = enhance, negative = reduce
                $img->contrastImage($contrast > 0);
            }

            // Apply Unsharp Mask
            if ($amount > 0 && $radius > 0) {
                $img->unsharpMaskImage($radius, 1, $amount, $threshold);
            }

            // Create preview in JPEG format for display (but keep original for saving)
            $preview_img = clone $img;
            $preview_img->setImageFormat('jpeg');
            $preview_img->setImageCompressionQuality(Advanced_Image_Filters::PREVIEW_QUALITY);
            $preview_blob = $preview_img->getImageBlob();
            $preview_base64 = base64_encode($preview_blob);
            $preview_img->clear();

            wp_send_json_success([
                'preview' => 'data:image/jpeg;base64,' . $preview_base64,
                'original_format' => $original_format,
                'mime_type' => advanced_image_editor_get_mime_type_from_format($original_format)
            ]);

        } catch (Exception $e) {
            $this->log_error(
                'Preview processing failed',
                [
                    'image_id' => $attachment_id,
                    'error' => $e->getMessage(),
                    'file_size' => $file_size ?? 0,
                    'dimensions' => [$width ?? 0, $height ?? 0]
                ]
            );

            /* translators: %s: Error message from image processing */
            wp_send_json_error(
                sprintf(
                    __('Image processing failed: %s', 'advanced-image-editor'),
                    $e->getMessage()
                )
            );
        } finally {
            // Clean up Imagick resource
            if ($img instanceof Imagick) {
                $img->clear();
            }
        }
    }

    /**
     * AJAX handler for saving edited image
     */
    public function ajax_save() {
        // Check user capability
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'advanced-image-editor'));
        }

        // Check rate limiting (stricter for save operations)
        if ($this->check_rate_limit('save')) {
            wp_send_json_error(__('Too many save requests. Please wait a moment before trying again.', 'advanced-image-editor'));
        }

        // Validate nonce
        if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce(wp_unslash($_POST['_ajax_nonce']), 'aie_nonce')) {
            wp_send_json_error(__('Security check failed.', 'advanced-image-editor'));
        }

        // Validate required parameters
        if (!isset($_POST['image_id']) || empty($_POST['image_id'])) {
            wp_send_json_error(__('No image selected.', 'advanced-image-editor'));
        }

        if (!isset($_POST['image_data']) || empty($_POST['image_data'])) {
            wp_send_json_error(__('No image data provided.', 'advanced-image-editor'));
        }

        $attachment_id = absint($_POST['image_id']);
        // Don't sanitize base64 data with sanitize_text_field - it will corrupt it
        // Instead, validate the base64 string format
        $image_data = wp_unslash($_POST['image_data']);

        // Validate base64 format - accept any image MIME type
        if (!preg_match('/^data:image\/[a-z]+;base64,/', $image_data)) {
            $this->log_save_error(
                'Invalid base64 format received',
                $attachment_id,
                ['data_length' => strlen($image_data)]
            );
            wp_send_json_error(__('Invalid image data format.', 'advanced-image-editor'));
        }

        // Extract MIME type and data
        preg_match('/^data:image\/([a-z]+);base64,/', $image_data, $matches);
        $mime_type = 'image/' . ($matches[1] ?? 'jpeg');
        $image_data = str_replace('data:image/' . ($matches[1] ?? 'jpeg') . ';base64,', '', $image_data);

        // Verify attachment exists
        if (!wp_attachment_is_image($attachment_id)) {
            wp_send_json_error(__('Invalid image attachment.', 'advanced-image-editor'));
        }

        // Get original image for filename reference
        $original_path = get_attached_file($attachment_id);
        $original_info = pathinfo($original_path);
        $original_name = $original_info['filename'];

        // Strip base64 header
        $decoded = base64_decode($image_data, true);

        if ($decoded === false) {
            wp_send_json_error(__('Failed to decode image data.', 'advanced-image-editor'));
        }

        // Validate decoded data is actually an image
        $image_info = @getimagesizefromstring($decoded);
        if ($image_info === false) {
            wp_send_json_error(__('Decoded data is not a valid image.', 'advanced-image-editor'));
        }

        // Create new file with unique name
        $upload_dir = wp_upload_dir();
        if ($upload_dir['error'] !== false) {
            wp_send_json_error(__('Failed to access upload directory.', 'advanced-image-editor'));
        }

        $extension = advanced_image_editor_get_extension_from_mime_type($mime_type);
        $filename = sanitize_file_name($original_name . '-edited-' . time() . '.' . $extension);
        $file_path = trailingslashit($upload_dir['path']) . $filename;

        // Use wp_upload_bits for better WordPress integration
        $upload = wp_upload_bits($filename, null, $decoded);

        if ($upload['error']) {
            /* translators: %s: Upload error message */
            wp_send_json_error(sprintf(__('Failed to save image file: %s', 'advanced-image-editor'), $upload['error']));
        }

        $file_path = $upload['file'];

        // Prepare attachment data
        $attachment = [
            'post_mime_type' => $mime_type,
            'post_title'     => sanitize_text_field($original_name . ' (Edited)'),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_excerpt'   => __('Edited with Advanced Image Editor', 'advanced-image-editor'),
            'post_parent'    => 0, // No parent post
        ];

        // Insert attachment
        $new_id = wp_insert_attachment($attachment, $file_path);

        if (is_wp_error($new_id)) {
            // Clean up uploaded file on error
            wp_delete_file($file_path);

            $this->log_save_error(
                'Failed to insert attachment',
                $attachment_id,
                ['wp_error' => $new_id->get_error_data()]
            );

            wp_send_json_error($new_id->get_error_message());
        }

        // Generate metadata
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($new_id, $file_path);
        wp_update_attachment_metadata($new_id, $metadata);

        // Get edit link for the new attachment
        $edit_link = get_edit_post_link($new_id, 'raw');

        wp_send_json_success([
            'new_attachment_id' => $new_id,
            'message' => __('Image saved successfully!', 'advanced-image-editor'),
            'edit_link' => $edit_link ?: admin_url('post.php?post=' . $new_id . '&action=edit')
        ]);
    }

    /**
     * Check rate limiting for AJAX requests
     *
     * @param string $action Action name for rate limiting
     * @return bool True if rate limit exceeded
     */
    private function check_rate_limit($action = 'general') {
        $user_id = get_current_user_id();
        $ip = $this->get_client_ip();

        // Use IP + user ID as identifier for rate limiting
        $identifier = md5($ip . '_' . $user_id . '_' . $action);
        $transient_key = 'aif_rate_limit_' . $identifier;

        $requests = get_transient($transient_key);

        if ($requests === false) {
            // First request in window
            set_transient($transient_key, 1, Advanced_Image_Filters::RATE_LIMIT_WINDOW);
            return false;
        }

        if ($requests >= Advanced_Image_Filters::RATE_LIMIT_REQUESTS) {
            // Rate limit exceeded
            $this->log_error(
                'Rate limit exceeded',
                [
                    'action' => $action,
                    'requests' => $requests,
                    'ip' => $ip,
                    'user_id' => $user_id
                ],
                'warning'
            );
            return true;
        }

        // Increment counter
        set_transient($transient_key, $requests + 1, Advanced_Image_Filters::RATE_LIMIT_WINDOW);
        return false;
    }

    /**
     * Get memory limit in bytes
     *
     * @return int Memory limit in bytes
     */
    private function get_memory_limit_bytes() {
        $memory_limit = ini_get('memory_limit');

        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];

            switch (strtoupper($unit)) {
                case 'G':
                    $value *= 1024 * 1024 * 1024;
                    break;
                case 'M':
                    $value *= 1024 * 1024;
                    break;
                case 'K':
                    $value *= 1024;
                    break;
            }

            return $value;
        }

        return 134217728; // Default 128MB if parsing fails
    }

    /**
     * Log errors with context for debugging
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param string $level Log level (error, warning, info)
     */
    private function log_error($message, $context = [], $level = 'error') {
        $log_message = sprintf(
            '[Advanced Image Editor] %s - User: %s, IP: %s',
            $message,
            get_current_user_id(),
            $this->get_client_ip()
        );

        if (!empty($context)) {
            $log_message .= ' - Context: ' . wp_json_encode($context);
        }

        if (function_exists('wp_log_error')) {
            // WordPress 6.5+ has wp_log_error function
            wp_log_error($log_message);
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            error_log($log_message);
        }
    }

    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = wp_unslash($_SERVER[$header]);
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Log save operation errors
     *
     * @param string $message Error message
     * @param int $attachment_id Original attachment ID
     * @param array $context Additional context
     */
    private function log_save_error($message, $attachment_id, $context = []) {
        $this->log_error(
            $message,
            array_merge([
                'original_image_id' => $attachment_id,
                'action' => 'save_edited_image'
            ], $context)
        );
    }
}