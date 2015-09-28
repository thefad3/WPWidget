<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WPJMR_Moderate.
 *
 * Handle the moderation of reviews.
 *
 * @class       WPJMR_Moderate
 * @version     1.1.0
 * @author      Jeroen Sormani
 */
class WPJMR_Moderate {


	/**
	 * Constructor.
	 *
	 * Initialize this class including hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( isset( $_GET['c'] ) ) :
			add_action( 'init', array( $this, 'moderate_comment_action' ) );
		endif;

	}


	/**
	 * Moderate comment action.
	 *
	 * Check if a user has clicked on a moderate action link.
	 *
	 * @since 1.0.0
	 */
	public function moderate_comment_action() {

		// User must be logged in
		if ( ! is_user_logged_in() ) :
			return false;
		endif;

		// Bail if required values are not set
		if ( ! isset( $_GET['c'] ) || ! isset( $_GET['action'] ) || ! isset( $_GET['moderate_nonce'] ) ) :
			return false;
		endif;

		// Bail if nonce is not verified
		if ( ! wp_verify_nonce( $_GET['moderate_nonce'], 'moderate_comment' ) ) :
			return false;
		endif;

		$comment_id = absint( $_GET['c'] );
		$comment 	= get_comment( $comment_id );

		if ( ! is_object( $comment ) || ! isset( $comment->comment_post_ID ) ) :
			return false;
		endif;

		$post = get_post( $comment->comment_post_ID );

		// Bail if user is not the listing author
		if ( get_current_user_id() != $post->post_author ) :
			return false;
		endif;


		$comment_args = array(
			'comment_ID'		=> $comment->comment_ID,
		);

		switch( $_GET['action'] ) :

			case 'approve':
				$comment_args['comment_approved'] = 1;
				break;
			case 'unapprove':
				$comment_args['comment_approved'] = 0;
				break;
			case 'spam':
				$comment_args['comment_approved'] = 'spam';
				break;
			case 'trash':
				$comment_args['comment_approved'] = 'trash';
				break;

		endswitch;

		wp_update_comment( $comment_args );

		wp_redirect( esc_url( remove_query_arg( array( 'action', 'c', 'moderate_nonce' ) ) ) );
		exit;

	}





}
