# Development Notes
Just some notes for my own personal development. This file will be deleted for
good once the first release is made stable.

## Release To Dos
- Ensure that products and settings are created and hidden upon
  installation and uninstall.

## Various Settings Checklist
√ Tax rate?
√ Are you selling a physical product ?
  ! Don't need this BUT ensure that you are checking that
    the shipping vendor info is filled out before checkout
Do you have an SSL cert? ( find a way to verify this for live environments )
  - If they do not have one on a live mode site, disable everything gracefully!
  - If they do not, allow a manual override. Their call.

Checkout
  Credit Card
    [radio] Stripe
      -API Keys
  Alternate
    [radio] Paypal
      -API Keys

Shipping
  EasyPost

## Shopping Cart
   Shopping cart store in local storage

   +
   <li class="product" data-product-meta="">Product Title</li>


## Checkout

   + Load necessary API's and libraries (possibly saves time on submission)

   + Retrieve cart info from

   + Retrieve user billing address
     - if we are shipping a physical product
       - is your billing address the same as your mailing address

   + Retrieve customer payment info


## File Structure
/(theme)
  /ourstore (own repo, tracked separately from theme)
    scripts.js
    ShoppingCart.php (this should be small - custom config of the setting)
    Checkout.php
    /lib
      /HandBasket
        /img
      /Stripe
      /PayPal
      /EasyPost
