<?php
/**
 * Main Advanced Image Editor class
 *
 * Handles plugin initialization, assets, and core functionality
 *
 * @package AdvancedImageEditor
 * @author Rafael Minuesa
 * @license GPL-2.0+
 * @link https://github.com/rafael-minuesa/advanced-image-editor
 */

if (!defined('ABSPATH')) {
    exit;
}

class Advanced_Image_Editor {

    /**
     * Plugin version
     */
    const VERSION = AIE_VERSION;

    /**
     * Maximum image file size in bytes (10MB)
     */
    const MAX_FILE_SIZE = 10485760;

    /**
     * JPEG quality for preview images
     */
    const PREVIEW_QUALITY = 90;

    /**
     * Maximum image width for processing (pixels)
     */
    const MAX_IMAGE_WIDTH = 4096;

    /**
     * Maximum image height for processing (pixels)
     */
    const MAX_IMAGE_HEIGHT = 4096;

    /**
     * Rate limiting: maximum requests per minute
     */
    const RATE_LIMIT_REQUESTS = 30;

    /**
     * Rate limiting window in seconds
     */
    const RATE_LIMIT_WINDOW = 60;

    /**
     * AJAX handler instance
     *
     * @var AIE_Ajax_Handler
     */
    private $ajax_handler;

    /**
     * Constructor - Initialize hooks and filters
     */
    public function __construct() {
        $this->ajax_handler = new AIE_Ajax_Handler();

        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // Add plugin action links
        add_filter('plugin_action_links_' . AIE_PLUGIN_BASENAME, [$this, 'add_plugin_action_links']);
    }

    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('advanced-image-editor', false, dirname(AIE_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Add action links to plugin row
     *
     * @param array $links Existing plugin action links
     * @return array Modified links array
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('upload.php?page=advanced-image-editor') . '">' . __('Open Editor', 'advanced-image-editor') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add admin menu page under Media
     */
    public function add_menu() {
        add_media_page(
            __('Advanced Image Editor', 'advanced-image-editor'),
            __('Advanced Image Editor', 'advanced-image-editor'),
            'upload_files',
            'advanced-image-editor',
            [$this, 'render_editor_page']
        );
    }

    /**
     * Enqueue admin assets (CSS and JS)
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'media_page_advanced-image-editor') {
            return;
        }

        wp_enqueue_media(); // Enables WP media modal

        // Get file modification times for cache busting
        $css_path = AIE_PLUGIN_DIR . 'assets/css/admin.css';
        $js_path = AIE_PLUGIN_DIR . 'assets/js/editor.js';

        $css_version = file_exists($css_path) ? filemtime($css_path) : self::VERSION;
        $js_version = file_exists($js_path) ? filemtime($js_path) : self::VERSION;

        // Enqueue CSS
        wp_enqueue_style(
            'aie-admin-css',
            AIE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $css_version
        );

        // Enqueue JS
        wp_enqueue_script(
            'aie-editor-js',
            AIE_PLUGIN_URL . 'assets/js/editor.js',
            ['jquery'],
            $js_version,
            true
        );

        // Localize script with translations and AJAX data
        wp_localize_script('aie-editor-js', 'AIE_AJAX', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('aif_nonce'),
            'i18n'     => [
                'select_image'       => __('Select an Image to Edit', 'advanced-image-editor'),
                'use_this_image'     => __('Use This Image', 'advanced-image-editor'),
                'saving'             => __('Saving...', 'advanced-image-editor'),
                'processing'         => __('Processing...', 'advanced-image-editor'),
                'no_image'           => __('Please select an image first', 'advanced-image-editor'),
                'save_success'       => __('Image saved successfully!', 'advanced-image-editor'),
                'preview_failed'     => __('Failed to generate preview', 'advanced-image-editor'),
                'network_error'      => __('Network error - please try again', 'advanced-image-editor'),
                'save_failed'        => __('Failed to save image', 'advanced-image-editor'),
                'confirm_save'       => __('Save this edited image to your media library?', 'advanced-image-editor'),
                'view_edited'        => __('Would you like to view the edited image?', 'advanced-image-editor'),
                'unknown_error'      => __('Unknown error occurred', 'advanced-image-editor'),
                'rate_limit_error'   => __('Too many requests. Please wait a moment before trying again.', 'advanced-image-editor'),
                'reset_confirm'      => __('Reset all filters to default values?', 'advanced-image-editor'),
                'save_status_enabled' => __('Ready to save edited image', 'advanced-image-editor'),
                'reset_status_enabled' => __('Ready to reset filters', 'advanced-image-editor'),
            ]
        ]);
    }

    /**
     * Render the editor page
     */
    public function render_editor_page() {
        // Check user capability
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'advanced-image-editor'));
        }

