<?php
/*
Plugin Name: Armoris x402 Agent Pay
Plugin URI: https://www.armoris.io/
Description: Connects your WooCommerce store to the Armoris x402 Agent Pay Gateway. Adds metadata for AI agents and enables on-chain USDC payments via the x402 protocol.
Version: 0.1.6
Author: Armoris Team
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: armoris-x402-agent-pay
Domain Path: /languages
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
            $this->method_title = 'Armoris x402 Agent Pay';
            $this->method_description = 'Accept payments from AI Agents via the x402 Protocol using USDC on-chain.';

            $this->init_form_fields();
            $this->init_settings();

            // Hardcoded — not merchant-configurable
            $this->title       = 'Pay with Agent (x402)';
            $this->description = 'Complete payment using an AI Agent via the x402 protocol.';

            $this->enabled    = $this->get_option( 'enabled' );
            $this->store_id   = $this->get_option( 'store_id' );
            $this->gateway_url = $this->get_option( 'gateway_url', 'https://api.armoris.io' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable Armoris x402 Agent Pay',
                    'default' => 'yes',
                ),
                'store_id' => array(
                    'title'       => 'Store ID',
                    'type'        => 'text',
                    'description' => 'The unique Store ID from your <a href="https://armoris.io/dashboard" target="_blank">Armoris Dashboard</a>.',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'gateway_url' => array(
                    'title'       => 'Gateway URL',
                    'type'        => 'text',
                    'description' => 'The Armoris Gateway API URL. Leave default unless testing locally.',
                    'default'     => 'https://api.armoris.io',
                    'desc_tip'    => false,
                ),
                'client_secret' => array(
                    'title'       => 'Client Secret',
                    'type'        => 'password',
                    'description' => 'The secret key generated in your Armoris Dashboard. Used to authenticate requests from the Armoris Gateway to this store.',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
            );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            
            // Mark as on-hold (we're waiting for payment)
            $order->update_status( 'on-hold', __( 'Awaiting x402 Agent payment.', 'armoris-x402-agent-pay' ) );

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
    $gateway_url = ! empty( $settings['gateway_url'] ) ? esc_url( rtrim( $settings['gateway_url'], '/' ) ) : 'https://api.armoris.io';
    
    // Construct the context URL (ensure leading slash on path if needed, but here we append to base)
    $context_url = $gateway_url . '/proxy/context/' . $store_id;

    echo '<meta name="x402:store_id" content="' . esc_attr( $store_id ) . '" />' . "\n";
    echo '<meta name="x402:gateway_url" content="' . esc_url( $gateway_url ) . '" />' . "\n";
    echo '<meta name="x402:context_url" content="' . esc_url( $context_url ) . '" />' . "\n";
    
    // Optionally add a link tag for discovery
    echo '<link rel="x402-agent-context" href="' . esc_url( $context_url ) . '" />' . "\n";
}

/**
 * Helper to check x402 permissions via Client Secret
 */
function x402_check_permission( $request ) {
    $settings = get_option( 'woocommerce_x402_settings' );
    $stored_secret = isset( $settings['client_secret'] ) ? $settings['client_secret'] : '';

    if ( empty( $stored_secret ) ) {
        return new WP_Error( 'x402_config_error', 'Client Secret not configured in plugin settings.', array( 'status' => 500 ) );
    }

    $header_secret = $request->get_header( 'X-402-Client-Secret' );

    if ( hash_equals( $stored_secret, (string) $header_secret ) ) {
        return true;
    }

    return new WP_Error( 'x402_forbidden', 'Invalid Client Secret.', array( 'status' => 403 ) );
}

