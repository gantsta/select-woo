<?php

/**
 * Like PHP's in_array() function except that it returns the value of $needle instead of true on success.
 *
 */
function ajg_needle_in_array($needle, $haystack){
	return in_array($needle, $haystack) ? $needle : false;
}


/**
 * Renders an accessible, multiple select element that is searchable using the selectWoo JavaScript function.
 * NB: Relies on a custom postmeta field called '_ajg_featured_product_ids' to be saved as a comma-separated list of post IDs.
 * This function should be run as a callback function to the add_meta_box hook.
 *
 * @return		void
 * @author		gantsta
 * @see			https://developer.woocommerce.com/2017/08/08/selectwoo-an-accessible-replacement-for-select2/
 */
function ajg_render_select(){
	global $post;
	$feat_product_ids = get_post_meta($post->ID, '_ajg_featured_product_ids', true );
	$feat_product_ids = ( $feat_product_ids != '' ? explode(',', $feat_product_ids) : array() );
	$args = array(
		'post_type'             => 'product',
		'posts_per_page'        => -1,
	);
	$posts = get_posts($args);
	if ( is_array($posts) && count($posts) >= 1 ):
		$options_markup = '';
		foreach ($posts as $post_obj):
			$_product = wc_get_product( $post_obj->ID );
			$options_markup .= '<option value="' . $post_obj->ID . '"' . selected($post_obj->ID, needle_in_array($post_obj->ID, $feat_product_ids), false) . '>' . $_product->get_formatted_name() . '</option>';
		endforeach;
		ob_start();
		?>
		<p class="form-field">
			<select class="ajg-product-selector wc-enhanced-select" multiple="multiple" id="featured_product_ids" name="featured_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'text-domain' ); ?>">
				<?php echo $options_markup; ?>
			</select>
		</p>
		<?php
		echo ob_get_clean();
	else:
		echo '<p class="howto">No products found.</p>';
	endif;
}


/**
 * Instructs WordPress to load any required JavaScript and CSS files within WP Admin on new post or post edit screens.
 * Will use the version of selectWoo bundled with WooCommerce if possible. Otherwise will look for a version of selectWoo within the theme.
 *
 * @return		void
 * @author		gantsta
 */
function ajg_enqueue_admin_scripts(){
	global $pagenow;
	if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' ):
		if ( class_exists( 'woocommerce' ) ):
			$script_url = plugin_dir_url('woocommerce.php') . 'assets/js/selectWoo/selectWoo.full.min.js';
		else:
			$script_url = get_template_directory_uri() . '/js/vendor/selectWoo/selectWoo.full.min.js';
		endif;

		wp_enqueue_script(
			'selectWoo',
			$script_url,
			array('jquery')
		);
		wp_enqueue_script(
			'selectWoo-dom',
			get_template_directory_uri() . '/js/backend/selectWoo-dom.js',
			array(
				'jquery',
				'selectWoo'
			)
		);
	endif;
}
if ( is_admin() ):
	add_action( 'admin_enqueue_scripts', 'ajg_enqueue_admin_scripts' );
endif;
