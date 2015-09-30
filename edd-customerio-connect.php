<?php
/**
 * Plugin Name:     Easy Digital Downloads - Customer.io Connect
 * Plugin URI:      https://easydigitaldownloads.com/extensions/customerio-connect/
 * Description:     Track your customers the easy way with Customer.io
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-customerio-connect
 *
 * @package         EDD\Customerio_Connect
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'EDD_Customerio_Connect' ) ) {


	/**
	 * Main EDD_Customerio_Connect class
	 *
	 * @since       1.0.0
	 */
	class EDD_Customerio_Connect {


		/**
		 * @access      private
		 * @var         EDD_Customerio_Connect $instance The one true EDD_Customerio_Connect
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * @access      public
		 * @var         $api The Customer.io API object
		 * @since       1.0.0
		 */
		public $api;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      self::$instance The one true EDD_Customerio_Connect
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new EDD_Customerio_Connect();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();

				$site_id = edd_get_option( 'edd_customerio_connect_site_id', false );
				$api_key = edd_get_option( 'edd_customerio_connect_api_key', false );

				if( $site_id && $api_key ) {
					self::$instance->api = new Customerio( $site_id, $api_key );
				}
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_CUSTOMERIO_CONNECT_VER', '1.0.0' );

			// Plugin path
			define( 'EDD_CUSTOMERIO_CONNECT_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_CUSTOMERIO_CONNECT_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/scripts.php';
			require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/functions.php';
			require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/libraries/class.customerio.php';
			require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/actions.php';
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {
			// Add our extension settings
			add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );

			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Customer.io Connect', EDD_CUSTOMERIO_CONNECT_VER, 'Daniel J Griffiths' );
			}
		}


		/**
		 * Add settings
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       array $settings The existing plugin settings
		 * @return      array The modified plugin settings
		 */
		public function add_settings( $settings ) {
			$new_settings = array(
				array(
					'id'    => 'edd_customerio_connect_settings',
					'name'  => '<strong>' . __( 'Customer.io Connect', 'edd-customerio-connect' ) . '</strong>',
					'desc'  => '',
					'type'  => 'header'
				),
				array(
					'id'    => 'edd_customerio_connect_site_id',
					'name'  => __( 'Site ID', 'edd-customerio-connect' ),
					'desc'  => __( 'Your site ID can be found on the Integrations page of the Customer.io dashboard.', 'edd-customerio-connect' ),
					'type'  => 'text'
				),
				array(
					'id'    => 'edd_customerio_connect_api_key',
					'name'  => __( 'API Key', 'edd-customerio-connect' ),
					'desc'  => __( 'Your API key can be found on the Integrations page of the Customer.io dashboard.', 'edd-customerio-connect' ),
					'type'  => 'text'
				),
			);

			return array_merge( $settings, $new_settings );
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'edd_customerio_connect_language_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), '' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-customerio-connect', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/edd-customerio-connect/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-customerio-connect/ folder
				load_textdomain( 'edd-customerio-connect', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-customerio-connect/ folder
				load_textdomain( 'edd-customerio-connect', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-customerio-connect', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the one true EDD_Customerio_Connect
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Customerio_Connect The one true EDD_Customerio_Connect
 */
function edd_customerio_connect() {
	if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if( ! class_exists( 'S214_EDD_Activation' ) ) {
			require_once 'includes/libraries/class.s214-edd-activation.php';
		}

		$activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		return EDD_Customerio_Connect::instance();
	} else {
		return EDD_Customerio_Connect::instance();
	}
}
add_action( 'plugins_loaded', 'edd_customerio_connect' );
