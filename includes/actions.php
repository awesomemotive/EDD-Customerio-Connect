<?php
/**
 * Customer.io actions
 *
 * @package     EDD\Customerio_Connect\Actions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Track new users
 *
 * @since       1.0.0
 * @param       int $payment_id The ID of a given payment
 * @return      void
 */
function edd_customerio_connect_register_user( $payment_id ) {
	// Bail if API isn't setup
	if( ! edd_customerio_connect()->api ) {
		return;
	}

	// Setup the request body
	$user_info      = edd_get_payment_meta_user_info( $payment_id );
	$payment_meta   = edd_get_payment_meta( $payment_id );
	$cart_items     = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : false;
	$user_name      = false;

	if( $payment_meta['user_info']['first_name'] ) {
		$user_name = $payment_meta['user_info']['first_name'];

		if( $payment_meta['user_info']['last_name'] ) {
			$user_name .= ' ' . $payment_meta['user_info']['last_name'];
		}
	}

	$body = array(
		'email'         => $payment_meta['user_info']['email'],
		'created_at'    => $payment_meta['date']
	);

	if( $user_name ) {
		$body['name'] = $user_name;
	}

	$response = edd_customerio_connect()->api->call( $payment_meta['user_info']['id'], $body );

	// Track the purchases
	if( empty( $cart_items ) || ! $cart_items ) {
		$cart_items = maybe_unserialize( $payment_meta['downloads'] );
	}

	if( $cart_items ) {
		$body = array(
			'name' => 'purchased',
			'data' => array(
				'discount' => $payment_meta['user_info']['discount']
			)
		);

		foreach( $cart_items as $key => $cart_item ) {
			$item_id    = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;
			$price      = $cart_item['price'];

			$body['data']['items'][$cart_item['id']] = array(
				'price'        => $price,
				'product_id'   => $cart_item['id'],
				'product_name' => esc_attr( $cart_item['name'] ),
			);

			if( edd_has_variable_prices( $cart_item['id'] ) ) {
				$body['data']['items'][$cart_item['id']]['price_id']   = $cart_item['item_number']['options']['price_id'];
				$body['data']['items'][$cart_item['id']]['price_name'] = edd_get_price_option_name( $cart_item['id'], $cart_item['item_number']['options']['price_id'] );
				$body['data']['items'][$cart_item['id']]['quantity']   = $cart_item['item_number']['quantity'];
			} else {
				$body['data']['items'][$cart_item['id']]['quantity']   = $cart_item['quantity'];
			}

			if( edd_use_taxes() ) {
				$body['data']['items'][$cart_item['id']]['tax'] = $cart_item['tax'];
			}
		}

		$response = edd_customerio_connect()->api->call( $payment_meta['user_info']['id'], $body, 'POST', 'events' );
	}
}
add_action( 'edd_complete_purchase', 'edd_customerio_connect_register_user', 100, 1 );


/**
 * Update user on profile save
 *
 * @since       1.0.0
 * @param       int $user_id The ID of the user
 * @param       object $old_user_data The old data for the user
 * @return      void
 */
function edd_customerio_connect_update_user( $user_id, $old_user_data ) {
	// Bail if API isn't setup
	if( ! edd_customerio_connect()->api ) {
		return;
	}

	// Setup the request body
	$user_info = get_userdata( $user_id );
	$user_name = false;

	if( $user_info->first_name ) {
		$user_name = $user_info->first_name;

		if( $user_info->last_name ) {
			$user_name .= ' ' . $user_info->last_name;
		}
	}

	if( ! $user_name ) {
		$user_name = $user_name->user_nicename;
	}

	$body = array(
		'email'         => $user_info->user_email,
		'name'          => $user_name
	);

	$response = edd_customerio_connect()->api->call( $user_id, $body );
}
add_action( 'profile_update', 'edd_customerio_connect_update_user', 10, 2 );