<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Output_JM extends WP_Job_Manager_Visibility_Output {

	/**
	 * WP_Job_Manager_Visibility_Output_RM constructor.
	 */
	public function __construct() {

		// Specific fields with filters
		add_filter( 'the_job_description', array( $this, 'job_description' ), 9999999, 2 );
		add_filter( 'single_post_title', array( $this, 'job_title' ), 9999999, 2 );
		add_filter( 'the_title', array( $this, 'job_title' ), 9999999, 2 );
		//add_filter( 'the_job_type', array( $this, 'job_type' ), 9999999, 2 );
		add_filter( 'the_job_location', array( $this, 'job_location' ), 9999999, 2 );
		add_filter( 'the_company_logo', array( $this, 'company_logo' ), 9999999, 2 );
		add_filter( 'the_company_website', array( $this, 'company_website' ), 9999999, 2 );
		add_filter( 'the_company_twitter', array( $this, 'company_twitter' ), 9999999, 2 );
		add_filter( 'the_company_name', array( $this, 'company_name' ), 9999999, 2 );
		add_filter( 'the_company_tagline', array( $this, 'company_tagline' ), 9999999, 2 );
		add_filter( 'the_company_video', array( $this, 'company_video' ), 9999999, 2 );

		add_filter( 'jmv_output_taxonomies', array($this, 'init_taxonomies') );
		add_filter( 'jmv_output_maps', array($this, 'init_maps') );
	}

	function init_taxonomies( $taxonomies ){

		$add_taxes = array(
				'job_listing_type' => array(
					'meta_key' => 'job_type',
					'return' => 'object',
					'key'    => 'name',
					'slug'   => true
				)
			);

		return array_merge( $taxonomies, $add_taxes );
	}

	function init_maps( $maps ){

		$add_maps = array();

		return array_merge( $maps, $add_maps );
	}

	function job_title( $name, $post = null) {

		if( get_post_type( $post ) !== 'job_listing' ) return $name;

		return $this->get_placeholder( 'job_title', $post, $name, ucfirst( WP_Job_Manager_Visibility::get_job_post_label() ) . " " . __( 'Listing', 'wp-job-manager-visibility' ) );
	}

	function company_website( $website, $job ){

		$value = $this->get_placeholder( 'company_website', $job, $website );

		// If returned value is not same as actual value, and option is set to remove website, return false
		if( $website !== $value && get_option('jmv_job_remove_website') ) return false;

		return $value;
	}

	function company_tagline( $tagline, $job ){

		return $this->get_placeholder( 'company_tagline', $job, $tagline );
	}

	function company_name( $name, $job ){

		return $this->get_placeholder( 'company_name', $job, $name );
	}

	function job_location( $location, $job ) {

		return $this->get_placeholder( 'job_location', $job, $location );
	}

	function company_logo( $logo, $job ) {

		return $this->get_placeholder( 'company_logo', $job, $logo );
	}

	function company_video( $video, $job ) {

		return $this->get_placeholder( 'company_video', $job, $video );
	}

	function company_twitter( $twitter, $job ) {

		$value = $this->get_placeholder( 'company_twitter', $job, $twitter );
		// Return string with length of 0 if there should be a placeholder to prevent link output
		if( $value !== $twitter ) return '';

		return $twitter;
	}

	function job_description( $content ) {

		return $this->get_placeholder( 'job_description', get_the_ID(), $content );
	}
}