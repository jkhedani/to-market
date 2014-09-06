jQuery( document ).ready( function($) {

  /**
   * Stripe Payments
   * Requires stripejs, stripe-php
   */
  $(document).on('click', '[data-action="checkout"]', function() {

    // # Bring up "Processing Payment"
    $('#checkout .overlay.loading').show();

    // # Present Publishable API Key
    Stripe.setPublishableKey(stripe_vars.publishable_key);

    // # Construct payment token
    Stripe.card.createToken({
      number: $('#checkout #payment input.card-number').val(),
      cvc: $('#checkout #payment input.card-cvc').val(),
      exp_month: $('#checkout #payment input.card-exp-month').val(),
      exp_year: $('#checkout #payment input.card-exp-year').val()
    }, stripeResponseHandler);

    // # Stripe response handler: Gets token back or an error notification
    //   then submits form
    function stripeResponseHandler(status, response) {
      var $form = $('#stripe-payment-form');

      if ( response.error ) {
        console.log( response.error );
        // Show the errors on the form
        //$form.find('.payment-errors').text(response.error.message);
        $form.find('button').prop('disabled', false);
      } else {
        // response contains id and card, which contains additional card details
        var token = response.id;
        console.log( response );
        // Insert the token into the form so it gets submitted to the server
        $form.append($('<input type="hidden" name="stripeToken" />').val(token));
        // and submit
        $form.get(0).submit();
      }
    }

    return false;
  });

  // # Stripe: Payments Formatting
  $('#checkout #payment input.card-number').payment('formatCardNumber');
  $('#checkout #payment input.card-cvc').payment('formatCardExpiry');

  // # Stripe: Payments Client-side Validation

  // # Validate Info
     // Email

  // # Validate Payments



});
