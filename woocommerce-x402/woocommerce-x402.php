<?php
/*
Plugin Name: x402 Agent Gateway
Plugin URI: https://x402.com
Version: 1.0.1
Author: x402 Team
Author URI: https://x402.com
License: MIT
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Initialize the payment gateway.
 */
add_action( 'plugins_loaded', 'x402_init_gateway_class' );
function x402_init_gateway_class() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    class WC_Gateway_X402 extends WC_Payment_Gateway {

        public function __construct() {
            $this->id = 'x402';
            $this->icon = ''; // TODO: Add icon functionality
            $this->has_fields = false;
            $this->method_title = 'x402 Agent Payment';
            $this->method_description = 'Accept payments from AI Agents via the x402 Protocol.';

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            
            // Core Settings
            $this->store_id = $this->get_option( 'store_id' );
            $this->gateway_url = $this->get_option( 'gateway_url' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable x402 Agent Payments',
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'Pay with Agent (x402)',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Payment method description that the customer will see on your checkout.',
                    'default'     => 'Complete payment using an AI Agent via x402 protocol.',
                ),
                'store_id' => array(
                    'title'       => 'Store ID',
                    'type'        => 'text',
                    'description' => 'The Unique Store ID from your Agent Gateway Dashboard.',
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'gateway_url' => array(
                    'title'       => 'Gateway URL',
                    'type'        => 'text',
                    'description' => 'URL of the Agent Gateway API (e.g., https://gateway.x402.com)',
                    'default'     => 'https://gateway.x402.com', 
                ),
            );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            
            // Mark as on-hold (we're waiting for payment)
            $order->update_status( 'on-hold', __( 'Awaiting x402 Agent payment.', 'x402-gateway' ) );

            // Return thank you redirect
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        }
    }
}

/**
 * Add the gateway to WooCommerce
 */
add_filter( 'woocommerce_payment_gateways', 'x402_add_gateway_class' );
function x402_add_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_X402'; 
    return $methods;
}

/**
 * Inject Agent Context Metadata into Header
 */
add_action( 'wp_head', 'x402_inject_agent_meta' );
function x402_inject_agent_meta() {
    // Only inject if plugin is active and configured
    $settings = get_option( 'woocommerce_x402_settings' );
    
    // Check if settings is an array and store_id exists
    if ( ! is_array( $settings ) || empty( $settings['store_id'] ) ) {
        return;
    }

    $store_id = esc_attr( $settings['store_id'] );
    // Default gateway URL if not set
    $gateway_url = ! empty( $settings['gateway_url'] ) ? esc_url( rtrim( $settings['gateway_url'], '/' ) ) : 'https://gateway.x402.com';
    
    // Construct the context URL (ensure leading slash on path if needed, but here we append to base)
    $context_url = $gateway_url . '/agent/stores/' . $store_id . '/context';

    echo '<meta name="x402:store_id" content="' . $store_id . '" />' . "\n";
    echo '<meta name="x402:gateway_url" content="' . $gateway_url . '" />' . "\n";
    echo '<meta name="x402:context_url" content="' . $context_url . '" />' . "\n";
    
    // Optionally add a link tag for discovery
    echo '<link rel="x402-agent-context" href="' . $context_url . '" />' . "\n";
}
