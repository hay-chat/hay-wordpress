<?php
/**
 * Uninstall handler for Hay.chat.
 *
 * Removes all plugin options when the plugin is deleted from the Plugins screen.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('hay_chat_settings');
