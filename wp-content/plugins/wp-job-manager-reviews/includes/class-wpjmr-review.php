<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WPJMR_Review.
 *
 * Handle all reviews.
 *
 * @class       WPJMR_Review_Handler
 * @version     1.0.0
 * @author      Jeroen Sormani
 */
class WPJMR_Review {


	/**
	 * Constructor.
	 *
	 * Initialize this class including hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Save comment meta
		add_action( 'comment_post', array( $this, 'save_comment_review' ) );

		// Replace themes 'comments.php' with
		add_filter( 'get_comment_text', array( $this, 'review_comment_text' ), 10, 3 );

		// Add stars to form
		add_action( 'comment_form_top', array( $this, 'comment_form_stars' ) );

		// Restrict fields when ratings are restricted
		add_filter( 'comments_open', array( $this, 'restrict_comment_form' ), 10, 2 );

		// Check if a user is allowed to post (multiple reviews)
		add_filter( 'pre_comment_approved', array( $this, 'check_comment_approved' ), 10, 2 );

		// Save average rating in post meta
		add_action( 'wpjmr_after_save_comment_review', array( $this, 'save_rating_average_post_meta' ), 10, 1 );

		// Update average in listing post on comment update
		add_action( 'transition_comment_status', array( $this, 'update_rating_on_comment_change' ), 10, 3 );

		// Disable Jetpack on listings
		add_action( 'comment_form_top', array( $this, 'disable_jetpack_on_listings' ) );
	}


	/**
	 * Can review?
	 *
	 * Check if the current user can post a review.
	 * Used to check if the user is a verified buyer.
	 *
	 * @since 1.1.0
	 */
	public function can_post_review() {
		// Check if reviewing is restricted to verified buyer
		if ( 1 == get_option( 'wpjmr_restrict_review' ) ) {

			$listing_id 	= get_the_ID();
			$products 		= get_post_meta( $listing_id, '_products', true );
			$current_user 	= wp_get_current_user();

			if ( $products ) {
				foreach ( $products as $product_id ) {

					if ( woocommerce_customer_bought_product( $current_user->email, $current_user->ID, $product_id ) ) {
						return apply_filters( 'wpjmr_can_post_review', true );
					}

				}
			}

			// Default to false
			return apply_filters( 'wpjmr_can_post_review', false );

		}

		return apply_filters( 'wpjmr_can_post_review', true );
	}


	/**
	 * Multiple reviews.
	 *
	 * Check if it is allowed to publish multiple reviews.
	 * Defaults to false to only allow a single review per person.
	 *
	 * @since 1.2.0
	 */
	public function can_post_multiple_reviews() {
		// Check if reviewer already has left a review
		if ( true == apply_filters( 'wpjmr_allow_single_review', true ) ) {
			
			if ( is_user_logged_in() ) {
				$comments = get_comments( array(
					'user_id' 	=> get_current_user_id(),
					'post_id' 	=> get_the_ID(),
					'count' 	=> true,
				) );				
			} else {
				$comments = 0;
			}
			
			if ( $comments >= 1 ) {
				return apply_filters( 'wpjmr_can_post_multiple_reviews', false );
			} else {
				return apply_filters( 'wpjmr_can_post_multiple_reviews', true );
			}

		}

		return apply_filters( 'wpjmr_can_post_multiple_reviews', false );
	}


	/**
	 * Add stars to comment.
	 *
	 * Add the stars based on categories to default comment text.
	 *
	 * @since 1.0.0
	 *
	 * @param string $comment_content Text of the comment.
	 * @param object $comment         The comment object.
	 * @param array  $args            An array of arguments.
	 */
	public function review_comment_text( $content, $comment, $args ) {
		if ( 0 != $comment->comment_parent || ! is_singular( 'job_listing' ) ) {
			return $content;
		}

		ob_start();

			?><div id='wpjmr-list-reviews'><?php

				$ratings 	= WPJMR()->review->get_ratings( get_comment_ID() );
				$categories = WPJMR()->wpjmr_get_review_categories();
				foreach ( $ratings as $category => $rating ) : ?>
					<div class='star-rating'>
						<div class='star-rating-title'><?php echo isset( $categories[ $category ] ) ? $categories[ $category ] : $category; ?></div>
						<?php for ( $i = 0; $i < WPJMR()->wpjmr_get_count_stars(); $i++ ) : ?>
							<?php if ( $i < $rating ) : ?>
								<span class="dashicons dashicons-star-filled"></span><?php else : ?><span class="dashicons dashicons-star-empty"></span><?php endif; ?>
						<?php endfor; ?>
					</div>
				<?php endforeach; ?>
			</div><?php

			$stars = ob_get_contents();
		ob_end_clean();

		$content = $stars . $content;
		return $content;
	}


