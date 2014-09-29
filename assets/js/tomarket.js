jQuery( document ).ready( function($) {

	/**
	 * Hand Basket
	 * Requires: simpleStorage.js, jQuery
	 *
	 * @failure Code may fail in generating a new item in the basket when a
   *				  setting other than color can differentiate the same item.
	 * @todo Wrap this all up into one big class
	 *
	 * cart must fixes
	 *	1. ensure option selection loads correct image url and price
	 *	2. ensure the click to select option triggers the on.change event
	 *	3. Popover stylings a little wonky on small devices, home page & minster
	 *
	 */

	// # Disable scripts for clients that cannot properly utilize simpleStorage
	if ( ! simpleStorage.canUse() ) {
		var message = "In order to have the best experience on our site, please leave private browsing or use a different browser. Thank you!";
		alert( message );
		return;
	}

	// #
	// # HandBasket Functions
	// #

	// # Format money to us dollars with cents as input amount
	var format_money = function( cents ) {
		return '$ ' + ( cents / 100 ).toFixed(2);
	}

	// # Generate basket
	//   This function doesn't display the basket but rather
	//   it constructs the basket to be shown using stored
	//   local data.
	var refresh_handbasket = function( type ) {
		var handbasket_items = "";
		var handbasket_subtotal = 0
		// # Check if we have any products in our basket.
		if ( simpleStorage.index().length < 1 ) {
			var handbasket_items = "<h1>Shopping Cart</h1><p>Your shopping cart is currently empty.</p>"; // Load empty basket
		} else {

			// Retrieve each existing product from the basket
			var handbasket_item_keys = simpleStorage.index();
			for ( i = 0; i < handbasket_item_keys.length; i++ ) {
				var handbasket_item_object = simpleStorage.get(handbasket_item_keys[i]);
				handbasket_subtotal = handbasket_subtotal + ( handbasket_item_object['product_qty'] * handbasket_item_object['sku_price'] );
				var handbasket_item  = "<div class='hand-basket-product' data-sku='"+handbasket_item_object['sku']+"'>";
						handbasket_item += "	<span class='product-preview'><img src='"+handbasket_item_object['sku_checkout_image_preview']+"' /></span>";
						handbasket_item += "	<div class='product-description'>";
						handbasket_item += "		<span class='product-title'>"+handbasket_item_object['product_name']+"</span>";
						handbasket_item += "		<span class='product-color'><span class='product-meta-title'>Color: </span>"+handbasket_item_object['sku_color_name']+"</span>";
						handbasket_item += "	</div>";
						handbasket_item += "	<span class='product-price'>"+format_money(handbasket_item_object['sku_price'])+"</span>";
						handbasket_item += "	<span class='product-qty'>"+handbasket_item_object['product_qty']+"</span>";
						handbasket_item += "	<span class='product-subtotal'>"+format_money(handbasket_item_object['product_qty'] * handbasket_item_object['sku_price'])+"</span>";
						handbasket_item += " 	<a href='javascript:void(0);' class='btn remove-handbasket-item'>x</a>";
						handbasket_item += "</div>";
				// Place item in basket
				handbasket_items += handbasket_item;
			}

			// Generate totals & tax
			var tax_rate_dollars = to_market_scripts.tax_rate * handbasket_subtotal;
			var grand_total = tax_rate_dollars + handbasket_subtotal;

			// Add totals to content
			handbasket_items += '<div class="checkout-totals">';
			handbasket_items += '<div class="subtotal"><span class="total-title">Subtotal: </span><span class="line-item-cost">'+format_money(handbasket_subtotal)+'</span></div>';
			handbasket_items += '<div class="auxfees"><span class="total-title">Tax ('+(to_market_scripts.tax_rate * 100).toFixed(3)+'%): </span><span class="line-item-cost">'+format_money(tax_rate_dollars)+'</span></div>';
			handbasket_items += '<div class="auxfees"><span class="total-title">Shipping: </span><span class="line-item-cost">Free Domestic Shipping<a class="shipping-popover-trigger" data-toggle="tooltip" title="'+to_market_scripts.shipping_text+'" href="javascript:void(0);" ><i class="fa fa-info-circle"></i></a></span></div>';
			handbasket_items += '<div class="total"><span class="total-title">Total: </span><span class="line-item-cost">'+format_money(grand_total)+'</span></div>';
			handbasket_items += '</div>';

		} // handbasket_has_items check

		// # Determine which type of hand basket we are going to generate
		// a = typeof a !== 'undefined' ? a : 42;
		type = typeof type !== 'undefined' ? type : 'popover';

		// # Primary function of popver is of the shopping cart
		if ( type === 'popover' ) {

			if ( simpleStorage.index().length > 0 ) {
				// Prepend "legend" and title in the hand basket (gets overwritten if in template)
				handbasket_items = "<h1>Shopping Cart</h1><div class='hand-basket-preview-legend'><span class='product-title'>Product Name</span><span class='product-price'>Unit Price</span><span class='product-qty'>Qty</span><span class='product-subtotal'>Subtotal</span><span class='product-remove'>Remove</span></div>" + handbasket_items;
				// Append promo text and basket trigger checkout button
				handbasket_items += '<hr />';
				handbasket_items += '<span class="donation-promo-text">'+to_market_scripts.donation_promo_text+'</span>';
				handbasket_items += '<a class="toggle-checkout" data-toggle="checkout" data-target="#checkout">Checkout</a>';
			}


			$('[data-toggle="hand-basket"]').popover({
				'html'			: true,
				'placement' : 'bottom',
				'trigger'   : 'manual',
				'container' : '#primary',
				'content'   : handbasket_items,
				'template'  : "<div class='popover hand-basket' role='tooltip'><div class='arrow'></div><div class='popover-content hand-basket-content'></div></div>",
			});

		// # Return just the basket items (no wrapper)
		} else if ( type === 'raw' ) {
			return handbasket_items;
		}
	}

	// # Determine which sku has been selected
	var determine_selected_sku = function() {
		var selected_sku_options = {};
		$('.user-selectable-option').each(function() {
			// Set their option type and values to the object
			selected_sku_options[$(this).data('sku-option-type')] = $(this).val();
		});
		$('.descriptor').removeClass('selected');
		$('.descriptor').each(function() {
			// Set descriptor switch here
			var desired_descriptor = false;
			// Using this object, find the appropriate descriptor to select
			for ( var key in selected_sku_options ) {
				// If each of the values of the data in this descriptor match the values
				// of data in the selected sku object...
				if ( $(this).data(key) === selected_sku_options[key] ) {
					// Determine that this may be the descriptor.
					desired_descriptor = true;
				} else {
					desired_descriptor = false;
				}
			}
			// Find the descriptor
			if ( desired_descriptor === true ) {
				$(this).addClass('selected');
			}
		});
	}

	// # Returns the sku name
	var get_selected_sku = function() {
		var selected_sku = $(document).find('.descriptor.selected').data('sku');
		return selected_sku;
	}

	// # Determines which sku has been selected and shows displays
	//   various options to users (e.g. change in price, maybe change of picture)
	var redraw_selected_sku_options = function() {
		determine_selected_sku();
		$('[data-sku]').not('.descriptor').removeClass('selected');
		$('[data-sku="'+get_selected_sku()+'"]').addClass('selected');
	}

	// #
	// # HandBasket Event Handlers
	// #

	// # Toggle View of Hand Basket
	$(document).on('click', '[data-toggle="hand-basket"]', function() {
		refresh_handbasket();
		$('[data-toggle="hand-basket"]').popover('toggle');
	});

	// # Remove product from cart
	$(document).on( 'click', '.remove-handbasket-item', function() {
		// Remove from local storage
		simpleStorage.deleteKey( $(this).parent().data('sku') );
		// Hide DOM element
		$(this).parent().animate( { opacity:0 }, 500, function() {
			$(this).css('display','none');
			// If handbasket is empty
			if ( simpleStorage.index().length < 1 ) {
				$('[data-toggle="hand-basket"]').popover('destroy');
				refresh_handbasket();
				$('[data-toggle="hand-basket"]').popover('toggle');
			}
		});
	});

	// # Change the hidden select element to the appropriate value
	$(document).on( 'click', '.jquery-color-selection a', function() {
		// Disable product selection for sold out options.
		if ( $(this).data('option-sold-out') == 1 ) {
			return false;
		}
		// Affect the <select> element
		$('.jquery-color-selection a').removeClass('selected');
		$(this).addClass('selected');
		var colorValue = $(this).data('color-value');
		//var previewUrl = $(this).attr('data-checkout-image-preview');
		$('select.product-color-selection').val(colorValue); // Update select with latest value
		// Redraw the newly select sku option
		redraw_selected_sku_options();
		return false;
	});

	// # Users Add Products to Hand Basket
	$(document).on('click', '.add-handbasket-item', function() {

		// Retrieve any existing cart items (index)
		var handbasket = simpleStorage.index();
		var handbasket_item_count = handbasket.length;

		// Compile product values based on the selected sku
		var selected_qty = $('.product-qty-selection').val();
		$(document).find('.descriptor.selected').data( 'product_qty', selected_qty );
		var product_options = $(document).find('.descriptor.selected').data();

		// Duplicate handling
		// Add to cart, if product SKU does not exist
		if ( handbasket.indexOf( product_options['sku'] ) ) {
			var new_product = simpleStorage.set( product_options['sku'], product_options, { ttl: 172800000 });
			// Destroy existing cart, refresh our handbasket and re-display
			$('[data-toggle="hand-basket"]').popover('destroy');
			refresh_handbasket();
			$('[data-toggle="hand-basket"]').popover('toggle');
		} else {
			$('[data-toggle="hand-basket"]').popover('toggle');
		}

	});

	// # Handbasket inner popover (primarily used ofr shipping)
	$(document).on('shown.bs.popover',function() {
		$('.shipping-popover-trigger').tooltip({
			'placement' : 'left',
		});
	});


	// # Using jQuery, allow users to manipulate a select with click options.
	// Hide <select> element and title
	$('.product-color-selection').hide();
	// Create container for selections
	$('.product-color-selection').after('<div class="jquery-color-selection"><ul></ul></div>');
	// Grab available color options and create buttons in color container
	$('.product-color-selection').children('option').each( function() {
		//$('.jquery-color-selection ul').append('<li><a href="#" data-color-value="'+$(this).val()+'" data-checkout-image-preview="'+$(this).data('checkout-image-preview')+'" class="'+$(this).val()+'" style="background-color:'+$(this).data('background-color')+'" data-option-sold-out="'+$(this).data('option-sold-out')+'">'+$(this).val()+'</a></li>').addClass('capitalize');
		$('.jquery-color-selection ul').append('<li><a href="#" data-color-value="'+$(this).val()+'" class="'+$(this).val()+'" style="background-color:'+$(this).data('sku-color')+'" data-option-sold-out="'+$(this).data('option-sold-out')+'">'+$(this).val()+'</a></li>');
	});

	// # Determine default sku on page load
	redraw_selected_sku_options();



	/**
	 * Checkout
	 * The modal popover, processing of address and payment script.
	 * Requires: jQuery, BootstrapJS (Modal), Stripe & EasyPost
	 */

	// # Checkout Testing Parameters
	var allow_dev_inputs = true;
	function insert_dev_inputs ( steps ) {
		// Step "Basic"
		function input_step_basic_dev_data() {
			$('input[name="customer-name"]').val('Justin Hedani');
			$('input[name="customer-email"]').val('jkhedani@gmail.com');
			$('input[name="customer-phone"]').val('808-349-0746');

			$('input[data-stripe="address-line1"]').val('3927 Koko Drive');
			$('input[data-stripe="address-city"]').val('Honolulu');
			$('input[data-stripe="address-zip"]').val('96816');
			$('input[data-stripe="address-state"]').val('HI');
			$('input[data-stripe="address-country"]').val('USA');

			$('input[name="shipping-address-line1"]').val('3927 Koko Drive');
			$('input[name="shipping-address-city"]').val('Honolulu');
			$('input[name="shipping-address-zip"]').val('96816');
			$('input[name="shipping-address-state"]').val('HI');
			$('input[name="shipping-address-country"]').val('USA');
		}
		// Step "Payment"
		function input_step_payment_dev_data() {
			$('input[data-stripe="name"]').val('Justin Hedani');
			$('input[data-stripe="number"]').val('4242424242424242');
			$('input[data-stripe="cvc"]').val('123');
			$('input[data-stripe="expiry"]').val('09 / 2015');
		}
		// # Determine which steps to add input data to
		if ( steps === "all" ) {
			input_step_basic_dev_data();
			input_step_payment_dev_data();
		}
	}

	if ( allow_dev_inputs === true ) {
		$(document).on( 'show.bs.modal', '#checkout', function() {
			insert_dev_inputs( 'all' );
		});
	}

	// # Get URL variables
	// 	 http://css-tricks.com/snippets/javascript/get-url-variables/
	function get_query_variable(variable) {
     var query = window.location.search.substring(1); // everything after(?)
     var vars = query.split("&");
     for (var i=0;i<vars.length;i++) {
       var pair = vars[i].split("=");
       if(pair[0] == variable){return pair[1];}
     }
     return(false);
	}

	// # Convert form data to json
	//	 Requires: jQuery
	var convert_form_to_json = function( form ) {
		var url_encoded_array = form.serializeArray();
		var json = {};
		for ( i = 0; i < url_encoded_array.length; i++ ) {
			json[ url_encoded_array[i]['name'] ] = url_encoded_array[i]['value'];
		}
		return json;
	}

	/**
	 * Simple delay function.
	 * @function
	 * @param {function} callback - Function to be called back.
	 * @param {int} ms - The amount of time before callback occurs.
	 */
	var delay = (function(){
	  var timer = 0;
	  return function(callback, ms){
	    clearTimeout (timer);
	    timer = setTimeout(callback, ms);
	  };
	})();

	//
	//
	// Checkout Event Listeners
	//
	//

	// @event Copy shipping address values to billing address values
	// $(document).on('keyup','form#shipping-address input', function() {
	// 	var input_target = $(this).data('target');
	// 	var input_data = $(this).val();
	// 	delay( function() {
	// 		$(document).find('form#billing-address input[data-stripe="'+input_target+'"]').val( input_data );
	// 	}, 4000);
	// });

	// @event Show billing address field
	$(document).on( 'click', '#show-billing-address-fields', function() {
		if ( $("#show-billing-address-fields").is(':checked') ) {
			$('#billing-address').show();
		} else {
			$('#billing-address').hide();
		}
	});

	// @event Allow tabbed interface through checkout
	$(document).on('click', '.checkout-tab', function() {
		// Remove current class from all tabs
		$('.checkout-tabs a').removeClass('current');
		// Make clicked tab current
		$(this).addClass('current');
		// Hide current step, target and show desired step.
		var target_step = $(this).data('target');
		$('#checkout .checkout-step').removeClass('current').hide();
		$('#checkout [data-step="'+target_step+'"]').addClass('current').show();
		return false;
	});

	// @event Select a checkout tab.
	$(document).on('click', 'a.select-checkout-tab', function() {
		var target_step = $(this).data('target');
		$('ul.checkout-tabs li a[data-target="'+target_step+'"]').click();
		return false;
	});

	// @event Prevent page scrolling when modal is present
	$(document).on( 'shown.bs.modal', '#checkout', function() {
		$('html').css( 'overflow', 'hidden' );
		$('html').addClass('fixed');

	});

	// @event Modal finished closing events.
	$(document).on( 'hidden.bs.modal', '#checkout', function() {
		// Restore scrolling functionality
		$('html').css( 'overflow-y', 'scroll' );
		$('html').removeClass('fixed');
		// Reload the page to remove any query string parameters
		var current_url = document.location.origin + document.location.pathname; // without query string
		window.location.href = current_url; // Reload!
	});

	// @event Add Hand Basket Items to Checkout
	$(document).on( 'shown.bs.modal', '#checkout', function() {
		var handbasket_items = refresh_handbasket('raw');
		$(document).find('#review.checkout-step .modal-body .review-cart .hand-basket-product').remove(); // Clear existing
		$(document).find('#review.checkout-step .modal-body .review-cart .checkout-totals').remove(); // Clear existing
		$(document).find('#review.checkout-step .modal-body .review-cart .cart-items').append( handbasket_items );
	});

	// QUERY VARIABLES
	// # Display checkout via URL query
	if ( get_query_variable('checkout') === 'yes' ) {
		$('#checkout').modal('show');
		// Click the first tab to show first step
		$(document).find('#checkout .checkout-tab[data-target="1"]').click();
	}
	if ( get_query_variable('step') ) {
		// Determine which step to show
		var step_to_show = get_query_variable( 'step' );
		$(document).find('#checkout .checkout-tabs a[data-target="'+step_to_show+'"]').click();
	}
	if ( get_query_variable('result') ) {
		// # Display the checkout step 4, message screen
		$(document).find('#checkout .checkout-tabs a[data-target="4"]').click();
		if ( get_query_variable('result') === 'success' ) {

		}
	}

	// @event Display checkout via click event
	$(document).on('click', '[data-toggle="checkout"]', function() {
		// Click the first tab to show first step
		$(document).find('#checkout .checkout-tab[data-target="1"]').click();
		// Hide handbasket
		$('.hand-basket').popover('toggle');
		$('#checkout').modal('show');
	});

	// @event Populate review steps when third tab is clicked
	$(document).on('click','.checkout-tab[data-target="3"]',function(){

		// @event Populate shipping address review
		$(document).find('#checkout .review-address .review-shipping-address').empty();
		var shipping_address_review = convert_form_to_json( $('form#shipping-address') );
		var ship_address  = shipping_address_review['shipping-address-line1'] + '</br>';
				if ( shipping_address_review['shipping-address-line2'] !== "" )
				ship_address += shipping_address_review['shipping-address-line2'] + '</br>';
				ship_address += shipping_address_review['shipping-address-city'] + ', ';
				ship_address += shipping_address_review['shipping-address-state'] + ' ';
				ship_address += shipping_address_review['shipping-address-zip'] + '</br>';
				ship_address += shipping_address_review['shipping-address-country'];
		// Append address to review
		$(document).find('#checkout .review-address .review-shipping-address').append( ship_address );

		// @event Populate billing address review
		$(document).find('#checkout .review-address .review-billing-address').empty();
		var billing_address = {};
		$(document).find('form#billing-address input').each(function() {
			var key = $(this).data('stripe');
			billing_address[key] = $(this).val();
		});
		var address  = billing_address['address-line1'] + '</br>';
				if ( billing_address['address-line2'] !== "" )
				address += billing_address['address-line2'] + '</br>';
				address += billing_address['address-city'] + ', ';
				address += billing_address['address-state'] + ' ';
				address += billing_address['address-zip'] + '</br>';
				address += billing_address['address-country'];
		// Append billing address to review if it differs from shipping. Otherwise,
		// just load the shipping address.
		if ( $("#show-billing-address-fields").is(':checked') ) {
			$(document).find('#checkout .review-address .review-billing-address').append( address );
		} else {
			$(document).find('#checkout .review-address .review-billing-address').append( ship_address );
		}

		// @event Populate Card review
		// Clear the details DOM for next insertion
		$(document).find('#checkout .review-payment-method .card-details').empty();
		var card_number = $(document).find('form#stripe-payment-form input[data-stripe="number"]');
		if ( $.payment.cardType === 'amex' ) {
			var card_number_last_digits = card_number.val().slice(-5);
		} else if ( $.payment.cardType === 'dinersclub' ) {
			var card_number_last_digits = card_number.val().slice(-2);
		} else {
			var card_number_last_digits = card_number.val().slice(-4);
		}
		var card_overview = '•••• •••• •••• ' + card_number_last_digits;
		// Append the card overview + image
		$(document).find('#checkout .review-payment-method .card-details').append( card_overview );
		$(document).find('#checkout .cc-icon img.color.active').clone().prependTo('#checkout .review-payment-method .card-details').css('margin-right','10px');
	});

	// @event Create Checkout (create as "last" as possible)
	$('#checkout').modal({ show : false, });

	// @event Process checkout
 	$(document).on('click', '[data-action="checkout"]', function() {

		// Show processing checkout
		$('#checkout .overlay.loading').show(); // # Bring up "Processing Payment"
		$(this).prop('disabled', true); // Disable submission to avoid double submits

		// Basic Info (Used by multiple services)
		var basic_info = convert_form_to_json( $('form#basic-info') );

		// Shipping Address
		// If shipping address is filled out, serialize data for processing.
		// Convert serialized data to a json object for parsing later
		var shipping_address = convert_form_to_json( $('form#shipping-address') );

		// Billing Address
		if ( $("#show-billing-address-fields").is(':checked') ) {
			var billing_address = {};
			$(document).find('form#billing-address input').each(function() {
				var key = $(this).data('stripe');
				billing_address[key] = $(this).val();
			});
		} else {
			var billing_address = shipping_address;
		}

		// Basket Info
		var basket_contents = {};
		for ( i = 0; i < simpleStorage.index().length; i++ ) {
			basket_contents[ simpleStorage.index()[i] ] = simpleStorage.get( simpleStorage.index()[i] );
		}

		// ### Stripe Construct payment token
		Stripe.setPublishableKey(to_market_scripts.stripe_publishable_key); // # Present Publishable API Key
		Stripe.card.createToken({
			number: $('#checkout #payment input[data-stripe="number"]').val(),
			cvc: $('#checkout #payment input[data-stripe="cvc"]').val(),
			exp_month: $('#checkout #payment input[data-stripe="expiry"]').payment('cardExpiryVal')['month'],
			exp_year: $('#checkout #payment input[data-stripe="expiry"]').payment('cardExpiryVal')['year']
		}, stripeResponseHandler);
		function stripeResponseHandler(status, response) {
			var $form = $('#stripe-payment-form');
			if ( response.error ) {
				// Show the errors on current tab
				//$form.find('.payment-errors').text(response.error.message);
				$('[data-action="checkout"]').prop('disabled', false); // Re-enable checkout button
				return false;
			} else {
				// response contains id and card, which contains additional card details
				var token = response.id;
				stripe_token = response.id;
				// Insert the token into the form so it gets submitted to the server
				$form.append($('<input type="hidden" name="stripeToken" />').val(token));
				//$form.get(0).submit();
				// ### Submit for serverside processing
				$.post(to_market_scripts.ajaxurl, {
					dataType: "jsonp",
					action: 'process_checkout',
					basicinfo: basic_info,
					shippingaddress: shipping_address,
					shippingaddress: billing_address,
					stripetoken: stripe_token,
					basketcontents: basket_contents,
					redirectURL: document.URL,
					nonce: to_market_scripts.nonce,
				}, function(response) {
					if ( response.success === true ) {
						window.location.replace( document.URL + "/?checkout=yes&step=4" );
						// window.location.href = document.URL + "/?checkout=yes&step=4";
						// $(this).parents('.checkout-step').find('.overlay.loading').hide(); // Show loading message
						// $('ul.checkout-tabs li a[data-step="4"]').click();
					}
				});
				return false;

			}
		} // stripeResponseHandler

		// response
			// # error bad address
			// # error bad cc

			// # success

	 return false;


	});


	/**
	* jQuery Regex Email Validation
	*/
	function isValidEmailAddress(emailAddress) {
		//var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		return pattern.test(emailAddress);
	};

	// # Stripe: Payments Formatting
	// # https://github.com/stripe/jquery.payment
	$('#checkout #payment input.card-number').payment('formatCardNumber');
	$('#checkout #payment input.card-cvc').payment('formatCardCVC');
	$('#checkout #payment input.card-expiry').payment('formatCardExpiry');

	/**
	 * Stripe: Payments Client-side Validation
	 * @link: https://github.com/stripe/jquery.payment
	 */
	$(document).on( 'blur', '#checkout input', function() {

		var input = $(this);

		// @function Indicated to user that field has an error.
		$.fn.has_error = function( err_msg ) {
			var error_message = err_msg;
			this.prev().removeClass('ok');
			this.prev().addClass('error'); // Mark field as error.
		}

		// @function Indicated to user that field has no errors.
		$.fn.is_ok = function() {
			this.prev().removeClass('error');
			this.prev().addClass('ok');
		}

		// @function Indicate selected payment method
		var show_payment_method = function( cardtype ) {
			$(document).find('ul.cc-icons li img').each( function() {
				if ( $(this).hasClass('bw') ) {
					$(this).addClass('active');
				} else {
					$(this).removeClass('active');
				}
			});
			$(document).find('ul.cc-icons li.'+ cardtype +' img.bw').removeClass('active');
			$(document).find('ul.cc-icons li.'+ cardtype +' img.color').addClass('active');
		}

		// @event Check if the field is blank.
		if ( input.val() === "" ) {
			input.has_error( "This field is blank." );
			return false;
		}

		// @event Validate Card Number.
		if ( input.data('stripe') === 'number' ) {
			if ( ! $.payment.validateCardNumber( input.val() ) ) {
				input.has_error( "The card number is not a valid card number." );
				return false;
			}
			show_payment_method( $.payment.cardType( input.val() ) );
		}
		// @event Validate Card Expiry (function seems to be tripping on something)
		// if ( input.data('stripe') === 'expiry' ) {
		// 	if ( ! $.payment.validateCardExpiry( input.val() ) ) {
		// 		input.has_error( "The card expiry is not valid." );
		// 		return false;
		// 	}
		// }
		// @event Validate CVC.
		if ( input.data('stripe') === 'cvc' ) {
			if ( ! $.payment.validateCardCVC( input.val() ) ) {
				input.has_error( "The card expiry is not valid." );
				return false;
			}
		}

		// If field validates, mark as ok
		$(this).is_ok();

	});

	/** * * * PayPal * * * **/

	/**
	 *	Start Payment Process for PayPal Users
	 *	@event
	 */
	$(document).on('click', '#checkout-with-paypal', function (){

		// Basic Info (Used by multiple services)
		var basic_info = convert_form_to_json( $('form#basic-info') );

		// Shipping Address
		// If shipping address is filled out, serialize data for processing.
		// Convert serialized data to a json object for parsing later
		var shipping_address = convert_form_to_json( $('form#shipping-address') );

		// Billing Address
		if ( $("#show-billing-address-fields").is(':checked') ) {
			var billing_address = {};
			$(document).find('form#billing-address input').each(function() {
				var key = $(this).data('stripe');
				billing_address[key] = $(this).val();
			});
		} else {
			var billing_address = shipping_address;
		}

		// Basket Info
		var basket_contents = {};
		for ( i = 0; i < simpleStorage.index().length; i++ ) {
			basket_contents[ simpleStorage.index()[i] ] = simpleStorage.get( simpleStorage.index()[i] );
		}

		$.post( to_market_scripts.ajaxurl, {
			dataType: "jsonp",
			action: 'paypal_prepare_payment',
			nonce: to_market_scripts.nonce,
			basicinfo: basic_info,
			shippingaddress: shipping_address,
			billingaddress: billing_address,
			basketcontents: basket_contents,
			redirectURL: document.URL,
		}, function(response) {
			if ( response.success === true ) {
				console.log('asdf');
				window.location = response.redirecturl;
			}
		});
	});



}); // jQuery
