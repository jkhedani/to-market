<?php

/**
 * To Market
 * A simple shopping + checkout solution for WP.
 * @url https://github.com/jkhedani/ToMarket
 * @author jkhedani
 */

// Store path to 'plugin' separate as this 'plugin' currently lives in the
// theme. Note there is no trailing slash.
$path_to_plugin = get_stylesheet_directory_uri() . '/lib/ToMarket';

/**
 * Enqueue Scripts, Libraries & Settings
 */

// # CSS & JS
function tomarket_enqueue_scripts() {

  global $path_to_plugin;
  // Assign the appropriate protocol if necessary.
  $protocol = 'http:';
  if ( !empty($_SERVER['HTTPS']) ) $protocol = 'https:';

  // Bootstrap Scripts & Styles
  wp_enqueue_style( 'bootstrap-styles', $path_to_plugin . '/scripts/css/bootstrap/bootstrap.css' );
  //wp_enqueue_style( 'bootstrap-forms-styles', $path_to_plugin . '/scripts/css/bootstrap/forms.min.css' );
  wp_enqueue_script( 'bootstrap-transition-script', $path_to_plugin .'/scripts/js/bootstrap/transition.js', array(), false, true );
  wp_enqueue_script( 'bootstrap-modal-script', $path_to_plugin .'/scripts/js/bootstrap/modal.js', array(), false, true );
  wp_enqueue_script( 'bootstrap-tooltip-script', $path_to_plugin .'/scripts/js/bootstrap/tooltip.js', array(), false, true );
  wp_enqueue_script( 'bootstrap-popover-script', $path_to_plugin .'/scripts/js/bootstrap/popover.js', array(), false, true );

  // To Market Scripts & Styles
  wp_enqueue_style( 'to-market-styles', $path_to_plugin . '/scripts/css/tomarket.css' );
  wp_enqueue_script( 'to-market-scripts', $path_to_plugin . '/scripts/js/tomarket.js', array('jquery','json2') );
  wp_localize_script( 'to-market-scripts', 'to_market_scripts', array(
    'tax_rate' => get_field('tax_rate', 'option'),
    'donation_promo_text' => get_field('donation_promo_text', 'option'),
    'shipping_text' => get_field('shipping_text', 'option'),
  ));

  // HandBasket
  wp_enqueue_script( 'simpleStorage-script', $path_to_plugin . '/scripts/js/simpleStorage.js', array('jquery','json2') );
  wp_enqueue_script( 'handbasket-scripts', $path_to_plugin . '/lib/HandBasket/handbasket.js', array('jquery','json2'), true );
  wp_localize_script( 'handbasket-scripts', 'handbasket_scripts', array(
    'ajaxurl' => admin_url('admin-ajax.php',$protocol),
    'nonce' => wp_create_nonce('handbasket_scripts_nonce')
  ));

  // Stripe
  if ( get_field( 'stripe_api_mode', 'option' ) === true ) {
    $stripe_publishable_api_key = get_field( 'stripe_live_publishable_api_key', 'option' ); // Use Test API Key for Stripe Processing
  } else {
    $stripe_publishable_api_key = get_field( 'stripe_test_publishable_api_key', 'option' ); // Use Test API Key for Stripe Processing
  }
  wp_enqueue_script( 'stripejs-script', $path_to_plugin . '/scripts/js/stripe/stripejs-v2.js', array(), false, true );
  wp_enqueue_script( 'stripe-processing', $path_to_plugin . '/scripts/js/stripe/payments.js', array('jquery'));
  wp_localize_script('stripe-processing', 'stripe_vars', array(
    'publishable_key' => $stripe_publishable_api_key,
  ));

  // // PayPal
  // wp_enqueue_script('paypal-scripts', get_stylesheet_directory_uri().'/lib/PayPal/payments/paypal-payment-scripts.js', array('jquery','json2'), true);
  // wp_localize_script('paypal-scripts', 'paypal_data', array(
  //     'ajaxurl' => admin_url('admin-ajax.php',$protocol),
  //     'nonce' => wp_create_nonce('paypal_nonce')
  // ));
}
add_action( 'wp_enqueue_scripts', 'tomarket_enqueue_scripts' );

// # ACF: Settings & Fields
if ( function_exists( 'acf_add_options_sub_page' ) && function_exists( 'get_field' ) ) {
  function tomarket_options_config() {
    acf_set_options_page_menu( __('Shop Settings') ); // Changes menu name
    acf_set_options_page_title( __('Our Shop Settings') ); // Changes option/setting page title
    // acf_set_options_page_capability( 'manage_options' );
  }
  add_action( 'after_setup_theme', 'tomarket_options_config' );
}

