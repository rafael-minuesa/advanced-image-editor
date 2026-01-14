# Advanced Image Editor for WordPress

![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)
![Version](https://img.shields.io/badge/version-2.1-green.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-0073aa.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-96588a.svg)

== Description ==

A professional image editing plugin for WordPress featuring advanced filters, real-time previews, and seamless media library integration. 

Apply contrast adjustments, unsharp masking, and other professional-grade image enhancements directly within your WordPress admin.

## Features
- **Real-time Preview**: See filter changes instantly as you adjust sliders
- **Professional Filters**: Advanced contrast adjustment and unsharp masking
- **Accessibility**: Full keyboard navigation and screen reader support
- **Security**: Rate limiting, input validation, and secure file handling
- **Performance**: Optimized image processing with memory management
- **Media Library Integration**: Seamless WordPress media library workflow
- **Responsive Design**: Works perfectly on all screen sizes

## Installation
1. Upload the `advanced-image-editor` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Media → Advanced Image Editor to start editing

## Requirements
- PHP 7.4 or higher
- WordPress 5.6 or higher
- Imagick PHP extension

## Usage
1. Navigate to Media → Advanced Image Editor
2. Click "Select Image" to choose an image from your media library
3. Adjust the sliders to apply filters in real-time
4. Click "Save Edited Image" to save a copy to your media library

## Filters Available
- **Contrast**: Adjusts the difference between light and dark areas
- **Sharpness Amount**: Controls the intensity of sharpening
- **Sharpness Radius**: Determines how far the sharpening effect spreads
- **Sharpness Threshold**: Sets the minimum contrast level for sharpening

## Support
For support, feature requests, or bug reports, please contact the plugin author.

## Changelog

### 2.1
- **Major Refactoring**: Complete plugin rename from "Advanced Image Filters" to "Advanced Image Editor"
- **Enhanced Security**: Rate limiting, comprehensive input validation, capability checks
- **Accessibility**: Full ARIA support, keyboard navigation, screen reader compatibility
- **Performance**: Memory validation, dimension limits, optimized resource management
- **User Experience**: Enhanced loading states, progress indicators, better error messages
- **Code Quality**: PHPDoc documentation, structured logging, WordPress standards compliance
- **Internationalization**: Complete i18n support with all strings translatable

### 2.0
- Initial release with basic image editing functionality
