<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WPJMCL_Claims.
 *
 *	Class to handle all claim business.
 *
 * @class		WPJMCL_Claims
 * @version		1.0.0
 * @author		Jeroen Sormani
 */
class WPJMCL_Claims {


	/**
	 * Constructor.
	 *
	 * Initialize this class including hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Update post type messages
		add_filter( 'post_updated_messages', array( $this, 'custom_messages' ) );

		// Add custom columns
		add_filter( 'manage_edit-claim_columns', array( $this, 'custom_columns' ), 11 );
		// Add contents to custom columns
		add_action( 'manage_claim_posts_custom_column', array( $this, 'custom_column_contents' ), 10, 2 );

		// Check for status-change action
		add_action( 'init', array( $this, 'claim_status_action' ) );

		// Add meta box
		add_action( 'add_meta_boxes', array( $this, 'data_meta_box' ) );
		// Save meta box
		add_action( 'save_post', array( $this, 'save_data_meta_box' ) );

		// Add to menu
		add_action( 'admin_menu', array( $this, 'add_claim_to_menu' ) );

		// Check every order when updates to completed
		$check_order_on = str_replace( 'wc-', '', get_option( 'wpjmcl_check_order_on_status', 'completed' ) );
		add_action( 'woocommerce_order_status_' . $check_order_on, array( $this, 'check_order_for_claims' ) );

		// Add Claim filter
		add_action( 'restrict_manage_posts', array( $this, 'post_type_claim_filters' ) );
		add_filter( 'request', array( $this, 'post_type_claim_filters_request' ) );

	}


	/**
	 * Post type.
	 *
	 * Register post type.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Claims ', 'wp-job-manager-claim-listing' ),
			'singular_name'      => __( 'Claim', 'wp-job-manager-claim-listing' ),
			'menu_name'          => __( 'Claims', 'wp-job-manager-claim-listing' ),
			'name_admin_bar'     => __( 'Claims', 'wp-job-manager-claim-listing' ),
			'add_new'            => __( 'Add New', 'wp-job-manager-claim-listing' ),
			'add_new_item'       => __( 'Add New Claim', 'wp-job-manager-claim-listing' ),
			'new_item'           => __( 'New Claim', 'wp-job-manager-claim-listing' ),
			'edit_item'          => __( 'Edit Claim', 'wp-job-manager-claim-listing' ),
			'view_item'          => __( 'View Claim', 'wp-job-manager-claim-listing' ),
			'all_items'          => __( 'All Claims', 'wp-job-manager-claim-listing' ),
			'search_items'       => __( 'Search Claims', 'wp-job-manager-claim-listing' ),
			'parent_item_colon'  => __( 'Parent Claims:', 'wp-job-manager-claim-listing' ),
			'not_found'          => __( 'No Claims found.', 'wp-job-manager-claim-listing' ),
			'not_found_in_trash' => __( 'No Claims found in Trash.', 'wp-job-manager-claim-listing' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'claim' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
		);
		register_post_type( 'claim', $args );

	}


	/**
	 * Admin messages.
	 *
	 * Custom admin messages when using claim post type.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $messages List of existing post messages.
	 * @return 	array 			Full list of all messages.
	 */
	public function custom_messages( $messages ) {

		$post             = get_post();
		$post_type        = 'claim';
		$post_type_object = get_post_type_object( $post_type );

		$messages[ $post_type ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Claim updated.', 'wp-job-manager-claim-listing' ),
			2  => __( 'Custom field updated.', 'wp-job-manager-claim-listing' ),
			3  => __( 'Custom field deleted.', 'wp-job-manager-claim-listing' ),
			4  => __( 'Claim updated.', 'wp-job-manager-claim-listing' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Claim restored to revision from %s', 'wp-job-manager-claim-listing' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Claim published.', 'wp-job-manager-claim-listing' ),
			7  => __( 'Claim saved.', 'wp-job-manager-claim-listing' ),
			8  => __( 'Claim submitted.', 'wp-job-manager-claim-listing' ),
			9  => sprintf(
				__( 'Claim scheduled for: <strong>%1$s</strong>.', 'wp-job-manager-claim-listing' ),
				date_i18n( __( 'M j, Y @ G:i', 'wp-job-manager-claim-listing' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Claim draft updated.', 'wp-job-manager-claim-listing' )
		);

		$permalink 					= admin_url( 'edit.php?post_type=claim' );
		$return_to_claims_link 		= sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'Return to claims', 'wp-job-manager-claim-listing' ) );
		$messages[ $post_type ][1] 	.= $return_to_claims_link;
		$messages[ $post_type ][6] 	.= $return_to_claims_link;
		$messages[ $post_type ][9] 	.= $return_to_claims_link;
		$messages[ $post_type ][8]  .= $return_to_claims_link;
		$messages[ $post_type ][10] .= $return_to_claims_link;

		return $messages;

	}


	/**
	 * Post columns.
	 *
	 * Set custom columns for the new post type.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $columns 	List of existing post columns.
	 * @return 	array 			List of edited columns.
	 */
	public function custom_columns( $existing_columns ) {

		$columns['cb']    		= '<input type="checkbox" />';
		$columns['status']		= __( 'Status', 'wp-job-manager-claim-listing' );
		$columns['title']		= __( 'Title', 'wp-job-manager-claim-listing' );
		$columns['listing']		= __( 'Listing', 'wp-job-manager-claim-listing' );
		$columns['order_id']	= __( 'Order #', 'wp-job-manager-claim-listing' );
		$columns['claimer']		= __( 'Claimer', 'wp-job-manager-claim-listing' );
		$columns['date']		= __( 'Date', 'wp-job-manager-claim-listing' );
		$columns['actions']		= __( 'Actions', 'wp-job-manager-claim-listing' );

		$merged_columns = array_merge( $columns, $existing_columns );

		unset( $merged_columns['title'] );

		return $merged_columns;

	}


	/**
	 * Column contents.
	 *
	 * Ouput the custom columns contents.
	 *
	 * @since 1.0.0
	 *
	 * @param string 	$columns Slug of the current columns to ouput data for.
	 * @param int 		$post_id ID of the current post.
	 */
	public function custom_column_contents( $column, $post_id ) {

		switch( $column ) :

			case 'status' :
				$status 	= get_post_meta( $post_id, '_status', true );
				$statuses 	= WP_Job_Manager_Claim_Listing()->statuses;

				?><span class='status status-<?php echo strtolower( $status ); ?>'>
					<?php echo isset( $statuses[ $status ] ) ? $statuses[ $status ] : __( 'Unknown', 'wp-job-manager-claim-listing' ); ?>
				</span><?php
			break;

			case 'listing' :
				$listing 	= get_post( get_post_meta( $post_id, '_listing_id', true ) );
				$href 		= admin_url( sprintf( 'post.php?post=%s&action=edit', $listing->ID ) );
				?><a href='<?php echo $href; ?>'><?php echo $listing->post_title; ?></a><?php
			break;

			case 'order_id' :
				$order_id 	= get_post_meta( $post_id, '_order_id', true );
				$href 		= admin_url( sprintf( 'post.php?post=%s&action=edit', $order_id ) );
				?><a href='<?php echo $href; ?>'>#<?php echo $order_id; ?></a><?php
			break;

			case 'claimer' :
				$post = get_post( $post_id );
				$user = get_userdata( $post->post_author );
				?><a href='user-edit.php?user_id=<?php echo $user->ID; ?>'><?php echo $user->display_name; ?></a><?php
			break;

			case 'actions' :

				if ( 'approved' != get_post_meta( $post_id, '_status', true ) && 'declined' != get_post_meta( $post_id, '_status', true ) ) :
					$approve_href = wp_nonce_url( add_query_arg( array(
						'actions' 	=> 'claim_status_update',
						'claim_id' 	=> $post_id,
						'status' 	=> 'approved',
					) ), 'claim_status_update', 'claim_status_update_nonce' );
					$decline_href = wp_nonce_url( add_query_arg( array(
						'actions' 	=> 'claim_status_update',
						'claim_id' 	=> $post_id,
						'status' 	=> 'declined',
					) ), 'claim_status_update', 'claim_status_update_nonce' );

					?><a class='button' href='<?php echo $approve_href; ?>' title='<?php _e( 'Approve', 'wp-job-manager-claim-listing' ); ?>'>
						<span class='dashicons dashicons-yes'></span></a>
					<a class='button' href='<?php echo $decline_href; ?>' title='<?php _e( 'Decline', 'wp-job-manager-claim-listing' ); ?>'>
						<span class='dashicons dashicons-no'></span></a>&nbsp;<?php
				endif;

				?><a class='button' href='<?php echo admin_url( "post.php?post=$post_id&action=edit" ); ?>' title='<?php _e( 'View claim', 'wp-job-manager-claim-listing' ); ?>'>
					<span class='dashicons dashicons-visibility'></span></a><?php

			break;

		endswitch;

	}



	/**
	 * Meta box.
	 *
	 * Add an meta box with all the claim data.
	 *
	 * @since 1.0.0
	 */
	public function data_meta_box() {
		add_meta_box( 'claim_data', __( 'Claim data', 'wp-job-manager-claim-listing' ), array( $this, 'data_meta_box_contents' ), 'claim', 'normal' );
	}


	/**
	 * Meta box content.
	 *
	 * Get contents from file and put them in the meta box.
	 *
	 * @since 1.0.0
	 */
	public function data_meta_box_contents() {

		$statuses = WP_Job_Manager_Claim_Listing()->statuses;

		/**
		 * Data meta box
		 */
		require_once plugin_dir_path( __FILE__ ) . 'views/meta-box-claims-data.php';

	}


	/**
	 * Save Meta box.
	 *
	 * Save the given contents from the meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id ID of the current post.
	 */
	public function save_data_meta_box( $post_id ) {

		if ( ! isset( $_POST['data_meta_box_nonce'] ) ) :
			return $post_id;
		endif;

		$nonce = $_POST['data_meta_box_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'data_meta_box' ) ) :
			return $post_id;
		endif;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) :
			return $post_id;
		endif;

		// Update status if its not the same
		$current_status = get_post_meta( $post_id, '_status', true );
		if ( $current_status != $_POST['status'] ) :
			$this->update_claim_status( $post_id, $_POST['status'] );
		endif;

	}


	/**
	 * Menu item.
	 *
	 * Add 'Claims' to the sub-menu of 'Job Listings'.
	 *
	 * @since 1.0.0
	 */
	public function add_claim_to_menu() {

		add_submenu_page( 'edit.php?post_type=job_listing', 'Claims', 'Claims', 'manage_options', '/edit.php?post_type=claim' );

	}


	/**
	 * Status action.
	 *
	 * Check if the there is an admin-fired action to change the status.
	 *
	 * @since 1.0.0
	 */
	public function claim_status_action() {

		// Check action
		if ( ! isset( $_GET['actions'] ) || 'claim_status_update' != $_GET['actions'] ) :
			return;
		endif;

		// Verify nonce
		if ( ! isset( $_GET['claim_status_update_nonce'] ) || ! wp_verify_nonce( $_GET['claim_status_update_nonce'], 'claim_status_update' ) ) :
			return;
		endif;

		// Check claim ID & status
		if ( ! isset( $_GET['claim_id'] ) || ! isset( $_GET['status'] ) ) :
			return;
		endif;

		$post_id 	= $_GET['claim_id'];
		$status 	= $_GET['status'];
		$statuses 	= WP_Job_Manager_Claim_Listing()->statuses;

		// Stop if status doesn't exist
		if ( ! array_key_exists( $status, $statuses ) ) :
			return;
		endif;

		$this->update_claim_status( $post_id, $status );

		// redirect
		wp_redirect( remove_query_arg( array( 'actions', 'claim_status_update_nonce', 'claim_id', 'status' ) ) );
		exit;

	}


	/**
	 * Update status.
	 *
	 * Update the status of a claim.
	 *
	 * @since 1.0.0
	 *
	 * @param int 		$claim_id 	ID of the (claim) post ID to update.
	 * @param string 	$new_status	New status to update to.
	 */
	public function update_claim_status( $claim_id, $new_status) {

		$listing_id = get_post_meta( $claim_id, '_listing_id', true );

		// Update status
		update_post_meta( $claim_id, '_status', $new_status );


		// Change post author
		if ( 'approved' == $new_status ) :

			remove_action( 'save_post', array( $this, 'save_data_meta_box' ) );
			$claim = get_post( $claim_id );
			wp_update_post( array( 'ID' => $listing_id, 'post_author' => $claim->post_author ) );
			add_action( 'save_post', array( $this, 'save_data_meta_box' ) );

		endif;

		do_action( 'wpjmcl_claim_status_update_to_' . $new_status, $claim_id );

	}


	/**
	 * Create claim.
	 *
	 * Create a new claim. Fires when user clicks the 'claim this listing'
	 * link or after an order has been completed (paid claiming).
	 *
	 * @since 1.0.0
	 *
	 * @param 	int $listing_id ID of the listing one wants to claim.
	 * @param 	int $order_id	ID of the order where the claim was paid in (if paid claiming).
	 * @return	int				ID of the (claim) post.
	 */
	public function create_claim( $listing_id, $order_id = '' ) {

		// Check if user is logged in
		if ( ! is_user_logged_in() ) :
			return;
		endif;

		// Bail if the listing is already claimed (by this user)
		if ( $this->claimed_before( $listing_id ) ) :
			return;
		endif;

		$listing = get_post( $listing_id );

		$claim_args = array(
			'post_title' 	=> sprintf( __( 'Claim listing: %s', 'wp-job-manager-claim-listing' ), $listing->post_title ),
			'post_status' 	=> 'publish',
			'post_type' 	=> 'claim',
		);
		if ( isset( $order_id ) ) :
			$claim_args['post_author'] = get_post_meta( $order_id, '_customer_user', true );
		endif;
		$claim_id = wp_insert_post( $claim_args );

		update_post_meta( $claim_id, '_status', apply_filters( 'wpjmcl_new_claim_status', 'pending' ) );
		update_post_meta( $claim_id, '_order_id', apply_filters( 'wpjmcl_new_claim_order_id', $order_id ) );
		update_post_meta( $claim_id, '_listing_id', apply_filters( 'wpjmcl_new_claim_listing_id', $listing->ID ) );

		do_action( 'wpjmcl_after_create_claim', $claim_id );

		return $claim_id;

	}


	/**
	 * Check order.
	 *
	 * Check an order to see if there are any claiming products
	 * in the order. If so, a claim will be made.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id ID of the order to check.
	 */
	public function check_order_for_claims( $order_id ) {

		$order 	= new WC_Order( $order_id );
		$items 	= $order->get_items();

		foreach ( $items as $key => $product ) :

			if ( isset( $product['listing_id'] ) && ! $this->claimed_before( $product['listing_id'], $order_id ) ) :

				// Create claim
				$claim_id = $this->create_claim( $product['listing_id'], $order->id );

				// Set order note
				$view_claim = sprintf( '<a href="post.php?post=%s&action=edit">%s</a>', $claim_id, __( 'View claim', 'wp-job-manager-claim-listing' ) );
				$order->add_order_note( sprintf( 'Claim has been created. %s', $view_claim ) );

			endif;

		endforeach;

	}


	/**
	 * Claimed before.
	 *
	 * Check if a claim has already been done via an order.
	 * Prevents double claims when a order status goes to completed
	 * multiple times (for whatever reason).
	 *
	 * @since 1.0.0
	 *
	 * @param 	int 	$listing_id ID of the listing thats being claimed.
	 * @param 	int 	$order_id 	ID of the order where the claim is in.
	 * @return 	BOOL				TRUE if a claim already has been done, else FALSE.
	 */
	public function claimed_before( $listing_id, $order_id = '' ) {

		$claimed_before = false;

		// Get claims
		$claim_args = array(
			'post_type' => 'claim',
			'meta_query' => array(
				array(
					'key'     => '_listing_id',
					'value'   => $listing_id,
					'compare' => '=',
				),
			),
		);

		// Add order argument
		if ( ! empty( $order_id ) ) :
			$claim_args['meta_query'][] = array(
				'key'     => '_order_id',
				'value'   => $order_id,
				'compare' => '=',
			);
		endif;

		// Get the claims of the current user (when its not order related)
		if ( empty( $order_id ) ) :
			$claim_args['post_author'] = get_current_user_id();
		endif;

		$claims = get_posts( $claim_args );

		// Claims found with the given arguments
		if ( $claims ) :
			$claimed_before = true;
		endif;

		return apply_filters( 'wpjmcl_claimed_before', $claimed_before, $listing_id, $order_id );

	}


	/**
	 * Status filter dropdown.
	 *
	 * Display the claim status filter dropdown at the
	 * claims post overview page.
	 *
	 * @since 1.1.0
	 *
	 * @global string 	$typenow 	The current post type of the page loading.
	 */
	public function post_type_claim_filters() {

		global $typenow;

	    if ( $typenow != 'claim' ) :
	    	return;
	    endif;

		?><select name='claim_status' id='dropdown_claim_status'>
			<option value=''><?php _e( 'All claim statuses', 'wp-job-manager-claim-listing' ); ?></option><?php

			foreach ( WP_Job_Manager_Claim_Listing()->statuses as $key => $status ) :
				?><option value='<?php echo $key; ?>' <?php selected( isset( $_GET['claim_status'] ) ? $_GET['claim_status'] : '', $key ); ?>><?php echo $status; ?></option><?php
			endforeach;

		?></select><?php

	}


	/**
	 * Filter request.
	 *
	 * Filter the posts request when the claim status is set.
	 *
	 * @since 1.1.0
	 *
	 * @param 	array $vars Variables set for the Query request.
	 * @return	array		Modified variables with the status meta query.
	 */
	public function post_type_claim_filters_request( $vars ) {

		// Bail if claim status GET is not set.
		if ( ! isset( $_GET['claim_status'] ) || empty( $_GET['claim_status'] ) ) :
			return $vars;
		endif;

		$vars['meta_query'][] = array(
			'key'		=> '_status',
			'comare'	=> '=',
			'value'		=> $_GET['claim_status'],
		);

		return $vars;

	}


}
