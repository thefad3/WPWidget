<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WPJMCL_Settings.
 *
 * This class handles everything concerning the settings.
 *
 * @class		WPJMCL_Settings
 * @version		1.0.0
 * @author		Jeroen Sormani
 */
class WPJMCL_Settings {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Settings tab
		add_action( 'job_manager_settings', array( $this, 'wpjmcl_settings' ) );

	}


	/**
	 * Settings page.
	 *
	 * Add an settings tab to the Listings -> settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array 	$settings	Array of default settings.
	 * @return 	array	$settings	Array including the new settings.
	 */
	public function wpjmcl_settings( $settings )  {

		$product_list = array();
		$product_args = array(
			'posts_per_page'	=> '-1',
			'post_type' 		=> 'product',
		);
		$products = get_posts( $product_args );

		if ( $products ) :
			foreach ( $products as $product ) :
				$product_list[ $product->ID ] = $product->post_title;
			endforeach;
		endif;

		// Backwards compatibility
		if ( version_compare( WC()->version, '2.2', '<' ) ) :
			$order_statuses = wp_list_pluck( (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) ), 'name', 'slug' );
		else :
			$order_statuses = wc_get_order_statuses();
		endif;

		foreach ( $order_statuses as $key => $status ) :
			$status_list[ $key ] = $status;
		endforeach;

		$settings['wpjmcl_settings'] = array(
			__( 'Claim Listing', 'wp-job-manager-claim-listing' ),
			array(

				array(
					'name'			=> 'wpjmcl_paid_claiming',
					'type'			=> 'checkbox',
					'label'			=> __( 'Paid or Free', 'wp-job-manager-claim-listing' ),
					'cb_label'		=> __( 'Paid listing claiming', 'wp-job-manager-claim-listing' ),
					'desc'			=> __( 'When checked, users must pay for claiming a listing. (Uses WC defined product below)', 'wp-job-manager-claim-listing' ),
					'std'			=> 0,
				),

				array(
					'name'			=> 'wpjmcl_paid_claiming_product',
					'type'			=> 'select',
					'label'			=> __( 'Product', 'wp-job-manager-claim-listing' ),
					'desc'			=> __( 'Product required to buy to claim a listing. (option above must be checked require this)', 'wp-job-manager-claim-listing' ),
					'options'		=> $product_list,
					'class'			=> 'chosen',
				),

				array(
					'name'			=> 'wpjmcl_check_order_on_status',
					'std'			=> 'completed',
					'type'			=> 'select',
					'label'			=> __( 'Check Order On', 'wp-job-manager-claim-listing' ),
					'desc'			=> __( 'Select the order status to check the order on claims.', 'wp-job-manager-claim-listing' ),
					'options'		=> $status_list,
				),

			),
		);

		return $settings;

	}

}