/**
 * Register x402 REST Routes
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'x402/v1', '/quote', array(
        'methods' => 'POST',
        'callback' => 'x402_get_quote',
        'permission_callback' => 'x402_check_permission'
    ) );

    register_rest_route( 'x402/v1', '/context', array(
        'methods' => 'GET',
        'callback' => 'x402_get_context',
        'permission_callback' => '__return_true' // Context is public for agents discovery
    ) );
    register_rest_route( 'x402/v1', '/order', array(
        'methods' => 'POST',
        'callback' => 'x402_create_order',
        'permission_callback' => 'x402_check_permission'
    ) );
    
    register_rest_route( 'x402/v1', '/products', array(
        'methods' => 'GET',
        'callback' => 'x402_get_products',
        'permission_callback' => 'x402_check_permission'
    ) );

    register_rest_route( 'x402/v1', '/active-currency', array(
        'methods' => 'GET',
        'callback' => 'x402_get_active_currency',
        'permission_callback' => 'x402_check_permission'
    ) );

    register_rest_route( 'x402/v1', '/order/(?P<order_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'x402_get_order_status',
        'permission_callback' => 'x402_check_permission'
    ) );
} );

function x402_get_order_status( $data ) {
    $order_id = absint( $data['order_id'] );
    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return new WP_Error( 'x402_not_found', 'Order not found.', array( 'status' => 404 ) );
    }

    $line_items = array();
    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        $line_items[] = array(
            'name'     => $item->get_name(),
            'sku'      => $product ? $product->get_sku() : '',
            'quantity' => $item->get_quantity(),
            'total'    => $item->get_total(),
        );
    }

    return array(
        'id'              => $order->get_id(),
        'status'          => $order->get_status(),
        'total'           => $order->get_total(),
        'currency'        => $order->get_currency(),
        'payment_method'  => $order->get_payment_method_title(),
        'date_created'    => $order->get_date_created() ? $order->get_date_created()->format('c') : null,
        'date_modified'   => $order->get_date_modified() ? $order->get_date_modified()->format('c') : null,
        'billing'         => $order->get_address( 'billing' ),
        'shipping'        => $order->get_address( 'shipping' ),
        'line_items'      => $line_items,
        'transaction_id'  => $order->get_meta( 'x402_transaction_id' ),
        'chain'           => $order->get_meta( 'chain' ),
    );
}

function x402_get_active_currency() {
    return array( 'value' => get_woocommerce_currency() );
}

/**
 * Display x402 Metadata as Read-Only in Admin Order View
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'x402_display_admin_order_meta', 10, 1 );
function x402_display_admin_order_meta( $order ) {
    $tx_id = $order->get_meta( 'x402_transaction_id' );
    $chain = $order->get_meta( 'chain' );
    $wallet = $order->get_meta( 'walletAddress' );

    if ( $tx_id || $chain || $wallet ) {
        echo '<h3>Armoris x402 Details</h3>';
        if ( $tx_id ) {
            echo '<p><strong>Transaction ID:</strong> ' . esc_html( $tx_id ) . '</p>';
        }
        if ( $chain ) {
            echo '<p><strong>Chain ID:</strong> ' . esc_html( $chain ) . '</p>';
        }
        if ( $wallet ) {
            echo '<p><strong>Wallet Address:</strong> ' . esc_html( $wallet ) . '</p>';
        }
    }
}

/**
 * Hide x402 Metadata from the Editable Custom Fields Box
 */
add_filter( 'woocommerce_hidden_order_itemmeta', 'x402_hide_order_item_meta' );
add_filter( 'woocommerce_hidden_order_meta', 'x402_hide_order_item_meta' );
function x402_hide_order_item_meta( $hidden_meta ) {
    $hidden_meta[] = 'x402_transaction_id';
    $hidden_meta[] = 'chain';
    $hidden_meta[] = 'walletAddress';
    return $hidden_meta;
}

function x402_get_products( $data ) {
    $params = $data->get_params();
    $args = array(
        'limit' => isset($params['per_page']) ? $params['per_page'] : 10,
        'page' => isset($params['page']) ? $params['page'] : 1,
    );
    
    if ( ! empty( $params['sku'] ) ) {
        $args['sku'] = $params['sku'];
    }
    
    if ( ! empty( $params['include'] ) ) {
        $args['include'] = $params['include']; // Array of IDs
    }
    
    if ( ! empty( $params['parent'] ) ) {
         $args['parent'] = $params['parent'];
    }

    $products = wc_get_products( $args );
    $results = array();

    foreach ( $products as $product ) {
        $data = $product->get_data();
        // Add variations if variable
        if ( $product->is_type( 'variable' ) ) {
            $data['variations'] = $product->get_children();
        }
        
        // Flatten attributes for easier matching if needed, or keep standard
        // WC returns attributes as object array.
        
        $results[] = $data;
    }
    
    return $results;
}