	/**
	 * Comment form stars.
	 *
	 * Add stars to the comment form based on review categories. Done via action hook.
	 *
	 * @since 1.0.0
	 */
	public function comment_form_stars() {
		if ( ! is_singular( 'job_listing' ) ) {
			return;
		}

		?><div id='wpjmr-submit-ratings' class='review-form-stars'>

			<div class='star-ratings'>

				<?php foreach ( WPJMR()->wpjmr_get_review_categories() as $category_slug => $category ) : ?>

					<div class='rating-row'>

						<label for='<?php echo $category_slug; ?>'><?php echo $category; ?></label>

						<div class='choose-rating' data-rating-category='<?php echo $category_slug; ?>'>
							<?php for ( $i = WPJMR()->wpjmr_get_count_stars(); $i > 0 ; $i-- ) : ?>
									<span data-star-rating='<?php echo $i; ?>' class="star dashicons dashicons-star-empty"></span>
							<?php endfor; ?>
							<input type='hidden' class='required' name='star-rating-<?php echo $category_slug; ?>' value=''>

						</div>

					</div>

				<?php endforeach; ?>

			</div>

		</div><?php
	}


	/**
	 * Save comment meta.
	 *
	 * Save the ratings as comment meta in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param int @comment_id ID of the current comment.
	 */
	public function save_comment_review( $comment_id ) {
		$comment = get_comment( $comment_id );

		if ( 0 != $comment->comment_parent ) {
			return;
		}

		$review_categories = WPJMR()->wpjmr_get_review_categories();
		$review_average    = 0;

		// Save review categories in database for this review.
		update_comment_meta( $comment_id, 'review_categories', $review_categories );

		foreach ( $review_categories as $category_slug => $review_category ) {

			if ( isset ( $_POST['star-rating-' . $category_slug ] ) ) {
				$value = $_POST['star-rating-' . $category_slug ];
				$review_average += $value;

				update_comment_meta( $comment_id, 'star-rating-' . $category_slug, $value );
			}

		}

		if ( $review_average > 0 ) {
			$review_average = $review_average / count( $review_categories );
			$review_average = round( $review_average * 2 ) / 2;
		}

		update_comment_meta( $comment_id, 'review_average', $review_average );

		do_action( 'wpjmr_after_save_comment_review', $comment_id );
	}


	/**
	 * Get reviews.
	 *
	 * Get all the reviews based on post_id.
	 *
	 * @since 1.0.0
	 *
	 * @param 	int $post_id ID of the current listing.
	 * @return 	array List of ratings with slug and rating.
	 */
	public function get_reviews_by_id( $post_id = '' ) {
		if ( ! is_integer( $post_id ) && ! $post_id ) {
			$post_id = get_the_ID();
		}

		// return if its not an job listing.
		if ( 'job_listing' != get_post_type( $post_id ) ) {
			return;
		}

		$args = array(
			'post_id' 	=> $post_id,
			'parent' 	=> 0,
			'status' 	=> 'approve',
		);
		$reviews = get_comments( $args );

		return $reviews;
	}


	/**
	 * Ratings.
	 *
	 * Get review categories saved in database; these are saved for
	 * future compatibility since categories might change in the future.
	 *
	 * @since 1.0.0
	 *
	 * @param 	int @comment_id ID of the current comment.
	 * @return 	array List of ratings with slug and rating.
	 */
	public function get_ratings( $comment_id ) {
		$review_categories = get_comment_meta( $comment_id, 'review_categories', true );

		if ( ! $review_categories ) {
			return array();
		}

		$ratings = array();
		foreach ( $review_categories as $category_slug => $review_category ) {
			$ratings[ $category_slug ] = get_comment_meta( $comment_id, 'star-rating-' . $category_slug, true );
		}

		return $ratings;
	}


	/**
	 * Average rating review.
	 *
	 * Get the average rating of a review.
	 * NOTE: this is the average of a single review (all categories), not the average of the post.
	 *
	 * @since 1.0.0
	 *
	 * @param 	int $comment_id ID of the current comment.
	 * @return 	int Average of the review.
	 */
	public function average_rating_review( $comment_id ) {
		$average = get_comment_meta( $comment_id, 'review_average', true );

		if ( ! $average ) {
			$average = 0;
		}

		return number_format( $average, 1, '.', ',' );
	}


	/**
	 * Average rating listing.
	 *
	 * Get the average rating of a listing.
	 *
	 * @since 1.0.0
	 *
	 * @param 	int $post_id 	ID of the current listing.
	 * @return 	int Average 	of the review.
	 */
	public function average_rating_listing( $post_id ) {

		if ( 'job_listing' != get_post_type( $post_id ) ) {
			return false;
		}

		$reviews = $this->get_reviews_by_id( $post_id );

		$reviews_added = 0;
		if ( $reviews ) {
			foreach ( $reviews as $review ) {
				$reviews_added += $this->average_rating_review( $review->comment_ID );
			}
		}

		// Check if $reviews exists and is not 0
		if ( $reviews ) {
			$review_average = $reviews_added / count( $reviews );
			$review_average = round( $review_average * 2, apply_filters( 'wpjmr_review_average_round', 1 ) ) / 2;
		} else {
			return 0;
		}

		return $review_average;
	}


