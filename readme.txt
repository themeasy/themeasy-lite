=== Themeasy Lite ===

Contributors: themeasy
Tags: elementor, elementor widgets, page builder, widgets, design
Requires at least: 5.8
Tested up to: 7.0
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

Themeasy Lite is fully functional on its own; the free widgets above never expire. If you want more, the premium Themeasy plugin and the Themeasy themes add Themeasy Motion (entrance, hover, text and scroll animations), header, footer, and site-builder widgets, a WooCommerce widget set with variation swatches, global sections, Contact Form 7 styling, and white-label tools. The upgrade is entirely optional.

== Frequently Asked Questions ==

= Do I need a Themeasy theme to use this? =

No. Themeasy Lite works on any WordPress theme. The free widgets and design tools have no theme dependency and no license requirement.

= What is the difference between Free and Pro? =

Free includes the core Elementor content widgets plus the shared design controls — everything you need to design pages on any theme. Pro (the premium Themeasy plugin, unlocked by a Themeasy theme or a plugin license) adds Themeasy Motion (entrance, hover, text and scroll animations), header, footer, and site widgets, a WooCommerce widget set with variation swatches, global sections, Contact Form 7 styling, and white-label tools.

= Does it require Elementor? =

Yes. The widgets are built for Elementor — the free Elementor plugin is enough.

= Will it slow down my site? =

No. Themeasy Lite ships no animation library, and a widget's scripts are enqueued only on pages where that widget is actually used. A small shared stylesheet loads site-wide.

== Screenshots ==

1. The Themeasy Lite widget category in the Elementor editor.
2. Building a page with the free content widgets.
3. Per-widget design controls.

== External services ==

Themeasy Lite contacts a third-party service only when a page uses the Video widget with a YouTube or Vimeo source, or opens such a video in the lightbox (Button or Video widget).

YouTube (Google): with a YouTube source and no custom poster image, the visitor's browser loads the video thumbnail from i.ytimg.com when the page loads. When the visitor presses play, the video loads from youtube-nocookie.com (YouTube's privacy-enhanced mode). These requests send the video ID and the visitor's IP address and browser details to Google. Terms of service: https://www.youtube.com/t/terms - Privacy policy: https://policies.google.com/privacy

Vimeo: with a Vimeo source, the player loads from player.vimeo.com only after the visitor presses play, with Do Not Track enabled. The request sends the video ID and the visitor's IP address and browser details to Vimeo. Terms of service: https://vimeo.com/terms - Privacy policy: https://vimeo.com/privacy

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

See changelog.txt for the full version history.

== Upgrade Notice ==

= 1.0.1 =
Stability and efficiency improvements following a comprehensive code audit.
