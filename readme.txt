=== Video Gallery for WooCommerce ===
Contributors: martinvalchev
Donate link: https://linktr.ee/martinvalchev
Tags: video gallery, video player, product video, for woocommerce, product page
Requires at least: 5.3
Tested up to: 6.7.2
Requires PHP: 7.4
Stable tag: 1.38
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Video Gallery: Add WordPress library videos to product pages with customization. Requires WooCommerce activation.

== Description ==

Introducing the Video Gallery for WooCommerce plugin - the perfect solution for businesses looking to enhance their product pages with visually engaging content. With this powerful plugin, adding video files from the WP library is a breeze, and they will be beautifully showcased on the product page. The plugin comes packed with a variety of additional options to help customize the display of videos on your site, including the ability to move videos around on the page to your desired position. You can also change the video file icon and color to match your brand's aesthetic. Video clip settings are fully customizable, giving you control over autoplay, sound, and loop options, as well as the ability to show or hide video control options. Please note that in order to use this plugin, the Woocommerce plugin must be activated. Upgrade your product pages today with Video Gallery for WooCommerce!

The options for this plugin include:

*   Add video files from the WP library to your product pages
*   Move videos around on the product page to your desired position
*   Customize the video file icon and color
*   Choose autoplay, sound, and loop options for video clips
*   Show or hide video control options for a more personalized playback experience
*   Enhance user engagement on your product pages with visually engaging video content
*   Customize your video gallery to match your brand's aesthetic
*   Manage your media library with ease
*   Increase conversions by showcasing your products with multimedia content
*   Compatible with Woocommerce plugin to ensure seamless integration with your online store

The helper libraries plugin uses the following:

