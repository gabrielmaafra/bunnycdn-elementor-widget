<?php
/**
 * Plugin Name:       BunnyCDN Video Widget for Elementor
 * Plugin URI:        https://github.com/gabrielmaafra/bunnycdn-elementor-widget
 * Description:       Adds a secure BunnyCDN Stream video widget to Elementor, with signed, expiring embed URLs.
 * Version:           1.6.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Requires Plugins:  elementor
 * Author:            Gabriel Mafra
 * Author URI:        https://gabrielmafra.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bunnycdn-video-widget
 */

if (! defined('ABSPATH')) exit;

define('BUNNYCDN_VIDEO_WIDGET_VERSION', '1.6.0');

// Register Elementor widget
function bunnycdn_register_elementor_widgets($widgets_manager) {
    require_once __DIR__ . '/widgets/class-bunnycdn-video-widget.php';
    $widgets_manager->register(new \BunnyCDN_Video_Widget());
}
add_action('elementor/widgets/register', 'bunnycdn_register_elementor_widgets');

// Warn when Elementor is not active (older WordPress versions ignore "Requires Plugins")
add_action('admin_notices', function () {
    if (did_action('elementor/loaded') || ! current_user_can('activate_plugins')) {
        return;
    }
    echo '<div class="notice notice-warning"><p>'
        . esc_html__('BunnyCDN Video Widget needs Elementor to be installed and active.', 'bunnycdn-video-widget')
        . '</p></div>';
});

// Register admin settings page
add_action('admin_menu', function () {
    add_options_page(
        __('BunnyCDN Settings', 'bunnycdn-video-widget'),
        'BunnyCDN',
        'manage_options',
        'bunnycdn-settings',
        'bunnycdn_settings_page_callback'
    );
});

// "Settings" link on the Plugins screen
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    array_unshift($links, '<a href="' . esc_url(admin_url('options-general.php?page=bunnycdn-settings')) . '">' . esc_html__('Settings', 'bunnycdn-video-widget') . '</a>');
    return $links;
});

// Register the settings with validation
add_action('admin_init', function () {
    register_setting('bunnycdn_settings_group', 'bunnycdn_library_id', [
        'type'              => 'string',
        'sanitize_callback' => 'bunnycdn_sanitize_library_id',
        'default'           => '',
    ]);
    register_setting('bunnycdn_settings_group', 'bunnycdn_token_key', [
        'type'              => 'string',
        'sanitize_callback' => 'bunnycdn_sanitize_token_key',
        'default'           => '',
    ]);
});

// WordPress can run a sanitize callback twice on the first save, so each error is only added once.
function bunnycdn_settings_error_once($setting, $code, $message, $type = 'error') {
    static $shown = [];
    if (isset($shown[$code])) {
        return;
    }
    $shown[$code] = true;
    add_settings_error($setting, $code, $message, $type);
}

function bunnycdn_sanitize_library_id($input) {
    $input    = trim((string) $input);
    $previous = get_option('bunnycdn_library_id');

    if ($input === '' || ctype_digit($input)) {
        return $input;
    }

    bunnycdn_settings_error_once(
        'bunnycdn_library_id',
        'bunnycdn_library_id_invalid',
        __('Invalid Library ID. It must be a number (e.g. 123456).', 'bunnycdn-video-widget')
    );
    return $previous;
}

function bunnycdn_sanitize_token_key($input) {
    $input    = trim((string) $input);
    $previous = get_option('bunnycdn_token_key');

    // Empty field means "keep the saved key", so the key never has to be shown again.
    if ($input === '' || $input === $previous) {
        return $previous;
    }

    // Validate as UUID
    if (! preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $input)) {
        bunnycdn_settings_error_once(
            'bunnycdn_token_key',
            'bunnycdn_token_key_invalid',
            __('Invalid Token Key format. It must be a UUID (e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).', 'bunnycdn-video-widget')
        );
        return $previous;
    }

    return sanitize_text_field($input);
}

// Render the settings page
function bunnycdn_settings_page_callback() {
    $has_key = (bool) get_option('bunnycdn_token_key');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('BunnyCDN Settings', 'bunnycdn-video-widget'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('bunnycdn_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bunnycdn_library_id"><?php esc_html_e('Library ID', 'bunnycdn-video-widget'); ?></label></th>
                    <td>
                        <input type="text" inputmode="numeric" id="bunnycdn_library_id" name="bunnycdn_library_id" value="<?php echo esc_attr(get_option('bunnycdn_library_id')); ?>" class="regular-text" />
                        <p class="description">
                            <?php echo wp_kses_post(__('Find it under <em>Stream > your Video Library > API</em> (Video Library ID). Each widget can override it.', 'bunnycdn-video-widget')); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bunnycdn_token_key"><?php esc_html_e('Token Key', 'bunnycdn-video-widget'); ?></label></th>
                    <td>
                        <input type="password" id="bunnycdn_token_key" name="bunnycdn_token_key" value="" autocomplete="off" class="regular-text" style="max-width: 600px; width: 100%;"
                            placeholder="<?php echo $has_key ? esc_attr__('Saved. Leave empty to keep the current key.', 'bunnycdn-video-widget') : 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; ?>" />
                        <p class="description">
                            <?php echo wp_kses_post(__('Find your token key in your Video Library, under <em>Security</em> (embed token authentication). It is never shown again after saving.', 'bunnycdn-video-widget')); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register the plugin's privacy policy
function bunnycdn_register_privacy_policy() {
    if (function_exists('wp_add_privacy_policy_content')) {
        wp_add_privacy_policy_content(
            'BunnyCDN Video Widget for Elementor',
            wp_kses_post(
                __('This plugin does not collect, store, or share any personal data directly. However, it loads videos from Bunny.net using a secure token generated on your server.', 'bunnycdn-video-widget')
                . '<br><br>'
                . __('Depending on how Bunny.net processes video delivery, some visitor metadata (like IP address or browser info) may be handled by their servers. Please review <a href="https://bunny.net/privacy" target="_blank" rel="noopener noreferrer">Bunny.net’s privacy policy</a> and include any necessary disclosures in your site’s privacy statement.', 'bunnycdn-video-widget')
            )
        );
    }
}
add_action('admin_init', 'bunnycdn_register_privacy_policy');
