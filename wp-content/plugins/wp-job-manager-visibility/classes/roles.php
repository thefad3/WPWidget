<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Roles {

	/**
	 * WP_Job_Manager_Visibility_Roles constructor.
	 */
	public function __construct() {



	}

	static function get_display_label( $role ){

		// Make sure to strip group- if passed
		$role = substr( $role, 0, 5 ) == "role-" ? substr( $role, 5 ) : $role;
		global $wp_roles;

		if ( ! is_object( $wp_roles ) ) $wp_roles = new WP_Roles();
		$roles = $wp_roles->get_names();

		if( isset( $roles[ $role ] ) && ! empty( $roles[ $role ] ) ) return $roles[ $role ];

		return $role;
	}

	static function add_anonymous(){

		if( get_role( 'anonymous' ) ) return;

		$result = add_role(
			'anonymous',
			__( 'Anonymous (users not logged in)', 'wp-job-manager-visibility' ),
			array(
				'read'         => TRUE,  // true allows this capability
				'edit_posts'   => FALSE,
				'delete_posts' => FALSE, // Use false to explicitly deny
			)
		);

	}

	static function get_roles(){
		global $wp_roles, $role;

		if( ! is_object( $wp_roles ) ) $wp_roles = new WP_Roles();
		$roles = $wp_roles->get_names();

		return $roles;

	}

}