// # Utilities
require_once( __DIR__ . '/lib/ToMarket/Util.php');

// # Stripe

// # PayPal
// require_once( get_stylesheet_directory() . '/lib/PayPal/payments/method-paypal.php' );

// # EasyPost
//require_once( get_stylesheet_directory() . '/lib/easypost.php' );








/**
 * Funnnnctioooonssss
 */

/**
 * Hand Basket Functions
 */

/**
 * Checkout Functions
 */
function render_checkout() {
  global $path_to_plugin;
  $checkout = '

  <div class="modal fade in" id="checkout" tabindex="-1" role="dialog" aria-labelledby="checkout" aria-hidden="true" data-backdrop="static">
    <div class="checkout-header">
      <a class="site-title white" href="'.home_url( '/' ).'" title="'. esc_attr( get_bloginfo( 'name', 'display' ) ) .'" rel="home">'.get_bloginfo( 'name' ).'</a>
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>

      <ul class="checkout-tabs">
        <li>
          <a href="#basic" class="checkout-tab current" data-target="1">
            <span class="step-number">1</span>
            <i class="fa fa-home"></i>
            <span class="step-name">Basic Information</span>
          </a>
        </li>
        <li>
          <a href="#payment" class="checkout-tab" data-target="2">
            <span class="step-number">2</span>
            <i class="fa fa-credit-card"></i>
            <span class="step-name">Payment</span>
          </a>
        </li>
        <li>
          <a href="#review" class="checkout-tab" data-target="3">
            <span class="step-number">3</span>
            <i class="fa fa-check-square-o"></i>
            <span class="step-name">Payment</span>
          </a>
        </li>
        <li>
          <a href="#message" class="checkout-tab" data-target="4">
            <span class="step-number">3</span>
            <i class="fa fa-check-square-o"></i>
            <span class="step-name">Payment</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Step 1: Basic Information -->
    <div id="basic" class="checkout-step" data-step="1">
      <div class="modal-header">
        <h3 class="checkout-step-title">'. __('Basic Information','litton_bags') .'</h3>
      </div>
      <div class="modal-body">

        <fieldset class="form-row checkoutBasic basic-info" id="basic-info" >
          <!-- <legend>Basic Information</legend> -->
          <div class="input-group">
            <label>'. __('Full Name', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-user"></i></div>
            <input type="text" class="form-control" size="20" autocomplete="off" name="customer-name" placeholder="Your Name"  />
          </div>
          <div class="input-group">
            <label>'. __('Email Address', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
            <input type="text"  class="form-control" size="20" autocomplete="off" class="email" name="customer-email" placeholder="Email Address" />
          </div>
        </fieldset>

        <div class="form-row checkoutBasic basic-info" id="addr-info">
          <legend>Billing Address</legend>
          <div class="input-group">
            <label>'. __('Address Line 1', 'litton_bags') .'</label>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-line1" class="address" placeholder="Address Line 1" />
          </div>
          <div class="input-group">
            <label>'. __('Address Line 2', 'litton_bags') .'</label>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-line2" class="optional address" placeholder="Address Line 2" />
          </div>
          <div class="input-group">
            <label>'. __('City', 'litton_bags') .'</label>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-city" placeholder="City" />

            <label>'. __('Zip Code', 'litton_bags') .'</label>
            <input type="text" size="20" autocomplete="off" class="zip-code" data-stripe="address-zip" placeholder="Zipcode" />

            <label>'. __('State', 'litton_bags') .'</label>
            <input type="text" size="5" autocomplete="off" class="state" data-stripe="address-state" placeholder="State" />

            <label>'. __('Country', 'litton_bags') .'</label>
            <input type="text" size="7" autocomplete="off" class="country" data-stripe="address-country" placeholder="USA" />
          </div>

          <p class="formHelperText">Currently, we are only shipping to the United States on our website. Please email us for international purchases.</p>
        </div>

        <input id="shippingIsDifferent" type="checkbox" />
        <span class="formHelperText">My shipping address is different from my billing address.</span>

        <div class="form-row basic-info shipping-info hide" id="addr-info-shipping">
          <legend>Shipping Address</legend>
          <label>'. __('Address Line 1', 'litton_bags') .'</label>
          <input type="text" size="20" autocomplete="off" data-easypost="shipping-address-line1" name="shipping-address-line1" class="address" />
          <label>'. __('Address Line 2', 'litton_bags') .'</label>
          <input type="text" size="20" autocomplete="off" data-easypost="shipping-address-line2" name="shipping-address-line2" class="address optional" />
          <div class="form-row-single">
          <div>
          <label>'. __('City', 'litton_bags') .'</label>
          <input type="text" size="20" autocomplete="off" data-easypost="shipping-address-city" name="shipping-address-city" />
          </div>
          <div>
          <label>'. __('State', 'litton_bags') .'</label>
          <input type="text" size="20" autocomplete="off" class="state" data-easypost="shipping-address-state" name="shipping-address-state" />
          </div>
          <div>
          <label>'. __('Zip Code', 'litton_bags') .'</label>
          <input type="text" size="20" autocomplete="off" class="zip-code" data-easypost="shipping-address-zip" name="shipping-address-zip" />
          </div>
          <div>
          <label>'. __('Country', 'litton_bags') .'</label>
          <input type="text" size="20" autocomplete="off" class="country" data-easypost="shipping-address-country" name="shipping-address-country" />
          </div>
          </div>
        </div>

      </div><!-- .modal-body -->
      <div class="checkout-footer">
        <a href="#" class="mint">Next</a>
      </div>
    </div><!-- end step 1 -->

    <!-- Step Two: Payment Info -->
    <div id="payment" class="checkout-step" data-step="2">
      <div class="modal-header">
        <h3 class="checkout-step-title">'. __('Payment Information','litton_bags') .'</h3>
      </div>
      <div class="modal-body">
        <form action="" method="POST" id="stripe-payment-form">
          <legend>Card Information</legend>
          <ul class="cc-icons">
            <li class="cc-icon visa"></li>
            <li class="cc-icon mastercard"></li>
            <li class="cc-icon amex"></li>
            <li class="cc-icon discover"></li>
            <li class="cc-icon jcb"></li>
          </ul>
          <div class="input-group">
            <label>'. __('Name on Card', 'litton_bags') .'</label>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="name" placeholder="Name on card" />
          </div>
          <div class="input-group">
            <label>'. __('Card Number', 'litton_bags') .'</label>
            <input type="text" class="form-control card-number" size="20" autocomplete="off" data-stripe="number" placeholder="Card Number" />
          </div>
          <div class="input-group">
            <label>'. __('CVC', 'litton_bags') .'</label>
            <input type="text" class="form-control card-cvc" size="4" autocomplete="off" data-stripe="cvc" placeholder="CVC" />
            <label>'. __('Expiration (MM/YYYY)', 'litton_bags') .'</label>
            <input type="text" class="card-exp-month" size="2" data-stripe="exp-month" data-numeric placeholder="MM" />
            <span> / </span>
            <input type="text" class="card-exp-year" size="4" data-stripe="exp-year" data-numeric placeholder="YYYY" />
          </div>

          <!-- Additonal/Hidden Form Parameters -->
          <input type="hidden" name="redirect" value="'. get_permalink() .'"/>
          <input type="hidden" name="form-type" value="stripe-payment" />
          '.wp_nonce_field( "stripe-payment" ).'
        </form>
      </div><!-- modal-body -->
      <div class="checkout-footer">
        <a href="#" class="mint">Next</a>
        <!-- <a class="paypal-checkout" href="javascript:void(0);" title="Checkout via Paypal instead." data-payment-method="paypal"><img src="'.get_stylesheet_directory_uri().'/lib/ToMarket/media/paypal-checkout-icon.png" alt="Checkout via Paypal instead." /></a> -->
      </div>
    </div><!-- end step 2 -->

    <!-- Step Three: Review -->
    <div id="review" class="checkout-step" data-step="3">
      <div class="modal-header">
        <h3 class="checkout-step-title">'. __('Review','litton_bags') .'</h3>
      </div>
      <div class="modal-body">
        <!-- <div class="basic-overview">basic</div> -->
        <!-- <div class="payment-overview">overview</div> -->
      </div>
      <div class="checkout-footer">
        <a class="watermelon" href="#" data-action="checkout">Checkout</a>
      </div>
      <div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Processing Payment</h4></div></div>
    </div>

    <!-- Step Four: Message Screen -->
    <div id="message" class="checkout-step" data-step="4">
      <div class="modal-header">

        <!-- Successful Payment -->
        <h3 class="checkout-step-title successful-payment">'. __('Successful Payment','litton_bags') .'</h3>

      </div>
      <div class="modal-body">

        <!-- Successful Payment -->
        <div class="successful-payment">
          <img src="'.$path_to_plugin.'/media/payment-success.jpg" />
          <h4>You have successfully made a payment. An email with your shipping label and confirmation has been sent to you.</h4>
        </div>

      </div>
      <div class="checkout-footer">
      </div>
      <div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Processing Payment</h4></div></div>
    </div>

  </div>

  ';
  echo $checkout;
}
add_action('wp_footer','render_checkout');


