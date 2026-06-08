<?php
/**
 * Plugin Name: Dark Mode for WP Dashboard
 * Plugin URI: https://wordpress.org/plugins/dark-mode-for-wp-dashboard/
 * Description: Enable dark mode for the WordPress dashboard
 * Author: Naiche
 * Author URI: https://profiles.wordpress.org/naiches/
 * Text Domain: dark-mode-for-wp-dashboard
 * Version: 1.3.3
 * Tested up to: 7.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

define( 'DARK_MODE_DASHBOARD_VERSION', '1.3.3' );
define( 'DARK_MODE_DASHBOARD_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

/**
 * Check if dark mode is active for the current user.
 *
 * Returns true when the preference is 'enabled' (or empty/unset, since dark
 * mode is on by default) and also for 'auto' (CSS handles the media query).
 *
 * @return bool
 */
function dark_mode_dashboard_is_active() {
    $pref = get_user_meta( get_current_user_id(), 'dark_mode_preference', true );

    if ( '' === $pref || 'enabled' === $pref ) {
        return true; // default ON
    }

    if ( 'auto' === $pref ) {
        return true; // CSS handles via media query
    }

    return false;
}

/**
 * Get the current user's dark mode preference.
 *
 * @return string 'enabled', 'disabled', or 'auto'
 */
function dark_mode_dashboard_get_preference() {
    $pref = get_user_meta( get_current_user_id(), 'dark_mode_preference', true );

    if ( '' === $pref ) {
        $pref = apply_filters( 'dark_mode_dashboard_default_preference', 'enabled' );
    }

    return $pref;
}

/**
 * Migrate old dark_mode_dashboard user meta to dark_mode_preference.
 *
 * Old logic was inverted: value '1' meant disabled, empty meant enabled.
 * Runs once per site, flagged with option 'dark_mode_migration_done'.
 */
function dark_mode_dashboard_migrate() {
    if ( get_option( 'dark_mode_migration_done' ) ) {
        return;
    }

    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-time migration, runs once then flags itself done.
    $users = get_users( array( 'meta_key' => 'dark_mode_dashboard' ) );

    foreach ( $users as $user ) {
        $old_value = get_user_meta( $user->ID, 'dark_mode_dashboard', true );

        if ( '1' === (string) $old_value ) {
            update_user_meta( $user->ID, 'dark_mode_preference', 'disabled' );
        } else {
            update_user_meta( $user->ID, 'dark_mode_preference', 'enabled' );
        }

        delete_user_meta( $user->ID, 'dark_mode_dashboard' );
    }

    update_option( 'dark_mode_migration_done', true );
}
add_action( 'admin_init', 'dark_mode_dashboard_migrate' );

/**
 * Enqueue the main dark mode stylesheet.
 *
 * For 'enabled': always enqueue.
 * For 'auto': always enqueue (CSS uses prefers-color-scheme media query).
 * For 'disabled': do not enqueue.
 */
function dark_mode_dashboard_enqueue_styles() {
    // Always load the CSS — it's scoped under body.dark-mode so it has no
    // effect in light mode, but must be present for the JS toggle to work.
    $dark_mode_css = apply_filters(
        'dark_mode_dashboard_css',
        DARK_MODE_DASHBOARD_PLUGIN_PATH . 'assets/css/dark-mode.css'
    );

    $css_file = plugin_dir_path( __FILE__ ) . 'assets/css/dark-mode.css';
    $css_ver  = file_exists( $css_file ) ? filemtime( $css_file ) : DARK_MODE_DASHBOARD_VERSION;
    wp_register_style( 'dark-mode-dashboard', $dark_mode_css, array(), $css_ver );
    wp_enqueue_style( 'dark-mode-dashboard' );
}
add_action( 'admin_enqueue_scripts', 'dark_mode_dashboard_enqueue_styles' );

/**
 * Enqueue block editor dark mode stylesheet.
 */
function dark_mode_dashboard_enqueue_editor_styles() {
    if ( ! dark_mode_dashboard_is_active() || ! apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        return;
    }

    $editor_file = plugin_dir_path( __FILE__ ) . 'assets/css/dark-mode-editor.css';
    $editor_ver  = file_exists( $editor_file ) ? filemtime( $editor_file ) : DARK_MODE_DASHBOARD_VERSION;
    wp_enqueue_style(
        'dark-mode-dashboard-editor',
        DARK_MODE_DASHBOARD_PLUGIN_PATH . 'assets/css/dark-mode-editor.css',
        array(),
        $editor_ver
    );
}
add_action( 'enqueue_block_editor_assets', 'dark_mode_dashboard_enqueue_editor_styles' );

/**
 * Register editor style via add_editor_style — this loads inside the iframe
 * very early in the editor lifecycle, before enqueued stylesheets.
 */
