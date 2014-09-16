<?php

/**
 * To Market
 * A simple shopping + checkout solution for WP.
 * @url https://github.com/jkhedani/ToMarket
 * @author jkhedani
 */

// # Store path to 'plugin' separate as this 'plugin' currently lives in the
// theme. Note there is no trailing slash.
$path_to_plugin_uri = get_stylesheet_directory_uri() . '/lib/ToMarket';

/**
 * Enqueue Scripts, Libraries & Settings
 * CSS & JS
 */
function tomarket_enqueue_scripts() {

  global $path_to_plugin_uri;
  // Assign the appropriate protocol if necessary.
  $protocol = 'http:';
  if ( !empty($_SERVER['HTTPS']) ) $protocol = 'https:';

  // Bootstrap Scripts & Styles
  wp_enqueue_style( 'bootstrap-styles', $path_to_plugin_uri . '/assets/css/bootstrap/bootstrap.css' );
  //wp_enqueue_style( 'bootstrap-forms-styles', $path_to_plugin_uri . '/assets/css/bootstrap/forms.min.css' );
  wp_enqueue_script( 'bootstrap-transition-script', $path_to_plugin_uri .'/assets/js/bootstrap/transition.js', array(), false, true );
  wp_enqueue_script( 'bootstrap-modal-script', $path_to_plugin_uri .'/assets/js/bootstrap/modal.js', array(), false, true );
  wp_enqueue_script( 'bootstrap-tooltip-script', $path_to_plugin_uri .'/assets/js/bootstrap/tooltip.js', array(), false, true );
  wp_enqueue_script( 'bootstrap-popover-script', $path_to_plugin_uri .'/assets/js/bootstrap/popover.js', array(), false, true );

  // HandBasket
  wp_enqueue_script( 'simpleStorage-script', $path_to_plugin_uri . '/assets/js/simpleStorage.js', array('jquery','json2') );
  // Stripe
  if ( get_field( 'stripe_api_mode', 'option' ) === true ) {
    $stripe_publishable_api_key = get_field( 'stripe_live_publishable_api_key', 'option' ); // Use Test API Key for Stripe Processing
  } else {
    $stripe_publishable_api_key = get_field( 'stripe_test_publishable_api_key', 'option' ); // Use Test API Key for Stripe Processing
  }
  wp_enqueue_script( 'stripejs-script', $path_to_plugin_uri . '/assets/js/stripe/stripejs-v2.js', array(), false, true );

  // To Market Scripts & Styles
  // Note: Currently all scripts share the same ajax nonce.
  wp_enqueue_style( 'to-market-styles', $path_to_plugin_uri . '/assets/css/tomarket.css' );
  wp_enqueue_script( 'to-market-scripts', $path_to_plugin_uri . '/assets/js/tomarket.js', array('jquery','json2') );
  wp_localize_script( 'to-market-scripts', 'to_market_scripts', array(
    'ajaxurl' => admin_url('admin-ajax.php',$protocol),
    'nonce' => wp_create_nonce('to_market_scripts_nonce'),
    'tax_rate' => get_field('tax_rate', 'option'),
    'donation_promo_text' => get_field('donation_promo_text', 'option'),
    'shipping_text' => get_field('shipping_text', 'option'),
    'stripe_publishable_key' => $stripe_publishable_api_key,
  ));

  // wp_enqueue_script( 'handbasket-scripts', $path_to_plugin_uri . '/lib/HandBasket/handbasket.js', array('jquery','json2'), true );
  // wp_localize_script( 'handbasket-scripts', 'handbasket_scripts', array(
  //   'ajaxurl' => admin_url('admin-ajax.php',$protocol),
  //   'nonce' => wp_create_nonce('handbasket_scripts_nonce')
  // ));


  // wp_enqueue_script( 'stripe-processing', $path_to_plugin_uri . '/scripts/js/stripe/payments.js', array('jquery'));
  // wp_localize_script('stripe-processing', 'stripe_vars', array(
  //   'publishable_key' => $stripe_publishable_api_key,
  // ));

  // EasyPost
  // wp_enqueue_script( 'easypost-scripts', $path_to_plugin_uri . '/scripts/js/easypost/easypost.js', array('jquery'));
  // wp_localize_script('easypost-scripts', 'easypost_vars', array(
  //   'nonce' => wp_create_nonce('easypost_scripts_nonce')
  // ));

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
require_once( dirname( __FILE__ ) . '/lib/ToMarket/Util.php');

// # PayPal
// require_once( get_stylesheet_directory() . '/lib/PayPal/payments/method-paypal.php' );

/**
 * Hand Basket Functions
 */

/**
 * Checkout Functions
 */
function render_checkout() {
  global $path_to_plugin_uri;
  $checkout = '

  <div class="modal fade" id="checkout" tabindex="-1" role="dialog" aria-labelledby="checkout" aria-hidden="true">
    <div class="checkout-header">
      <a class="site-title white" href="#checkout" title="'. esc_attr( get_bloginfo( 'name', 'display' ) ) .'" rel="home">'.get_bloginfo( 'name' ).'</a>
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
        <div class="alert-message error"><i class="fa fa-bullhorn"></i><span>Some alert message.</span></div>
      </div>
      <div class="modal-body">

        <form action="" method="POST" id="basic-info" >
          <!-- <legend>Basic Information</legend> -->
          <div class="input-group">
            <label>'. __('Full Name', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-user"></i></div>
            <input type="text" class="form-control" size="20" autocomplete="off" name="customer-name" placeholder="Your Name"  />
          </div>
          <div class="input-group">
            <label>'. __('Email Address', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-at"></i></div>
            <input type="text"  class="customer-email form-control" size="20" autocomplete="off" class="email" name="customer-email" placeholder="Email Address" />
          </div>
          <div class="input-group">
            <label>'. __('Phone Number', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-phone"></i></div>
            <input type="text"  class="customer-phone form-control" size="20" autocomplete="off" class="phone" name="customer-phone" placeholder="808-123-4567" />
          </div>
        </form>

        <form action="" method="POST" id="billing-address">
          <legend>Billing Address</legend>
          <div class="input-group">
            <label>'. __('Address Line 1', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-line1" data-shipping-target="shipping-address-line1" class="address" placeholder="Address Line 1" />
          </div>
          <div class="input-group">
            <label>'. __('Address Line 2', 'litton_bags') .'</label>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-line2" data-shipping-target="shipping-address-line2" class="address" placeholder="Address Line 2" />
          </div>
          <div class="input-row">
            <div class="input-group city">
              <label>'. __('City', 'litton_bags') .'</label>
              <div class="input-group-addon"><i class="fa fa-building"></i></div>
              <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-city" data-shipping-target="shipping-address-city" class="address" placeholder="City" />
            </div>
            <div class="input-group zip">
              <label>'. __('Zip Code', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="zip-code" data-stripe="address-zip" data-shipping-target="shipping-address-zip" class="address" placeholder="Zipcode" />
            </div>
            <div class="input-group state">
              <label>'. __('State', 'litton_bags') .'</label>
              <input type="text" size="5" autocomplete="off" class="state" data-stripe="address-state" data-shipping-target="shipping-address-state" class="address" placeholder="State" />
            </div>
            <div class="input-group country">
              <label>'. __('Country', 'litton_bags') .'</label>
              <input type="text" size="7" autocomplete="off" class="country" data-stripe="address-country" data-shipping-target="shipping-address-country" class="address" placeholder="USA" />
            </div>
          </div>

          <div class="input-group">
            <input id="show-shipping-address-fields" type="checkbox" />
            <span class="formHelperText">My shipping address is different from my billing address.</span>
          </div>

          <p class="form-helper-text">Currently, we are only shipping to the United States on our website. Please <a href="mailto:support@littonbags.com">email us</a> for international purchases.</p>
        </form>

        <form action="" method="POST" id="shipping-address">
          <legend>Shipping Address</legend>
          <div class="input-group">
            <label>'. __('Address Line 1', 'litton_bags') .'</label>
            <input type="text" size="20" autocomplete="off" data-easypost="shipping-address-line1" name="shipping-address-line1" class="address" placeholder="Address Line 1" />
          </div>
          <div class="input-group">
            <label>'. __('Address Line 2', 'litton_bags') .'</label>
            <input type="text" size="20" autocomplete="off" data-easypost="shipping-address-line2" name="shipping-address-line2" class="address optional" placeholder="Address Line 2" />
          </div>
          <div class="input-row">
            <div class="input-group city">
              <label>'. __('City', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" data-easypost="shipping-address-city" name="shipping-address-city" placeholder="City" />
            </div>
            <div class="input-group state">
              <label>'. __('State', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="state" data-easypost="shipping-address-state" name="shipping-address-state" placeholder="State" />
            </div>
            <div class="input-group zip">
              <label>'. __('Zip Code', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="zip-code" data-easypost="shipping-address-zip" name="shipping-address-zip" placeholder="Zipcode" />
            </div>
            <div class="input-group country">
              <label>'. __('Country', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="country" data-easypost="shipping-address-country" name="shipping-address-country" placeholder="USA" />
            </div>
          </div>
        </form>

      </div><!-- .modal-body -->
      <div class="checkout-footer">
        <a href="#payment" data-target="2" class="checkout-next mint">Next »</a>
      </div>
      <!-- Message Overlay -->
      <div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Validating Address</h4></div></div>
    </div><!-- end step 1 -->

    <!-- Step Two: Payment Info -->
    <div id="payment" class="checkout-step" data-step="2">
      <div class="modal-header">
        <h3 class="checkout-step-title">'. __('Payment Information','litton_bags') .'</h3>
      </div>
      <div class="modal-body">
        <form action="" method="POST" id="stripe-payment-form">
          <!-- <legend>Card Information</legend> -->
          <ul class="cc-icons">
            <li class="cc-icon visa"></li>
            <li class="cc-icon mastercard"></li>
            <li class="cc-icon amex"></li>
            <li class="cc-icon discover"></li>
            <li class="cc-icon jcb"></li>
          </ul>
          <div class="input-group">
            <label>'. __('Name on Card', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-user"></i></div>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="name" placeholder="Name on card" />
          </div>
          <div class="input-group">
            <label>'. __('Card Number', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-credit-card"></i></div>
            <input type="text" class="form-control card-number" size="20" autocomplete="off" data-stripe="number" placeholder="Card Number" />
          </div>
          <div class="input-group">
            <label>'. __('CVC', 'litton_bags') .'</label>
            <input type="text" class="card-cvc" size="4" autocomplete="off" data-stripe="cvc" placeholder="CVC" />
            <label>'. __('Expiration (MM/YYYY)', 'litton_bags') .'</label>
            <input type="text" class="card-exp-month" size="2" data-stripe="exp-month" data-numeric placeholder="MM" />
            <span> / </span>
            <input type="text" class="card-exp-year" size="4" data-stripe="exp-year" data-numeric placeholder="YYYY" />
          </div>

          <hr />

          <p class="form-helper-text"><i class="fa fa-cc-stripe"></i>We proudly use <a href="http://stripe.com">Stripe</a> to securely process your payment information.</p>

          <input type="hidden" name="redirect" value="'. get_permalink() .'"/>
          <input type="hidden" name="form-type" value="stripe-payment" />
          '.wp_nonce_field( "stripe-payment" ).'
        </form>
      </div><!-- modal-body -->
      <div class="checkout-footer">
        <a href="#review" data-target="3" class="checkout-next mint">Next</a>
        <!-- <a class="paypal-checkout" href="javascript:void(0);" title="Checkout via Paypal instead." data-payment-method="paypal"><img src="'.$path_to_plugin_uri.'/media/paypal-checkout-icon.png" alt="Checkout via Paypal instead." /></a> -->
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
      <div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Processing checkout</h4></div></div>
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
          <img src="'.$path_to_plugin_uri.'/media/payment-success.jpg" />
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

// # EasyPost
function set_easypost_api_key() {
  if ( get_field( 'easypost_api_mode', 'option' ) === true ) {
    $stripe_api_key = get_field( 'easypost_live_secret_api_key', 'option' );
  } else {
    $stripe_api_key = get_field( 'easypost_test_secret_api_key', 'option' );
  }
  return $stripe_api_key;
}


// Verify Checkout Charge Amount
// @desc  A helper function to ensure no one is affecting prices
//        at checkout.
// @param basketitems object An object containing basket items and qty
//        as members.
function verify_checkout_charge_amount( $basketitems ) {
  // # Retrieve submitted cart contents (sku + qty)
}



function process_checkout() {

    // ### Nonce Verification
    $nonce = $_REQUEST['nonce'];
    if ( !wp_verify_nonce($nonce, 'to_market_scripts_nonce')) die(__('Busted.') );

    // ### Setup Data
    $basicinfo = $_REQUEST['basicinfo'];
    $shippingaddress = $_REQUEST['shippingaddress'];
    $stripetoken = $_REQUEST['stripetoken'];
    $basketcontents = $_REQUEST['basketcontents'];

    // ### Determine True Cost of Basket
    $subtotal = 0;
    foreach ( $basketcontents as $sku => $data ) {
      $post_id = $data['post_id'];
      if ( have_rows('product_skus', $post_id ) ) {
        while ( have_rows('product_skus', $post_id) ) : the_row();
          // if the sku matches the current product
          if ( get_sub_field('sku') === $sku ) {
            $subtotal = $subtotal + ( get_sub_field('sku_price') * $data['product_qty'] );
          }
        endwhile;
      } else {
        // Exit and send message back (someone fucking with the system)
      }
    }
    $grandtotal = floor( $subtotal + ($subtotal * get_field( 'tax_rate', 'option' )) );

    // ### Verify Address via EasyPost
    global $path_to_plugin_uri;
    require_once( dirname( __FILE__ ) . "/lib/EasyPost/lib/easypost.php");
    \EasyPost\EasyPost::setApiKey( set_easypost_api_key() );
    $shipping_address = \EasyPost\Address::create(array(
      'name' => $basicinfo['customer-name'],
      'street1' => $shippingaddress['shipping-address-line1'],
      'city' => $shippingaddress['shipping-address-city'],
      'state' => $shippingaddress['shipping-address-state'],
      'zip' => $shippingaddress['shipping-address-zip'],
      'country' => $shippingaddress['shipping-address-country'],
      'email' => $basicinfo['customer-email'],
    ));
    try {
      $verified_address = $shipping_address->verify();
    } catch(Exception $e) {
      // We should append some but about if
      error_log($e->getMessage());
    }

    // ### Stripe: Attempt to charge card
    // If their shipping adress is valid
    if ( isset($verified_address) && !empty($verified_address) ) {
      require_once( dirname( __FILE__ ) . '/lib/Stripe/lib/Stripe.php'); // Load Stripe Client Library (PHP)
      Stripe::setApiKey( stripe_api_key('secret') ); // # Present Secret API Key
      try {
        $charge = Stripe_Charge::create( array(
          "amount" => $grandtotal, // amount in cents, again
          "currency" => "usd",
          "card" => $stripetoken,
          "description" => $basicinfo['customer-email']
        ));

        // ### If charge is successful, create shipping label
        try {

          $to_address = \EasyPost\Address::create(
            array(
              "name"    => $basicinfo['customer-name'],
              "street1" => $shippingaddress['shipping-address-line1'],
              "street2" => $shippingaddress['shipping-address-line2'],
              "city"    => $shippingaddress['shipping-address-city'],
              "state"   => $shippingaddress['shipping-address-state'],
              "zip"     => $shippingaddress['shipping-address-zip'],
              "phone"   => $basicinfo['customer-phone']
            )
          );
          $from_address = \EasyPost\Address::create(
            array(
              "company" => get_field( 'dba', 'option'),
              "street1" => get_field( 'ship_from_street1', 'option'),
              "city"    => get_field( 'ship_from_city', 'option'),
              "state"   => get_field( 'ship_from_state', 'option'),
              "zip"     => get_field( 'ship_from_zip_code', 'option'),
              "phone"   => "620-123-4567"
            )
          );

          //@todo: ensure correct package details are inserted for each product
          // ! disregard throw in products.
          $parcel = \EasyPost\Parcel::create(
              array(
                  "predefined_package" => "LargeFlatRateBox",
                  "weight" => 76.8
              )
          );
          $shipment = \EasyPost\Shipment::create(
              array(
                  "to_address"   => $to_address,
                  "from_address" => $from_address,
                  "parcel"       => $parcel
              )
          );

          $shipment->buy($shipment->lowest_rate());
          error_log($shipment->postage_label->label_url);


          // $redirect  = add_query_arg( array('checkout' => 'yes', 'step' => '4'), $_REQUEST['redirectURL']);
          // error_log( $redirect );
          // ### Display appropriate message
          // @todo: redirect fussy since we are making an ajax call
          // if ( isset( $redirect ) ) {
          //   error_log('as');
          //   wp_redirect( $redirect );
          //   exit;
          // }
          /**
           * Build the response...
           */
          $success = true;
          $response = json_encode(array(
            'success' => $success,
            // 'errors' => $errors,
          ));

          // Construct and send the response
          header("content-type: application/json");
          echo $response;
          exit;

        } catch( Exception $e ) {
          error_log($e->getMessage());
          // Change email messages here.
            // One email to litton notifying her shipping label not printed.
            // One email to customer letting them know product will be shipped
            // ASAP and a tracking number will be emailed as well.
        }

        // send admin and customer emails
        // click fourth tab and show success message.


      } catch(Stripe_CardError $e) {
        // The card has been declined
        error_log($e->getMessage());
      }

    } // if address is verified



}
add_action('wp_ajax_nopriv_process_checkout', 'process_checkout');
add_action('wp_ajax_process_checkout', 'process_checkout');

//Run Ajax calls even if user is logged in
// if ( isset($_REQUEST['action']) && ($_REQUEST['action']=='process_checkout') ):
//   do_action( 'wp_ajax_' . $_REQUEST['action'] );
//   do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
// endif;

/**
 * Process Stripe Payment
 * Listen for Stripe: Payment requests
 */
function process_stripe_payment() {
  // Verify the client request is legit upon Stripe payment form submission
  if ( isset( $_POST['form-type'] ) && $_POST['form-type'] === 'stripe-payment' && wp_verify_nonce( $_REQUEST['_wpnonce'], 'stripe-payment' ) ) {
    // Load Stripe Client Library (PHP)
    require_once( dirname( __FILE__ ) . '/lib/Stripe/lib/Stripe.php');
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

    // # Display appropriate message
    if ( isset( $redirect ) ) {
      wp_redirect( $redirect );
      exit;
    }
  } // end if
}
add_action('init', 'process_stripe_payment');



function easypost_create_label() {

  global $path_to_plugin_uri;
  require_once( dirname( __FILE__ ) . "/lib/EasyPost/lib/easypost.php");
  \EasyPost\EasyPost::setApiKey( set_easypost_api_key() );
  // $to_address = \EasyPost\Address::create(
  //     array(
  //         "name"    => "Dirk Diggler",
  //         "street1" => "388 Townsend St",
  //         "street2" => "Apt 20",
  //         "city"    => "San Francisco",
  //         "state"   => "CA",
  //         "zip"     => "94107",
  //         "phone"   => "415-456-7890"
  //     )
  // );
  // $from_address = \EasyPost\Address::create(
  //     array(
  //         "company" => "Simpler Postage Inc",
  //         "street1" => "764 Warehouse Ave",
  //         "city"    => "Kansas City",
  //         "state"   => "KS",
  //         "zip"     => "66101",
  //         "phone"   => "620-123-4567"
  //     )
  // );
  // $parcel = \EasyPost\Parcel::create(
  //     array(
  //         "predefined_package" => "LargeFlatRateBox",
  //         "weight" => 76.9
  //     )
  // );
  // $shipment = \EasyPost\Shipment::create(
  //     array(
  //         "to_address"   => $to_address,
  //         "from_address" => $from_address,
  //         "parcel"       => $parcel
  //     )
  // );
  //
  // $shipment->buy($shipment->lowest_rate());
  //
  // echo $shipment->postage_label->label_url;
}
add_action( 'init', 'easypost_create_label' );




?>
