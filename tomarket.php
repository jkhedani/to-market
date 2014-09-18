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
  wp_enqueue_script( 'stripe-jquery-payment', $path_to_plugin_uri . '/assets/js/stripe/jquery.payment.js', array('jquery') ); // Validation script

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
    <div class="modal-dialog checkout-dialog">
    <div class="checkout-header">
      <a class="site-title white" href="#checkout" title="'. esc_attr( get_bloginfo( 'name', 'display' ) ) .'" rel="home">'.get_bloginfo( 'name' ).'</a>
      <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>-->

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
          <legend>Basic Information</legend>
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
            <input type="text"  class="customer-phone form-control" size="20" autocomplete="off" class="phone" name="customer-phone" placeholder="XXX-XXX-XXXX" />
          </div>
        </form>

        <form action="" method="POST" id="shipping-address">
          <legend>Shipping Address</legend>
          <div class="input-group">
            <label>'. __('Address Line 1', 'litton_bags') .'</label>
            <input type="text" size="20" autocomplete="off" data-target="address-line1" name="shipping-address-line1" class="address" placeholder="Address Line 1" />
          </div>
          <div class="input-group">
            <label>'. __('Address Line 2', 'litton_bags') .'</label>
            <input type="text" size="20" autocomplete="off" data-target="address-line2" name="shipping-address-line2" class="address optional" placeholder="Address Line 2" />
          </div>
          <div class="input-row">
            <div class="input-group city">
              <label>'. __('City', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" data-target="address-city" name="shipping-address-city" placeholder="City" />
            </div>
            <div class="input-group state">
              <label>'. __('State', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="state" data-target="address-state" name="shipping-address-state" placeholder="State" />
            </div>
            <div class="input-group zip">
              <label>'. __('Zip Code', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="zip-code" data-target="address-zip" name="shipping-address-zip" placeholder="Zipcode" />
            </div>
            <div class="input-group country">
              <label>'. __('Country', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="country" data-target="address-country" name="shipping-address-country" placeholder="Country" />
            </div>
          </div>

          <p class="form-helper-text">Currently, we are only shipping to the United States on our website. Please <a href="mailto:support@littonbags.com">email us</a> for international purchases.</p>
        </form>

      </div><!-- .modal-body -->
      <div class="checkout-footer">
        <a href="#payment" data-target="2" class="select-checkout-tab mint">Next »</a>
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
          <!-- legend>Card Information</legend> -->
          <div class="payment-method-container">
            <p class="form-helper-text">We accept the following methods of payment:</p>
            <ul class="cc-icons">
              <li class="cc-icon visa">
                <img class="color active" src="'.$path_to_plugin_uri.'/assets/media/cc/visa_32.png" />
                <img class="bw" src="'.$path_to_plugin_uri.'/assets/media/cc/visa_32-bw.png" />
              </li>
              <li class="cc-icon mastercard">
                <img class="color active" src="'.$path_to_plugin_uri.'/assets/media/cc/mastercard_32.png" />
                <img class="bw" src="'.$path_to_plugin_uri.'/assets/media/cc/mastercard_32-bw.png" />
              </li>
              <li class="cc-icon amex">
                <img class="color active" src="'.$path_to_plugin_uri.'/assets/media/cc/american_express_32.png" />
                <img class="bw" src="'.$path_to_plugin_uri.'/assets/media/cc/american_express_32-bw.png" />
              </li>
              <li class="cc-icon discover">
                <img class="color active" src="'.$path_to_plugin_uri.'/assets/media/cc/discover_32.png" />
                <img class="bw" src="'.$path_to_plugin_uri.'/assets/media/cc/discover_32-bw.png" />
              </li>
              <li class="cc-icon jcb">
                <img class="color active" src="'.$path_to_plugin_uri.'/assets/media/cc/jcb_32.png" />
                <img class="bw" src="'.$path_to_plugin_uri.'/assets/media/cc/jcb_32-bw.png" />
              </li>
            </ul>
            <div class="or">- or -</div>
            <a id="checkout-with-paypal" class="paypal-checkout" href="javascript:void(0);" title="Checkout via Paypal instead." data-payment-method="paypal"><img src="'.$path_to_plugin_uri.'/assets/media/paypal-checkout-icon.png" alt="Checkout via Paypal instead." /></a>
          </div>

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
          <div class="input-row">
            <div class="input-group expiry">
              <label>'. __('Expiration (MM/YYYY)', 'litton_bags') .'</label>
              <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
              <input type="text" class="card-expiry" size="2" data-stripe="expiry" placeholder="MM / YYYY" />
            </div>
            <div class="input-group cvc">
              <label>'. __('CVC', 'litton_bags') .'</label>
              <div class="input-group-addon"><i class="fa fa-lock"></i></div>
              <input type="text" class="card-cvc" size="4" autocomplete="off" data-stripe="cvc" placeholder="CVC" />
            </div>
          </div>
          <input type="hidden" name="redirect" value="'. get_permalink() .'"/>
          <input type="hidden" name="form-type" value="stripe-payment" />
          '.wp_nonce_field( "stripe-payment" ).'
        </form>

        <div class="input-group">
          <input id="show-billing-address-fields" type="checkbox" />
          <p class="form-helper-text">My billing address is different from my shipping address.</p>
        </div>

        <form action="" method="POST" id="billing-address">
          <legend>Billing Address</legend>
          <div class="input-group">
            <label>'. __('Address Line 1', 'litton_bags') .'</label>
            <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-line1" class="address" placeholder="Address Line 1" />
          </div>
          <div class="input-group">
            <label>'. __('Address Line 2', 'litton_bags') .'</label>
            <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-line2" class="address" placeholder="Address Line 2" />
          </div>
          <div class="input-row">
            <div class="input-group city">
              <label>'. __('City', 'litton_bags') .'</label>
              <div class="input-group-addon"><i class="fa fa-building"></i></div>
              <input type="text" class="form-control" size="20" autocomplete="off" data-stripe="address-city" class="address" placeholder="City" />
            </div>
            <div class="input-group zip">
              <label>'. __('Zip Code', 'litton_bags') .'</label>
              <input type="text" size="20" autocomplete="off" class="zip-code" data-stripe="address-zip" class="address" placeholder="Zipcode" />
            </div>
            <div class="input-group state">
              <label>'. __('State', 'litton_bags') .'</label>
              <input type="text" size="5" autocomplete="off" class="state" data-stripe="address-state" class="address" placeholder="State" />
            </div>
            <div class="input-group country">
              <label>'. __('Country', 'litton_bags') .'</label>
              <input type="text" size="7" autocomplete="off" class="country" data-stripe="address-country" class="address" placeholder="USA" />
            </div>
          </div>
        </form>

        <hr />
        <p class="form-helper-text"><i class="fa fa-cc-stripe"></i>We proudly use <a href="http://stripe.com">Stripe</a> to securely process your payment information.</p>

      </div><!-- modal-body -->
      <div class="checkout-footer">
        <a href="#review" data-target="3" class="select-checkout-tab mint">Next &raquo;</a>
      </div>
    </div><!-- end step 2 -->


    <!-- Step Three: Review -->
    <div id="review" class="checkout-step" data-step="3">
      <div class="modal-header">
        <h3 class="checkout-step-title">'. __('Review','litton_bags') .'</h3>
      </div>
      <div class="modal-body">

        <div class="review-address review-row">
          <a href="#basic" data-target="1" class="select-checkout-tab edit-tab"><i class="fa fa-pencil"></i></a>
          <div class="shipping-address">
            <h4>Shipping Address</h4>
            <div class="review-shipping-address review-row-content"></div>
          </div>
          <div class="billing-address">
            <h4>Billing Address</h4>
            <div class="review-billing-address review-row-content"></div>
          </div>
        </div>
        <hr />

        <div class="review-payment-method review-row">
          <h4>Payment method</h4>
          <a href="#basic" data-target="2" class="select-checkout-tab edit-tab"><i class="fa fa-pencil"></i></a>
          <div class="card-details review-row-content"></div>
        </div>
        <hr />

        <div class="review-cart review-row">
          <h4>Cart items</h4>
          <div class="cart-items review-row-content"></div>
        </div>

      </div>
      <div class="checkout-footer">
        <a class="mint" href="#" data-action="checkout">Checkout</a>
      </div>
      <div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Processing checkout</h4></div></div>
    </div><!-- end step 3 -->


    <!-- Step Four: Message Screen -->
    <div id="message" class="checkout-step" data-step="4">
      <div class="modal-header">

        <!-- Successful Payment -->
        <h3 class="checkout-step-title successful-payment">'. __('Successful Payment','litton_bags') .'</h3>

      </div>
      <div class="modal-body">

        <!-- Successful Payment -->
        <div class="successful-payment">
          <img src="'.$path_to_plugin_uri.'/assets/media/payment-success.jpg" />
          <h4>You have successfully made a payment. An email with your shipping label and confirmation has been sent to you.</h4>
        </div>

        <!-- Error -->
        <div class="error">
          <div class="error-message"></div>
          <p>If you are experiencing problems with checkout, <a href="#">Contact us</a> an we\'ll get back to you right away.</p>
        </div>

      </div>
      <div class="checkout-footer">
      </div>
      <div class="overlay loading"><i class="spinner medium"></i><div class="overlay-message-container"><h4>Processing Payment</h4></div></div>
    </div><!-- end step 4 -->

    </div><!-- .modal-dialog -->

  </div>

  ';
  echo $checkout;
}
add_action('wp_footer','render_checkout');

