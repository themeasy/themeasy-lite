=== Themeasy Lite ===

Contributors: themeasy
Tags: elementor, elementor widgets, page builder, widgets, design
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free, beautifully crafted Elementor content widgets and design tools that work on any WordPress theme — no account, no license, no lock-in.

== Description ==

Themeasy Lite is a free toolkit of well-crafted Elementor content widgets and design controls that work on ANY WordPress theme. No account, no license key, no theme lock-in — install it and start building.

Whether you run a blog, a portfolio, or a small business site, Themeasy Lite gives you the content building blocks Elementor leaves out, on a clean, lightweight, performance-minded codebase.

= Free Elementor widgets, Design tools, on any theme =

* A consistent set of design controls across every widget
* Lightweight by design — no animation library, and a widget's scripts load only on pages that use it
* SVG-safe icon handling
* Works alongside your current theme — nothing to migrate

= Optional upgrade =

Themeasy Lite is fully functional on its own; the free widgets above never expire. Three paid plans add more, and each one is optional:

* **Themeasy Widgets:** the Pro content widgets (text, media, elements, blocks, sliders and data) and Themeasy Motion (entrance, hover, text and scroll animations, on the free widgets too), in any theme.
* **Themeasy Pro:** everything in Widgets, plus the header, footer, post, site and mega menu widgets in any theme, the Pro templates of the Themeasy Library, and the Themeasy themes. With a Themeasy theme active, it also adds the theme builder, global sections, the settings panel, the WooCommerce widgets and features (variation swatches and more), and the Contact Form 7 form widget and styling.
* **Themeasy Agency:** everything in Pro, plus white label: your brand in place of Themeasy's in the plugin's admin panel and in the Elementor editor.

== Installation ==

1. Install and activate Elementor. The free version is enough.
2. In your WordPress admin, go to Plugins > Add New Plugin, search for "Themeasy Lite", then click Install Now and Activate. To install from a zip file instead, use Plugins > Add New Plugin > Upload Plugin.
3. Open Themeasy > Getting Started for a short guide, or edit any page with Elementor: the widgets are in the "Themeasy — Essentials" category of the Elements panel.

== Frequently Asked Questions ==

= Do I need a Themeasy theme to use this? =

No. Themeasy Lite works on any WordPress theme. The free widgets and design tools have no theme dependency and no license requirement. Of the paid plans, only some Themeasy Pro features need a Themeasy theme: the theme builder, global sections, the settings panel, the WooCommerce features and the Contact Form 7 styling.

= What do the paid plans add? =

Free includes the core Elementor content widgets plus the shared design controls — everything you need to design pages on any theme. Themeasy Widgets adds the Pro content widgets and Themeasy Motion, in any theme. Themeasy Pro adds the site toolkit on top: more widgets, the Pro templates, the Themeasy themes and, with a Themeasy theme, the theme builder, global sections, WooCommerce and Contact Form 7. Themeasy Agency adds white label to Pro. "Optional upgrade" above has the details.

= Does it require Elementor? =

Yes. The widgets are built for Elementor — the free Elementor plugin is enough.

= Will it slow down my site? =

No. Themeasy Lite ships no animation library, and a widget's scripts are enqueued only on pages where that widget is actually used. A small shared stylesheet loads site-wide.

== Screenshots ==

1. Editing the Section Intro widget: an eyebrow with an icon, a title with accent words, a description and a button.
2. The Pricing Table widget, with a monthly/yearly price toggle and a check or a cross for each feature.
3. The free widgets in the Themeasy — Essentials category of the Elementor editor.

== External services ==

Themeasy Lite uses the services below, and each one only after an action by a site administrator (the Themeasy Library and Freemius) or by a visitor (the video services).

Themeasy Library (Themeasy): the Elementor editor gets a Themeasy button that opens a library of ready-made templates. Nothing is sent until you click it. While the library is open, your site's server requests the catalog from cms.themeasy.co: the categories and tags, the list of templates with the category, tag and search words you choose, and, when you insert a template, its content. WordPress adds your site's address to each request (its standard User-Agent header). Your browser loads the template preview images from the addresses the catalog returns, and an inserted template may use images hosted on cms.themeasy.co until you replace them. Terms of service: https://themeasy.co/terms - Privacy policy: https://themeasy.co/privacy

