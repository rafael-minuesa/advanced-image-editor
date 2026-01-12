<?php
/**
 * Plugin Name: Advanced Image Editor
 * Description: Professional image editing tool with advanced filters, contrast adjustment, and unsharp masking.
 * Version: 2.1
 * Author: Rafael Minuesa
 * Text Domain: advanced-image-editor
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIE_VERSION', '2.1');
define('AIE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once AIE_PLUGIN_DIR . 'includes/class-advanced-image-editor.php';
require_once AIE_PLUGIN_DIR . 'includes/class-aie-ajax-handler.php';
require_once AIE_PLUGIN_DIR . 'includes/aie-functions.php';

// Initialize the plugin
new Advanced_Image_Editor();