function stripe_api_key( $type ) {
  if ( $type === 'secret' ) {
    if ( get_field( 'stripe_api_mode', 'option' ) === true ) {
      $stripe_api_key = get_field( 'stripe_live_secret_api_key', 'option' );
    } else {
      $stripe_api_key = get_field( 'stripe_test_secret_api_key', 'option' );
    }
    return $stripe_api_key;
  }
}

// Verify Checkout Charge Amount
// @desc  A helper function to ensure no one is affecting prices
//        at checkout.
// @param basketitems object An object containing basket items and qty
//        as members.
function verify_checkout_charge_amount( $basketitems ) {

}

/**
 * Process Stripe Payment
 * Listen for Stripe: Payment requests
 */
function process_stripe_payment() {
  // Verify the client request is legit upon Stripe payment form submission
  if ( isset( $_POST['form-type'] ) && $_POST['form-type'] === 'stripe-payment' && wp_verify_nonce( $_REQUEST['_wpnonce'], 'stripe-payment' ) ) {
    // Load Stripe Client Library (PHP)
    require_once( __DIR__ . '/lib/Stripe/lib/Stripe.php');
    // # Present Secret API Key
    Stripe::setApiKey( stripe_api_key('secret') );
    // # Retrieve Payment Token from Submitted Form
    $token = $_POST['stripeToken'];

    // # Calculate product costs here

    // # Attempt to charge the Card
    try {
      $charge = Stripe_Charge::create(array(
        "amount" => 38639, // amount in cents, again
        "currency" => "usd",
        "card" => $token,
        "description" => "payinguser@example.com")
      );
      // Charge successful!
      // 1. Send customer email
      // 2. Send litton email

      $redirect  = add_query_arg( array('checkout' => 'yes', 'result' => 'success'), $_POST['redirect']);

    } catch(Stripe_CardError $e) {
      error_log('card declined');
      // The card has been declined
    }
    error_log('asdf');

    // # Display appropriate message
    if ( isset( $redirect ) ) {
      wp_redirect( $redirect );
      exit;
    }
  } // end if
}
add_action('init', 'process_stripe_payment');


