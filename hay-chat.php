<?php
/**
 * Plugin Name: Hay.chat
 * Plugin URI: https://github.com/hay-chat/hay-wordpress
 * Description: Add the Hay.chat AI chat widget to your WordPress website.
 * Version: 1.0.0
 * Author: Hay.chat
 * Author URI: https://hay.chat
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hay-chat
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HAY_CHAT_VERSION', '1.0.0');
define('HAY_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HAY_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));

class HayChat
{
    private static $instance = null;

    private $option_name = 'hay_chat_settings';

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
        ];
    }

    public function get_settings()
    {
        $saved = get_option($this->option_name, []);
        return wp_parse_args($saved, $this->get_defaults());
    }

    // ──────────────────────────────────────────────
    // Admin menu & settings page
    // ──────────────────────────────────────────────

    public function add_admin_menu()
    {
        add_menu_page(
            __('Hay.chat Settings', 'hay-chat'),
            __('Hay.chat', 'hay-chat'),
            'manage_options',
            'hay-chat',
            [$this, 'render_settings_page'],
            self::MENU_ICON,
            58
        );
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=hay-chat">' . __('Settings', 'hay-chat') . '</a>';
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

        $sanitized['organization_id']       = sanitize_text_field($input['organization_id'] ?? $defaults['organization_id']);
        $sanitized['base_url']              = esc_url_raw($input['base_url'] ?? $defaults['base_url']);
        $sanitized['position']              = in_array($input['position'] ?? '', ['left', 'right'], true) ? $input['position'] : $defaults['position'];
        $sanitized['theme']                 = sanitize_text_field($input['theme'] ?? $defaults['theme']);
        $sanitized['show_greeting']         = !empty($input['show_greeting']);
        $sanitized['widget_title']          = sanitize_text_field($input['widget_title'] ?? $defaults['widget_title']);
        $sanitized['widget_subtitle']       = sanitize_text_field($input['widget_subtitle'] ?? $defaults['widget_subtitle']);
        $sanitized['greeting_message']      = sanitize_text_field($input['greeting_message'] ?? $defaults['greeting_message']);
        $sanitized['agent_name']            = sanitize_text_field($input['agent_name'] ?? $defaults['agent_name']);
        $sanitized['agent_avatar_url']      = esc_url_raw($input['agent_avatar_url'] ?? $defaults['agent_avatar_url']);
        $sanitized['organization_logo_url'] = esc_url_raw($input['organization_logo_url'] ?? $defaults['organization_logo_url']);
        $sanitized['enabled']               = !empty($input['enabled']);

        return $sanitized;
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
            <?php if ($connected_flag === '1') : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Connected to Hay.chat — your Organization ID has been saved.', 'hay-chat'); ?></p></div>
            <?php elseif ($connected_flag !== '') : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Could not connect to Hay.chat. Please try again.', 'hay-chat'); ?></p></div>
            <?php endif; ?>

            <div class="card" style="max-width:640px;margin:16px 0;">
                <?php if (empty($settings['organization_id'])) : ?>
                    <h2 style="margin-top:0;"><?php esc_html_e('Connect your site', 'hay-chat'); ?></h2>
                    <p><?php esc_html_e('Sign in to Hay.chat and pick the organization for this site. Your Organization ID will be filled in automatically.', 'hay-chat'); ?></p>
                    <a class="button button-primary button-hero" href="<?php echo esc_url($this->get_connect_url()); ?>"><?php esc_html_e('Connect with Hay.chat', 'hay-chat'); ?></a>
                <?php else : ?>
                    <p style="margin:0;">
                        <span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span>
                        <?php esc_html_e('Connected to Hay.chat', 'hay-chat'); ?>
                        &nbsp;·&nbsp;<a href="<?php echo esc_url($this->get_connect_url()); ?>"><?php esc_html_e('Reconnect / switch organization', 'hay-chat'); ?></a>
                    </p>
                <?php endif; ?>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('hay_chat'); ?>

                <table class="form-table" role="presentation">
                    <!-- Enable/Disable -->
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Widget', 'hay-chat'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enabled]" value="1" <?php checked($settings['enabled']); ?> />
                                <?php esc_html_e('Show the Hay.chat widget on your website', 'hay-chat'); ?>
                            </label>
                        </td>
                    </tr>

                    <!-- Organization ID -->
                    <tr>
                        <th scope="row">
                            <label for="hay_organization_id"><?php esc_html_e('Organization ID', 'hay-chat'); ?></label>
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
                                    esc_html__('Or copy it manually from your Hay.chat dashboard: %s', 'hay-chat'),
                                    '<a href="' . esc_url($tokens_url) . '" target="_blank" rel="noopener">' . esc_html__('Settings › API Tokens', 'hay-chat') . ' ↗</a>'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Base URL -->
                    <tr>
                        <th scope="row">
                            <label for="hay_base_url"><?php esc_html_e('API Base URL', 'hay-chat'); ?></label>
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
                                <?php esc_html_e('Your Hay.chat server URL. Default: https://eu.hay.chat (EU). The widget script is loaded automatically from this URL.', 'hay-chat'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Appearance', 'hay-chat'); ?></h2></th>
                    </tr>

                    <!-- Position -->
                    <tr>
                        <th scope="row">
                            <label for="hay_position"><?php esc_html_e('Widget Position', 'hay-chat'); ?></label>
                        </th>
                        <td>
                            <select id="hay_position" name="<?php echo esc_attr($this->option_name); ?>[position]">
                                <option value="right" <?php selected($settings['position'], 'right'); ?>><?php esc_html_e('Right', 'hay-chat'); ?></option>
                                <option value="left" <?php selected($settings['position'], 'left'); ?>><?php esc_html_e('Left', 'hay-chat'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- Theme -->
                    <tr>
                        <th scope="row">
                            <label for="hay_theme"><?php esc_html_e('Theme Color', 'hay-chat'); ?></label>
                        </th>
                        <td>
                            <select id="hay_theme" name="<?php echo esc_attr($this->option_name); ?>[theme]">
                                <?php
                                $themes = ['blue', 'green', 'purple', 'black'];
                                foreach ($themes as $theme) : ?>
                                    <option value="<?php echo esc_attr($theme); ?>" <?php selected($settings['theme'], $theme); ?>>
                                        <?php echo esc_html(ucfirst($theme)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <!-- Show Greeting -->
                    <tr>
                        <th scope="row"><?php esc_html_e('Show Greeting', 'hay-chat'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[show_greeting]" value="1" <?php checked($settings['show_greeting']); ?> />
                                <?php esc_html_e('Display a greeting message when the widget loads', 'hay-chat'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Custom Text', 'hay-chat'); ?></h2></th>
                    </tr>

                    <!-- Widget Title -->
                    <tr>
                        <th scope="row">
                            <label for="hay_widget_title"><?php esc_html_e('Widget Title', 'hay-chat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_widget_title"
                                name="<?php echo esc_attr($this->option_name); ?>[widget_title]"
                                value="<?php echo esc_attr($settings['widget_title']); ?>"
                                class="regular-text"
                                placeholder="Chat with us"
                            />
                        </td>
                    </tr>

                    <!-- Widget Subtitle -->
                    <tr>
                        <th scope="row">
                            <label for="hay_widget_subtitle"><?php esc_html_e('Widget Subtitle', 'hay-chat'); ?></label>
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
                            <label for="hay_greeting_message"><?php esc_html_e('Greeting Message', 'hay-chat'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="hay_greeting_message"
                                name="<?php echo esc_attr($this->option_name); ?>[greeting_message]"
                                value="<?php echo esc_attr($settings['greeting_message']); ?>"
                                class="regular-text"
                                placeholder="Hello! How can we help?"
                            />
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2><?php esc_html_e('Branding', 'hay-chat'); ?></h2></th>
                    </tr>

                    <!-- Agent Name -->
                    <tr>
                        <th scope="row">
                            <label for="hay_agent_name"><?php esc_html_e('Agent Name', 'hay-chat'); ?></label>
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
                            <label for="hay_agent_avatar_url"><?php esc_html_e('Agent Avatar URL', 'hay-chat'); ?></label>
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
                            <label for="hay_organization_logo_url"><?php esc_html_e('Organization Logo URL', 'hay-chat'); ?></label>
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

        $config = [
            'organizationId' => $settings['organization_id'],
            'baseUrl'        => $settings['base_url'],
            'position'       => $settings['position'],
            'theme'          => $settings['theme'],
            'showGreeting'   => (bool) $settings['show_greeting'],
        ];

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
        wp_enqueue_style('hay-chat-widget', $base . '/v1/webchat/widget.css', [], null); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_enqueue_script('hay-chat-widget', $base . '/v1/webchat/widget.js', [], null, ['strategy' => 'async', 'in_footer' => false]); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_add_inline_script(
            'hay-chat-widget',
            'window.HayChat = window.HayChat || {}; window.HayChat.config = ' . wp_json_encode($config) . ';',
            'before'
        );
    }
}

// Initialize
HayChat::instance();
