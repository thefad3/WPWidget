<?php

/*
Plugin Name: WP All Import - Yoast WordPress SEO Add-On
Plugin URI: http://www.wpallimport.com/
Description: Import data into Yoast WordPress SEO with WP All Import.
Version: 1.0.1
Author: Soflyy
*/

include "rapid-addon.php";

$yoast_addon = new RapidAddon( 'Yoast WordPress SEO Add-On', 'yoast_addon' );

$yoast_addon->add_field( '_yoast_wpseo_focuskw', 'Focus Keyword', 'text', null, 'Pick the main keyword or keyphrase that this post/page is about.' );

$yoast_addon->add_field( '_yoast_wpseo_title', 'SEO Title', 'text', null, 'The SEO title defaults to what is generated based on this sites title template for this posttype.' );

$yoast_addon->add_field( '_yoast_wpseo_metadesc', 'Meta Description', 'text', null, 'The meta description will be limited to 156 chars. It is often shown as the black text under the title in a search result. For this to work it has to contain the keyword that was searched for.' );

$yoast_addon->add_options(
	$yoast_addon->add_field( '_yoast_wpseo_opengraph-title', 'Facebook Title', 'text', null, "If you don't want to use the post title for sharing the post on Facebook but instead want another title there, import it here." ),
	'Facebook Options',
	array(
		$yoast_addon->add_field( '_yoast_wpseo_opengraph-description', 'Description', 'text', null, "If you don't want to use the meta description for sharing the post on Facebook but want another description there, write it here." ),
		$yoast_addon->add_field( '_yoast_wpseo_opengraph-image', 'Image', 'image', null, "If you want to override the image used on Facebook for this post, import one here. The recommended image size for Facebook is 1200 x 628px."),
	)
);

$yoast_addon->add_options(
	$yoast_addon->add_field( '_yoast_wpseo_twitter-title', 'Twitter Title', 'text', null, "If you don't want to use the post title for sharing the post on Twitter but instead want another title there, import it here." ),
	'Twitter Options',
	array(
		$yoast_addon->add_field( '_yoast_wpseo_twitter-description', 'Description', 'text', null, "If you don't want to use the meta description for sharing the post on Twitter but want another description there, import it here." ),
		$yoast_addon->add_field( '_yoast_wpseo_twitter-image', 'Image', 'image', null, "If you want to override the image used on Twitter for this post, import one here. The recommended image size for Twitter is 1024 x 512px."),
	)
);

$yoast_addon->add_options(
	null,
	'Advanced SEO Options',
	array(
		$yoast_addon->add_field( '_yoast_wpseo_meta-robots-noindex', 'Meta Robots Index', 'radio', 
			array(
				'' => 'default',
				'2' => 'index',
				'1' => 'noindex',
			),
			"This setting can be overwritten by Yoast WordPress SEO's sitewide privacy settings"
		),
		$yoast_addon->add_field( '_yoast_wpseo_meta-robots-nofollow', 'Meta Robots Nofollow', 'radio', 
			array(
				'' => 'Follow',
				'1' => 'Nofollow'
			) ),
		$yoast_addon->add_field( '_yoast_wpseo_meta-robots-adv', 'Meta Robots Advanced', 'radio', 
			array(
				'' => 'default',
				'none' => 'None',
				'noodp' => 'NO ODP',
				'noydir' => 'NO YDIR',
				'noimageindex' => 'No Image Index',
				'noarchive' => 'No Archive',
				'nosnippet' => 'No Snippet'
			),
			'Advanced meta robots settings for this page.'
		),
		$yoast_addon->add_field( '_yoast_wpseo_sitemap-include', 'Include in Sitemap', 'radio', 
			array(
				'' => 'Auto detect',
				'always' => 'Always include',
				'never' => 'Never include'
			),
			'Should this page be in the XML Sitemap at all times, regardless of Robots Meta settings?'
		),
		$yoast_addon->add_field( '_yoast_wpseo_sitemap-prio', 'Sitemap Priority', 'radio',
			array(
				'' => 'Automatic Prioritization',
				'1' => '1 - Highest priority',
				'0.9' => '0.9',
				'0.8' => '0.8 - Default for first tier pages',
				'0.7' => '0.7',
				'0.6' => '0.6 - Default for second tier pages and posts',
				'0.5' => '0.5 - Medium priority',
				'0.4' => '0.4',
				'0.3' => '0.3',
				'0.2' => '0.2',
				'0.1' => '0.1 - Lowest priority'
			), 
			'The priority given to this page in the XML sitemap. '
			),
		$yoast_addon->add_field( '_yoast_wpseo_canonical', 'Canonical URL', 'text', null, 'The canonical URL that this page should point to, leave empty to default to permalink. Cross domain canonical supported too.' ),
		$yoast_addon->add_field( '_yoast_wpseo_redirect', '301 Redirect', 'text', null, 'The URL that this page should redirect to.' )

	)
);

// add twitter settings

$yoast_addon->set_import_function( 'yoast_seo_addon_import' );

$yoast_addon->admin_notice(
	'The Yoast WordPress SEO Add-On requires WP All Import <a href="http://www.wpallimport.com/order-now/?utm_source=free-plugin&utm_medium=dot-org&utm_campaign=yoast" target="_blank">Pro</a> or <a href="http://wordpress.org/plugins/wp-all-import" target="_blank">Free</a>, and the <a href="https://yoast.com/wordpress/plugins/seo/">Yoast WordPress SEO</a> plugin.',
	array(
		"plugins" => array( "wordpress-seo/wp-seo.php" )
) );

$yoast_addon->run(
	array(
		"plugins" => array( "wordpress-seo/wp-seo.php" )
) );

function yoast_seo_addon_import( $post_id, $data, $import_options ) {

	global $yoast_addon;

    // all fields except for slider and image fields
    $fields = array(
    	'_yoast_wpseo_focuskw',
    	'_yoast_wpseo_title',
    	'_yoast_wpseo_metadesc',
    	'_yoast_wpseo_meta-robots-noindex',
    	'_yoast_wpseo_meta-robots-nofollow',
    	'_yoast_wpseo_meta-robots-adv',
    	'_yoast_wpseo_sitemap-include',
    	'_yoast_wpseo_sitemap-prio',
    	'_yoast_wpseo_canonical',
    	'_yoast_wpseo_redirect',
    	'_yoast_wpseo_opengraph-title',
    	'_yoast_wpseo_opengraph-description',
    	'_yoast_wpseo_twitter-title',
    	'_yoast_wpseo_twitter-description'

    );
    
    // image fields
    $image_fields = array(
 		'_yoast_wpseo_opengraph-image',
 		'_yoast_wpseo_twitter-image'
    );
    
    $fields = array_merge( $fields, $image_fields );
    
    // update everything in fields arrays
    foreach ( $fields as $field ) {

        if ( $yoast_addon->can_update_meta( $field, $import_options ) ) {

            if ( in_array( $field, $image_fields ) ) {

                if ( $yoast_addon->can_update_image( $import_options ) ) {

                    $id = $data[$field]['attachment_id'];
                    
                    $url = wp_get_attachment_url( $id );

                    update_post_meta( $post_id, $field, $url );

                }

            } else {

                update_post_meta( $post_id, $field, $data[$field] );

            }
        }
    }

}