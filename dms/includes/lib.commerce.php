<?php


// switch up button class
add_filter('woocommerce_loop_add_to_cart_link', 'pl_commerce_switch_buttons', 10, 2);
function pl_commerce_switch_buttons( $button, $product ){
	
	$button = str_replace('button', 'btn btn-overlay', $button); 
	
	$button = str_replace('Add to cart', '<i class="icon icon-shopping-cart"></i>', $button); 
	
	return $button;
	
}


// --- Add to cart buttons --- // 
// This output is HTML escaped so we can't add a shopping cart icon easily
// instead we set to an easily replaceable value via str_replace 
// and use the 'woocommerce_loop_add_to_cart_link' filter to replace it with icon
// add_filter('woocommerce_product_single_add_to_cart_text', 'pl_add_to_cart_text'); 
// add_filter('woocommerce_product_add_to_cart_text', 'pl_add_to_cart_text'); 
// function pl_add_to_cart_text( $text ){
// 	
// 	$text = 'Cart';
// 	
// 	return $text;
// 	
// }

// Remove normal button
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
// Remove normal thumb markup
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

add_action('woocommerce_before_shop_loop_item_title', 'product_thumbnail_with_cart', 10 );
function product_thumbnail_with_cart() { ?>
	
   <div class="product-thumb-wrap">
	   	<?php echo  woocommerce_get_product_thumbnail(); ?>
	   	<?php woocommerce_get_template( 'loop/add-to-cart.php' ); ?>
   	</div>
<?php 
}


 

// update the cart with ajax

//add_filter('add_to_cart_fragments', 'add_to_cart_fragment');
function add_to_cart_fragment( $fragments ) {
	global $woocommerce;
	ob_start();
	$fragments['a.cart-parent'] = ob_get_clean();
	return $fragments;
}

//add_filter('add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');
	 
function woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;
	
	ob_start(); ?>
	<a class="cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>"><div class="cart-icon-wrap"><i class="icon-salient-cart"></i> <div class="cart-wrap"><span><?php echo $woocommerce->cart->cart_contents_count; ?> </span></div> </div></a>
	<?php
	
	$fragments['a.cart-contents'] = ob_get_clean();
	
	return $fragments;
}


//chnge how many products are displayed per page	
//add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 12;' ), 20 );



//add link to item titles
add_action('woocommerce_before_shop_loop_item_title','product_item_title_link_open');
add_action('woocommerce_after_shop_loop_item_title','product_item_title_link_close');
function product_item_title_link_open(){
	echo '<a class="the-item-title" href="'.get_permalink().'">';
}
function product_item_title_link_close(){
	echo '</a>';
}
