<?php
/**
 * Listify child theme.
 */

function listify_child_styles() {
    wp_enqueue_style( 'listify-child', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'listify_child_styles', 999 );

/** Place any new code below this line */

/**
 * Plugin Name: Listify - Disable Default Google Fonts and Enqueue Custom
 */
function custom_listify_fonts() {
	wp_dequeue_style( 'listify-fonts' );
    wp_dequeue_script( 'listify-scripts' );

    wp_enqueue_script( 'listify-scripts', '<script type="text/javascript" src="scripts/main.js"></script>' );
    wp_enqueue_style( 'listify-fonts', 'http://fonts.googleapis.com/css?family=Architects+Daughter' );
}
add_action( 'wp_enqueue_scripts', 'custom_listify_fonts', 20 );



/**
 * Plugin Name: Listify - Custom Single Listing Hero Button
 */

function custom_listify_single_job_listing_actions_after() {
    echo '<a href="#listify_widget_panel_listing_map-2" class="button">Get Directions</a>';
}
add_filter( 'listify_single_job_listing_actions_after', 'custom_listify_single_job_listing_actions_after' );


add_action( 'wp_directions', 'script_hook' );
 
function custom_listify_cover_image( $image, $args ) {
	if ( ! isset( $args[ 'term' ] ) ) {
		return $image;
	}
	
	$term = $args[ 'term' ];
	/**
	 * Only edit the URL here. 
	 * 
	 * Do not add the name of the image to this URL.
	 * 
	 * Once the URL is set upload images to your web server's directory with the name
	 * of each of your terms slug.
	 * 
	 * Example:
	 *   Restaurants = http://yourwebsite.com/images/directory/restaurants.jpg
	 */
	$url = 'http://dogpark.directory/images/directory/';
	
	$image = array( $url . $term->slug . '.jpg' );
	
	return $image;
}
add_filter( 'listify_cover_image', 'custom_listify_cover_image', 10, 2 );

/**
 * Listify - Default Image for Listings
 */
function custom_default_listify_cover_image( $image, $args ) {
	global $post;
	
	if ( $image ) {
		return $image;
	}
	
	$image = array( 'http://yourwebsite.com/images/default-image.png' );
	
	return $image;
}
add_filter( 'listify_cover_image', 'custom_default_listify_cover_image', 10, 2 );

/** Limit Upload Size for Non-Admins */

function limit_upload_size_limit_for_non_admin( $limit ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		$limit = '1500000'; // 1.5mb in bytes
	}
	return $limit;
}
add_filter( 'upload_size_limit', 'limit_upload_size_limit_for_non_admin' );