<div id="job-manager-review-moderate-board">

	<p><?php _e( 'Moderate your reviews below.', 'wp-job-manager-reviews' ); ?></p>

	<table class="job-manager-reviews">

		<thead>
			<tr>
				<th class="" style="width: 50%;"><?php _e( 'Review', 'wp-job-manager-reviews' ); ?></th>
				<th class="" style="width: 15%;"><?php _e( 'Author', 'wp-job-manager-reviews' ); ?></th>
				<th class="" style="width: 20%;"><?php _e( 'Ratings', 'wp-job-manager-reviews' ); ?></th>
				<th class="" style="width: 25%;"><?php _e( 'Actions', 'wp-job-manager-reviews' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if ( ! $reviews ) : ?>
				<tr>
					<td colspan="6"><?php _e( 'There are currently no reviews found for any of your listings.', 'wp-job-manager-reviews' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $reviews as $review ) : ?>
					<tr class='wp-job-manger-reviews-status-<?php echo $review->comment_approved; ?>'>
						<td>
							<div class='review-content'><?php
								$content = $review->comment_content;
								echo strlen( $content ) <= 200 ? $content : substr( $content, 0, strrpos( $content, ' ', -( strlen( $content ) - 200 ) ) );
								if ( 200 < strlen( $content ) ) :
									echo '...';
								endif;
							?></div>
							<div class='review-content-listing'><strong><?php
								$title = ! empty( $review->post_title ) ? $review->post_title : __( '(no title)' );
								echo sprintf( __( 'On listing %s', 'wp-job-manager-reviews' ), '<a href="' . get_permalink( $review->ID ) . '">' . $title . '</a>' );
							?></strong></div>
						</td>
						<td><?php echo $review->comment_author; ?></td>
						<td>
							<div id='wpjmr-list-reviews'><?php

								$ratings 	= WPJMR()->review->get_ratings( $review->comment_ID );
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
							</div>
						</td>

						<td><?php
							$status = '';
							if ( '0' == $review->comment_approved ) :
								$status = __( 'Unapproved', 'wp-job-manager-reviews' );
							elseif ( '1' == $review->comment_approved ) :
								$status = __( 'Approved', 'wp-job-manager-reviews' );
							elseif ( 'spam' == $review->comment_approved ) :
								$status = __( 'Spam', 'wp-job-manager-reviews' );
							elseif ( 'trash' == $review->comment_approved ) :
								$status = __( 'Deleted', 'wp-job-manager-reviews' );
							endif;

							?><div class='review-action-status'><strong><?php
								echo $status;
							?></strong></div>

							<?php
							$approve_href 	= wp_nonce_url( add_query_arg( array( 'c' => $review->comment_ID, 'action' => 'approve' ) ), 'moderate_comment', 'moderate_nonce' );
							$unapprove_href = wp_nonce_url( add_query_arg( array( 'c' => $review->comment_ID, 'action' => 'unapprove' ) ), 'moderate_comment', 'moderate_nonce' );
							$spam_href 		= wp_nonce_url( add_query_arg( array( 'c' => $review->comment_ID, 'action' => 'spam' ) ), 'moderate_comment', 'moderate_nonce' );
							$delete_href 	= wp_nonce_url( add_query_arg( array( 'c' => $review->comment_ID, 'action' => 'trash' ) ), 'moderate_comment', 'moderate_nonce' );

							?><div class='job-dashboard-actions'><?php
								if ( '1' != $review->comment_approved ) :
									?><div><a class='review-action review-action-approve' href='<?php echo esc_url( $approve_href ); ?>'><?php
										_e( 'Approve', 'wp-job-manager-reviews' );
									?></a></div><?php
								endif;

								if ( '0' != $review->comment_approved ) :
									?><div><a class='review-action review-action-unapprove' href='<?php echo esc_url( $unapprove_href ); ?>'><?php
										_e( 'Unapprove', 'wp-job-manager-reviews' );
									?></a></div><?php
								endif;

								if ( 'spam' != $review->comment_approved ) :
									?><div><a class='review-action review-action-spam' href='<?php echo esc_url( $spam_href ); ?>'><?php
										_e( 'Spam', 'wp-job-manager-reviews' );
									?></a></div><?php
								endif;

								if ( 'trash' != $review->comment_approved ) :
									?><div><a class='review-action review-action-delete' href='<?php echo esc_url( $delete_href ); ?>'><?php
										_e( 'Delete', 'wp-job-manager-reviews' );
									?></a></div><?php
								endif;
							?></div>

						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>

	</table>

	<?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>

</div>