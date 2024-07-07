<?php
/**
 * Plugin Name: Payment Fees for WooCommerce
 * Plugin URI: https://nadir.blog/2024/07/02/reacting-to-selected-payment-method-in-woocommerce-checkout-block/
 * Description: Add fees to WooCommerce payment gateways.
 * Version: 1.0.0
 * Author: Nadir Seghir
 * Author URI: https://nadir.blog
 * Requires at least: 6.5
 * Tested up to: 6.6
 * WC requires at least: 8.9
 * WC tested up to: 9.0
 * Text Domain: payment-fees-for-woo
 */

add_action(
	'woocommerce_blocks_enqueue_checkout_block_scripts_after',
	function () {
		$asset_file = require __DIR__ . '/build/custom.asset.php';
		wp_register_script(
			'enqueued-custom-js',
			plugins_url( '/build/custom.js', __FILE__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_enqueue_script( 'enqueued-custom-js' );
	}
);


add_action(
	'woocommerce_init',
	function () {
		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'persisted-chosen-payment-method',
				'callback'  => function ( $data ) {
					if ( ! isset( $data['method'] ) ) {
						return;
					}
					wc()->session->set( 'chosen_payment_method', $data['method'] );
				},
			)
		);
	}
);

add_action(
	'woocommerce_init',
	function () {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! wc()->session ) {
			return;
		}

		$selected_payment_method = wc()->session->get( 'chosen_payment_method' );
		if ( $selected_payment_method ) {
			$data_registry = Automattic\WooCommerce\Blocks\Package::container()->get(
				Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class
			);
			$data_registry->add( 'persistedSelectedPaymentMethod', $selected_payment_method );
		}
	}
);

add_action(
	'woocommerce_cart_calculate_fees',
	function () {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$chosen_payment_method_id = WC()->session->get( 'chosen_payment_method' );
		$cart                     = WC()->cart;

		if ( 'stripe' === $chosen_payment_method_id ) {
			$percentage = 0.05;
			$surcharge  = ( $cart->cart_contents_total + $cart->shipping_total ) * $percentage;
			$cart->add_fee( 'Stripe convenience fee', $surcharge ); // Please don't be this kind of merchant, only one ticketmaster is enough.
		}
	}
);
