<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WPJMR_Settings.
 *
 * Handle the admin settings.
 *
 * @class		WPJMR_Settings
 * @version		1.1.0
 * @author		Jeroen Sormani
 */
class WPJMR_Settings {


	/**
	 * Construct.
	 *
	 * Initialize this class including hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add settings
		add_action( 'job_manager_settings', array( $this, 'settings_tab' ) );

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
	public function settings_tab( $settings ) {
		$default_categories = '';

		if ( is_array( wpjmr()->wpjmr_get_review_categories() ) ) :
			$default_categories = implode( PHP_EOL, wpjmr()->wpjmr_get_review_categories() );
		endif;

		$settings['wpjmr_settings'] = array(
			__( 'Reviews', 'wp-job-manager-reviews' ),
			array(
				array(
					'name'			=> 'wpjmr_star_count',
					'std'			=> '5',
					'placeholder'	=> '',
					'label'			=> __( 'Stars', 'wp-job-manager-reviews' ),
					'desc'			=> __( 'How many stars would you like to use?', 'wp-job-manager-reviews' ),
					'attributes'	=> array()
				),
				array(
					'name'			=> 'wpjmr_categories',
					'std'			=> $default_categories,
					'placeholder'	=> '',
					'label'			=> __( 'Review categories', 'wp-job-manager-reviews' ),
					'desc'			=> __( 'Categories you would you like to use, each category on one line.', 'wp-job-manager-reviews' ),
					'attributes'	=> array(),
					'type'			=> 'textarea'
				),
				array(
					'name'			=> 'wpjmr_listing_authors_can_moderate',
					'std'			=> '0',
					'placeholder'	=> '',
					'label'			=> __( 'Listing owners can moderate reviews', 'wp-job-manager-reviews' ),
					'cb_label'		=> __( 'Listing owners can moderate reviews', 'wp-job-manager-reviews' ),
					'desc'			=> __( 'Let listing owners moderate the reviews on their listings.', 'wp-job-manager-reviews' ),
					'attributes'	=> array(),
					'type'			=> 'checkbox'
				),
				array(
					'name'			=> 'wpjmr_restrict_review',
					'std'			=> '0',
					'placeholder'	=> '',
					'label'			=> __( 'Restrict reviews to buyers', 'wp-job-manager-reviews' ),
					'cb_label'		=> __( 'Restrict reviews', 'wp-job-manager-reviews' ),
					'desc'			=> __( 'Restrict giving a review to users that are validated buyers of associated products.', 'wp-job-manager-reviews' ),
					'attributes'	=> array(),
					'type'			=> 'checkbox'
				),
			),
		);

		return $settings;
	}


}
