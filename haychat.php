<?php
/**
 * Plugin Name: Hay.chat
 * Plugin URI: https://github.com/hay-chat/hay-wordpress
 * Description: Add the Hay.chat AI chat widget to your WordPress website.
 * Version: 1.1.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Hay.chat
 * Author URI: https://hay.chat
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: haychat
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HAY_CHAT_VERSION', '1.1.0');
define('HAY_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HAY_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));

class Hay_Chat
{
    private static $instance = null;

    private $option_name = 'hay_chat_settings';

    private $themes = ['blue', 'green', 'purple', 'black'];

    private $positions = ['left', 'right'];

    const MENU_ICON = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMCAzMCI+PHBhdGggZmlsbD0iI2E3YWFhZCIgZD0iTTIxLjM1MzggMEMyNS4yNjg1IDAgMjcuMjI1OCA0LjgyMjEyZS0wNSAyOC40NDE5IDEuMjMwMTlDMjkuNjU4MSAyLjQ2MDM0IDI5LjY1ODEgNC40NDAyIDI5LjY1ODEgOC4zOTk5OVYyMS42QzI5LjY1ODEgMjUuNTU5OCAyOS42NTgxIDI3LjUzOTcgMjguNDQxOSAyOC43Njk4QzI3LjIyNTggMzAgMjUuMjY4NSAzMCAyMS4zNTM4IDMwSDguMzA0MjZDNC4zODk2IDMwIDIuNDMyMyAzMCAxLjIxNjE3IDI4Ljc2OThDNC43NjcxNmUtMDUgMjcuNTM5NyAwIDI1LjU1OTggMCAyMS42VjguMzk5OTlDMCA0LjQ0MDIgMy44ODE2NmUtMDUgMi40NjAzNCAxLjIxNjE3IDEuMjMwMTlDMi40MzIzIDMuODY1MDVlLTA1IDQuMzg5NiAwIDguMzA0MjYgMEgyMS4zNTM4Wk0xOC4zMTIxIDE1LjUwMjNDMTguMzEyMSAyLjQzNjA5IDE2LjMxODYgNC43ODgwMSAxMy4xOTg1IDE1Ljg1MDdDMTIuNDE4NCAwLjQzMjU3OCAxMC4wNzgzIDguNTMzNzEgNi41MjQ3OSAyMS42QzEwLjg1ODMgMjEuNiAyMi4yMTI0IDIxLjA3NzMgMjIuMjEyNCAyMS4wNzczQzIyLjczMjQgMTQuMjgyOCAyNC40NjU4IDEuNDc3ODkgMTguMzEyMSAxNS41MDIzWiIvPjwvc3ZnPg==';

    const LOGO_PATH = 'M21.3538 0C25.2685 0 27.2258 4.82212e-05 28.4419 1.23019C29.6581 2.46034 29.6581 4.4402 29.6581 8.39999V21.6C29.6581 25.5598 29.6581 27.5397 28.4419 28.7698C27.2258 30 25.2685 30 21.3538 30H8.30426C4.3896 30 2.4323 30 1.21617 28.7698C4.76716e-05 27.5397 0 25.5598 0 21.6V8.39999C0 4.4402 3.88166e-05 2.46034 1.21617 1.23019C2.4323 3.86505e-05 4.3896 0 8.30426 0H21.3538ZM18.3121 15.5023C18.3121 2.43609 16.3186 4.78801 13.1985 15.8507C12.4184 0.432578 10.0783 8.53371 6.52479 21.6C10.8583 21.6 22.2124 21.0773 22.2124 21.0773C22.7324 14.2828 24.4658 1.47789 18.3121 15.5023Z';

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_connect_return']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_widget']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
        // Tell the WP Consent API that this plugin follows its consent signals.
        add_filter('wp_consent_api_registered_' . plugin_basename(__FILE__), '__return_true');
    }

    public function get_defaults()
    {
        return [
            'organization_id' => '',
            'base_url'        => 'https://eu.hay.chat',
            'position'        => 'right',
            'theme'           => 'blue',
            'show_greeting'   => true,
            'widget_title'    => '',
            'widget_subtitle' => '',
            'greeting_message' => '',
            'agent_name'      => '',
            'agent_avatar_url' => '',
            'organization_logo_url' => '',
            'enabled'         => true,
            // "Hide on" rules. All off by default.
            'hide_on_cart'     => false,
            'hide_on_checkout' => false,
            'hide_on_account'  => false,
            'hide_on_paths'    => '',
        ];
    }

    public function get_settings()
    {
        $saved = get_option($this->option_name, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return wp_parse_args($saved, $this->get_defaults());
    }

    // ──────────────────────────────────────────────
    // Admin menu & settings page
    // ──────────────────────────────────────────────

    public function add_admin_menu()
    {
        add_menu_page(
            __('Hay.chat Settings', 'haychat'),
            __('Hay.chat', 'haychat'),
            'manage_options',
            'hay-chat',
            [$this, 'render_settings_page'],
            self::MENU_ICON,
            58
        );
    }

    public function add_settings_link($links)
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=hay-chat')),
            esc_html__('Settings', 'haychat')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * URL that sends the admin to the Hay.chat dashboard to pick an organization.
     * The dashboard redirects back to admin.php?page=hay-chat with hay_org_id + state.
     */
    public function get_connect_url()
    {
        $settings   = $this->get_settings();
        $return_url = admin_url('admin.php?page=hay-chat');
        $state      = wp_create_nonce('hay_chat_connect');

        return rtrim($settings['base_url'], '/') . '/settings/api-tokens?' . http_build_query([
            'connect'    => 'wordpress',
            'site_name'  => get_bloginfo('name'),
            'site_url'   => home_url(),
            'return_url' => $return_url,
            'state'      => $state,
        ]);
    }

    /**
     * Handle the redirect back from the Hay.chat dashboard.
     */
    public function handle_connect_return()
    {
        // The nonce lives in the `state` param and is verified below before anything is saved.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['page'], $_GET['hay_org_id']) || sanitize_key(wp_unslash($_GET['page'])) !== 'hay-chat') {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        $redirect = admin_url('admin.php?page=hay-chat');

        if (!isset($_GET['state']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['state'])), 'hay_chat_connect')) {
            wp_safe_redirect(add_query_arg('hay_connected', 'invalid', $redirect));
            exit;
        }

        $org_id = sanitize_text_field(wp_unslash($_GET['hay_org_id'])); // nonce verified above
        if ($org_id === '') {
            wp_safe_redirect(add_query_arg('hay_connected', 'invalid', $redirect));
            exit;
        }

        $settings                    = $this->get_settings();
        $settings['organization_id'] = $org_id;
        $settings['enabled']         = true;
        update_option($this->option_name, $settings);

        wp_safe_redirect(add_query_arg('hay_connected', '1', $redirect));
        exit;
    }

    // ──────────────────────────────────────────────
    // WooCommerce store actions (one-click connect)
    // ──────────────────────────────────────────────

    public function is_woocommerce_active()
    {
        return class_exists('WooCommerce');
    }

    /**
     * Dashboard page that starts WooCommerce's own key approval for this site.
     * The Hay.chat dashboard saves the site URL, then its "Connect WooCommerce"
     * button sends the admin to /wc-auth/v1/authorize on this site, where they
     * approve read/write keys for Hay.chat. WooCommerce posts the keys straight
     * to Hay.chat; they never pass through this plugin.
     */
    public function get_store_connect_url()
    {
        $settings = $this->get_settings();

        return rtrim($settings['base_url'], '/') . '/integrations/plugins/hay-plugin-woocommerce?' . http_build_query([
            'connect'        => '1',
            'config.siteUrl' => home_url(),
        ]);
    }

    /**
     * Whether WooCommerce holds read/write REST keys approved for Hay.chat.
     * WooCommerce names keys from its approval screen "<app name> - API (<date>)".
     */
    public function has_store_keys()
    {
        global $wpdb;

        if (!$this->is_woocommerce_active()) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WooCommerce has no API to list keys; admin screen only.
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE %s AND permissions = %s",
                $wpdb->esc_like('Hay.chat') . '%',
                'read_write'
            )
        );

        return (int) $count > 0;
    }

    public function register_settings()
    {
        register_setting('hay_chat', $this->option_name, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => $this->get_defaults(),
        ]);
    }

    public function sanitize_settings($input)
    {
        $defaults  = $this->get_defaults();
        $sanitized = [];

        if (!is_array($input)) {
            $input = [];
        }

        $sanitized['organization_id']       = sanitize_text_field($input['organization_id'] ?? $defaults['organization_id']);
        $sanitized['base_url']              = untrailingslashit(esc_url_raw($input['base_url'] ?? $defaults['base_url']));
        $sanitized['position']              = in_array($input['position'] ?? '', $this->positions, true) ? $input['position'] : $defaults['position'];
        $sanitized['theme']                 = in_array($input['theme'] ?? '', $this->themes, true) ? $input['theme'] : $defaults['theme'];
        $sanitized['show_greeting']         = !empty($input['show_greeting']);
        $sanitized['widget_title']          = sanitize_text_field($input['widget_title'] ?? $defaults['widget_title']);
        $sanitized['widget_subtitle']       = sanitize_text_field($input['widget_subtitle'] ?? $defaults['widget_subtitle']);
        $sanitized['greeting_message']      = sanitize_text_field($input['greeting_message'] ?? $defaults['greeting_message']);
        $sanitized['agent_name']            = sanitize_text_field($input['agent_name'] ?? $defaults['agent_name']);
        $sanitized['agent_avatar_url']      = esc_url_raw($input['agent_avatar_url'] ?? $defaults['agent_avatar_url']);
        $sanitized['organization_logo_url'] = esc_url_raw($input['organization_logo_url'] ?? $defaults['organization_logo_url']);
        $sanitized['enabled']               = !empty($input['enabled']);
        $sanitized['hide_on_cart']          = !empty($input['hide_on_cart']);
        $sanitized['hide_on_checkout']      = !empty($input['hide_on_checkout']);
        $sanitized['hide_on_account']       = !empty($input['hide_on_account']);
        $sanitized['hide_on_paths']         = $this->sanitize_paths($input['hide_on_paths'] ?? $defaults['hide_on_paths']);

        if (empty($sanitized['base_url'])) {
            $sanitized['base_url'] = $defaults['base_url'];
        }

        return $sanitized;
    }

    /**
     * One path per line, each starting with "/". A "*" matches anything.
     * Full URLs are reduced to their path; empty and duplicate lines are dropped.
     */
    public function sanitize_paths($raw)
    {
        if (!is_string($raw)) {
            return '';
        }

        $paths = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim(sanitize_text_field($line));
            if ($line === '') {
                continue;
            }
            if (preg_match('#^https?://#i', $line)) {
                $line = (string) wp_parse_url($line, PHP_URL_PATH);
            }
            $line = '/' . ltrim($line, '/');
            $paths[$line] = true;
            if (count($paths) >= 100) {
                break;
            }
        }

        return implode("\n", array_keys($paths));
    }

    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = $this->get_settings();
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:10px;">
                <svg width="28" height="28" viewBox="0 0 30 30" aria-hidden="true"><path fill="#001BF4" d="<?php echo esc_attr(self::LOGO_PATH); ?>"/></svg>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>

            <?php
            // Read-only status flag set by our own redirect; no data is processed from it.
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $connected_flag = isset($_GET['hay_connected']) ? sanitize_text_field(wp_unslash($_GET['hay_connected'])) : '';
            ?>
            <?php settings_errors(); ?>
            <?php if ($connected_flag === '1') : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Connected to Hay.chat — your Organization ID has been saved.', 'haychat'); ?></p></div>
            <?php elseif ($connected_flag !== '') : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Could not connect to Hay.chat. Please try again.', 'haychat'); ?></p></div>
            <?php endif; ?>

            <div class="card" style="max-width:640px;margin:16px 0;">
                <?php if (empty($settings['organization_id'])) : ?>
                    <h2 style="margin-top:0;"><?php esc_html_e('Connect your site', 'haychat'); ?></h2>
                    <p><?php esc_html_e('Sign in to Hay.chat and pick the organization for this site. Your Organization ID will be filled in automatically.', 'haychat'); ?></p>
                    <a class="button button-primary button-hero" href="<?php echo esc_url($this->get_connect_url()); ?>"><?php esc_html_e('Connect with Hay.chat', 'haychat'); ?></a>
                <?php else : ?>
                    <p style="margin:0;">
                        <span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span>
                        <?php esc_html_e('Connected to Hay.chat', 'haychat'); ?>
                        &nbsp;·&nbsp;<a href="<?php echo esc_url($this->get_connect_url()); ?>"><?php esc_html_e('Reconnect / switch organization', 'haychat'); ?></a>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($settings['organization_id']) && $this->is_woocommerce_active()) : ?>
                <?php $keys_url = admin_url('admin.php?page=wc-settings&tab=advanced&section=keys'); ?>
                <div class="card" style="max-width:640px;margin:16px 0;">
                    <?php if ($this->has_store_keys()) : ?>
                        <p style="margin:0;">
                            <span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span>
                            <?php esc_html_e('Store actions connected', 'haychat'); ?>
                            &nbsp;·&nbsp;<a href="<?php echo esc_url($this->get_store_connect_url()); ?>" target="_blank" rel="noopener"><?php esc_html_e('Reconnect', 'haychat'); ?> ↗</a>
                            &nbsp;·&nbsp;<a href="<?php echo esc_url($keys_url); ?>"><?php esc_html_e('Manage keys', 'haychat'); ?></a>
                        </p>
                    <?php else : ?>
                        <h2 style="margin-top:0;"><?php esc_html_e('Connect store actions', 'haychat'); ?></h2>
                        <p><?php esc_html_e('Let the Hay.chat agent look up orders, track shipments and answer stock questions in your WooCommerce store. Hay.chat opens with your store filled in; click "Connect WooCommerce" there, then approve read/write access on the WooCommerce screen that follows.', 'haychat'); ?></p>
                        <a class="button button-primary" href="<?php echo esc_url($this->get_store_connect_url()); ?>" target="_blank" rel="noopener"><?php esc_html_e('Connect store actions', 'haychat'); ?> ↗</a>
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: link to the WooCommerce REST API keys screen */
                                esc_html__('Prefer to do it by hand? Create a read/write key in %s and paste it into the WooCommerce settings in Hay.chat.', 'haychat'),
                                '<a href="' . esc_url($keys_url . '&create-key=1') . '">' . esc_html__('WooCommerce › Settings › Advanced › REST API', 'haychat') . '</a>'
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('hay_chat'); ?>

                <table class="form-table" role="presentation">
                    <!-- Enable/Disable -->
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Widget', 'haychat'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                                <?php esc_html_e('Show the Hay.chat widget on your website', 'haychat'); ?>
                            </label>
                        </td>
                    </tr>

                    <!-- Organization ID -->
                    <tr>
                        <th scope="row">
                            <label for="hay_organization_id"><?php esc_html_e('Organization ID', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_organization_id"
                                name="<?php echo esc_attr($this->option_name); ?>[organization_id]"
                                value="<?php echo esc_attr($settings['organization_id']); ?>"
                                class="regular-text"
                                required
                            />
                            <p class="description">
                                <?php
                                $tokens_url = rtrim($settings['base_url'], '/') . '/settings/api-tokens';
                                printf(
                                    /* translators: %s: link to the Hay.chat API tokens page */
                                    esc_html__('Or copy it manually from your Hay.chat dashboard: %s', 'haychat'),
                                    '<a href="' . esc_url($tokens_url) . '" target="_blank" rel="noopener">' . esc_html__('Settings › API Tokens', 'haychat') . ' ↗</a>'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Base URL -->
                    <tr>
                        <th scope="row">
                            <label for="hay_base_url"><?php esc_html_e('API Base URL', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="url"
                                id="hay_base_url"
                                name="<?php echo esc_attr($this->option_name); ?>[base_url]"
                                value="<?php echo esc_attr($settings['base_url']); ?>"
                                class="regular-text"
                            />
                            <p class="description">
                                <?php esc_html_e('Your Hay.chat server URL. Default: https://eu.hay.chat (EU). The widget script is loaded automatically from this URL.', 'haychat'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Hide on', 'haychat'); ?></h2></th>
                    </tr>

                    <!-- WooCommerce pages -->
                    <tr>
                        <th scope="row"><?php esc_html_e('Store pages', 'haychat'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[hide_on_cart]" value="1" <?php checked($settings['hide_on_cart']); ?> />
                                    <?php esc_html_e('Cart', 'haychat'); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[hide_on_checkout]" value="1" <?php checked($settings['hide_on_checkout']); ?> />
                                    <?php esc_html_e('Checkout', 'haychat'); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[hide_on_account]" value="1" <?php checked($settings['hide_on_account']); ?> />
                                    <?php esc_html_e('My account', 'haychat'); ?>
                                </label>
                            </fieldset>
                            <p class="description">
                                <?php esc_html_e('The widget is not loaded on the pages you tick. These options use WooCommerce and do nothing without it.', 'haychat'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Custom paths -->
                    <tr>
                        <th scope="row">
                            <label for="hay_hide_on_paths"><?php esc_html_e('Other pages', 'haychat'); ?></label>
                        </th>
                        <td>
                            <textarea
                                id="hay_hide_on_paths"
                                name="<?php echo esc_attr($this->option_name); ?>[hide_on_paths]"
                                rows="4"
                                class="large-text code"
                                placeholder="/landing-page&#10;/members/*"
                            ><?php echo esc_textarea($settings['hide_on_paths']); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('One path per line, for example /landing-page. A * matches anything, so /members/* hides the widget on every page under /members/.', 'haychat'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Appearance', 'haychat'); ?></h2></th>
                    </tr>

                    <!-- Position -->
                    <tr>
                        <th scope="row">
                            <label for="hay_position"><?php esc_html_e('Widget Position', 'haychat'); ?></label>
                        </th>
                        <td>
                            <select id="hay_position" name="<?php echo esc_attr($this->option_name); ?>[position]">
                                <option value="right" <?php selected($settings['position'], 'right'); ?>><?php esc_html_e('Right', 'haychat'); ?></option>
                                <option value="left" <?php selected($settings['position'], 'left'); ?>><?php esc_html_e('Left', 'haychat'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- Theme -->
                    <tr>
                        <th scope="row">
                            <label for="hay_theme"><?php esc_html_e('Theme Color', 'haychat'); ?></label>
                        </th>
                        <td>
                            <select id="hay_theme" name="<?php echo esc_attr($this->option_name); ?>[theme]">
                                <?php foreach ($this->themes as $theme) : ?>
                                    <option value="<?php echo esc_attr($theme); ?>" <?php selected($settings['theme'], $theme); ?>>
                                        <?php echo esc_html(ucfirst($theme)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <!-- Show Greeting -->
                    <tr>
                        <th scope="row"><?php esc_html_e('Show Greeting', 'haychat'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[show_greeting]" value="1" <?php checked($settings['show_greeting']); ?> />
                                <?php esc_html_e('Display a greeting message when the widget loads', 'haychat'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Custom Text', 'haychat'); ?></h2></th>
                    </tr>

                    <!-- Widget Title -->
                    <tr>
                        <th scope="row">
                            <label for="hay_widget_title"><?php esc_html_e('Widget Title', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_widget_title"
                                name="<?php echo esc_attr($this->option_name); ?>[widget_title]"
                                value="<?php echo esc_attr($settings['widget_title']); ?>"
                                class="regular-text"
                                placeholder="<?php esc_attr_e('Chat with us', 'haychat'); ?>"
                            />
                        </td>
                    </tr>

                    <!-- Widget Subtitle -->
                    <tr>
                        <th scope="row">
                            <label for="hay_widget_subtitle"><?php esc_html_e('Widget Subtitle', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_widget_subtitle"
                                name="<?php echo esc_attr($this->option_name); ?>[widget_subtitle]"
                                value="<?php echo esc_attr($settings['widget_subtitle']); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>

                    <!-- Greeting Message -->
                    <tr>
                        <th scope="row">
                            <label for="hay_greeting_message"><?php esc_html_e('Greeting Message', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_greeting_message"
                                name="<?php echo esc_attr($this->option_name); ?>[greeting_message]"
                                value="<?php echo esc_attr($settings['greeting_message']); ?>"
                                class="regular-text"
                                placeholder="<?php esc_attr_e('Hello! How can we help?', 'haychat'); ?>"
                            />
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Branding', 'haychat'); ?></h2></th>
                    </tr>

                    <!-- Agent Name -->
                    <tr>
                        <th scope="row">
                            <label for="hay_agent_name"><?php esc_html_e('Agent Name', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_agent_name"
                                name="<?php echo esc_attr($this->option_name); ?>[agent_name]"
                                value="<?php echo esc_attr($settings['agent_name']); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>

                    <!-- Agent Avatar URL -->
                    <tr>
                        <th scope="row">
                            <label for="hay_agent_avatar_url"><?php esc_html_e('Agent Avatar URL', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="url"
                                id="hay_agent_avatar_url"
                                name="<?php echo esc_attr($this->option_name); ?>[agent_avatar_url]"
                                value="<?php echo esc_attr($settings['agent_avatar_url']); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>

                    <!-- Organization Logo URL -->
                    <tr>
                        <th scope="row">
                            <label for="hay_organization_logo_url"><?php esc_html_e('Organization Logo URL', 'haychat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="url"
                                id="hay_organization_logo_url"
                                name="<?php echo esc_attr($this->option_name); ?>[organization_logo_url]"
                                value="<?php echo esc_attr($settings['organization_logo_url']); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // ──────────────────────────────────────────────
    // Frontend script injection
    // ──────────────────────────────────────────────

    public function enqueue_widget()
    {
        if (is_admin()) {
            return;
        }

        $settings = $this->get_settings();

        if (empty($settings['enabled']) || empty($settings['organization_id'])) {
            return;
        }

        if ($this->is_hidden_here($settings)) {
            return;
        }

        $config = [
            'organizationId' => $settings['organization_id'],
            'baseUrl'        => $settings['base_url'],
            'position'       => $settings['position'],
            'theme'          => $settings['theme'],
            'showGreeting'   => (bool) $settings['show_greeting'],
        ];

        // With the WP Consent API active, the widget keeps nothing on the device
        // between visits until the visitor's consent says it may (see consent_script()).
        $use_consent_api = function_exists('wp_has_consent');
        if ($use_consent_api) {
            $config['consent'] = 'strict';
        }

        // Only include optional fields if they have values
        $optional_fields = [
            'widget_title'          => 'widgetTitle',
            'widget_subtitle'       => 'widgetSubtitle',
            'greeting_message'      => 'greetingMessage',
            'agent_name'            => 'agentName',
            'agent_avatar_url'      => 'agentAvatarUrl',
            'organization_logo_url' => 'organizationLogoUrl',
        ];

        foreach ($optional_fields as $setting_key => $config_key) {
            if (!empty($settings[$setting_key])) {
                $config[$config_key] = $settings[$setting_key];
            }
        }

        $base = rtrim($settings['base_url'], '/');

        // The widget is served by the Hay.chat server; version is managed there, not here.
        // widget.js carries its own styles, so there is no stylesheet to block rendering.
        wp_enqueue_script('hay-chat-widget', $base . '/v1/webchat/widget.js', [], null, ['strategy' => 'defer', 'in_footer' => true]); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_add_inline_script(
            'hay-chat-widget',
            'window.HayChat = window.HayChat || {}; window.HayChat.config = ' . wp_json_encode($config) . ';'
                . ($use_consent_api ? $this->consent_script() : ''),
            'before'
        );
    }

    /**
     * True when a "Hide on" rule matches the current request.
     */
    private function is_hidden_here($settings)
    {
        if (!empty($settings['hide_on_cart']) && function_exists('is_cart') && is_cart()) {
            return true;
        }
        if (!empty($settings['hide_on_checkout']) && function_exists('is_checkout') && is_checkout()) {
            return true;
        }
        if (!empty($settings['hide_on_account']) && function_exists('is_account_page') && is_account_page()) {
            return true;
        }
        if (empty($settings['hide_on_paths']) || !isset($_SERVER['REQUEST_URI'])) {
            return false;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only compared against patterns, never output or stored.
        $path = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
        $path = '/' . trim(rawurldecode($path), '/');

        // Also match paths relative to the site root when WordPress lives in a subfolder.
        $candidates = [$path];
        $home_path  = '/' . trim((string) wp_parse_url(home_url(), PHP_URL_PATH), '/');
        if ($home_path !== '/' && strpos($path . '/', $home_path . '/') === 0) {
            $candidates[] = '/' . trim(substr($path, strlen($home_path)), '/');
        }

        foreach (explode("\n", $settings['hide_on_paths']) as $pattern) {
            $pattern = '/' . trim($pattern, '/');
            $regex   = '#^' . str_replace('\\*', '.*', preg_quote($pattern, '#')) . '$#i';
            foreach ($candidates as $candidate) {
                if (preg_match($regex, $candidate)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Keeps the widget's consent in step with the WP Consent API (Complianz,
     * CookieYes and other consent plugins that support it). The widget only
     * keeps chat history on the device between visits with "functional" consent;
     * revoking wipes what it stored. Waits for widget.js, which may load deferred
     * or be delayed by a caching plugin.
     */
    private function consent_script()
    {
        return <<<'JS'
(function () {
    var wanted = null, tries = 0, timer = null;
    function apply() {
        timer = null;
        var hay = window.HayChat;
        if (!hay || typeof hay.grantConsent !== 'function' || typeof hay.revokeConsent !== 'function') {
            if (tries++ < 120) { timer = setTimeout(apply, 250); }
            return;
        }
        if (wanted) { hay.grantConsent(); } else { hay.revokeConsent(); }
    }
    function sync(allowed) {
        if (allowed === wanted) { return; }
        wanted = allowed;
        tries = 0;
        if (!timer) { apply(); }
    }
    function current() {
        return typeof window.wp_has_consent === 'function' && !!window.wp_has_consent('functional');
    }
    document.addEventListener('wp_listen_for_consent_change', function (event) {
        var changed = (event && event.detail) || {};
        if (Object.prototype.hasOwnProperty.call(changed, 'functional')) {
            sync(changed.functional === 'allow');
        }
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { sync(current()); });
    } else {
        sync(current());
    }
})();
JS;
    }
}

// Initialize
Hay_Chat::instance();
