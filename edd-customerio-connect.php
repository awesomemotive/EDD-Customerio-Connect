<?php
/**
 * Plugin Name:     Easy Digital Downloads - Customer.io Connect
 * Plugin URI:      https://wordpress.org/plugins/edd-customerio-connect/
 * Description:     Track your customers the easy way with Customer.io
 * Version:         1.0.1
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-customerio-connect
 *
 * @package         EDD\Customerio_Connect
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


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
			require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/libraries/class.customerio.php';
			require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/actions.php';

			if( is_admin() ) {
				require_once EDD_CUSTOMERIO_CONNECT_DIR . 'includes/admin/settings/register.php';
			}
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