function x402_create_order( $data ) {
    $params = $data->get_params();

    // Basic Validation
    if ( empty( $params['line_items'] ) ) {
        return new WP_Error( 'x402_missing_items', 'Line items are required.', array( 'status' => 400 ) );
    }

    try {
        $order_args = array(
            'status'        => isset($params['set_paid']) && $params['set_paid'] ? 'processing' : 'pending',
            'customer_id'   => 0, // Guest or link via email later if needed
            'customer_note' => isset($params['customer_note']) ? $params['customer_note'] : '',
            'created_via'   => 'x402_agent_gateway',
        );

        // Create Order
        $order = wc_create_order( $order_args );

        if ( is_wp_error( $order ) ) {
            return $order;
        }

        // Add Products
        foreach ( $params['line_items'] as $item ) {
            $product_id = isset($item['product_id']) ? $item['product_id'] : 0;
            $variation_id = isset($item['variation_id']) ? $item['variation_id'] : 0;
            $qty = isset($item['quantity']) ? $item['quantity'] : 1;
            
            // If variation_id is present, passing it as 2nd arg to 'args' array found in some docs is complex with wc_create_order
            // simpler: utilize add_product which handles variations if the product object is correct
            
            $product = wc_get_product( $variation_id ? $variation_id : $product_id );
            if ( ! $product ) {
                // Skip invalid product? Or Fail? Let's skip and warn log if possible, but here just continue
                continue;
            }
            
            $item_id = $order->add_product( $product, $qty );
        }

        // Set Addresses
        if ( ! empty( $params['billing'] ) ) {
            $order->set_address( $params['billing'], 'billing' );
        }
        if ( ! empty( $params['shipping'] ) ) {
            $order->set_address( $params['shipping'], 'shipping' );
        }

        // Add Shipping Lines
        if ( ! empty( $params['shipping_lines'] ) && is_array( $params['shipping_lines'] ) ) {
            foreach ( $params['shipping_lines'] as $shipping_line ) {
                $item = new WC_Order_Item_Shipping();
                $item->set_method_title( isset( $shipping_line['method_title'] ) ? $shipping_line['method_title'] : 'Flat Rate' );
                $item->set_method_id( isset( $shipping_line['method_id'] ) ? $shipping_line['method_id'] : 'flat_rate' );
                $item->set_total( isset( $shipping_line['total'] ) ? $shipping_line['total'] : 0 );
                $order->add_item( $item );
            }
        }

        // Set Payment Method
        if ( ! empty( $params['payment_method'] ) ) {
            $order->set_payment_method( $params['payment_method'] );
        }
        if ( ! empty( $params['payment_method_title'] ) ) {
            $order->set_payment_method_title( $params['payment_method_title'] );
        }

        // Meta Data (Transaction ID, Chain, Wallet)
        if ( ! empty( $params['meta_data'] ) ) {
            foreach ( $params['meta_data'] as $meta ) {
                $order->update_meta_data( $meta['key'], $meta['value'] );
            }
        }

        // Calculate Totals
        $order->calculate_totals();
        
        // Save
        $order->save();

        return array(
            'id' => $order->get_id(),
            'status' => $order->get_status(),
            'order_key' => $order->get_order_key(),
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
        );

    } catch ( Exception $e ) {
        return new WP_Error( 'x402_order_error', $e->getMessage(), array( 'status' => 500 ) );
    }
}

function x402_get_context() {
    // Get Allowed Countries
    $countries = WC()->countries->get_allowed_countries();
    
    // Get Featured Products (Limit 5) using WooCommerce API to avoid slow tax_query.
    $featured_ids     = wc_get_featured_product_ids();
    $featured_ids     = array_slice( $featured_ids, 0, 5 );
    $featured_products = array();
    foreach ( $featured_ids as $pid ) {
        $product = wc_get_product( $pid );
        if ( ! $product ) {
            continue;
        }
        $featured_products[] = array(
            'id'    => $product->get_id(),
            'name'  => $product->get_name(),
            'sku'   => $product->get_sku(),
            'price' => $product->get_price(),
            'image' => wp_get_attachment_url( $product->get_image_id() ),
        );
    }

    return array(
        'store_name' => get_bloginfo( 'name' ),
        'currency' => get_woocommerce_currency(),
        'units' => array(
            'weight' => get_option( 'woocommerce_weight_unit' ),
            'dimension' => get_option( 'woocommerce_dimension_unit' ),
        ),
        'shipping_locations' => array_keys($countries),
        'featured_products' => $featured_products,
        'policy' => array(
            'returns' => 'Please contact store for return policy.', // Placeholder
        )
    );
}

