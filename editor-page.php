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
    </div>
</div>