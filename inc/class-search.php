<?php

class Listify_Search {
	
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'filter_search_results' ), 1 );
	}

	public function filter_search_results( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_search ) {
			return;
		}

		$query->set( 'post_type', 'post' );
	}

}

$GLOBALS[ 'listify_search' ] = new Listify_Search();