function dark_mode_dashboard_register_editor_style() {
    if ( ! dark_mode_dashboard_is_active() || ! apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        return;
    }
    add_editor_style( plugin_dir_url( __FILE__ ) . 'assets/css/dark-mode-critical.css' );
    add_editor_style( plugin_dir_url( __FILE__ ) . 'assets/css/dark-mode-editor.css' );
}
add_action( 'admin_init', 'dark_mode_dashboard_register_editor_style' );

/**
 * Inject dark mode CSS into block editor settings — processed by Gutenberg
 * before the iframe content is rendered, preventing the white flash.
 */
function dark_mode_dashboard_editor_settings( $settings ) {
    if ( ! dark_mode_dashboard_is_active() || ! apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        return $settings;
    }
    $settings['styles'][] = array(
        'css' => 'body.dark-mode{color-scheme:dark}body.dark-mode,body.dark-mode .editor-styles-wrapper{background-color:#1a1e26!important;color:#eceff4!important}',
    );
    return $settings;
}
add_filter( 'block_editor_settings_all', 'dark_mode_dashboard_editor_settings' );

/**
 * Inject dark body styles into TinyMCE (Classic Editor) iframe.
 */
function dark_mode_dashboard_tinymce_init( $mce_init ) {
    if ( ! dark_mode_dashboard_is_active() || ! apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        return $mce_init;
    }
    $styles = 'html,body,body#tinymce,body.mce-content-body{background:#1e232c!important;color:#eceff4!important}body a{color:#6b9cff}body p,body li,body td,body th,body div,body span{color:#eceff4}';
    if ( isset( $mce_init['content_style'] ) ) {
        $mce_init['content_style'] .= ' ' . $styles;
    } else {
        $mce_init['content_style'] = $styles;
    }
    return $mce_init;
}
add_filter( 'tiny_mce_before_init', 'dark_mode_dashboard_tinymce_init' );

/**
 * Load dark mode CSS into TinyMCE iframe via mce_css filter.
 */
function dark_mode_dashboard_mce_css( $mce_css ) {
    if ( ! dark_mode_dashboard_is_active() || ! apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        return $mce_css;
    }
    if ( ! empty( $mce_css ) ) {
        $mce_css .= ',';
    }
    $mce_css .= DARK_MODE_DASHBOARD_PLUGIN_PATH . 'assets/css/dark-mode-critical.css';
    return $mce_css;
}
add_filter( 'mce_css', 'dark_mode_dashboard_mce_css' );

/**
 * Prevent white flash on admin page load by injecting critical CSS inline.
 *
 * The editor iframe flash is handled separately by block_editor_settings_all
 * and add_editor_style(). This handles the admin shell itself.
 */
function dark_mode_dashboard_anti_flash() {
    if ( ! dark_mode_dashboard_is_active() ) {
        return;
    }
    $css = 'html body.wp-admin.dark-mode{color-scheme:dark}html body.wp-admin.dark-mode,html body.wp-admin.dark-mode #wpwrap,html body.wp-admin.dark-mode #wpcontent,html body.wp-admin.dark-mode #wpbody{background-color:#1a1e26}html body.wp-admin.dark-mode #adminmenuback,html body.wp-admin.dark-mode #adminmenuwrap,html body.wp-admin.dark-mode #wpadminbar{background-color:#1a1e26}';
    if ( apply_filters( 'dark_mode_dashboard_editor_canvas', true ) ) {
        $css .= 'body.wp-admin.dark-mode .editor-visual-editor,body.wp-admin.dark-mode .edit-post-visual-editor,body.wp-admin.dark-mode .editor-visual-editor iframe,body.wp-admin.dark-mode .edit-post-visual-editor iframe,body.wp-admin.dark-mode .interface-interface-skeleton__content{background-color:#1a1e26!important}body.wp-admin.dark-mode .wp-editor-container,body.wp-admin.dark-mode .wp-editor-area,body.wp-admin.dark-mode #wp-content-editor-container,body.wp-admin.dark-mode .wp-editor-wrap,body.wp-admin.dark-mode #content_ifr,body.wp-admin.dark-mode .mce-edit-area,body.wp-admin.dark-mode .mce-edit-area iframe{background-color:#1e232c!important;color:#eceff4!important}';
    }
    echo '<style>' . $css . '</style>';
}
add_action( 'admin_head', 'dark_mode_dashboard_anti_flash', 1 );

/**
 * Add body class for dark mode.
 *
 * 'dark-mode' for enabled, 'dark-mode-auto' for auto, nothing for disabled.
 *
 * @param string $classes Space-separated list of admin body classes.
 * @return string
 */
