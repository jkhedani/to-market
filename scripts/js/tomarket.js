jQuery( document ).ready( function($) {

 /**
	* jQuery Click Options
	* @requires jQuery
	* @desc Allow users to make dropdown/radio selections just by clicking! Ideal
	* for selecting options in shopping carts. All clickable elements are
	* created in JS so if JS fails, no worries!
	*/
	$.fn.clickop = function() {
		// Hide <select> element and title
		this.hide();
		// Create container for selections
		this.after('<div class="jquery-color-selection"><ul></ul></div>');
		// Grab available color options and create buttons in color container
		this.children('option').each(function() {
			$('.jquery-color-selection ul').append('<li><a href="#" data-color-value="'+$(this).val()+'" class="'+$(this).val()+'" style="background-color:'+$(this).data('background-color')+'" data-option-sold-out="'+$(this).data('option-sold-out')+'">'+$(this).val()+'</a></li>').addClass('capitalize');
		});
		// Select the appropriate color value
		$('.jquery-color-selection a').on('click',function() {
			$('.jquery-color-selection a').removeClass('selected');
			$(this).addClass('selected');
			var colorValue = $(this).attr('data-color-value');
			$('.product-color-selection').val(colorValue);
			return false;
		});
	}

	/**
	 * Hand Basket
	 * Requires: simpleStorage.js, jQuery
	 *
	 * @failure Code may fail in generating a new item in the basket when a
   *				  setting other than color can differentiate the same item.
	 * @to do Wrap this all up into one big class
	 *
	 * cart must fixes
	 *	1. ensure option selection loads correct image url and price
	 *	2. ensure the click to select option triggers the on.change event
	 *	3. Popover stylings a little wonky on small devices, home page & minster
	 *
	 */

	var format_money = function( cents ) {
		return '$ ' + ( cents / 100 ).toFixed(2);
	}

	// # Disable scripts for clients that cannot properly utilize simpleStorage
	if ( ! simpleStorage.canUse() ) {
		var message = "In order to have the best experience on our site, please leave private browsing or use a different browser. Thank you!";
		alert( message );
		return;
	}

	// # Generate basket
	//   This function doesn't display the basket but rather
	//   it constructs the basket to be shown using stored
	//   local data.
	var refresh_handbasket = function() {
		var handbasket_items = "";
		var handbasket_subtotal = 0
		// # Check if we have any products in our basket.
		if ( simpleStorage.index().length < 1 ) {
			var handbasket_items = "<h1>Shopping Cart</h1><p>Your shopping cart is currently empty.</p>"; // Load empty basket
		} else {

			// Place "legend" in the hand basket (gets overwritten if in template)
			handbasket_items = handbasket_items + "<h1>Shopping Cart</h1><div class='hand-basket-preview-legend'><span class='product-title'>Product Name</span><span class='product-price'>Unit Price</span><span class='product-qty'>Qty</span><span class='product-subtotal'>Subtotal</span><span class='product-remove'>Remove</span></div>";

			// Retrieve each existing product from the basket
			var handbasket_item_keys = simpleStorage.index();
			for ( i = 0; i < handbasket_item_keys.length; i++ ) {
				var handbasket_item_object = simpleStorage.get(handbasket_item_keys[i]);
				handbasket_subtotal = handbasket_subtotal + handbasket_item_object['product_option_price'];
				var handbasket_item  = "<div class='hand-basket-product' data-local-storage-key='"+handbasket_item_object['product_name'] + ' ' + handbasket_item_object['product_color_name']+"'>";
						handbasket_item += "	<span class='product-preview'><img src='"+handbasket_item_object['product_checkout_image_preview']+"' /></span>";
						handbasket_item += "	<div class='product-description'>";
						handbasket_item += "		<span class='product-title'>"+handbasket_item_object['product_name']+"</span>";
						handbasket_item += "		<span class='product-color'><span class='product-meta-title'>Color: </span>"+handbasket_item_object['product_color_name']+"</span>";
						handbasket_item += "	</div>";
						handbasket_item += "	<span class='product-price'>"+format_money(handbasket_item_object['product_option_price'])+"</span>";
						handbasket_item += "	<span class='product-qty'>"+handbasket_item_object['product_qty']+"</span>";
						handbasket_item += "	<span class='product-subtotal'>"+format_money(handbasket_item_object['product_qty'] * handbasket_item_object['product_option_price'])+"</span>";
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
			handbasket_items += '<hr />';
			handbasket_items += '<span class="donation-promo-text">'+to_market_scripts.donation_promo_text+'</span>';
			handbasket_items += '<a class="checkout">Checkout</a>';
		} // handbasket_has_items check

		// # Generate hand basket in #primary container
		$('[data-toggle="hand-basket"]').popover({
			'html'			: true,
			'placement' : 'bottom',
			'trigger'   : 'manual',
			'container' : '#primary',
			'content'   : handbasket_items,
			'template'  : "<div class='popover hand-basket' role='tooltip'><div class='arrow'></div><div class='popover-content hand-basket-content'></div></div>",
		});

	}

	// # Toggle View of Hand Basket
	$(document).on('click', '[data-toggle="hand-basket"]', function() {
		refresh_handbasket();
		$('[data-toggle="hand-basket"]').popover('toggle');
	});

	// # Remove product from cart
	$(document).on( 'click', '.remove-handbasket-item', function() {
		// Remove from local storage
		simpleStorage.deleteKey( $(this).parent().data('local-storage-key') );
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

	// # The <select> element you wish to click instead.
	$('.product-color-selection').clickop();

	// # Users select options on product
	$(document).on( 'change', '.product-option', function() {
		// Retrieve the data value we wish to retrieve and update
		var option_target = $(this).data('target');
		// Retrieve option values
		var option_new_data = $(this).find('option:selected').val();
		console.log(option_new_data);
		// Update #add-to-handbasket values
		$('.add-handbasket-item').attr( option_target, option_new_data);
	});

	// # Users Add Products to Hand Basket
	$(document).on('click', '.add-handbasket-item', function() {

		// Retrieve any existing cart items (index)
		var handbasket = simpleStorage.index();
		var handbasket_item_count = handbasket.length;

		// Compile product values based on data attributes
		var product_options = $(this).data();

		// Look through array. If any are left blank, error out.
		for ( var key in product_options ) {
			if ( product_options[key] ==  '' ) {
				console.log('Select an option');
				return;
			}
		}

		// Duplicate handling
		// Essentially we can have two of the same products with different options.
		// If the product is not a duplicate of an existing product.
		if ( handbasket.indexOf( product_options['product_name'] + ' ' + product_options['product_color_name'] ) < 0 ) {
			// Add product to basket (store as object) and set expiration for two days
			var new_product = simpleStorage.set( product_options['product_name'] + ' ' + product_options['product_color_name'] , product_options, { ttl: 172800000 });
			// Destroy existing cart, refresh our handbasket and re-display
			$('[data-toggle="hand-basket"]').popover('destroy');
			refresh_handbasket();
			$('[data-toggle="hand-basket"]').popover('toggle');
		} else {
			$('[data-toggle="hand-basket"]').popover('toggle');
		}


	});
	// simpleStorage.flush();

	/**
	 * Checkout
	 * Requires: jQuery, BootstrapJS (Modal)
	 */

}); // jQuery



// /**
// * 	jQuery Click Price Selection
// *	Changes front facing cost display values.
// */
// // If a product option selected is different than the current price and the price is not empty
// $('.jquery-color-selection a').on('click',function() {
// 	var standardPrice = $('body').find('.product-price').data('standard-product-price');
// 	var optionPrice = $('.product-color-selection').find(':selected').data('option-price');
// 	var displayedText = $('body .product-price').text();
// 	// Animate price if current option price is different from the product standard price or if the displayed price is  and is not equal to zero dollars.
// 	if ( ( ( standardPrice != optionPrice ) || ( displayedText != standardPrice ) ) && ( optionPrice != '$0.00' ) ) {
// 		// If option price is showing, insert Standard Price
// 		if ( displayedText ==  optionPrice ) {
// 			var priceToDisplay = standardPrice;
// 		// If standard price is showing, insert Option Price
// 		} else if ( displayedText == standardPrice ) {
// 			var priceToDisplay = optionPrice;
// 		}
// 		// Animate the price
// 		$( "body .product-price" ).animate({
// 			opacity: 0,
// 		}, 400, function() {
// 			$('body .product-price').html(optionPrice).css('opacity','1.0');
// 		});
// 	}
// });