function x402_get_quote( $data ) {
    $params = $data->get_params();
    
    // Initialize Session/Cart if not present (REST API context)
    if ( null === WC()->session ) {
        $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WooCommerce filter.
        WC()->session = new $session_class();
        WC()->session->init();
    }
    if ( null === WC()->customer ) {
        WC()->customer = new WC_Customer( get_current_user_id(), true );
    }
    if ( null === WC()->cart ) {
        WC()->cart = new WC_Cart();
    }

    // 1. Setup Customer Location
    $address = isset($params['address']) ? $params['address'] : array();
    if ( ! empty( $address['country'] ) ) {
        WC()->customer->set_billing_location( $address['country'], $address['state'] ?? '', $address['postcode'] ?? '', $address['city'] ?? '' );
        WC()->customer->set_shipping_location( $address['country'], $address['state'] ?? '', $address['postcode'] ?? '', $address['city'] ?? '' );
    }

    // 2. Clear Cart before calculation
    WC()->cart->empty_cart();

    // 3. Add items
    $items = isset($params['items']) ? $params['items'] : array();
    foreach ( $items as $item ) {
        $product_id = wc_get_product_id_by_sku( $item['sku'] );
        if ( ! $product_id ) {
             return new WP_Error( 'invalid_sku', 'Product not found: ' . $item['sku'], array( 'status' => 404 ) );
        }
        
        $product = wc_get_product( $product_id );
        $variation_id = 0;
        $variation_attributes = array();

        if ( $product->is_type( 'variable' ) ) {
            if ( ! empty( $item['attributes'] ) ) {
                $data_store = WC_Data_Store::load( 'product' );
                
                // Build all possible attribute key formats for matching
                // find_matching_product_variation expects: attribute_<slug> or attribute_pa_<slug>
                
                // Helper: strip any existing prefix to get clean slug
                $build_attrs = function($prefix, $lowercase_val) use ($item) {
                    $result = array();
                    foreach ($item['attributes'] as $key => $value) {
                        $clean = strtolower( str_replace( 'attribute_', '', str_replace( 'pa_', '', strtolower($key) ) ) );
                        $result[$prefix . $clean] = $lowercase_val ? strtolower($value) : $value;
                    }
                    return $result;
                };
                
                // Try all 4 combinations: (attribute_ or attribute_pa_) x (original case or lowercase)
                $attempts = array(
                    $build_attrs('attribute_', false),      // attribute_size=XL (original case)
                    $build_attrs('attribute_', true),       // attribute_size=xl (lowercase)
                    $build_attrs('attribute_pa_', false),   // attribute_pa_size=XL
                    $build_attrs('attribute_pa_', true),    // attribute_pa_size=xl
                );
                
                foreach ($attempts as $attrs) {
                    $variation_id = $data_store->find_matching_product_variation( $product, $attrs );
                    if ( $variation_id ) break;
                }
                
                if ( ! $variation_id ) {
                     return new WP_Error( 'no_variation', 'Variation not found for the provided attributes.', array( 'status' => 400 ) );
                }
                
                // Load the variation to get the exact attributes expected by add_to_cart
                $variation_obj = wc_get_product( $variation_id );
                $variation_attributes = $variation_obj->get_variation_attributes();
            } else {
                 return new WP_Error( 'missing_attributes', 'Attributes required for variable product.', array( 'status' => 400 ) );
            }
        }
        
        $added = WC()->cart->add_to_cart( $product_id, $item['quantity'], $variation_id, $variation_attributes );
        if ( ! $added ) {
             return new WP_Error( 'add_to_cart_failed', 'Failed to add item to cart. SKU: ' . $item['sku'], array( 'status' => 400 ) );
        }
    }

    // 4. Calculate
    WC()->cart->calculate_totals();

    // 5. Build Response
    // get_total() sometimes returns formatted string depending on settings, so we calculate raw float explicitly from parts.
    $contents   = (float) WC()->cart->get_cart_contents_total();
    $tax        = (float) WC()->cart->get_total_tax();
    $shipping   = (float) WC()->cart->get_shipping_total();
    
    $response = array(
        'total' => $contents + $tax + $shipping,
        'contents_total' => $contents,
        'currency' => get_woocommerce_currency(),
        'tax_total' => WC()->cart->get_total_tax(),
        'shipping_total' => WC()->cart->get_shipping_total(),
    );

    return $response;
}