function dark_mode_dashboard_body_class( $classes ) {
    $pref = dark_mode_dashboard_get_preference();

    if ( 'enabled' === $pref ) {
        $classes .= ' dark-mode';
    } elseif ( 'auto' === $pref ) {
        $classes .= ' dark-mode dark-mode-auto';
    }

    return $classes;
}
add_filter( 'admin_body_class', 'dark_mode_dashboard_body_class' );

/**
 * Add dark mode toggle to the admin toolbar.
 *
 * Only shown in the admin area.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function dark_mode_dashboard_toolbar_link( $wp_admin_bar ) {
    if ( ! is_admin() ) {
        return;
    }

    $wp_admin_bar->add_node( array(
        'id'    => 'dark-mode-dashboard',
        'title' => '<span class="dm-icon dm-icon-moon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9.37 5.51A7.35 7.35 0 0 0 9.1 7.5c0 4.08 3.32 7.4 7.4 7.4.68 0 1.35-.09 1.99-.27A7.014 7.014 0 0 1 12 19c-3.86 0-7-3.14-7-7 0-2.93 1.81-5.45 4.37-6.49zM12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg></span><span class="dm-icon dm-icon-sun" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM11 1h2v3h-2zm0 19h2v3h-2zM3.52 4.93l1.41-1.41 2.12 2.12-1.41 1.42zM16.95 18.36l1.41-1.41 2.12 2.12-1.41 1.41zM1 11h3v2H1zm19 0h3v2h-3zM4.93 20.48l2.12-2.12 1.42 1.41-2.13 2.12zM18.36 7.05l2.12-2.12 1.41 1.41-2.12 2.12z"/></svg></span><span class="screen-reader-text">' . esc_html__( 'Dark Mode', 'dark-mode-for-wp-dashboard' ) . '</span>',
        'href'  => '#',
        'meta'  => array(
            'class' => 'dark-mode-dashboard',
            'title' => esc_attr__( 'Dark Mode', 'dark-mode-for-wp-dashboard' ),
        ),
    ) );
}
add_action( 'admin_bar_menu', 'dark_mode_dashboard_toolbar_link', 999 );

/**
 * Output inline JS and CSS for the admin bar toggle.
 *
 * Vanilla JS, no jQuery. Toggles the body class immediately and sends an
 * AJAX request to persist the preference.
 */
function dark_mode_dashboard_admin_footer() {
    if ( ! is_admin() ) {
        return;
    }

    $nonce          = wp_create_nonce( 'dark_mode_dashboard_nonce' );
    $ajax_url       = esc_url( admin_url( 'admin-ajax.php' ) );
    $editor_canvas  = apply_filters( 'dark_mode_dashboard_editor_canvas', true );
    ?>
    <style>
        #wpadminbar #wp-admin-bar-dark-mode-dashboard .dm-icon {
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
            position: relative;
            top: -2px;
        }
        #wpadminbar #wp-admin-bar-dark-mode-dashboard .dm-icon svg {
            fill: currentColor;
        }
        #wpadminbar #wp-admin-bar-dark-mode-dashboard .dm-icon-sun { display: none; }
        #wpadminbar #wp-admin-bar-dark-mode-dashboard.dm-light .dm-icon-moon { display: none; }
        #wpadminbar #wp-admin-bar-dark-mode-dashboard.dm-light .dm-icon-sun { display: inline-flex; }
    </style>
    <script>
    (function () {
        var node = document.getElementById('wp-admin-bar-dark-mode-dashboard');
        var toggle = node ? node.querySelector('.ab-item') : null;
        if (!toggle || !node) return;

        function updateIcon() {
            if (document.body.classList.contains('dark-mode')) {
                node.classList.remove('dm-light');
            } else {
                node.classList.add('dm-light');
            }
        }
        updateIcon();

        <?php if ( $editor_canvas ) : ?>
        function syncIframes(isDark) {
            document.querySelectorAll('iframe[name="editor-canvas"]').forEach(function (iframe) {
                try {
                    var b = iframe.contentDocument.body;
                    if (isDark) { b.classList.add('dark-mode'); } else { b.classList.remove('dark-mode', 'dark-mode-auto'); }
                } catch (e) {}
            });
            var mce = document.getElementById('content_ifr');
            if (mce) {
                try {
                    var doc = mce.contentDocument;
                    var id = 'dm-toggle-override';
                    var existing = doc.getElementById(id);
                    if (isDark) {
                        if (existing) existing.remove();
                    } else {
                        if (!existing) {
                            var s = doc.createElement('style');
                            s.id = id;
                            s.textContent = 'html,body,body#tinymce,body.mce-content-body{background:#fff!important;color:#444!important}body p,body li,body td,body th,body div,body span{color:#444!important}body a{color:#0073aa!important}';
                            doc.head.appendChild(s);
                        }
                    }
                } catch (e) {}
            }
        }

        var iframeReady = false;
        function checkIframe() {
            var iframe = document.querySelector('iframe[name="editor-canvas"]');
            if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
                if (!iframeReady && document.body.classList.contains('dark-mode')) {
                    iframe.contentDocument.body.classList.add('dark-mode');
                    iframeReady = true;
                }
            }
        }
        var obs = new MutationObserver(checkIframe);
        obs.observe(document.documentElement, { childList: true, subtree: true });
        document.addEventListener('load', function (e) {
            if (e.target.name === 'editor-canvas') checkIframe();
        }, true);
        checkIframe();
        <?php endif; ?>

        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            var body = document.body;
            var isDark = body.classList.contains('dark-mode');
            var isAuto = body.classList.contains('dark-mode-auto');
            var newPref;

            if (isDark || isAuto) {
                body.classList.remove('dark-mode', 'dark-mode-auto');
                newPref = 'disabled';
            } else {
                body.classList.add('dark-mode');
                newPref = 'enabled';
            }
            updateIcon();
            <?php if ( $editor_canvas ) : ?>
            syncIframes(newPref !== 'disabled');
            <?php endif; ?>

            var formData = new FormData();
            formData.append('action', 'dark_mode_dashboard_toggle');
            formData.append('security', '<?php echo esc_js( $nonce ); ?>');
            formData.append('preference', newPref);

            fetch('<?php echo esc_url( $ajax_url ); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
        });
    })();
    </script>
    <?php
}
add_action( 'admin_footer', 'dark_mode_dashboard_admin_footer' );