	/**
	 * Review count.
	 *
	 * Return the number of reviews.
	 *
	 * @since 1.0.0
	 *
	 * @param 	int $post_id ID of the current listing.
	 * @return 	int Review count.
	 */
	public function review_count( $post_id = '' ) {
		if ( ! is_integer( $post_id ) || ! $post_id ) {
			$post_id = get_the_ID();
		}

		$review_count = count( $this->get_reviews_by_id( $post_id ) );

		return $review_count;
	}


	/**
	 * Get stars.
	 *
	 * Get the stars according to the review average.
	 *
	 * @since 1.0.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param 	int 	$post_id 	ID to get the stars for.
	 * @param 	int 	$count		Custom count of stars.
	 * @return 	string 				HTML containing stars.
	 */
	public function get_stars( $post_id = '', $count = '' ) {
		if ( ! is_integer( $post_id ) || ! $post_id ) {
			$post_id = get_the_ID();
		}

		$stars = $this->average_rating_listing( $post_id );

		ob_start(); ?>

		<span class='stars-rating'>
			<?php for ( $i = 0; $i < WPJMR()->wpjmr_get_count_stars(); $i++ ) : ?>

				<?php if ( $i < $stars ) : ?>
					<span class="dashicons dashicons-star-filled"></span>
				<?php else : ?>
					<span class="dashicons dashicons-star-empty"></span>
				<?php endif; ?>

			<?php endfor; ?>
		</span><?php

			$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}


	/**
	 * Restrict reviews.
	 *
	 * Restrict the comment field, don't display when the user
	 * is not allowed to review.
	 *
	 * This happens when reviews need to be from verified buyers or
	 * when a user already posted a review and reviews are limited to 1 per person.
	 *
	 * @since 1.1.0
	 *
	 * @param	bool	$open		Current comment (review) status.
	 * @param	int		$post_id 	Post ID to check the comment form status for.
	 * @param	bool				True if the user is allowed to post a review, else false.
	 */
	public function restrict_comment_form( $open, $post_id ) {
		if ( ! $this->can_post_review() ) {
			return false;
		}

		return $open;
	}


	/**
	 * Comment approved.
	 *
	 * Check if a comment is approved or not. When a user tries to post
	 * multiple reviews it will die with a message (similar as the duplicate message).
	 * This only goes for multiple reviews, not replies.
	 *
	 * @since 1.2.0
	 *
	 * @param	bool 	$approved 		1 If the comment is approved, else 0.
	 * @param	array	$commentdata	List of comment data.
	 * @return	bool					1 If the comment is approved, else 0.
	 */
	public function check_comment_approved( $approved, $commentdata ) {

		// Only hold back on the reviews, not replies
		if ( 'job_listing' != get_post_type( $commentdata['comment_post_ID'] ) ) {
			return $approved;
		}

		// Check if review limit is not reached
		if ( 0 == $commentdata['comment_parent'] ) {
			if ( ! $this->can_post_multiple_reviews() ) {
				wp_die( __( 'Looks like you&#8217;ve already posted a review!' ) );
				$approved = 0;
			}
		}
		
		// Check if ratings are given for top-level comments
		if ( 0 == $commentdata['comment_parent'] ) {
			// Check if all ratings are set
			$review_categories = WPJMR()->wpjmr_get_review_categories();
			foreach ( $review_categories as $category_slug => $review_category ) {
	
				if ( ! isset( $_POST['star-rating-' . $category_slug ] ) || empty( $_POST['star-rating-' . $category_slug ] ) ) {
					wp_die( __( '<strong>ERROR:</strong> Please select a rating for all categories.' ) );
					$approved = 0;
				}
	
			}
		}


		return $approved;
	}


	/**
	 * Save average.
	 *
	 * Save the average rating of a listing in the post meta table.
	 *
	 * @since 1.2.0
	 *
	 * @param int $comment_id ID of the comment being posted.
	 */
	public function save_rating_average_post_meta( $comment_id ) {

		global $wpdb;

		if ( $comment = get_comment( $comment_id ) ) {
			$post_id = $comment->comment_post_ID;
	
			$average_rating = $this->average_rating_listing( $post_id );
	
			update_post_meta( $post_id, '_average_rating', $average_rating );
		}

	}


	/**
	 * Update average.
	 *
	 * Update the average rating when a comment gets updated. This ensures
	 * that when a comment gets approved or trashed the average will be updated.
	 *
	 * @since 1.2.0
	 */
	public function update_rating_on_comment_change( $new_status, $old_status, $comment ) {

		$this->save_rating_average_post_meta( $comment->comment_ID );

	}


	/**
	 * Disable Jetpack.
	 *
	 * Disable Jetpack when on a listing page. Jetpack doesn't
	 * support to alter the comments form.
	 *
	 * @since 1.2.0
	 */
	public function disable_jetpack_on_listings() {

		if ( 'job_listing' == get_post_type() && class_exists( 'Jetpack_Comments' ) ) {
			remove_action('comment_form_before', array( new Jetpack_Comments, 'comment_form_before'));
			remove_action('comment_form_after', array( new Jetpack_Comments, 'comment_form_after'));
		}

	}


}
