<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WPJMCL_Listing.
 *
 * Listing class.
 *
 * @class		WPJMCL_Listing
 * @version		1.0.0
 * @author		Jeroen Sormani
 */
class WPJMCL_Listing {


	/**
	 * Construct.
	 *
	 * Initialize this class including hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add 'claim this listing' link
		add_action( 'single_job_listing_start', array( $this, 'claim_listing_link' ) );

		// Check for claim-listing action
		add_action( 'template_redirect', array( $this, 'claim_listing_action' ) );

		// Add a notice when claiming a listing
		add_action( 'single_job_listing_start', array( $this, 'success_claim_notice' ) );

		// Add order item meta (at create_order() method)
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta_listing' ), 10, 3 );

		// Display meta at checkout
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_listing_at_checkout' ), 10, 2 );

		// Re-add meta when getting cart items session
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session_add_item_meta' ), 10, 3 );

		// Add post classs
		add_filter( 'post_class', array( $this, 'add_post_class' ) );

	}


	/**
	 * Is claimable.
	 *
	 * Check if the current listing is claimable.
	 *
	 * @since 1.0.0
	 */
	public function is_claimable() {

		$listing_id = get_the_ID();

		if ( ! $listing_id ) :
			return false;
		endif;

		// Check if the listing isn't claimed before (and approved)
		$claims = get_posts( array(
			'fields' 			=> 'ids',
			'posts_per_page' 	=> '1',
			'post_status'		=> 'publish',
			'post_type'			=> 'claim',
			'meta_query'		=> array(
				array(
					'key'		=> '_listing_id',
					'value'		=> $listing_id,
					'compare'	=> '=',
				),
				array(
					'key'		=> '_status',
					'value'		=> 'approved',
					'compare'	=> '=',
				),
			),
		) );

		if ( ! empty( $claims ) && isset( $claims[0] ) ) :
			return apply_filters( 'wpjmcl_is_claimable', false, $listing_id );
		else :
			return apply_filters( 'wpjmcl_is_claimable', true, $listing_id );
		endif;

	}


	/**
	 * Claim listing link.
	 *
	 * Display 'Claim this listing' link.
	 *
	 * @since 1.0.0
	 */
	public function claim_listing_link() {

		global $post;

		if ( $this->is_claimable() ) :

			$href = wp_nonce_url( add_query_arg( array( 'action' => 'claim_listing', 'listing_id' => $post->ID ) ), 'claim_listing', 'claim_listing_nonce' );
			?><a href='<?php echo $href; ?>' class='claim-listing'><?php _e( 'Claim this listing', 'wp-job-manager-claim-listing' ); ?></a><?php

		endif;

	}


	/**
	 * Claim action.
	 *
	 * Check if there is a user-fired claim-listing action.
	 * Depending if the claiming is free or paid, it will redirect to checkout.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id ID of the current post.
	 */
	public function claim_listing_action() {

		// Stop if its the wrong action
		if ( ! isset( $_GET['action'] ) || 'claim_listing' != $_GET['action'] ) :
			return;
		endif;

		// Verify nonce
		if ( ! isset( $_GET['claim_listing_nonce'] ) || ! wp_verify_nonce( $_GET['claim_listing_nonce'], 'claim_listing' ) ) :
			return;
		endif;

		$listing_id 	= $_GET['listing_id'];
		$paid_claiming 	= apply_filters( 'wpjmcl_paid_claiming', get_option( 'wpjmcl_paid_claiming', 'no' ), $listing_id );

		// Claiming a listing is free ('' == free)
		if ( '' == $paid_claiming ) :

			// Check if user is logged in
			if ( ! is_user_logged_in() ) :
				add_action( 'single_job_listing_start', array( $this, 'error_login_notice' ) );
				return;
			endif;

			WP_Job_Manager_Claim_Listing()->claims->create_claim( $listing_id );
			$redirect = remove_query_arg( array( 'action', 'claim_listing_nonce', 'listing_id' ) );
			$redirect = add_query_arg( 'claimed_listing', '1', $redirect );

		// Claiming a listing is paid
		else :
			$product_to_buy = apply_filters( 'wpjmcl_paid_claiming_product', get_option( 'wpjmcl_paid_claiming_product' ), $listing_id );

			// empty cart
			WC()->cart->empty_cart();

			// add product to cart
			WC()->cart->add_to_cart( $product_to_buy, 1, '', '', array( 'listing_id' => $listing_id ) );
			$redirect = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
		endif;

		$redirect = apply_filters( 'wpjmcl_claim_action_redirect', $redirect );
		wp_redirect( $redirect );
		exit;

	}


