# Development Notes
Just some notes for my own personal development. This file will be deleted for
good once the first release is made stable.

## Process

1. Hide and show hand basket.

## Current Installation Instructions
1. Install ACF & Import the config file. May want figure out a way to do this
   using some sort of dependency config software. ACF fields declared in theme
   is no bueno.

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

## Thinking Through Products

product
  options
    color
    price
    >>>
    size
      [sizes]
    gender
    dimensions
    weight
    other meta ( is this sold out, throw in shipping, etc. )
    <<<

example

[ state I ]
product A
    Orange (soldout) Black Taupe
    S M(soldout) L
    Qty

[ state II ]
product A

    product-option='color'
    Orange (soldout) √ Black Taupe
    product-option='size'
    S M(soldout) L
    product-option='qty'
    Qty

[ state III ]
product A

    product-option='color'
    Orange (soldout) √ Black Taupe
    product-option='size'
    S M(soldout) L
    product-option='qty'
    Qty

add-to-cart data-color="black"

# on the product page (PHP)
  construct data attributes for add to cart

# on product-option selection (JS)


# on add to basket selection (JS)
  if all data attributes are not filled out, throw proper error
  otherwise, collect data attributes, construct product and add to cart

# additional parameters
  if option is "sold" out
    lower opacity or something

## More Thoughts
// Product object
  // Generic content

// Product Options

  // Color

  // Size

  // Shipping Properties




// !! Stipulations



A pair of options can be tied together (e.g. a L bag might have )



-------------------------

things we are using the data attributes for

local storage
option selection


-------------------------


+ additional option to allow extra options may be required

displaying options for selection
collecting options for submission


Orange | Black
S      | M     | L
389    | 349


SKU GENERATOR + INVENTORY MGMT.
Generated on the front end only
Allows for 99 variants of one option (e.g. 99 Sizes, 99 Colors)
All products must be arranged a certain way.

1. Create Admin Menu Page to house listings of all products. (This could be a
   meta box on the products page itself as well)
2.


SKU Type
1. Basic
   ENUMERATOR : #
   PREFIX     : L20 (number/letters)
   PRODNUM    : 0199 (199 products)
     for each product, increment number, zero starting
   OPTIONS    : 0
     for each option, increment number, zero starting

Orange S 389 #200100
Orange M 389 #200101
Orange L 389 #200102
Black  S 349 #200200
Black  M 349 #200201
Black  L 349 #200102


The way we establish a particular product's option is by


// Process #1
// # Load all options

// # Display only one of each option

// # On selection, load properties

// Process #2
// #