// # Stripe API
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

/**
 * Process Checkout
 * @since 1.2.0
 */
function process_checkout() {

    // Nonce Verification
    $nonce = $_REQUEST['nonce'];
    if ( !wp_verify_nonce($nonce, 'to_market_scripts_nonce')) die(__('Busted.') );

    // Setup Data
    $basicinfo = $_REQUEST['basicinfo'];
    $shippingaddress = $_REQUEST['shippingaddress'];
    $stripetoken = $_REQUEST['stripetoken'];
    $basketcontents = $_REQUEST['basketcontents'];

    // Determine True Cost of Basket
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

    // A. Verify Address via EasyPost
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
      $error_message = "Please check to make sure you are using a valide shipping address. We don't want to lose your package! Your payment has NOT been processed yet.";
      error_log($e->getMessage());
      exit;
    }

    // B. Stripe: Attempt to charge card
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
      } catch(Stripe_CardError $e) {
        // The card has been declined
        error_log($e->getMessage());
        exit;
      }
    } // B. if address is verified


    // C. If charge is successful, create shipping label
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
      // Create a separate parcel for each product if it isn't considered throw in.
      $parcels = array();
      foreach ( $basketcontents as $sku => $data ) {
        $post_id = $data['post_id'];
        if ( get_field('throw_in_shipping', $post_id) === false ) {
          $parcelLength = get_field( 'shipping_length', $post_id );
          $parcelWidth  = get_field( 'shipping_width', $post_id );
          $parcelHeight = get_field( 'shipping_height', $post_id );
          $parcelWeight = get_field( 'shipping_weight', $post_id );
          $parcels[] = \EasyPost\Parcel::create( array(
            "length" => $parcelLength,
            "width"	 => $parcelWidth,
            "height" => $parcelHeight,
            "weight" => $parcelWeight
          ));
        }
      } // end foreach

      // Create shipping labels for each product
      $shipmentLabels = array();
      foreach ($parcels as $parcel) {
        $shipment = \EasyPost\Shipment::create(
          array(
            'to_address'   => $to_address,
            'from_address' => $from_address,
            'parcel'       => $parcel
          )
        );
        //error_log('Shipment Object:' . $shipment);
        //error_log('Shipment Rates:'.print_r($shipment->rates,true));
        $shipmentLabels[] = $shipment->buy($shipment->lowest_rate());
        error_log($shipment->postage_label->label_url);
      }

      // Send success emails!

    } catch( Exception $e ) {
      error_log($shipment->postage_label->label_url);
      error_log($e->getMessage());
      // Change email messages here.
        // One email to litton notifying her shipping label not printed.
        // One email to customer letting them know product will be shipped
        // ASAP and a tracking number will be emailed as well.
    }

    // Send the user back no matter what! (even if easypost fails to generate label)
    $success = true;
    $response = json_encode(array(
      'success' => $success,
    ));

    // Construct and send the response
    header("content-type: application/json");
    echo $response;
    exit;
}
add_action('wp_ajax_nopriv_process_checkout', 'process_checkout');
add_action('wp_ajax_process_checkout', 'process_checkout');




