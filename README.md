# Our Shop
Stripe + EasyPost + PayPal
A simple Wordpress shopping + checkout solution that doesn't require any fuss
with e-commercey-bankey nonsense. Secure and safe right out of the box. Yes,
PayPal gets to tag along because a large part of the world still pays with
PayPal but you can choose to alienate them! Your choice, as always, though.

### Dependencies
Your shop, Our Shop, requires the follow libraries and
plugins/add-ons/modules/whatever you want to reference them as:
- [Stripe API PHP Client Library ](https://github.com/stripe/stripe-php) (included)
- [Paypal Rest API PHP Client Library](https://github.com/paypal/rest-api-sdk-php) (included)
- [EasyPost PHP Client Library](https://github.com/EasyPost/easypost-php) (included)
- [HandBasket](#)(included)
- [Advanced Custom Fields WP Plugin](http://www.advancedcustomfields.com/)

### Installation
1. Coming soon!

### Updating
1. Planning on implementing an update routine.
2. Possibly thinking of using git submodules....



## Development Notes

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
