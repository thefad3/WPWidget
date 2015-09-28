<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Transients {

	protected $prefix;
	protected $cache_prefix = "jmvc_";

	public function __construct() { }

	function cache_enabled(){

		return get_option( 'jmv_enable_cache', true );

	}

	function get( $append = '' ){

		if( ! $this->cache_enabled() || isset( $_GET[ 'no_cache' ] ) ) return false;

		$check = get_transient( "{$this->cache_prefix}{$this->prefix}_{$append}" );

		if( $check === FALSE ) return false;

		return $check;
	}

	function set( $append = '', $data, $expire = null ){

		if ( ! $this->cache_enabled() || isset( $_GET['no_cache'] ) ) return FALSE;

		// Default expiration
		if( ! $expire ) $expire = ($default_expire = get_option('jmv_cache_expiration')) === FALSE ? 4 * WEEK_IN_SECONDS: $default_expire;

		return set_transient( "{$this->cache_prefix}{$this->prefix}_{$append}", $data, $expire );

	}

	function remove( $append = '' ){

		return delete_transient( "{$this->cache_prefix}{$this->prefix}_{$append}" );

	}

	function count(){

		global $wpdb;

		$prefix = esc_sql( "{$this->cache_prefix}{$this->prefix}" );

		$options = $wpdb->options;

		$t = esc_sql( "_transient_timeout_$prefix%" );

		$sql = $wpdb->prepare(
			"
      SELECT option_name
      FROM $options
      WHERE option_name LIKE '%s'
    ",
			$t
		);

		$transients = $wpdb->get_col( $sql );

		return count( $transients );
	}

	function purge() {

		global $wpdb;

		$prefix = esc_sql( "{$this->cache_prefix}{$this->prefix}" );

		$options = $wpdb->options;

		$t = esc_sql( "_transient_timeout_$prefix%" );

		$sql = $wpdb->prepare(
			"
      SELECT option_name
      FROM $options
      WHERE option_name LIKE '%s'
    ",
			$t
		);

		$transients = $wpdb->get_col( $sql );

		// For each transient...
		foreach ( $transients as $transient ) {

			// Strip away the WordPress prefix in order to arrive at the transient key.
			$key = str_replace( '_transient_timeout_', '', $transient );

			// Now that we have the key, use WordPress core to the delete the transient.
			delete_transient( $key );

		}

		// But guess what?  Sometimes transients are not in the DB, so we have to do this too:
		wp_cache_flush();

	}
}