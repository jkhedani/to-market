<?php

/**
 * To Market
 * A simple shopping + checkout solution for WP.
 * @url https://github.com/jkhedani/ToMarket
 * @author jkhedani
 */

/**
 * Load Required Scripts and Functions
 */
function TOMRKT_enqueue_scripts() {
  // Assign the appropriate protocol if necessary.
  $protocol = 'http:';
  if ( !empty($_SERVER['HTTPS']) ) $protocol = 'https:';

  // // jStorage
  // // http://www.jstorage.info/
  // wp_enqueue_script('jstorage-script', get_stylesheet_directory_uri().'/js/jstorage.js', array('jquery','json2'));
  // wp_enqueue_script('diamond-custom-script', get_stylesheet_directory_uri().'/js/scripts.js', array('jquery'), false, true);
  //
  // // Shopping Cart
  // wp_enqueue_script('shopping-cart-scripts', get_stylesheet_directory_uri().'/lib/ShoppingCart/shopping-cart.js', array('jquery','json2'), true);
  // wp_localize_script('shopping-cart-scripts', 'shopping_cart_scripts', array(
  //     'ajaxurl' => admin_url('admin-ajax.php',$protocol),
  //     'nonce' => wp_create_nonce('shopping_cart_scripts_nonce')
  // ));
  //
  // // Stripe
  // global $stripe_options;
  // if ( isset($stripe_options['test_mode']) && $stripe_options['test_mode'] ) {
  //     $publishable = $stripe_options['test_publishable_key']; // Use Test API Key for Stripe Processing
  // } else {
  //     $publishable = $stripe_options['live_publishable_key']; // Use Test API Key for Stripe Processing
  // }
  // wp_enqueue_script('stripe-processing', get_stylesheet_directory_uri().'/lib/StripeScripts/stripe-processing.js', array('jquery') );
  // wp_localize_script('stripe-processing', 'stripe_vars', array(
  //     'publishable_key' => $publishable,
  // ));
  //
  // // PayPal
  // wp_enqueue_script('paypal-scripts', get_stylesheet_directory_uri().'/lib/PayPal/payments/paypal-payment-scripts.js', array('jquery','json2'), true);
  // wp_localize_script('paypal-scripts', 'paypal_data', array(
  //     'ajaxurl' => admin_url('admin-ajax.php',$protocol),
  //     'nonce' => wp_create_nonce('paypal_nonce')
  // ));
}
add_action( 'wp_enqueue_scripts', 'TOMRKT_enqueue_scripts' );

/**
 * ACF: Settings & Fields
 * Configure the settings/option page and any other ACF necessity.
 */
if ( function_exists( 'acf_add_options_sub_page' ) && function_exists( 'get_field' ) ) {
  function TOMRKT_options_config() {
    acf_set_options_page_menu( __('Shop Settings') ); // Changes menu name
    acf_set_options_page_title( __('Our Shop Settings') ); // Changes option/setting page title
    // acf_set_options_page_capability( 'manage_options' );
  }
  add_action( 'after_setup_theme', 'TOMRKT_options_config' );
}

/**
 * Utilities & Helpers
 */
require_once( get_stylesheet_directory() . '/lib/ShoppingCart/shopping-cart.php');

/**
 * Hand Basket (Shopping Cart)
 * By Justin Hedani
 * Uses: Ajax, jStorage & Bootstrap
 */
// require_once( get_stylesheet_directory() . '/lib/ShoppingCart/shopping-cart.php');
// require_once( get_stylesheet_directory() . '/lib/ShoppingCart/shopping-cart-markup.php');

/**
 * Stripe
 * Reference: http://pippinsplugins.com/series/integrating-stripe-com-with-wordpress/
 */
// require_once( get_stylesheet_directory() . '/lib/StripeScripts/stripe-process-payment.php' );
// require_once( get_stylesheet_directory() . '/lib/StripeScripts/stripe-listener.php' );

/**
 * PayPal
 */
// require_once( get_stylesheet_directory() . '/lib/PayPal/payments/method-paypal.php' );


/**
 * EasyPost
 */
//require_once( get_stylesheet_directory() . '/lib/easypost.php' );



?>
