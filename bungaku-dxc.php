<?php
/**
 * Plugin Name: BungAku-DXC
 * Plugin URI:  https://github.com/bungakku/BungAku-DXC
 * Description: DocX Converter. Converts semantically marked up .docx documents to clean HTML for pasting from Word, Google Docs, etc.
 * Version:     1.2.0
 * Author:      Biswajit
 * Author URI:  https://github.com/bungakku
 * License:     BSD 2-clause
 * Text Domain: bungaku-dxc
 * Update URI:  https://github.com/bungakku/BungAku-DXC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// -------------------------------------------------------------------
// 1. Add meta box and enqueue assets (unchanged, but included)
// -------------------------------------------------------------------

add_action( 'add_meta_boxes', 'bungaku_dxc_add_post_meta_box' );
add_action( 'admin_enqueue_scripts', 'bungaku_dxc_admin_assets' );
add_action( 'admin_footer', 'bungaku_dxc_load_scripts' );

function bungaku_dxc_add_post_meta_box() {
    $post_types = get_post_types( array( 'public' => true ) );
    foreach ( $post_types as $post_type ) {
        if ( post_type_supports( $post_type, 'editor' ) ) {
            add_meta_box(
                'bungaku_dxc',
                __( 'BungAku-DXC', 'bungaku-dxc' ),
                'bungaku_dxc_render_meta_box',
                $post_type,
                'normal',
                'default'
            );
        }
    }
}

function bungaku_dxc_admin_assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }
    wp_enqueue_style(
        'bungaku-dxc-style',
        plugin_dir_url( __FILE__ ) . 'bungaku-dxc.css',
        array(),
        '1.2.0'
    );
}

function bungaku_dxc_render_meta_box( $post ) {
    wp_nonce_field( 'bungaku_dxc_upload', 'bungaku_dxc_nonce' );
    ?>
    <div id="bungaku-dxc-uploader" class="status-empty">
        <div>
            <label for="bungaku-dxc-upload">
                <?php esc_html_e( 'Select docx file:', 'bungaku-dxc' ); ?>
                <input type="file" id="bungaku-dxc-upload" accept=".docx" />
            </label>
        </div>

        <div id="bungaku-dxc-loading">
            <?php esc_html_e( 'Loading...', 'bungaku-dxc' ); ?>
        </div>

        <div id="bungaku-dxc-inserting">
            <?php esc_html_e( 'Inserting...', 'bungaku-dxc' ); ?>
        </div>

        <p class="bungaku-dxc-error">
            <?php esc_html_e( 'Error while attempting to convert file:', 'bungaku-dxc' ); ?>
            <span id="bungaku-dxc-error-message"></span>
        </p>

        <div class="bungaku-dxc-preview">
            <input type="hidden" id="bungaku-dxc-upload-image-nonce" value="<?php echo esc_attr( wp_create_nonce( 'media-form' ) ); ?>" />
            <input type="hidden" id="bungaku-dxc-upload-image-href" value="<?php echo esc_url( admin_url( 'async-upload.php' ) ); ?>" />
            <input type="hidden" id="bungaku-dxc-admin-ajax-href" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" />

            <p><input type="button" id="bungaku-dxc-insert" class="button button-primary" value="<?php esc_attr_e( 'Insert into editor', 'bungaku-dxc' ); ?>" /></p>

            <div class="bungaku-dxc-tabs">
                <div class="tab">
                    <h4><?php esc_html_e( 'Visual', 'bungaku-dxc' ); ?></h4>
                    <iframe id="bungaku-dxc-visual-preview"
                            src="about:blank"
                            data-stylesheets="<?php echo esc_attr( implode( ',', get_editor_stylesheets() ) ); ?>">
                    </iframe>
                </div>
                <div class="tab">
                    <h4><?php esc_html_e( 'Raw HTML', 'bungaku-dxc' ); ?></h4>
                    <pre id="bungaku-dxc-raw-preview"></pre>
                </div>
                <div class="tab">
                    <h4><?php esc_html_e( 'Messages', 'bungaku-dxc' ); ?></h4>
                    <div id="bungaku-dxc-messages"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function bungaku_dxc_load_scripts() {
    global $post;
    $screen = get_current_screen();
    if ( ! $post || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
        return;
    }

    wp_enqueue_script(
        'bungaku-dxc-editor',
        plugin_dir_url( __FILE__ ) . 'bungaku-dxc-editor.js',
        array( 'jquery' ),
        '1.2.0',
        true
    );

    wp_enqueue_script(
        'bungaku-dxc-tabs',
        plugin_dir_url( __FILE__ ) . 'bungaku-dxc-tabs.js',
        array(),
        '1.2.0',
        true
    );
}

// -------------------------------------------------------------------
// 2. GitHub Automatic Updater
// -------------------------------------------------------------------

/**
 * Check for updates from GitHub releases.
 */
