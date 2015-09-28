<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Integration_Jobify {

	/**
	 * WP_Job_Manager_Visibility_Integration_Jobify constructor.
	 */
	public function __construct() {

		add_filter( 'jmv_output_get_terms', array( $this, 'get_terms' ), 10, 5 );

	}

	function get_terms( $value, $object_ids, $meta_key, $taxonomy, $terms ){

		$jobify_terms = array(
			'job_listing_category' => array(
				'classes' => array( 'Jobify_Widget_Job_Categories' )
			),
			'resume_skill' => array(
				'classes' => array( 'Jobify_Widget_Resume_Skills' )
			)
		);

		// Jobify skills widget has hard-coded call to get term URL which causes fatal error
		if( in_array( $taxonomy, array_keys( $jobify_terms ) ) ){
			$backtrace = debug_backtrace();
			$backtrace_classes = array_column( $backtrace, 'class' );
			$array_check = array_intersect( $jobify_terms[ $taxonomy ]['classes'], $backtrace_classes );
			if( ! empty( $array_check ) ) return false;
		}

		return $value;
	}

}