<?php
/**
 * Mai Pro Engine.
 *
 * @author   Mike Hemberger
 *
 * @version  1.0.0
 */


/**
 * WooCommerce product gallery support.
 *
 * Remove these by adding any of the following in functions.php:
 *
 * remove_theme_support( 'wc-product-gallery-zoom' );
 * remove_theme_support( 'wc-product-gallery-lightbox' );
 * remove_theme_support( 'wc-product-gallery-slider' );
 */
add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );

/**
 * Load WooCommerce templates in the plugin,
 * while still allowing the theme to override.
 *
 * @return  string  The template file location
 */
add_filter( 'wc_get_template', 'mai_wc_get_template', 10, 4 );
function mai_wc_get_template( $template, $template_name, $args, $template_path ) {

	if ( ! $template_path ) {
		$template_path = WC()->template_path();
	}

	// Look for the file in the theme - this is priority
	$_template = locate_template( array( $template_path . $template_name, $template_name ) );

	if ( $_template ) {
		// Use theme template
		$template = $_template;
	} else {
		// Use our plugin template
		$plugin_path = MAI_PRO_ENGINE_PLUGIN_DIR . 'templates/woocommerce/';
		if ( file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}
	}
	return $template;
}

/**
 * Load WooCommerce templates in the plugin,
 * while still allowing the theme to override.
 *
 * @return  string  The template file location
 */
add_filter( 'wc_get_template_part', 'mai_wc_get_template_part', 10, 3 );
function mai_wc_get_template_part( $template, $slug, $name ) {

	$template_path = WC()->template_path();
	$template_name = "{$slug}-{$name}.php";

	// Look within passed path within the theme - this is priority
	$_template = locate_template( array( $template_path . $template_name, $template_name ) );

	if ( $_template ) {
		// Use theme template
		$template = $_template;
	} else {
		// Use our plugin template
		$plugin_path = MAI_PRO_ENGINE_PLUGIN_DIR . 'templates/woocommerce/';
		if ( file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}
	}
	return $template;
}

/**
 * Set default WooCommerce layouts.
 * We need to hook in later to make give
 * a chance for template to exist.
 *
 * @return  void
 */
// add_action( 'genesis_meta', 'mai_woocommerce_default_layouts' );
function mai_woocommerce_default_layouts() {
	// Bail if WooCommerce is not active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return ;
	}
	// Run filter
	add_filter( 'genesis_pre_get_option_site_layout', 'mai_woocommerce_default_layout' );
}

/**
 * genesis_site_layout() only calls genesis_get_option( 'site_layout' )
 * if a specific layout isn't chosen. So, it calls this for the default.
 *
 * @return  The site layout
 */
function mai_woocommerce_default_layout( $layout ) {

	// Bail if we have no layout or non-default layout is already chosen
	if ( ! empty( $layout ) && $layout != genesis_get_default_layout() ) {
		return $layout;
	}

	if ( is_shop() ) {
		$layout = 'md-content';
	}
	elseif ( is_product() ) {
	    $layout = 'md-content';
	}
	elseif ( is_account_page() ) {
	    $layout = 'full-width-content';
	}
	elseif ( is_cart() ) {
	    $layout = 'md-content';
	}
	elseif ( is_checkout() ) {
	    $layout = 'md-content';
	}
	return $layout;

}

/**
 * Alter the post classes for the related products loop.
 *
 * @return  void
 */
// add_action( 'woocommerce_after_single_product_summary', 'mai_before_woocommerce_output_related_products', 19 );
function mai_before_woocommerce_output_related_products() {

	echo '<h1>Here</h1>';

	// Remove the default flex loop classes, since they are based on layout
	// remove_action( 'woocommerce_before_template_part', 'mai_do_woo_flex_loop', 10, 4 );

	// Set column count with a filter so devs can change the default column count
	// $columns = apply_filters( 'mai_woo_related_products_columns', 3 );

	// Filter and add our flex classes
	// mai_do_flex_entry_classes_by_columns( $columns );
}

/**
 * Revert post classes after the related products loop has fired.
 *
 * @return  void
 */
// add_action( 'woocommerce_after_single_product_summary', 'mai_after_woocommerce_output_related_products', 21 );
function mai_after_woocommerce_output_related_products() {

	// Remove the related products flex loop
	remove_action( 'woocommerce_after_single_product_summary', 'mai_before_woocommerce_output_related_products', 19 );

	// Add back the default flex loop classes, incase there are any other custom product loops
	add_action( 'woocommerce_before_template_part', 'mai_do_woo_flex_loop', 10, 4 );
}

/**
 * Alter the post classes for any woocommerce product loops
 * coming from a shortcode.
 *
 * @return  void
 */
// add_action( 'woocommerce_shortcode_before_recent_products_loop',       'mai_do_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_before_sale_products_loop',         'mai_do_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_before_best_selling_products_loop', 'mai_do_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_before_top_rated_products_loop',    'mai_do_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_before_featured_products_loop',     'mai_do_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_before_related_products_loop',      'mai_do_woo_shortcode_flex_loop' );
function mai_do_woo_shortcode_flex_loop() {
	// Get the woo global so we know can access the data
	global $woocommerce_loop;
	// Remove the default woo flex loop function
	remove_action( 'woocommerce_before_template_part', 'mai_do_woo_flex_loop', 10, 4 );
	// Alter post classes based on columns in the shortcode
	mai_do_flex_entry_classes_by_columns( $woocommerce_loop['columns'] );
}

/**
 * Remove the shortcode post_class filters
 * after the shortcode has fired
 * so any additional shortcodes get processed on their own.
 *
 * @return  void
 */
// add_action( 'woocommerce_shortcode_after_recent_products_loop',       'mai_remove_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_after_sale_products_loop',         'mai_remove_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_after_best_selling_products_loop', 'mai_remove_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_after_top_rated_products_loop',    'mai_remove_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_after_featured_products_loop',     'mai_remove_woo_shortcode_flex_loop' );
// add_action( 'woocommerce_shortcode_after_related_products_loop',      'mai_remove_woo_shortcode_flex_loop' );
function mai_remove_woo_shortcode_flex_loop() {
	remove_action( 'woocommerce_shortcode_before_recent_products_loop',       'mai_do_woo_shortcode_flex_loop' );
	remove_action( 'woocommerce_shortcode_before_sale_products_loop',         'mai_do_woo_shortcode_flex_loop' );
	remove_action( 'woocommerce_shortcode_before_best_selling_products_loop', 'mai_do_woo_shortcode_flex_loop' );
	remove_action( 'woocommerce_shortcode_before_top_rated_products_loop',    'mai_do_woo_shortcode_flex_loop' );
	remove_action( 'woocommerce_shortcode_before_featured_products_loop',     'mai_do_woo_shortcode_flex_loop' );
	remove_action( 'woocommerce_shortcode_before_related_products_loop',      'mai_do_woo_shortcode_flex_loop' );
}

// Maybe remove woocommerce page title
add_filter( 'woocommerce_show_page_title', 'mai_woocommerce_show_page_title' );
function mai_woocommerce_show_page_title( $return ) {
	if ( mai_is_banner_area_enabled() ) {
		if ( is_shop() ) {
			return false;
		}
		if ( is_product() ) {
			return false;
		}
	}
	return false;
}

// Remove taxonomy archive description since Mai has this functionality already
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );

// Replace Woocommerce Default pagination with Genesis Framework Pagination
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
