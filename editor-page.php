<?php
/**
 * Advanced Image Editor Page
 *
 * @package AdvancedImageEditor
 * @author Rafael Minuesa
 * @license GPL-2.0+
 * @link https://github.com/rafael-minuesa/advanced-image-editor
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Advanced Image Editor', 'advanced-image-editor'); ?></h1>
    
    <div class="aie-container">
        
        <!-- Image Selection Section -->
        <div class="aie-section">
            <h2><?php esc_html_e('1. Select Image', 'advanced-image-editor'); ?></h2>
            <p><?php esc_html_e('Choose an image from your media library to edit.', 'advanced-image-editor'); ?></p>

            <button id="aie-select-image" class="aie-button button-primary" aria-describedby="aie-select-help">
                <?php esc_html_e('Select Image', 'advanced-image-editor'); ?>
            </button>
            <div id="aie-select-help" class="screen-reader-text">
                <?php esc_html_e('Opens WordPress media library to select an image for editing', 'advanced-image-editor'); ?>
            </div>

            <div id="aie-selected-image" style="display: none;" class="aie-selected-info" role="status" aria-live="polite">
                <p>
                    <strong><?php esc_html_e('Selected Image:', 'advanced-image-editor'); ?></strong>
                    <span id="aie-image-title"></span>
                </p>
                <input type="hidden" id="aie-image-id" value="" aria-label="<?php esc_attr_e('Selected image ID', 'advanced-image-editor'); ?>">
            </div>
        </div>
        
        <!-- Editor Section -->
        <div id="aie-editor" class="aie-section aie-editor-section" style="display: none;" role="region" aria-labelledby="editor-heading">
            <h2 id="editor-heading"><?php esc_html_e('2. Adjust Filters', 'advanced-image-editor'); ?></h2>

            <div class="aie-controls" role="group" aria-labelledby="controls-heading">
                <h3 id="controls-heading" class="screen-reader-text"><?php esc_html_e('Image Filter Controls', 'advanced-image-editor'); ?></h3>

                <!-- Contrast Control -->
                <div class="aie-control-group">
                    <label for="aie-contrast">
                        <?php esc_html_e('Contrast:', 'advanced-image-editor'); ?>
                        <span id="aie-contrast-value" aria-live="polite">0.5</span>
                    </label>
                    <input type="range" id="aie-contrast" min="0" max="1" step="0.01" value="0.5"
                           aria-describedby="contrast-help" aria-valuemin="0" aria-valuemax="1" aria-valuenow="0.5">
                    <div id="contrast-help" class="aie-help-text">
                        <small><?php esc_html_e('Adjust image contrast (-100 to 100)', 'advanced-image-editor'); ?></small>
                    </div>
                </div>

                <!-- Amount Control -->
                <div class="aie-control-group">
                    <label for="aie-amount">
                        <?php esc_html_e('Sharpness Amount:', 'advanced-image-editor'); ?>
                        <span id="aie-amount-value" aria-live="polite">0.5</span>
                    </label>
                    <input type="range" id="aie-amount" min="0" max="5" step="0.1" value="0.5"
                           aria-describedby="amount-help" aria-valuemin="0" aria-valuemax="5" aria-valuenow="0.5">
                    <div id="amount-help" class="aie-help-text">
                        <small><?php esc_html_e('Amount of sharpening to apply (0-5)', 'advanced-image-editor'); ?></small>
                    </div>
                </div>

                <!-- Radius Control -->
                <div class="aie-control-group">
                    <label for="aie-radius">
                        <?php esc_html_e('Sharpness Radius:', 'advanced-image-editor'); ?>
                        <span id="aie-radius-value" aria-live="polite">1.0</span>
                    </label>
                    <input type="range" id="aie-radius" min="0" max="10" step="0.1" value="1"
                           aria-describedby="radius-help" aria-valuemin="0" aria-valuemax="10" aria-valuenow="1">
                    <div id="radius-help" class="aie-help-text">
                        <small><?php esc_html_e('Radius of sharpening effect (0-10 pixels)', 'advanced-image-editor'); ?></small>
                    </div>
                </div>

                <!-- Threshold Control -->
                <div class="aie-control-group">
                    <label for="aie-threshold">
                        <?php esc_html_e('Sharpness Threshold:', 'advanced-image-editor'); ?>
                        <span id="aie-threshold-value" aria-live="polite">0</span>
                    </label>
                    <input type="range" id="aie-threshold" min="0" max="1" step="0.01" value="0"
                           aria-describedby="threshold-help" aria-valuemin="0" aria-valuemax="1" aria-valuenow="0">
                    <div id="threshold-help" class="aie-help-text">
                        <small><?php esc_html_e('Threshold for sharpening (0-1)', 'advanced-image-editor'); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Preview Section -->
            <div class="aie-preview-container" role="region" aria-labelledby="preview-heading">
                <h3 id="preview-heading"><?php esc_html_e('Preview', 'advanced-image-editor'); ?></h3>
                <img id="aie-preview" src="" alt="<?php esc_attr_e('Preview of edited image', 'advanced-image-editor'); ?>" style="max-width: 100%; height: auto; display: none;" role="img">
                <p id="aie-no-preview" style="color: #666; font-style: italic;" role="status" aria-live="polite">
                    <?php esc_html_e('Preview will appear here after selecting an image', 'advanced-image-editor'); ?>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="aie-button-group" role="group" aria-label="<?php esc_attr_e('Image editing actions', 'advanced-image-editor'); ?>">
                <button id="aie-save" class="aie-button aie-button-success" disabled aria-describedby="save-status">
                    <?php esc_html_e('Save Edited Image', 'advanced-image-editor'); ?>
                </button>
                <div id="save-status" class="screen-reader-text">
                    <?php esc_html_e('Save button is disabled until an image is selected and edited', 'advanced-image-editor'); ?>
                </div>

                <button id="aie-reset" class="aie-button aie-button-secondary" disabled aria-describedby="reset-status">
                    <?php esc_html_e('Reset to Defaults', 'advanced-image-editor'); ?>
                </button>
                <div id="reset-status" class="screen-reader-text">
                    <?php esc_html_e('Reset button is disabled until an image is selected', 'advanced-image-editor'); ?>
                </div>
            </div>
        </div>
        
          <!-- Help Section -->
         <div class="aie-section">
             <h2><?php esc_html_e('How to Use', 'advanced-image-editor'); ?></h2>
             <ol>
                 <li><?php esc_html_e('Click "Select Image" to choose an image from your media library', 'advanced-image-editor'); ?></li>
                 <li><?php esc_html_e('Adjust the sliders to apply filters in real-time', 'advanced-image-editor'); ?></li>
                 <li><?php esc_html_e('Click "Save Edited Image" to save a copy to your media library', 'advanced-image-editor'); ?></li>
             </ol>

             <h3><?php esc_html_e('Filter Explanations', 'advanced-image-editor'); ?></h3>
             <ul>
                 <li><strong><?php esc_html_e('Contrast:', 'advanced-image-editor'); ?></strong> <?php esc_html_e('Adjusts the difference between light and dark areas', 'advanced-image-editor'); ?></li>
                 <li><strong><?php esc_html_e('Sharpness Amount:', 'advanced-image-editor'); ?></strong> <?php esc_html_e('Controls the intensity of sharpening', 'advanced-image-editor'); ?></li>
                 <li><strong><?php esc_html_e('Sharpness Radius:', 'advanced-image-editor'); ?></strong> <?php esc_html_e('Determines how far the sharpening effect spreads', 'advanced-image-editor'); ?></li>
                 <li><strong><?php esc_html_e('Sharpness Threshold:', 'advanced-image-editor'); ?></strong> <?php esc_html_e('Sets the minimum contrast level for sharpening to apply', 'advanced-image-editor'); ?></li>
             </ul>
         </div>

         <!-- Premium Features Section -->
         <div class="aie-section aie-premium-section" style="border: 2px solid #007cba; background: #f8f9fa;">
             <h2 style="color: #007cba;">
                 <span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
                 <?php esc_html_e('Advanced Image Editor Pro', 'advanced-image-editor'); ?>
             </h2>
             <p><?php esc_html_e('Unlock professional-grade image editing features:', 'advanced-image-editor'); ?></p>

             <div class="aie-premium-features" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
                 <div class="aie-premium-feature" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                     <h4 style="margin: 0 0 10px 0; color: #007cba;">
                         <span class="dashicons dashicons-images-alt2" aria-hidden="true"></span>
                         <?php esc_html_e('Batch Processing', 'advanced-image-editor'); ?>
                     </h4>
                     <p style="margin: 0; font-size: 14px;"><?php esc_html_e('Process multiple images simultaneously. Apply filters to 10-100+ images at once with progress tracking.', 'advanced-image-editor'); ?></p>
                 </div>

                 <div class="aie-premium-feature" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                     <h4 style="margin: 0 0 10px 0; color: #007cba;">
                         <span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span>
                         <?php esc_html_e('Advanced Filters', 'advanced-image-editor'); ?>
                     </h4>
                     <p style="margin: 0; font-size: 14px;"><?php esc_html_e('Sepia, vignette, duotone, curves, levels, and professional color correction tools.', 'advanced-image-editor'); ?></p>
                 </div>

                 <div class="aie-premium-feature" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                     <h4 style="margin: 0 0 10px 0; color: #007cba;">
                         <span class="dashicons dashicons-format-image" aria-hidden="true"></span>
                         <?php esc_html_e('Watermarking', 'advanced-image-editor'); ?>
                     </h4>
                     <p style="margin: 0; font-size: 14px;"><?php esc_html_e('Add text or image watermarks with custom positioning, fonts, and transparency.', 'advanced-image-editor'); ?></p>
                 </div>

                 <div class="aie-premium-feature" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                     <h4 style="margin: 0 0 10px 0; color: #007cba;">
                         <span class="dashicons dashicons-download" aria-hidden="true"></span>
                         <?php esc_html_e('Bulk Export', 'advanced-image-editor'); ?>
                     </h4>
                     <p style="margin: 0; font-size: 14px;"><?php esc_html_e('Export multiple images as ZIP with format conversion and size optimization.', 'advanced-image-editor'); ?></p>
                 </div>

                 <div class="aie-premium-feature" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                     <h4 style="margin: 0 0 10px 0; color: #007cba;">
                         <span class="dashicons dashicons-cloud" aria-hidden="true"></span>
                         <?php esc_html_e('Cloud Processing', 'advanced-image-editor'); ?>
                     </h4>
                     <p style="margin: 0; font-size: 14px;"><?php esc_html_e('Unlimited processing power for large images and complex operations.', 'advanced-image-editor'); ?></p>
                 </div>

                 <div class="aie-premium-feature" style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ddd;">
                     <h4 style="margin: 0 0 10px 0; color: #007cba;">
                         <span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
                         <?php esc_html_e('Filter Presets', 'advanced-image-editor'); ?>
                     </h4>
                     <p style="margin: 0; font-size: 14px;"><?php esc_html_e('Save and reuse filter combinations. Create custom presets for consistent editing.', 'advanced-image-editor'); ?></p>
                 </div>
             </div>

             <div class="aie-premium-cta" style="text-align: center; margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border-radius: 8px;">
                 <h3 style="margin: 0 0 10px 0; color: white;"><?php esc_html_e('Upgrade to Pro Today!', 'advanced-image-editor'); ?></h3>
                 <p style="margin: 0 0 20px 0; font-size: 16px;"><?php esc_html_e('Get all premium features for just $49/year', 'advanced-image-editor'); ?></p>
                 <a href="https://your-website.com/advanced-image-editor-pro" target="_blank" class="button button-primary button-hero" style="background: white; color: #007cba; border: none; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                     <?php esc_html_e('Learn More & Upgrade', 'advanced-image-editor'); ?>
                 </a>
             </div>

             <div class="aie-opensource-support" style="text-align: center; margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border-radius: 8px;">
                 <h4 style="margin: 0 0 8px 0; color: white; font-size: 16px;">
                     <span class="dashicons dashicons-heart" aria-hidden="true"></span>
                     <?php esc_html_e('Supporting Open Source', 'advanced-image-editor'); ?>
                 </h4>
                 <p style="margin: 0; font-size: 14px; line-height: 1.4;">
                     <?php esc_html_e('When you purchase Advanced Image Editor Pro, 25% of proceeds are donated to support the ImageMagick project, helping sustain the free software that powers professional image processing worldwide.', 'advanced-image-editor'); ?>
                 </p>
             </div>

             <div class="aie-premium-disclaimer" style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; font-size: 13px; color: #856404;">
                 <strong><?php esc_html_e('Note:', 'advanced-image-editor'); ?></strong>
                 <?php esc_html_e('Premium features are available as a separate plugin. This free version includes all core editing functionality.', 'advanced-image-editor'); ?>
             </div>
         </div>
    </div>
</div>