// /**
//  *	Verify address using EasyPost API
//  */
// function easypost_verify_address() {
//   /**
//    *	Setup
//    */
//   do_action('init');
//   global $wpdb, $post, $easypost_options;
//
//   // Nonce check
//   $nonce = $_REQUEST['nonce'];
//   if ( !wp_verify_nonce($nonce, 'handbasket_scripts_nonce')) die(__('Busted.') );
//
//   // Check some random variables to see if data is being sent...
//   if ( isset( $_REQUEST['name'] ) && isset( $_REQUEST['streetOne'] ) && isset( $_REQUEST['zip'] ) ) :
//     $name = strip_tags( trim( $_REQUEST['name'] ) );
//     $streetOne = strip_tags( trim( $_REQUEST['streetOne'] ) );
//     $streetTwo = strip_tags( trim( $_REQUEST['streetTwo'] ) );
//     $city = strip_tags( trim( $_REQUEST['city'] ) );
//     $state = strip_tags( trim( $_REQUEST['state'] ) );
//     $zip = strip_tags( trim( $_REQUEST['zip'] ) );
//   endif;
//
//   /**
//    * Verify Address
//    */
//   $errors = false;
//   $success = false;
//
//   // A. Establish EasyPost API keys & load library
//   require_once( get_stylesheet_directory() . '/lib/easypost.php' );
//   if ( isset($easypost_options['test_mode']) && $easypost_options['test_mode'] ) {
//     \EasyPost\EasyPost::setApiKey( $easypost_options['test_secret_key'] );
//   } else {
//     \EasyPost\EasyPost::setApiKey( $easypost_options['live_secret_key'] );
//   }
//
//   try {
//
//       // B. Retrieve this customer's mailing address...
//       $to_address = \EasyPost\Address::create( array(
//         "name"    => $name,
//         "street1" => $streetOne,
//         "street2" => $streetTwo,
//         "city"    => $city,
//         "state"   => $state,
//         "zip"     => $zip,
//       ));
//
//       // C. Attempt to verify shipping address
//       $verfied_address = $to_address->verify();
//       $success = true;
//
//   } catch ( Exception $e ) {
//     // Error Notes:
//     // bad State = Invalid State Code.
//     // bad City = Invalid City.
//     // bad address = Address Not Found.
//     $easyPostFailStatus  = $e->getHttpStatus();
//     $easyPostFailMessage = $e->getMessage();
//     $errors = strval( $easyPostFailMessage );
//     error_log($easyPostFailMessage);
//   }
//
//   /*
//    * Build the response...
//    */
//   $response = json_encode(array(
//     'success' => $success,
//     'errors' => $errors,
//   ));
//
//   // Construct and send the response
//   header("content-type: application/json");
//   echo $response;
//   exit;
// }
// add_action('wp_ajax_nopriv_easypost_verify_address', 'easypost_verify_address');
// add_action('wp_ajax_easypost_verify_address', 'easypost_verify_address');
//
// //Run Ajax calls even if user is logged in
// if ( isset($_REQUEST['action']) && ($_REQUEST['action']=='easypost_verify_address') ):
//   do_action( 'wp_ajax_' . $_REQUEST['action'] );
//   do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
// endif;
//
//
// /**
//  *	Refresh/Build Hand Basket
//  */
// function refresh_handbasket() {
//
//   do_action('init');
//   global $wpdb, $post, $stripe_options;
//
//   // Nonce check
//   $nonce = $_REQUEST['nonce'];
//   if (!wp_verify_nonce($nonce, 'handbasket_scripts_nonce')) die(__('Busted.'));
//
//   // http://www.php.net/manual/en/function.money-format.php
//   setlocale(LC_MONETARY, 'en_US');
//
//   // Grab all post IDs that should be in basket
//   if ( isset($_REQUEST['products']) ) {
//     $products = $_REQUEST['products'];
//   }
//
//   // Set subtotal of all product costs combined
//   $grandSubtotal = 0;
//
//   $html = "";
//   $success = false;
//   $productDescription = ""; // Build annotated description to pass to Stripe pipe(|) separated
//   $basketDescription = ""; // Build a description of the basket containing the product and ID (comma and pipe separated)
//
//   if ( isset($products) ) {
//     foreach ( $products as $product ) {
//
//       /**
//        *	Let's build the Hand Basket!
//        */
//       $itemID = ''; // Grab the product ID for use outside this loop
//       $itemQty = ''; // Grab the product Qty for use outside this loop
//
//       /**
//        *	Populate basket title and legend
//        */
//
//       /**
//        *	Get Product Name/Post Data
//        */
//       $postID = $product['postID'];
//       $productsInBasket = new WP_Query(array(
//         'p' => $postID,
//         'post_type' => 'products',
//       ));
//       while($productsInBasket->have_posts()) : $productsInBasket->the_post();
//         $currentPostID 			= $post->ID;
//         $itemID 						= $currentPostID;
//         $itemTitle 					= get_the_title();
//         $basketDescription    = $basketDescription . $currentPostID . ','; // Add ID to basket description
//         $productDescription = $productDescription . $currentPostID . ','; // Add ID to product description
//         $productDescription = $productDescription . get_the_title() . ','; // Add Title to product description
//       endwhile;
//       wp_reset_postdata();
//
//       /**
//        *	Get Product Options
//        */
//
//       // # Product Color
//       $itemColor = $product['color'];
//       if ( $itemColor == 'none' || $itemColor == 'undefined' ) {
//         $itemColor = 'n/a';
//       }
//       $basketDescription 		= $basketDescription . $itemColor . ',';
//       $productDescription = $productDescription . $itemColor . ','; // Add Color to product description
//
//       // # Product Quantity
//       $itemQty = $product['qty'];
//       $basketDescription		= $basketDescription . $itemQty; // Add quantity to basket description
//       $productDescription = $productDescription . $itemQty; // Add Quantity to product description
//
//       // # Product Thumbnail
//       $optionPreview = ''; // Clear variable during loop
//       if ( have_rows( 'product_options', $postID ) ) :
//       while ( have_rows( 'product_options', $postID ) ) : the_row();
//         if ( get_sub_field('product_color_name') == $itemColor ) {
//           $optionPreview = get_sub_field('product_checkout_image_preview');
//         }
//       endwhile;
//       endif;
//
//       /*
//        * Generate User-facing totals
//        */
//       $optionPrice = ''; // Clear variable during loop
//       $productPrice = get_field( 'product_price' );
//       // Iterate through options to find the current options selected (looking for option based on color)
//       if ( have_rows( 'product_options', $postID ) ) :
//       while ( have_rows( 'product_options', $postID ) ) : the_row();
//         if ( get_sub_field('product_color_name') == $itemColor ) {
//           $optionPrice = get_sub_field('product_option_price');
//         }
//       endwhile;
//       endif;
//
//       // If cost of the option differs from the product price, set the product cost to the option amount
//       if ( ( $optionPrice != $productPrice ) && ( $optionPrice != 0 ) ) {
//         $actualPrice = $optionPrice;
//       } else {
//         $actualPrice = $productPrice;
//       }
//
//       // Generate Individual Product Subtotal
//       $individualProductSubtotal = $actualPrice * $itemQty;
//
//       // Add individual product subtotal to the grand subtotal
//       $grandSubtotal += $individualProductSubtotal;
//
//       /**
//        *	Popover Output
//        */
//       $html .= '<div class="handbasket-product" data-jStorage-key="'.$product['key'].'">';
//       $html .= 	'<span class="product-preview"><img src="'.$optionPreview.'" /></span>';
//       $html .=	'<div class="product-description">';
//       $html .= 		'<span class="product-title">'.$itemTitle.'</span>';
//       $html .= 		'<span class="product-color" data-product-color="'.$itemColor.'"><span class="product-meta-title">Color: </span>'.$itemColor.'</span>';
//       $html .= 	'</div>';
//       $html .= 	'<span class="product-price" data-product-price="'.$actualPrice.'">'.format_money($actualPrice,'US').'</span>';
//       $html .= 	'<span class="product-qty" data-product-qty="'.$itemQty.'">'.$itemQty.'</span>';
//       $html .= '<span class="product-subtotal">'.format_money($individualProductSubtotal,'US').'</span>';
//
//       /*
//        * Cleanup
//        */
//
//       // Generate a pipe between products & basket descriptors; never at the beginning or the end
//       if ( $product != end( $products ) ) {
//         $basketDescription = $basketDescription . '|';
//         $productDescription = $productDescription . '|';
//       }
//
//       // Create delete basket item key
//       $html .= '<a href="javascript:void(0);" class="btn remove">x</a>';
//       $html .= '</div>';
//
//     } // foreach product
//
//     /*
//      * Let's build the Review Totals!
//      */
//
//     // Generate user readable versions of Totals
//     // Subtotals
//     //$subtotal_productPriceInDollars = money_format('%n', $grandSubtotal/100); // in 'dollars'
//
//     // Tax
//     $currenttaxrate = $stripe_options['tax_rate'];
//     $tax = round($grandSubtotal * $currenttaxrate);
//     //$tax_productPriceInDollars = money_format('%n', $tax/100); // in 'dollars'
//
//     // Grand
//     $grandTotal = intval($grandSubtotal + $tax);
//     // $grand_productPriceInDollars = money_format('%n', $grandTotal/100); // in 'dollars'
//
//     // Shipping information
//     $shippingInfo = 'Free shipping for bags shipped within the U.S. offer valid on bags purchased from 192.168.10.40 only. Product ships from our warehouse within 1-2 business days via FedEx Home Delivery from Honolulu, Hawaii. We do not ship to PO boxes, please provide a physical address. Signature required upon delivery.';
//
//     // Display Subtotal, Add Tax/Fees/Whatever & show Grand Total
//     $html .= '<div class="checkout-totals">';
//     $html .= '<div class="subtotal"><span class="total-title">Subtotal: </span><span class="line-item-cost">'.format_money($grandSubtotal,'US').'</span></div>';
//     $html .= '<div class="auxfees"><span class="total-title">Tax ('.round((float)$currenttaxrate * 100, 3).'%): </span><span class="line-item-cost">'.format_money($tax,'US').'</span></div>';
//     $html .= '<div class="auxfees"><span class="total-title">Shipping: </span><span class="line-item-cost">Free Domestic Shipping<a class="shipping-popover-trigger" data-toggle="tooltip" title="'.$shippingInfo.'" href="javascript:void(0);" ><i class="fa fa-info-circle"></i></a></span></div>';
//     $html .= '<div class="total"><span class="total-title">Total: </span><span class="line-item-cost">'.format_money($grandTotal,'US').'</span></div>';
//     $html .= '</div>';
//
//     /**
//      *	Generate checkout button as well as other promo text
//      */
//
//     $html .= '<hr />';
//     $html .= '<span class="donation-promo-text">A portion of the profits donated to P&G PUR packets to provide safe drinking water around the world.</span>';
//     $html .= '<a class="checkout">Checkout</a>';
//
//   } // If products are being set
//   /*
//    * Build the response...
//    */
//   $success = true;
//   $response = json_encode( array(
//     'success' 				=> $success,
//     'html' 						=> $html,
//     'desc' 						=> $productDescription,
//     'basketdescription' => $basketDescription
//   ));
//
//   // Construct and send the response
//   header("content-type: application/json");
//   echo $response;
//   exit;
// }
// add_action('wp_ajax_nopriv_refresh_handbasket', 'refresh_handbasket');
// add_action('wp_ajax_refresh_handbasket', 'refresh_handbasket');
//
// //Run Ajax calls even if user is logged in
// if(isset($_REQUEST['action']) && ($_REQUEST['action']=='refresh_handbasket')):
//   do_action( 'wp_ajax_' . $_REQUEST['action'] );
//   do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
// endif;
//
// function render_handbasket() {
//
//   /*
//    * "Checkout" Modal
//    */
//   echo '<div id="checkoutModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">';
//   echo 	'<div class="container">';
//   echo 		'<div class="modal-meta">';
//   echo 			'<a class="site-title" href="'.home_url( '/' ).'" title="'. esc_attr( get_bloginfo( 'name', 'display' ) ) .'" rel="home">'.get_bloginfo( 'name' ).'</a>';
//   echo  		'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>';
//   echo 		'</div>'; // .modal-meta
//