add_filter( 'pre_set_site_transient_update_plugins', 'bungaku_dxc_check_for_update' );

function bungaku_dxc_check_for_update( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    $plugin_slug = plugin_basename( __FILE__ );
    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $this_plugin_data = get_plugin_data( __FILE__, false, false );
    $current_version  = $this_plugin_data['Version'] ?? '1.2.0';

    // Get the latest release info from GitHub API (cached for 12 hours).
    $release = get_transient( 'bungaku_dxc_github_release' );
    if ( false === $release ) {
        $url = 'https://api.github.com/repos/bungakku/BungAku-DXC/releases/latest';
        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return $transient;
        }

        $release = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $release['tag_name'] ) ) {
            return $transient;
        }
        set_transient( 'bungaku_dxc_github_release', $release, 12 * HOUR_IN_SECONDS );
    }

    $latest_version = ltrim( $release['tag_name'], 'v' ); // Remove 'v' prefix if present
    $download_url = $release['zipball_url'] ?? ''; // GitHub provides a zipball URL

    if ( version_compare( $latest_version, $current_version, '>' ) ) {
        $transient->response[ $plugin_slug ] = (object) array(
            'slug'        => $plugin_slug,
            'new_version' => $latest_version,
            'url'         => 'https://github.com/bungakku/BungAku-DXC',
            'package'     => $download_url,
            'tested'      => '6.9.4',
            'requires'    => '5.0',
        );
    }

    return $transient;
}

/**
 * Provide plugin information for the "View details" popup.
 */
add_filter( 'plugins_api', 'bungaku_dxc_plugin_info', 10, 3 );

function bungaku_dxc_plugin_info( $res, $action, $args ) {
    if ( 'plugin_information' !== $action ) {
        return $res;
    }

    $plugin_slug = plugin_basename( __FILE__ );
    if ( $args->slug !== $plugin_slug ) {
        return $res;
    }

    // Get release data from cache (same as above).
    $release = get_transient( 'bungaku_dxc_github_release' );
    if ( false === $release ) {
        $url = 'https://api.github.com/repos/bungakku/BungAku-DXC/releases/latest';
        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return $res;
        }
        $release = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $release['tag_name'] ) ) {
            return $res;
        }
        set_transient( 'bungaku_dxc_github_release', $release, 12 * HOUR_IN_SECONDS );
    }

    $res = new stdClass();
    $res->name          = 'BungAku-DXC';
    $res->slug          = $plugin_slug;
    $res->version       = ltrim( $release['tag_name'], 'v' );
    $res->author        = '<a href="https://github.com/bungakku">Biswajit</a>';
    $res->homepage      = 'https://github.com/bungakku/BungAku-DXC';
    $res->download_link = $release['zipball_url'] ?? '';
    $res->sections      = array(
        'description' => 'Converts .docx documents to clean HTML. For details, see the <a href="https://github.com/bungakku/BungAku-DXC">GitHub repository</a>.',
        'changelog'   => 'See the <a href="https://github.com/bungakku/BungAku-DXC/releases">release notes</a> on GitHub.',
    );

    return $res;
}

/**
 * Clear the update cache when the plugin is activated or deactivated.
 */
register_activation_hook( __FILE__, 'bungaku_dxc_clear_update_cache' );
register_deactivation_hook( __FILE__, 'bungaku_dxc_clear_update_cache' );

function bungaku_dxc_clear_update_cache() {
    delete_transient( 'bungaku_dxc_github_release' );
}