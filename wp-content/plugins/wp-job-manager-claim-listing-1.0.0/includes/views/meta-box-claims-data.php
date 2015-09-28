<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$order_id 		= get_post_meta( $post->ID, '_order_id', true );
$current_status	= get_post_meta( $post->ID, '_status', true );
$listing_id		= get_post_meta( $post->ID, '_listing_id', true );
$listing		= get_post( $listing_id );
$claimer_id		= $post->post_author;
$claimer_data	= get_userdata( $claimer_id );
?>

<div class='option-group'>

	<label for='listing'><?php _e( 'Listing', 'wp-job-manager-claim-listing' ); ?></label>
	<span class='option-value'><a href='post.php?post=<?php echo $listing_id; ?>&action=edit'><?php echo $listing->post_title; ?></a></span>

</div>

<?php if ( ! empty( $order_id ) ) : ?>
	<div class='option-group'>

		<label for='order_id'><?php _e( 'Order ID', 'wp-job-manager-claim-listing' ); ?></label>
		<span class='option-value'><a href='post.php?post=<?php echo $order_id; ?>&action=edit'>#<?php echo $order_id; ?></a></span>

	</div>
<?php endif; ?>

<div class='option-group'>

	<label for='claimer'><?php _e( 'Claimer', 'wp-job-manager-claim-listing' ); ?></label>
	<a class='view-profile' href='<?php echo admin_url( "user-edit.php?user_id=$claimer_id" ); ?>'><?php echo $claimer_data->display_name; ?></a>

</div>

<div class='option-group'>

	<label for='status'><?php _e( 'Status', 'wp-job-manager-claim-listing' ); ?></label>
	<select name='status'>
		<?php foreach ( $statuses as $key => $status ) : ?>
			<option <?php selected( $key, $current_status ); ?> value='<?php echo $key; ?>'><?php echo $status; ?></option>
		<?php endforeach; ?>
	</select>

</div>

<?php wp_nonce_field( 'data_meta_box', 'data_meta_box_nonce' );
