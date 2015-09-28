<?php

/*
Plugin Name: WP All Import - Listify Add-On
Plugin URI: http://www.wpallimport.com/
Description: Supporting imports into the Listify theme.
Version: 1.0.3
Author: Soflyy
*/


include "rapid-addon.php";

$listify_addon = new RapidAddon( 'Listify Add-On', 'listify_addon' );

$listify_addon->disable_default_images();

$listify_addon->import_images( 'listing_gallery', 'Listing Gallery' );

function listing_gallery( $post_id, $attachment_id, $image_filepath, $import_options ) {

    // build gallery_images
    $new_url = wp_get_attachment_url( $attachment_id );

    $urls = get_post_custom_values( '_gallery_images', $post_id );

    $new_urls = array();

    foreach( $urls as $key => $url ) {

        $url = unserialize( $url );

        $new_urls[] = $url[0];

    }

    $new_urls[] = $new_url;

    update_post_meta( $post_id, '_gallery_images', $new_urls );

    //build gallery
    $new_id = $attachment_id;

    $ids = get_post_custom_values( '_gallery', $post_id );

    $new_ids = array();

    foreach( $ids as $key => $id ) {

        $id = unserialize( $id );

        $new_ids[] = $id[0];

    }

    $new_ids[] = $new_id;

    update_post_meta( $post_id, '_gallery', $new_ids );

}

$listify_addon->add_field( '_job_location', 'Location', 'text', null, 'Leave this blank if location is not important' );

$listify_addon->add_field( '_application', 'Application Email or URL', 'text', null, 'This field is required for the "application" area to appear beneath the listing.');

$listify_addon->add_field( '_company_website', 'Company Website', 'text' );

// field is _company_video, will 'image' add_field support videos?
$listify_addon->add_field( 'video_type', 'Company Video', 'radio',
    array(
        'external' => array(
            'Externally Hosted',
            $listify_addon->add_field( '_company_video_url', 'Video URL', 'text')
        ),
        'local' => array(
            'Locally Hosted',
            $listify_addon->add_field( '_company_video_id', 'Upload Video', 'file')
)));

$listify_addon->add_field( '_job_expires', 'Listing Expiry Date', 'text', null, 'Import date in any strtotime compatible format.');

$listify_addon->add_field( '_phone', 'Company Phone', 'text' );

$listify_addon->add_field( '_claimed', 'Claimed', 'radio', 
    array(
        '0' => 'No',
        '1' => 'Yes'
    ),
    'The owner has been verified.'
);

$listify_addon->add_field( '_featured', 'Featured Listing', 'radio', 
    array(
        '0' => 'No',
        '1' => 'Yes'
    ),
    'Featured listings will be sticky during searches, and can be styled differently.'
);

$listify_addon->add_title( 'Hours of Operation', 'Use Closed or a time, for example 8:30 am.' );

$listify_addon->add_text( '<br><b>Monday</b>' );

$listify_addon->add_field( 'monday_open', 'Open', 'text' );

$listify_addon->add_field( 'monday_close', 'Close', 'text' );

$listify_addon->add_text( '<br><br><b>Tuesday</b>' );

$listify_addon->add_field( 'tuesday_open', 'Open', 'text' );

$listify_addon->add_field( 'tuesday_close', 'Close', 'text' );

$listify_addon->add_text( '<br><br><b>Wednesday</b>' );

$listify_addon->add_field( 'wednesday_open', 'Open', 'text' );

$listify_addon->add_field( 'wednesday_close', 'Close', 'text' );

$listify_addon->add_text( '<br><br><b>Thursday</b>' );

$listify_addon->add_field( 'thursday_open', 'Open', 'text' );

$listify_addon->add_field( 'thursday_close', 'Close', 'text' );

$listify_addon->add_text( '<br><br><b>Friday</b>' );

$listify_addon->add_field( 'friday_open', 'Open', 'text' );

$listify_addon->add_field( 'friday_close', 'Close', 'text' );

