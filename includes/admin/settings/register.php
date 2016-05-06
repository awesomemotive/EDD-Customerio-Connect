<?php
/**
 * Settings
 *
 * @package     EDD\Customerio_Connect\Admin\Settings
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add settings section
 *
 * @since       1.0.1
 * @param       array $sections The existing extensions sections
 * @return      array The modified extensions settings
 */
function edd_customerio_connect_add_settings_section( $sections ) {
	$sections['customerio-connect'] = __( 'Customer.IO Connect', 'edd-customerio-connect' );

	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_customerio_connect_add_settings_section' );


/**
 * Add settings
 *
 * @since       1.0.0
 * @param       array $settings The existing plugin settings
 * @return      array The modified plugin settings
 */
function edd_customerio_connect_add_settings( $settings ) {
	$new_settings = array(
		'customerio-connect' => array(
			array(
				'id'   => 'edd_customerio_connect_settings',
				'name' => '<strong>' . __( 'Customer.io Connect', 'edd-customerio-connect' ) . '</strong>',
				'desc' => '',
				'type' => 'header'
			),
			array(
				'id'   => 'edd_customerio_connect_site_id',
				'name' => __( 'Site ID', 'edd-customerio-connect' ),
				'desc' => __( 'Your site ID can be found on the Integrations page of the Customer.io dashboard.', 'edd-customerio-connect' ),
				'type' => 'text'
			),
			array(
				'id'   => 'edd_customerio_connect_api_key',
				'name' => __( 'API Key', 'edd-customerio-connect' ),
				'desc' => __( 'Your API key can be found on the Integrations page of the Customer.io dashboard.', 'edd-customerio-connect' ),
				'type' => 'text'
			)
		)
	);

	return array_merge( $settings, $new_settings );
}
add_filter( 'edd_settings_extensions', 'edd_customerio_connect_add_settings' );