	/**
	 * Success claim notice.
	 *
	 * Display a success claim notice when a claim has been done.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id ID of the current post.
	 */
	public function success_claim_notice() {

		// Bail if message shouldn't be shown
		if ( ! isset( $_GET['claimed_listing'] ) || '1' != $_GET['claimed_listing'] ) :
			return;
		endif;

		ob_start();
			?><div class='job-manager-message'><?php _e( 'Your claim has been successfully submitted', 'wp-job-manager-claim-listing' ); ?></div><?php
		$html = ob_get_clean();

		echo apply_filters( 'wpjmcl_success_claim_message', $html );

	}


	/**
	 * Login notice.
	 *
	 * Display a login notice when someone is attempting
	 * to claim a listing without being logged in.
	 *
	 * @since 1.0.0
	 */
	public function error_login_notice() {

		ob_start();
			?><div class='job-manager-error'><?php _e( 'Please log-in to claim this listing', 'wp-job-manager-claim-listing' ); ?></div><?php
		$html = ob_get_clean();

		echo apply_filters( 'wpjmcl_claim_login_mesage', $html );

	}


	/**
	 * Order item meta.
	 *
	 * Add item meta (listing ID) to the order item.
	 * Fires on WC()->order->create_order() method.
	 *
	 * @since 1.0.0
	 *
	 * @param int 		$item_id 		Order item ID.
	 * @param array		$values 		List of values given through WooCommerce.
	 * @param string	$cart_item_key	ID of the item in the cart.
	 */
	public function add_order_item_meta_listing( $item_id, $values, $cart_item_key ) {

		if ( isset( $values['listing_id'] ) ) :
			wc_add_order_item_meta( $item_id, '_listing_id', $values['listing_id'] );
		endif;

	}


	/**
	 * Display listing (cart).
	 *
	 * Display the listing ID at the checkout.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array 	$meta 		List of existing meta to display at checkout.
	 * @param 	array	$cart_item	List of cart product values.
	 * @return	array				List of modified meta to display at checkout.
	 */
	public function display_listing_at_checkout( $meta, $cart_item ) {

		if ( isset( $cart_item['listing_id'] ) ) :

			$listing = get_post( $cart_item['listing_id'] );
			$meta[] = array(
				'name' 	=> __( 'Listing', 'wp-job-manager-claim-listing' ),
				'value' => $listing->post_title,
			);
		endif;

		return $meta;

	}


	/**
	 * Session cart item meta.
	 *
	 * When cart items are retrieved from the session, it will remove all item meta
	 * this function makes sure it will re-add those meta.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array 	$cart_item 	Cart item values.
	 * @param	array 	$values		Old values.
	 * @param	string	$key		Cart item key.
	 * @return	array				Modified cart item values.
	 */
	public function get_cart_item_from_session_add_item_meta( $cart_item, $values, $key ) {

		if ( isset( $values['listing_id'] ) ) :
			$cart_item['listing_id'] = $values['listing_id'];
		endif;

		return $cart_item;

	}


	/**
	 * Post class.
	 *
	 * Add a post class 'claimed' or 'not-claimed'.
	 *
	 * @since 1.0.0
	 *
	 * @param	array $classes 	List of existing classes.
	 * @return	array 			List of modified classes.
	 */
	public function add_post_class( $classes ) {
		global $post;

		$id = $post->_listing_id;
		$status = $post->_status;

		if ( ! ( $id && $status ) ) :
			$classes[] = 'not-claimed';
		else :
			$classes[] = 'claimed';
		endif;

		return $classes;
	}

}
