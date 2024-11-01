<?php

/**
 * @wordpress-plugin
 * Plugin Name: Tripandfly Search Widget
 * Plugin URI: http://wordpress.org/plugins/tripandfly-search-widget/
 * Description: Install our WordPress travel-plugin to your website and help your visitors to find the cheapest flights, trains, buses and multimodal offers.
 * Version: 1.1.1
 * Author: Oneliya Team
 * Author URI: https://oneliya.ru/
 * License: GPLv2 or later
 * Text Domain: tripandfly-search-widget
 * Domain Path: /languages/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * TripandflySearchWidget Class
 */
class TripandflySearchWidget {
    public $languages = [
        'ru' => 'Русский',
        'en' => 'English',
    ];

    /** @var stdClass */
    public $plugin;

    /** @var [] */
    public $settings = [];
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( &$this, 'loadPluginTextDomain' ) );

        $file_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );

        $this->plugin                           = new stdClass;
        $this->plugin->name                     = 'tripandfly-search-widget';
        $this->plugin->displayName              = 'Tripandfly Search Widget';
        $this->plugin->version                  = $file_data['Version'];
        $this->plugin->folder                   = plugin_dir_path( __FILE__ );
        $this->plugin->url                      = plugin_dir_url( __FILE__ );
        $this->plugin->script = 'https://tripandfly.ru/micro-widget/tripandfly-widget.js';
        $this->plugin->debugModeScript = 'https://test-wl.onelya.ru/micro-widget/tripandfly-widget.js';

        add_action( 'admin_init', array( &$this, 'registerSettings' ) );
        add_action( 'admin_menu', array( &$this, 'adminPanelsAndMetaBoxes') );
        add_action( 'wp_enqueue_scripts', array( &$this, 'addWidgetScript' ) );
        add_filter( 'script_loader_tag', array( &$this, 'extendScriptAttributes' ), 10, 2 );

        add_shortcode( 'tripandfly_search', array( &$this, 'getWidgetCode') );
    }

    function loadPluginTextDomain() {
        load_plugin_textdomain(
            'tripandfly-search-widget',
            FALSE,
            basename( dirname( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * Register Settings
     */
    function registerSettings() {
        register_setting( $this->plugin->name, 'tripandfly_search_partner_id', 'trim' );
        register_setting( $this->plugin->name, 'tripandfly_search_lang', 'trim' );
    }

    /**
     * Register the plugin settings panel
     */
    function adminPanelsAndMetaBoxes() {
        add_submenu_page(
            'options-general.php',
            __( $this->plugin->displayName, 'tripandfly-search-widget' ),
            __( $this->plugin->displayName, 'tripandfly-search-widget' ),
            'manage_options',
            $this->plugin->name,
            array( &$this, 'adminPanel' )
        );
    }

    function adminPanel() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Sorry, you are not allowed to access this page.', 'tripandfly-search-widget' ) );
        }

        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $this->errorMessage = '<p>' . __( 'Sorry, only have read-only access to this page. Ask your administrator for assistance editing.', 'tripandfly-search-widget' ) . '</p>';
        }

        if ( isset( $_REQUEST['submit'] ) ) {
            if ( ! current_user_can( 'unfiltered_html' ) ) {
                wp_die( __( 'Sorry, you are not allowed to edit this page.', 'tripandfly-search-widget' ) );
            } elseif ( ! isset( $_REQUEST[ $this->plugin->name . '_nonce' ] ) ) {
                $this->errorMessage = __( 'nonce field is missing. Settings NOT saved.', 'tripandfly-search-widget' );
            } elseif ( ! wp_verify_nonce( $_REQUEST[ $this->plugin->name . '_nonce' ], $this->plugin->name ) ) {
                $this->errorMessage = __( 'Invalid nonce specified. Settings NOT saved.', 'tripandfly-search-widget' );
            } else {
                $language = isset($_REQUEST['tripandfly_search_lang']) ? sanitize_text_field($_REQUEST['tripandfly_search_lang']) : '';
                $partner_id = isset($_REQUEST['tripandfly_search_partner_id']) ? sanitize_text_field($_REQUEST['tripandfly_search_partner_id']) : '';
                $debugMode = isset($_REQUEST['tripandfly_search_debug_mode']) ? sanitize_text_field($_REQUEST['tripandfly_search_debug_mode']) : 'N';

                if ( !$language || !array_key_exists($language, $this->languages) ) {
                    $this->errorMessage = __( 'Invalid selected language. Settings NOT saved.', 'tripandfly-search-widget' );
                }

                if ( !$this->errorMessage ) {
                    update_option( 'tripandfly_search_partner_id', $partner_id );
                    update_option( 'tripandfly_search_lang', $language );
                    update_option( 'tripandfly_search_debug_mode', $debugMode );

                    $this->message = __( 'Settings Saved.', 'tripandfly-search-widget' );
                }
            }
        }

        $this->settings = array(
            'tripandfly_search_partner_id' => esc_html( wp_unslash( get_option( 'tripandfly_search_partner_id' ) ) ),
            'tripandfly_search_lang' => esc_html( wp_unslash( get_option( 'tripandfly_search_lang' ) ) ),
            'tripandfly_search_debug_mode' => esc_html( wp_unslash( get_option( 'tripandfly_search_debug_mode' ) ) ),
        );

        include_once( $this->plugin->folder . '/views/settings.php' );
    }

    function getWidgetScript() {
        $debugMode = esc_html( wp_unslash( get_option( 'tripandfly_search_debug_mode' ) ) );
        $script = $this->plugin->script;

        if ( $debugMode === 'Y' ) {
            $script = $this->plugin->debugModeScript;
        }

        return $script;
    }

    function getWidgetCode() {
        $partnerId = esc_html( wp_unslash( get_option( 'tripandfly_search_partner_id' ) ) );
        $lang = esc_html( wp_unslash( get_option( 'tripandfly_search_lang' ) ) );

        if ( $partnerId ) {
            $widget = '<tripandfly-widget';

            $widget .= ' partnerid="'.$partnerId.'"';
            $widget .= ' lang="'.$lang.'"';

            $widget .= '></tripandfly-widget>';

            return $widget;
        }

        return '';
    }

    /**
     * Enqueue widget script to the frontend footer
     */
    function addWidgetScript() {
        if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
            return;
        }

        if ( apply_filters( 'disable_tripandfly_search', false ) ) {
            return;
        }

        wp_enqueue_script( 'tripandfly_search_script', sanitize_url( $this->getWidgetScript() ), array(), null, true );
    }

    function extendScriptAttributes( $tag, $handle ) {
        if ( 'tripandfly_search_script' === $handle ) {
            $tag = str_replace( 'src=', 'type=\'module\' src=', $tag );
        }

        return $tag;
    }
}

$tripandflySearchWidget = new TripandflySearchWidget();