$listify_addon->add_text( '<br><br><b>Saturday</b>' );

$listify_addon->add_field( 'saturday_open', 'Open', 'text' );

$listify_addon->add_field( 'saturday_close', 'Close', 'text' );

$listify_addon->add_text( '<br><br><b>Sunday</b>' );

$listify_addon->add_field( 'sunday_open', 'Open', 'text' );

$listify_addon->add_field( 'sunday_close', 'Close', 'text' );

$listify_addon->set_import_function( 'listify_addon_import' );

$listify_addon->admin_notice(
    'The Listify Add-On requires WP All Import <a href="http://www.wpallimport.com/order-now/?utm_source=free-plugin&utm_medium=dot-org&utm_campaign=listify" target="_blank">Pro</a> or <a href="http://wordpress.org/plugins/wp-all-import" target="_blank">Free</a>, and the <a href="https://astoundify.com/go/wp-all-import-buy-listify/">Listify</a> theme.',
    array( 
        'themes' => array( 'Listify' )
) );

$listify_addon->run( array(
        'themes' => array( 'Listify' ),
        'post_types' => array( 'job_listing' ) 
) );

function listify_addon_import( $post_id, $data, $import_options ) {
    
    global $listify_addon;
    
    // all fields except for slider and image fields
    $fields = array(
        '_application',
        '_company_website',
        '_phone',
        '_claimed',
        '_featured',
        '_job_location'
    );

    // update everything in fields arrays
    foreach ( $fields as $field ) {

        if ( $listify_addon->can_update_meta( $field, $import_options ) ) {

            update_post_meta( $post_id, $field, $data[$field] );

        }
    }

    // update listing expiration date
    $field = '_job_expires';

    $date = $data[$field];

    $date = strtotime( $date );

    if ( $listify_addon->can_update_meta( $field, $import_options ) && !empty( $date ) ) {

        $date = date( 'Y-m-d', $date );

        update_post_meta( $post_id, $field, $date );

    }

    // clear image fields to override import settings
    $fields = array(
        'gallery_images',
        'gallery'
    );

    if ( $listify_addon->can_update_image( $import_options ) ) {

        foreach ($fields as $field) {

            delete_post_meta($post_id, $field);

        }

    }

    // update video

    if ( $listify_addon->can_update_meta( '_company_video', $import_options ) ) {

        if ( $data['video_type'] == 'external' ) {

            update_post_meta( $post_id, '_company_video', $data['_company_video_url'] );

        } elseif ( $data['video_type'] == 'local' ) {

            $attachment_id = $data['_company_video_id']['attachment_id'];

            $url = wp_get_attachment_url( $attachment_id );

            update_post_meta( $post_id, '_company_video', $url );
        }
    }

    // update hours
    $field = '_job_hours';

    if ( $listify_addon->can_update_meta( $field, $import_options ) ) {

        $hours = array(
            1 => array(
                'start' => $data['monday_open'],
                'end' => $data['monday_close']
            ),
            2 => array(
                'start' => $data['tuesday_open'],
                'end' => $data['tuesday_close']
            ),
            3 => array(
                'start' => $data['wednesday_open'],
                'end' => $data['wednesday_close']
            ),
            4 => array(
                'start' => $data['thursday_open'],
                'end' => $data['thursday_close']
            ),
            5 => array(
                'start' => $data['friday_open'],
                'end' => $data['friday_close']
            ),
            6 => array(
                'start' => $data['saturday_open'],
                'end' => $data['saturday_close']
            ),
            0 => array(
                'start' => $data['sunday_open'],
                'end' => $data['sunday_close']
        ) );

        foreach( $hours as $day => $key ) {

          foreach( $key as $subkey => $value ) {

              if ( strtotime( $value ) != false )  {

                $new_value = strtotime( $value );

                $new_value = date( 'g:i a', $new_value );

              } else {

                $new_value = ucwords( $value );

              }

              $hours[$day][$subkey] = $new_value;

            }
        }

        update_post_meta( $post_id, $field, $hours );

    }
}