*   [FontAwesome v6.6.0](https://fontawesome.com/)
*   [SweetAlert2 v11.4.8](https://sweetalert2.github.io/)
*   [VideoJS v7.15.4](https://videojs.com/)

Notes:

*   Video Gallery for WooCommerce requires the Woocommerce plugin to be activated in order to function properly. If you do not have Woocommerce installed, you will need to install and activate it before using Video Gallery for WooCommerce.
*   The plugin supports a wide range of video file formats, but it is important to ensure that your videos are in a format that is supported by WordPress. Commonly used formats such as MP4 and MOV are typically supported.
*   The plugin works with WooCommerce base elements. Added support for someone on known themes. If you have a problem with a theme, write to support to check if it can be made compatible.

== Installation ==

To install this plugin:

1. Install the plugin through the WordPress admin interface, or upload the plugin folder to /wp-content/plugins/ using ftp.
2. Activate the plugin through the 'Plugins' screen in WordPress. On a Multisite you can either network activate it or let users activate it individually.
3. Go to WordPress Admin > WooCommerce > Settings > Video Gallery for WooCommerce

== Frequently Asked Questions ==

= What is Video Gallery for WooCommerce? =
Video Gallery for WooCommerce is a plugin that allows you to add video files from your WordPress (WP) library to your product pages on your website. It comes with a range of customization options to enhance your video display and improve user engagement.
= What types of video files can I use with Video Gallery for WooCommerce? =
You can use any video file format that is supported by WordPress. This includes popular formats such as MP4 and MOV.
= Can I customize the display of my video files? =
Yes, Video Gallery for WooCommerce comes with a variety of customization options, such as the ability to change the video file icon and color, and move videos around on the product page to your desired position.
= Can I control how my video clips play? =
Yes, you can choose from a range of video clip settings, such as autoplay, sound, and loop options, as well as the ability to show or hide video control options.
= Is Video Gallery for WooCommerce compatible with other WordPress themes and plugins? =
Video Gallery for WooCommerce is compatible with most WordPress themes and plugins, although some may require additional customization. It is designed to seamlessly integrate with Woocommerce, ensuring a smooth user experience.

== Screenshots ==

1. Settings
2. Product settings
3. Product pages

== Changelog ==

= 1.38 =
* **Improved:** Enhanced CSS styling for video thumbnails to ensure consistent sizing for Flatsome theme
* **Fixed:** Vertical gallery layout for Flatsome theme
* **Added:** Better support for video thumbnails in product galleries for Flatsome theme
* **Improved:** Code optimization for better performance
**Release date: March 12, 2025**

= 1.37 =
* **Fix:** Fatal error when product object is not properly initialized by page builders or themes
* **Added:** Validation checks for product object to ensure compatibility with various page builders
**Release date: March 10, 2025**

= 1.36 =
* **Fix:** Issue with Default Form Values in variable products
* **Added:** Better support for Flatsome theme for variable products
* **Improved:** Code optimization and improvements
**Release date: February 17, 2025**

= 1.35 =
* **Added:** Explanatory information for two settings: "Show video first" and "Adjust video size"
* **Updated:** WooCommerce template files to ensure compatibility
* Tested with WordPress 6.7.2
**Release date: February 12, 2025**

= 1.34 =
* **Added:** New donate link
* **Removed:** Unused file from FontAwesome v6.6.0
* Tested with WordPress 6.7
**Release date: November 19, 2024**

= 1.33 =
* **Fix:** Issue with "srcset" attribute
**Release date: November 8, 2024**

= 1.32 =
* **Fix:** Security issue
* **Update:** FontAwesome v5 to FontAwesome v6.6.0
**Release date: November 5, 2024**

= 1.31 =
* **Fix:** Fatal Error with admin notices
* Tested with WordPress 6.6.1
**Release date: July 26, 2024**

= 1.30 =
* **Added:** Admin menu for Video Gallery for WooCommerce
* **Added:** Required plugins the new option of WordPress 6.5
* Tested with WordPress 6.5
**Release date: April 3, 2024**

= 1.29 =
* Fix: If have problem with first loading classes

= 1.28 =
* Fix: Small Bugs
* Added: VideoObject schema all required properties

= 1.27 =
* Fix: When use option show first - and product is variable with set default variable value
* Added: VideoObject schema add "uploadDate"

= 1.26 =
* Fix: When use option show first - fix issue with variable product with image (basic support for Flatsome theme)

= 1.25 =
* Fix: When use option show first - fix issue with variable product with image

= 1.24 =
* Added: New function in settings "Adjust the video size according to the theme settings"
* Fix: Security fixes

= 1.23 =
* Fix: Visual fix

= 1.22 =
* Added: VideoObject schema for better SEO
* Fix: Video container on product page

= 1.21 =
* Fix: Height inheritance script

= 1.20 =
* Added: Image resizer for video thumbnails. Resize to sizes WooCommerce: "woocommerce_gallery_thumbnail_url" and "woocommerce_thumbnail"
* Added: In the function for deleting unused thumbnails - add delete additional generated sizes
* Added: Confirmation popup when deleting unused thumbnails
* Fix: Uniformity of video thumbnails (this fixes design issues for different themes)
* Tested: New functionality for Flatsome theme

= 1.19 =
* Fix: If the initialize event of all galleries on the page is missing

= 1.18 =
* Added basic support for Flatsome theme
* Stop function for Feedback when deactivate plugin

= 1.17 =
* Fixed: Clicking on the video icon for mobile devices

= 1.16 =
* Optimization of video preload
* Optimization of video events for Safari browser

= 1.15 =
* Fix: PhotoSwipe option if theme not support product gallery zoom

= 1.14 =
* Added new setting to show videos first
* Fix: Images and Videos click event
* Security fixes

= 1.13 =
* Fix: Autoplay function for Safari browser
* Added Function if mute is off automatically turn off auto play
* Fix: When have video element disabled click event to prevent open in popup

= 1.12 =
* Fix video loading for Safari browser
* Fix problematic loading of hidden items
* Changed the way of visualizing video in the front page of the product, it is now visualized with VideoJS, which will improve the user experience
* Stop support AVI video formats

= 1.11 =
* Fix: Multiple generated sections from WP Bakery with gallery elements

= 1.10 =
* Fix: Many elements coming from using different page builders - optimized loop

= 1.9 =
* Fix: Many elements coming from using different page builders

= 1.8 =
* Fix: If not use default uploads folder

= 1.7 =
* Optimization function add icons on product page

= 1.6 =
* Changed version SweetAlert2 to v11.4.8

= 1.5 =
* Return function check if theme contains any mediaelement-migrate-js and deregister script. Prevent broke design and conflict with some plugins
* Changed version SweetAlert to v11.7.10

= 1.4 =
* Fixed generate thumbnails
* Fixed edit product with Beaver Builder – WordPress Page Builder
* Remove check if theme contains any mediaelementJS import

= 1.3 =
* Change thumbnails not use e base64-encoded image, thumbnails saved in uploads folder
* Added a function in the settings that finds generated thumbnails that are not used and can be deleted so they don't take up space

= 1.2 =
* Added check if your theme contains any mediaelementJS import will be disabled to prevent conflicts and use default library which is in WordPress core
* Fixes in product page if style is vertical media gallery

= 1.1 =
* Visual fixes.
* Feedback fix.

= 1.0 =
* First release of the plugin.

== Upgrade Notice ==

= 1.20 =
* To generate new thumbnail sizes you will need to in the product edit delete the video and add it again add the video to generate new thumbnails and save

= 1.14 =
* Added new setting to show videos first
* Fix: Images and Videos click event
* Security fixes

= 1.13 =
* Fix: Autoplay function for Safari browser
* Added Function if mute is off automatically turn off auto play
* Fix: When have video element disabled click event to prevent open in popup

= 1.12 =
* Fix video loading for Safari browser
* Fix problematic loading of hidden items
* Changed the way of visualizing video in the front page of the product, it is now visualized with VideoJS, which will improve the user experience
* Stop support AVI video formats

= 1.3 =
* Change thumbnails not use e base64-encoded image, thumbnails saved in uploads folder
* Added a function in the settings that finds generated thumbnails that are not used and can be deleted so they don't take up space

= 1.0 =
* First release of the plugin.

