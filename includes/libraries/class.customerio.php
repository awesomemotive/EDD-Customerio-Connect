<?php
/**
 * Customer.io helper class
 *
 * @package     EDD\Customerio_Connect\Customerio
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'Customerio' ) ) {


	/**
	 * Main Customerio class
	 *
	 * @since       1.0.0
	 */
	class Customerio {


		/**
		 * @access      public
		 * @var         $site_id The site ID to connect
		 * @since       1.0.0
		 */
		public $side_id;


		/**
		 * @access      public
		 * @var         $api_key The API key for a given site
		 * @since       1.0.0
		 */
		public $api_key;


		/**
		 * @access      public
		 * @var         $api_url The Customer.io API URL
		 * @since       1.0.0
		 */
		public $api_url = 'https://track.customer.io/api/v1/customers/';


		/**
		 * Get things started
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       string $site_id The site ID to connect
		 * @param       string $api_key The API key for a given site
		 * @return      void
		 */
		public function __construct( $site_id = null, $api_key = null ) {
			// Bail if site ID or API key are missing
			if( ! $site_id || ! $api_key ) {
				return;
			}

			$this->site_id = $site_id;
			$this->api_key = $api_key;
		}


		/**
		 * Execute an API call
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       int $customer_id The customer ID
		 * @param       array $body The body of the API call
		 * @param       string $method The type of call to make
		 * @param       string $endpoint The endpoint to call
		 * @return      mixed int $response If API call succeeded, false otherwise
		 */
		public function call( $customer_id = 0, $body = array(), $method = 'PUT', $endpoint = false ) {
			// Bail if no ID or body passed
			if( ! $customer_id || empty( $body ) ) {
				return false;
			}

			$args = array(
				'headers'   => array(
					'Authorization'     => 'Basic ' . base64_encode( $this->site_id . ':' . $this->api_key )
				),
				'method'    => $method,
				'body'      => $body
			);

			$url = $this->api_url . $customer_id;

			if( $endpoint ) {
				$url .= '/' . $endpoint;
			}

			try {
				$response = wp_remote_request( $url, $args );
			} catch( Exception $e ) {
				edd_record_gateway_error( __( 'EDD Customer.io Connect Error', 'edd-customerio-connect' ), print_r( $e->getMessage(), true ) );
				return false;
			}

			$status = wp_remote_retrieve_header( $response, 'status' );

			if( $status != '200 OK' ) {
				$body = json_decode( wp_remote_retrieve_body( $response ) );

				edd_record_gateway_error( __( 'EDD Customer.io Connect Error', 'edd-customerio-connect' ), $status . ': ' . $body->meta->error );
				return false;
			}

			return $response;
		}
	}
}