/**
 * PayPal
 * @since 1.2.0
 */
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

function paypal_prepare_payment() {

  // ### Nonce Verification
  $nonce = $_REQUEST['nonce'];
  if ( !wp_verify_nonce($nonce, 'to_market_scripts_nonce')) die(__('Busted.') );
  $success = false;
  $paypalRedirectURL = "";

  // ### Bootstrap PayPal API
  // Configure our API context
  // Include the composer autoloader if we aren't already set
  require dirname( __FILE__ ) . '/lib/PayPal/bootstrap.php';
  session_start();

  // ### Payer
  // A resource representing a Payer that funds a payment
  // For paypal account payments, set payment method
  // to 'paypal'.
  $payer = new Payer();
  $payer->setPaymentMethod("paypal");

  // ### Itemized information
  // (Optional) Lets you specify item wise
  // information

  // // Determine True Cost of Basket
  // $subtotal = 0;
  // foreach ( $basketcontents as $sku => $data ) {
  //   $post_id = $data['post_id'];
  //   if ( have_rows('product_skus', $post_id ) ) {
  //     while ( have_rows('product_skus', $post_id) ) : the_row();
  //       // if the sku matches the current product
  //       if ( get_sub_field('sku') === $sku ) {
  //         $subtotal = $subtotal + ( get_sub_field('sku_price') * $data['product_qty'] );
  //       }
  //     endwhile;
  //   } else {
  //     // Exit and send message back (someone fucking with the system)
  //   }
  // }

  $item1 = new Item();
  $item1->setName('Ground Coffee 40 oz')
  	->setCurrency('USD')
  	->setQuantity(1)
  	->setPrice('7.50');
  $item2 = new Item();
  $item2->setName('Granola bars')
  	->setCurrency('USD')
  	->setQuantity(5)
  	->setPrice('2.00');

  $itemList = new ItemList();
  $itemList->setItems(array($item1, $item2));

  // ### Additional payment details
  // Use this optional field to set additional
  // payment information such as tax, shipping
  // charges etc.
  $details = new Details();
  $details->setShipping('1.20')
  	->setTax('1.30')
  	->setSubtotal('17.50');

  // ### Amount
  // Lets you specify a payment amount.
  // You can also specify additional details
  // such as shipping, tax.
  $amount = new Amount();
  $amount->setCurrency("USD")
  	->setTotal("20.00")
  	->setDetails($details);

  // ### Transaction
  // A transaction defines the contract of a
  // payment - what is the payment for and who
  // is fulfilling it.
  $transaction = new Transaction();
  $transaction->setAmount($amount)
  	->setItemList($itemList)
  	->setDescription("Payment description");

  // ### Redirect urls
  // Set the urls that the buyer must be redirected to after
  // payment approval/ cancellation.
  $baseUrl = getBaseUrl();
  $redirectUrls = new RedirectUrls();
  $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
  	->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

  // ### Payment
  // A Payment Resource; create one using
  // the above types and intent set to 'sale'
  $payment = new Payment();
  $payment->setIntent("sale")
  	->setPayer($payer)
  	->setRedirectUrls($redirectUrls)
  	->setTransactions(array($transaction));

  // ### Create Payment
  // Create a payment by calling the 'create' method
  // passing it a valid apiContext.
  // (See bootstrap.php for more on `ApiContext`)
  // The return object contains the state and the
  // url to which the buyer must be redirected to
  // for payment approval
  try {
  	$payment->create($apiContext);
  } catch (PayPal\Exception\PPConnectionException $ex) {
  	echo "Exception: " . $ex->getMessage() . PHP_EOL;
  	var_dump($ex->getData());
  	exit(1);
  }

  // ### Get redirect url
  // The API response provides the url that you must redirect
  // the buyer to. Retrieve the url from the $payment->getLinks()
  // method
  foreach($payment->getLinks() as $link) {
  	if($link->getRel() == 'approval_url') {
  		$redirectUrl = $link->getHref();
  		break;
  	}
  }

  // ### Redirect buyer to PayPal website
  // Save the payment id so that you can 'complete' the payment
  // once the buyer approves the payment and is redirected
  // back to your website.
  //
  // It is not a great idea to store the payment id
  // in the session. In a real world app, you may want to
  // store the payment id in a database.
  $_SESSION['paymentId'] = $payment->getId();
  // if(isset($redirectUrl)) {
  //   header("Location: $redirectUrl");
  // 	exit;
  // }


  $paypalRedirectURL = $redirectUrl;
  $success = true;
  error_log($paypalRedirectURL);
  $response = json_encode( array(
      'success' => $success,
      'redirecturl' => $paypalRedirectURL,
  ));

  header( 'content-type: application/json' );
  echo $response;
  exit;
}
add_action( 'wp_ajax_nopriv_paypal_prepare_payment', 'paypal_prepare_payment' );
add_action( 'wp_ajax_paypal_prepare_payment', 'paypal_prepare_payment' );

?>