//   echo 	'<div class="checkoutBasicAndPay hide">';
//     // "STRIPE Variables
//     // $productPrice = get_field('product_price'); // in 'cents'
//     // $productPriceInDollars = $productPrice/100; // in 'dollars'
//     // $english_notation = number_format($productPriceInDollars,2,'.',''); // in eng notation 'dollars'
//
//     if( isset($_GET['payment']) && $_GET['payment'] == 'paid') {
//       echo '<p class="success">' . __('Thank you for your payment.', 'litton_bags') . '</p>';
//     } else {
//
//       // "Stripe": Basic/Payment Form
//       echo '<form action="" method="POST" id="stripe-payment-form">';
//
//       // 		FORM ERRORS
//       echo '<div class="payment-errors alert hide"></div>';
//
//       /**
//        *	B.1. Basic Info Collection
//        */
//       // 		PERSONAL INFO
//       echo 	'<div class="form-row checkoutBasic basic-info" id="basic-info" >';
//       echo 	'<legend>Basic Information</legend>';
//       echo 		'<label>'. __('Full Name', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" name="customer-name" />';
//       echo 		'<label>'. __('Email Address', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" class="email" name="email" />'; // ARE WE DOING THIS CORRECTLY?!
//       echo 	'</div>';
//
//       //		CC ADDRESS COLLECTION
//       echo 	'<div class="form-row checkoutBasic basic-info" id="addr-info">';
//       echo 		'<legend>Billing Address</legend>';
//       echo 		'<label>'. __('Address Line 1', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" data-stripe="address-line1" class="address" />';
//       echo 		'<label>'. __('Address Line 2', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" data-stripe="address-line2" class="optional address" />';
//       echo  	'<div class="form-row-single">';
//       echo 			'<div>';
//       echo 				'<label>'. __('City', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" data-stripe="address-city" />';
//       echo 			'</div>';
//       echo 			'<div>';
//       echo 				'<label>'. __('Zip Code', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" class="zip-code" data-stripe="address-zip" />';
//       echo 			'</div>';
//       echo 			'<div>';
//       echo 				'<label>'. __('State', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" class="state" data-stripe="address-state" />';
//       echo 			'</div>';
//       echo 			'<div>';
//       echo 				'<label>'. __('Country', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" class="country" data-stripe="address-country" />';
//       echo 			'</div>';
//       echo 		'</div>'; // .form-row-single
//
//       echo   	'<span class="formHelperText">Currently, we are only shipping to the United States on our website. Please email us for international purchases.</span>';
//       echo 		'<br />';
//       echo 		'<input id="shippingIsDifferent" type="checkbox" />';
//       echo   	'<span class="formHelperText">My shipping address is different from my billing address.</span>';
//       echo 	'</div>';
//
//       //		SHIPPING ADDRESS COLLECTION
//       echo 	'<div class="form-row basic-info shippingInfo hide" id="addr-info-shipping">';
//       echo 		'<legend>Shipping Address</legend>';
//       echo 		'<label>'. __('Address Line 1', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" data-easypost="shipping-address-line1" name="shipping-address-line1" class="address" />';
//       echo 		'<label>'. __('Address Line 2', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" data-easypost="shipping-address-line2" name="shipping-address-line2" class="address optional" />';
//       echo  	'<div class="form-row-single">';
//       echo 			'<div>';
//       echo 				'<label>'. __('City', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" data-easypost="shipping-address-city" name="shipping-address-city" />';
//       echo 			'</div>';
//       echo 			'<div>';
//       echo 				'<label>'. __('State', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" class="state" data-easypost="shipping-address-state" name="shipping-address-state" />';
//       echo 			'</div>';
//       echo 			'<div>';
//       echo 				'<label>'. __('Zip Code', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" class="zip-code" data-easypost="shipping-address-zip" name="shipping-address-zip" />';
//       echo 			'</div>';
//       echo 			'<div>';
//       echo 				'<label>'. __('Country', 'litton_bags') .'</label>';
//       echo 				'<input type="text" size="20" autocomplete="off" class="country" data-easypost="shipping-address-country" name="shipping-address-country" />';
//       echo 			'</div>';
//       echo 		'</div>'; // .form-row-single
//       echo 	'</div>';
//
//       // 		CARD NUMBER
//       echo 	'<div class="form-row checkoutPay payment-info hide" id="cc-info">';
//       echo 		'5% of your purchase will go to the charity WakaWaka Lights.';
//       echo 		'<legend>Card Information</legend>';
//       echo 		'<div class="cc-icons">';
//       echo 			'<div class="cc-icon visa"></div>';
//       echo 			'<div class="cc-icon mastercard"></div>';
//       echo 			'<div class="cc-icon amex"></div>';
//       echo 			'<div class="cc-icon discover"></div>';
//       echo 			'<div class="cc-icon jcb"></div>';
//       echo 		'</div>';
//       echo 		'<label>'. __('Name on Card', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" data-stripe="name" />';
//       echo 		'<label>'. __('Card Number', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="20" autocomplete="off" class="cc-num" data-stripe="number" />';
//       echo 		'<label>'. __('CVC', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="4" autocomplete="off" class="cc-cvc" data-stripe="cvc" />';
//       echo 		'<label>'. __('Expiration (MM/YYYY)', 'litton_bags') .'</label>';
//       echo 		'<input type="text" size="2" data-stripe="exp-month" class="cc-exp-month" data-numeric />';
//       echo 		'<span> / </span>';
//       echo 		'<input type="text" size="4" data-stripe="exp-year" class="cc-exp-year" data-numeric />';
//       echo 	'</div>';
//
//       //		WORDPRESS DATA VALUES (NO SENSITIVE FORMS BELOW THIS LINE!)
//       echo 	'<input type="hidden" name="action" value="stripe"/>';
//       echo 	'<input type="hidden" name="redirect" value="'. get_permalink() .'"/>';
//       echo 	'<input type="hidden" name="stripe_nonce" value="'. wp_create_nonce('stripe-nonce').'"/>';
//       echo 	'<input type="hidden" name="description" value=""/>';
//       echo 	'<input type="hidden" name="basketdescription" value=""/>';
//       echo 	'<button type="submit hidden" class="hide" id="stripe-submit">'. __('Submit Payment', 'litton_bags') .'</button>';
//       echo '</form>';
//     }
//   echo  '</div>'; // Pay
//
//   // Services Used (i.e. Stripe & EasyPost)
//   echo '<div class="services-used-container">';
//   //echo 	'<div class="services-used stripe"><a href="http://stripe.com" target="_blank"><i class="stripe-icon"></i></a></div>';
//   echo 	'<div class="services-used support">Having trouble with your checkout? <a href="mailto:support@littonbags.com">Contact our support team.</a></div>';
//   echo '</div>';
//
//   echo '</div>'; // .container
//
//   // Loading Overlay
//   echo '<div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Preparing to go to PayPal</h4></div></div>';
//
//   echo '</div>'; // .modal (#checkout)
//
// }



?>
