=== Hay.chat ===
Contributors: rgrjnr
Tags: chat, ai, chatbot, customer support, live chat
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add the Hay.chat AI chat widget to your WordPress website with a simple plugin.

== Description ==

Hay.chat adds an AI-powered chat widget to your WordPress site. Connect it to your Hay organization and your visitors can chat with your AI agent directly from your website.

**Features:**

* Simple setup - just enter your Organization ID
* Customize widget position (left or right)
* Choose from multiple theme colors
* Custom greeting messages and branding
* Enable/disable with one click
* Hide the widget on the cart, checkout or account pages, or on any path you list
* One-click WooCommerce connect, so the agent can look up orders and stock
* Follows your consent plugin through the WP Consent API (Complianz, CookieYes and others)

== Installation ==

1. Upload the `haychat` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Hay.chat menu in the admin sidebar
4. Enter your Organization ID (copy it from https://eu.hay.chat/settings/api-tokens)
5. Save changes - the chat widget will appear on your site

== External services ==

This plugin connects to the Hay.chat service (https://hay.chat) to provide the chat widget. It is not a standalone chat system.

* On every public page where the widget is enabled, the visitor's browser loads `widget.js` from the Hay.chat server you configure (default `https://eu.hay.chat`). Chat messages typed by visitors are sent to that server so the AI agent can respond.
* When you click "Connect with Hay.chat" in the settings, your browser is sent to the Hay.chat dashboard with your site name and URL so you can pick an organization; the dashboard redirects back with your Organization ID.
* When you click "Connect store actions" (WooCommerce only), your browser opens the Hay.chat dashboard with your site URL. From there WooCommerce's own approval screen creates read/write REST API keys for Hay.chat and sends them from your store to the Hay.chat server, which uses them to look up orders, shipments and products when shoppers ask. You can revoke the keys at any time in WooCommerce › Settings › Advanced › REST API.
* No data is sent from your server to Hay.chat by this plugin itself.

Terms of service: https://hay.chat/terms-of-service
Privacy policy: https://hay.chat/privacy-policy

== Frequently Asked Questions ==

= Where do I find my Organization ID? =

Log in to your Hay.chat dashboard and go to Settings > API Tokens (https://eu.hay.chat/settings/api-tokens). Your Organization ID is shown there.

= Can I customize the appearance? =

Yes. You can change the widget position, theme color, title, subtitle, greeting message, agent name, and logos from the settings page.

= Does this work with self-hosted Hay? =

Yes. Change the API Base URL in the settings (the widget script is loaded from it automatically) to point to your self-hosted instance.

= How do I connect my WooCommerce store? =

After connecting your site, click "Connect store actions". Hay.chat opens with your store filled in; click "Connect WooCommerce" and approve read/write access on the WooCommerce screen. The settings page then shows "Store actions connected". You can also create a key by hand under WooCommerce › Settings › Advanced › REST API and paste it into Hay.chat.

= Can I hide the widget on some pages? =

Yes. Under "Hide on" you can tick Cart, Checkout and My account (these need WooCommerce), and list other paths, one per line. A * matches anything, so /members/* hides the widget on every page under /members/. Nothing is hidden by default.

= Does it work with my cookie consent plugin? =

If your consent plugin supports the WP Consent API (Complianz, CookieYes and others do; the free "WP Consent API" plugin must be active), Hay.chat follows it. Until the visitor allows "functional" cookies, the widget keeps no chat history on the device between visits; the chat itself still works in the open tab. When the visitor withdraws consent, the widget deletes what it stored.

= What happens when I uninstall the plugin? =

All plugin settings are removed from your database. No data is stored on your server by the plugin apart from those settings.

== Screenshots ==

1. Settings page: connect your Hay.chat organization and enable the widget.
2. Appearance, custom text and branding options for the widget.
3. The Hay.chat widget open on the frontend of a WordPress site.

== Changelog ==

= 1.1.0 =
* New: "Hide on" settings for the cart, checkout and account pages and for any paths you list (all off by default).
* New: works with consent plugins through the WP Consent API. Without "functional" consent the widget keeps no chat history on the device between visits.
* New: "Connect store actions" connects WooCommerce to Hay.chat in a few clicks, with a "Store actions connected" status.
* Fix: "Connect with Hay.chat" now saves the Organization ID when the dashboard sends you back.

= 1.0.1 =
* Faster pages: the widget no longer loads a render-blocking stylesheet, and its script loads deferred in the footer.

= 1.0.0 =
* Initial release