Freemius: Themeasy Lite includes the Freemius SDK, which handles the licenses of the paid Themeasy plans. It runs in anonymous mode: it sends nothing on its own, and the plugin works the same whether you ever connect it or not. It contacts api.freemius.com in two cases only. (1) Opt in: the plugin's row on the Plugins screen has an "Opt In" link. If you opt in, the SDK sends your WordPress user's name and email address, the site's address, title and language, the WordPress and PHP versions, the plugin's version and state (active, deactivated or uninstalled) and, unless you turn it off on the opt-in screen, the names and versions of your plugins and themes. It then keeps that data up to date about once a day, until you click "Opt Out" in the same place. (2) Deactivation feedback: when you deactivate the plugin, an optional form asks why. If you answer, the answer is sent when you delete the plugin, with an anonymous ID instead of your site's details. If you untick the form's "Anonymous feedback" box, your answer opts the site in, as in (1). Terms of service: https://freemius.com/terms/ - Privacy policy: https://freemius.com/privacy/

YouTube (Google): with a YouTube source and no custom poster image, the Video widget makes the visitor's browser load the video thumbnail from i.ytimg.com when the page loads. When the visitor presses play, the video loads from youtube-nocookie.com (YouTube's privacy-enhanced mode). When a visitor opens a YouTube video in the lightbox (Button or Video widget), the browser loads YouTube's player script from www.youtube.com and plays the video from youtube-nocookie.com. These requests send the video ID and the visitor's IP address and browser details to Google. Terms of service: https://www.youtube.com/t/terms - Privacy policy: https://policies.google.com/privacy

noembed: when a visitor opens a YouTube video in the lightbox, the lightbox's video player (Plyr) asks noembed.com, an independent oEmbed service, for the video's title. The request sends the video's YouTube address and the visitor's IP address and browser details to noembed.com. noembed.com publishes no terms of service or privacy policy; the service and its source code: https://noembed.com - https://github.com/leedo/noembed

Vimeo: with a Vimeo source, the Video widget loads the player from player.vimeo.com only after the visitor presses play, with Do Not Track enabled. When a visitor opens a Vimeo video in the lightbox (Button or Video widget), the player and its script load from player.vimeo.com. These requests send the video ID and the visitor's IP address and browser details to Vimeo. Terms of service: https://vimeo.com/terms - Privacy policy: https://vimeo.com/privacy

== Source code ==

The plugin's own JavaScript and CSS files are minified, and the first line of each one names its readable source. That source is public at https://github.com/themeasy/themeasy-lite, with the same paths and file names and one tag per released version. JavaScript is minified with esbuild and CSS with Lightning CSS; nothing else is compiled.

assets/css/vendor/bootstrap-scoped.min.css is generated from the bundled assets/css/vendor/bootstrap.min.css by tools/bootstrap-scoped.mjs in that repository (run it with Node.js: node tools/bootstrap-scoped.mjs).

== Copyright ==

Themeasy Lite WordPress Plugin, (C) Themeasy.
Themeasy Lite is distributed under the terms of the GNU GPL v2 or later.

Themeasy Lite bundles Feather Icons, (C) Cole Bemis
(full notices: assets/media/svg/CREDITS.txt).
License: MIT — https://github.com/feathericons/feather/blob/main/LICENSE
Source: https://feathericons.com — https://github.com/feathericons/feather
Modifications: glyphs added/renamed; normalized to 24x24 currentColor strokes.

Themeasy Lite also bundles these libraries, all under the MIT license:
Bootstrap, (C) The Bootstrap Authors — https://getbootstrap.com
GLightbox, (C) Biati Digital — https://github.com/biati-digital/glightbox
Plyr, (C) Sam Potts — https://github.com/sampotts/plyr

== Changelog ==

= 1.0.1 =
* Stability and efficiency improvements following a comprehensive code audit.

The full version history is in changelog.txt.

== Upgrade Notice ==

= 1.0.1 =
Stability and efficiency improvements following a comprehensive code audit.
