add_action( 'add_meta_boxes', 'mri_metabox_related_posts_add' );
function mri_metabox_related_posts_add() {
	add_meta_box(
		'mri_related_posts',           // The HTML id attribute for the metabox section
		'Related Posts',               // The title of your metabox section
		'mri_metabox_related_posts_render',   // The metabox callback function (below)
		'post'                         // Your custom post type slug
	);
}


function mri_metabox_related_posts_render( $post ) {

	// Create a nonce field.
	wp_nonce_field( 'mri_metabox_related_posts_nonce', 'mri_metabox_related_posts_nonce_name' );

	// we store data as an array, we need to unserialize it
	$mrirelated = maybe_unserialize( get_post_meta( $post->ID, "_mrirelated", true ) );

	// Only right locale - uncomment the next line and line 29 (or so) if don't have multiple languages active on your WP site. I am using Bongo for multi language
	$mri_locale = get_post_meta( $post->ID, "_locale", true ); 
	
	$all_posts = get_posts( array( 
		'numberposts' => -1, 
		'orderby' => 'ID', 
		'order' => 'ASC',
		// List only posts with the right locale
		'meta_key' => '_locale',
		'meta_value' => $mri_locale,  /* <-- uncomment this as well if you use just one language on your WP site */
		
		// 26.04.2024 exclude self
		'exclude'      => array( $post->ID )
	) ); 
	?>
	<div style="column-count: 2;">
	<?php 
	foreach ( $all_posts as $this_post ) { 
		if ( in_array( $this_post->ID, (array) $mrirelated ) ) {
			$bold = '<strong style="color:blue">';
			$bold2 = '</strong>';
			$check = ' checked';
		} else {
			$bold = '';
			$bold2 = '';
			$check = '';
		}
		?>
		<input id="post_<?php echo $this_post->ID; ?>" type="checkbox" name="mrirelated[]" value="<?php echo $this_post->ID; ?>" <?php echo $check ?>><label for="post_<?php echo $this_post->ID; ?>"><?php echo $this_post->ID . ' - ' . $bold . $this_post->post_title . $bold2; ?></label><br>
		<?php
	}
	?>
	</div>
	<?php
}


add_action( 'save_post', 'mri_metabox_related_posts_save', 10, 2 );
function mri_metabox_related_posts_save( $post_id, $post ) {

	// Check if our nonce is set.
	if ( ! isset( $_POST['mri_metabox_related_posts_nonce_name'] ) ) {
      return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['mri_metabox_related_posts_nonce_name'], 'mri_metabox_related_posts_nonce' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Check for and sanitize user input.
	if ( ! isset( $_POST['mrirelated'] ) ) {
		delete_post_meta( $post_id, "_mrirelated" );
	}

	update_post_meta( $post_id, "_mrirelated", $_POST['mrirelated'] );

}