        // Check if Imagick is available
        if (!extension_loaded('imagick') && !class_exists('Imagick')) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php esc_html_e('Missing Required Extension: Imagick', 'advanced-image-editor'); ?></strong>
                </p>
                <p>
                    <?php esc_html_e('The Advanced Image Editor requires the Imagick PHP extension to function properly.', 'advanced-image-editor'); ?>
                </p>

                <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007cba; margin: 15px 0;">
                    <h4 style="margin-top: 0; color: #007cba;">
                        <?php esc_html_e('What is Imagick?', 'advanced-image-editor'); ?>
                    </h4>
                    <p style="margin-bottom: 10px;">
                        <?php esc_html_e('Imagick is a powerful PHP extension that provides advanced image processing capabilities. It\'s based on the ImageMagick library, which has been a cornerstone of professional image manipulation since the early 1990s.', 'advanced-image-editor'); ?>
                    </p>
                    <ul style="margin-bottom: 10px;">
                        <li><?php esc_html_e('Enables high-quality image editing and manipulation', 'advanced-image-editor'); ?></li>
                        <li><?php esc_html_e('Supports all major image formats (JPEG, PNG, GIF, WebP, TIFF, etc.)', 'advanced-image-editor'); ?></li>
                        <li><?php esc_html_e('Provides professional-grade filters and effects', 'advanced-image-editor'); ?></li>
                        <li><?php esc_html_e('Used by thousands of websites and professional applications worldwide', 'advanced-image-editor'); ?></li>
                    </ul>
                </div>

                <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #856404; margin: 15px 0;">
                    <h4 style="margin-top: 0; color: #856404;">
                        <?php esc_html_e('Why is it Required?', 'advanced-image-editor'); ?>
                    </h4>
                    <p style="margin-bottom: 0;">
                        <?php esc_html_e('Without Imagick, the plugin cannot perform essential image operations like contrast adjustment, sharpening, format conversion, or any advanced image processing. This is a server-side requirement that must be enabled by your hosting provider.', 'advanced-image-editor'); ?>
                    </p>
                </div>

                <div style="background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 15px 0;">
                    <h4 style="margin-top: 0; color: #17a2b8;">
                        <?php esc_html_e('How to Enable Imagick', 'advanced-image-editor'); ?>
                    </h4>
                    <ol style="margin-bottom: 10px;">
                        <li><?php esc_html_e('Contact your web hosting provider', 'advanced-image-editor'); ?></li>
                        <li><?php esc_html_e('Request that they enable the Imagick PHP extension', 'advanced-image-editor'); ?></li>
                        <li><?php esc_html_e('Most hosting providers can enable this quickly (usually within hours)', 'advanced-image-editor'); ?></li>
                        <li><?php esc_html_e('If using shared hosting, you may need to upgrade to a VPS or dedicated server', 'advanced-image-editor'); ?></li>
                    </ol>
                    <p style="margin-bottom: 0;">
                        <em><?php esc_html_e('Note: Imagick is extremely common and should be available on most modern hosting platforms.', 'advanced-image-editor'); ?></em>
                    </p>
                </div>

                <div style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; margin: 15px 0; text-align: center;">
                    <h4 style="margin-top: 0; color: #28a745;">
                        <?php esc_html_e('Support Open Source Software', 'advanced-image-editor'); ?>
                    </h4>
                    <p style="margin-bottom: 10px;">
                        <?php esc_html_e('ImageMagick is free, open-source software that powers millions of websites and applications worldwide. Consider supporting their important work:', 'advanced-image-editor'); ?>
                    </p>
                    <p style="margin-bottom: 0;">
                        <a href="https://imagemagick.org/support/#support" target="_blank" rel="noopener noreferrer" class="button button-primary">
                            <?php esc_html_e('Sponsor ImageMagick Development', 'advanced-image-editor'); ?>
                        </a>
                    </p>
                </div>

                <p>
                    <strong><?php esc_html_e('Need Help?', 'advanced-image-editor'); ?></strong>
                    <?php esc_html_e('If you continue to have issues after enabling Imagick, please check our documentation or contact support.', 'advanced-image-editor'); ?>
                </p>
            </div>
            <?php
            return;
        }

        include AIE_PLUGIN_DIR . 'editor-page.php';
    }
}