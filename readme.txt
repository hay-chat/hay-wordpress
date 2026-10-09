=== Hay.chat ===
Contributors: rgrjnr
Tags: chat, ai, chatbot, customer support, live chat
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
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

== Installation ==

1. Upload the `haychat` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Hay.chat menu in the admin sidebar
4. Enter your Organization ID (copy it from https://eu.hay.chat/settings/api-tokens)
5. Save changes - the chat widget will appear on your site

== External services ==

This plugin connects to the Hay.chat service (https://hay.chat) to provide the chat widget. It is not a standalone chat system.

* On every public page where the widget is enabled, the visitor's browser loads `widget.js` and `widget.css` from the Hay.chat server you configure (default `https://eu.hay.chat`). Chat messages typed by visitors are sent to that server so the AI agent can respond.
* When you click "Connect with Hay.chat" in the settings, your browser is sent to the Hay.chat dashboard with your site name and URL so you can pick an organization; the dashboard redirects back with your Organization ID.
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

= What happens when I uninstall the plugin? =

All plugin settings are removed from your database. No data is stored on your server by the plugin apart from those settings.

== Screenshots ==

1. Settings page: connect your Hay.chat organization and enable the widget.
2. Appearance, custom text and branding options for the widget.
3. The Hay.chat widget open on the frontend of a WordPress site.

== Changelog ==

= 1.0.0 =
* Initial release