/**
 * AJAX handler for the admin bar toggle.
 *
 * Verifies nonce and capability, sanitizes input, saves preference.
 */
function dark_mode_dashboard_ajax_toggle() {
    check_ajax_referer( 'dark_mode_dashboard_nonce', 'security' );

    if ( ! current_user_can( 'read' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $allowed    = array( 'enabled', 'disabled', 'auto' );
    $preference = isset( $_POST['preference'] ) ? sanitize_text_field( wp_unslash( $_POST['preference'] ) ) : '';

    if ( ! in_array( $preference, $allowed, true ) ) {
        wp_send_json_error( 'Invalid preference' );
    }

    update_user_meta( get_current_user_id(), 'dark_mode_preference', $preference );
    wp_send_json_success();
}
add_action( 'wp_ajax_dark_mode_dashboard_toggle', 'dark_mode_dashboard_ajax_toggle' );

/**
 * Display dark mode preference fields on the user profile page.
 *
 * Three radio buttons: Dark (always), Light (always), Auto (system).
 *
 * @param WP_User $user The user object being edited.
 */
function dark_mode_dashboard_user_profile_fields( $user ) {
    $preference = get_user_meta( $user->ID, 'dark_mode_preference', true );

    if ( '' === $preference ) {
        $preference = 'enabled'; // default
    }
    ?>
    <h3><?php esc_html_e( 'Dark Mode', 'dark-mode-for-wp-dashboard' ); ?></h3>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <?php esc_html_e( 'Dashboard appearance', 'dark-mode-for-wp-dashboard' ); ?>
            </th>
            <td>
                <fieldset>
                    <label>
                        <input type="radio" name="dark_mode_preference" value="enabled" <?php checked( $preference, 'enabled' ); ?>>
                        <?php esc_html_e( 'Dark (always)', 'dark-mode-for-wp-dashboard' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="dark_mode_preference" value="disabled" <?php checked( $preference, 'disabled' ); ?>>
                        <?php esc_html_e( 'Light (always)', 'dark-mode-for-wp-dashboard' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="dark_mode_preference" value="auto" <?php checked( $preference, 'auto' ); ?>>
                        <?php esc_html_e( 'Auto (system)', 'dark-mode-for-wp-dashboard' ); ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'dark_mode_dashboard_user_profile_fields' );
add_action( 'edit_user_profile', 'dark_mode_dashboard_user_profile_fields' );

/**
 * Save the dark mode preference from the user profile page.
 *
 * @param int $user_id The user ID being saved.
 */
function dark_mode_dashboard_save_user_profile_fields( $user_id ) {
    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    $allowed    = array( 'enabled', 'disabled', 'auto' );
    $preference = isset( $_POST['dark_mode_preference'] ) ? sanitize_text_field( wp_unslash( $_POST['dark_mode_preference'] ) ) : 'enabled';

    if ( ! in_array( $preference, $allowed, true ) ) {
        $preference = 'enabled';
    }

    update_user_meta( $user_id, 'dark_mode_preference', $preference );
}
add_action( 'personal_options_update', 'dark_mode_dashboard_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'dark_mode_dashboard_save_user_profile_fields' );
