<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Help_Default extends WP_Job_Manager_Visibility_Admin_Help {


	/**
	 * WP_Job_Manager_Visibility_Admin_Help_Default constructor.
	 */
	public function __construct() {

		$this->post_type = WP_Job_Manager_Visibility_CPT::get_conf( 'default', 'post_type' );
		parent::__construct();

	}

	function init_config(){

		$this->tabs = array(
			'visible_fields' => array(
				'title' => __( 'Visible Fields', 'wp-job-manager-visibility' ),
			),
			'hidden_fields' => array(
				'title' => __( 'Hidden Fields', 'wp-job-manager-visibility' ),
			)
		);

		$this->screens = array(
			'new' => true,
			'edit' => true,
			'list' => false
		);

	}

	function visible_fields(){

		echo "<p>" . __( '<p><strong><em>Any fields you set as visible will override other hidden field settings as long as one of these two requirements are met:</p></strong></em><p><strong>1.)</strong> This configuration is for a specific user (specific user config always has higher priority than groups)</p><p><strong>OR</strong></p><p><strong>2.)</strong> This configuration is for a group, and this group has a higher priority than any group that hides this field.</p>', 'wp-job-manager-visibility' ) . "</p>";

	}

	function hidden_fields(){

		echo "<p>" . __( '<p><strong><em>These fields will be hidden (and placeholder used if set) as long as one of these two requirements are met:</em></strong></p><p><strong>1.)</strong> This configuration is for a specific user (specific user config always has higher priority than groups)</p><p><strong>OR</strong></p><p><strong>2.)</strong> This configuration is for a group, and there are no other configurations to show this field, or if there are, this group has a higher priority than any groups that show this field.</p>', 'wp-job-manager-visibility' ) . "</p>";

	}

}