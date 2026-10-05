=== BunnyCDN Video Widget for Elementor ===
Contributors: gabrielmafra
Tags: bunnycdn, elementor, video, secure video, video hosting
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed secure BunnyCDN Stream videos in Elementor with signed, expiring URLs. Responsive, styleable and privacy-friendly.

== Description ==

This plugin adds a custom Elementor widget that embeds BunnyCDN Stream videos using token authentication. Every page view gets a signed URL that expires, so copied embed links stop working. Perfect for gated or paid content such as online courses.

Features:

* Add BunnyCDN videos by Video ID
* Library ID set once in Settings, with an optional per-widget override
* Supports autoplay, mute, loop, preload
* Token expiration time configuration (default: 180 minutes)
* Token key stored server-side and never printed back in the admin
* Responsive layout with 16:9, 4:3 and 1:1 aspect ratios
* Style the player wrapper (background, border, per-corner radius) from the Elementor editor

**Important**: You need an active BunnyCDN Stream library with token authentication enabled.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` (or upload the release .zip under Plugins > Add New)
2. Activate the plugin through the 'Plugins' menu in WordPress (Elementor must be active)
3. Go to **Settings > BunnyCDN** and enter your Library ID and token key (UUID format)
4. In the Elementor editor, search for the **BunnyCDN Video** widget
5. Drag the widget into your layout and enter the BunnyCDN video ID
6. Configure autoplay, mute, loop, token expiration and styling options

== Frequently Asked Questions ==

= Where do I find my Library ID and token key? =

In your BunnyCDN dashboard, open Stream and your video library. The Library ID is listed on the library's API page. The token key is in the library's Security settings, under embed token authentication.

= Is this plugin compatible with the BunnyCDN modern player? =

Currently, the plugin uses the iframe embed player for broader compatibility. Modern player support may be added in the future.

= Can I use this plugin with other page builders? =

No. This widget is built specifically for **Elementor**.

== Privacy ==

This plugin does not collect or store any personal data on its own. It dynamically generates signed URLs to load videos from Bunny.net using a token you provide.

When a video is loaded via Bunny.net's stream delivery, visitor metadata such as IP address, browser type, and playback activity may be processed by Bunny.net's infrastructure.

We recommend reviewing the Bunny.net privacy policy and including it in your site's own privacy statement:
https://bunny.net/privacy

== Screenshots ==

1. Widget settings inside the Elementor editor
2. Style controls: border, per-corner radius and aspect ratio
3. Library ID and token key settings screen in WordPress admin

== Changelog ==

= 1.6.0 =
* Library ID is now a setting (Settings > BunnyCDN) with an optional per-widget override, instead of being hard-coded
* Token key field is masked and never printed back; leave it empty to keep the saved key
* Setup messages are only shown to editors, visitors never see them
* Base player styles are printed once per page instead of once per video
* Fixed the token expiration fallback (180 minutes, matching the default)
* "Settings" link on the Plugins screen and a notice when Elementor is not active
* Translation-ready (text domain: bunnycdn-video-widget)

= 1.57 =
* Added support for individual border radius (top, right, bottom, left)
* Replaced single radius slider with Dimensions control

= 1.56 =
* Added validation for UUID-format token key
* Improved error handling for invalid token input
* Cleaned up duplicate admin notices
* Default token expiration set to 180 minutes

= 1.0 =
* Initial release with secure BunnyCDN token generation
* Elementor widget with basic autoplay/mute/loop options

== Upgrade Notice ==

= 1.6.0 =
After updating, enter your Library ID under Settings > BunnyCDN. Videos will not load until it is set.
