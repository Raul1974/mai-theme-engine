<?php
/**
 * Mai Theme Engine.
 *
 * @author   Mike Hemberger
 *
 * @version  1.0.0
 */


/**
 * Helper function to force a layout in a template
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * add_filter( 'genesis_pre_get_option_site_layout', '__mai_return_md_content' );
 */

function __mai_return_md_content() {
	return 'md-content';
}

function __mai_return_sm_content() {
	return 'sm-content';
}

function __mai_return_xs_content() {
	return 'xs-content';
}

/**
 * Add new sitewide layout options.
 *
 * @return  void
 */
add_action( 'init', 'mai_register_layouts' );
function mai_register_layouts() {

	// Layout image directory
	$dir = MAI_THEME_ENGINE_PLUGIN_URL . 'assets/images/layouts/';

	// Medium Content
	genesis_register_layout( 'md-content', array(
		'label' => __( 'Medium Content', 'mai-theme-engine' ),
		'img'   => $dir . 'mdc.gif',
	) );
	// Small Content
	genesis_register_layout( 'sm-content', array(
		'label' => __( 'Small Content', 'mai-theme-engine' ),
		'img'   => $dir . 'smc.gif',
	) );
	// Extra Small Content
	genesis_register_layout( 'xs-content', array(
		'label' => __( 'Extra Small Content', 'mai-theme-engine' ),
		'img'   => $dir . 'xsc.gif',
	) );
}

/**
 * Maybe set fallbacks for archive layouts.
 *
 * @return  array  The layouts.
 */
add_filter( 'genesis_site_layout', 'mai_get_layout' );
function mai_get_layout( $layout ) {

	/**
	 * Remove layout filter from Genesis Connect for WooCommerce.
	 * Mai Theme Engine handles this instead.
	 */
	remove_filter( 'genesis_pre_get_option_site_layout', 'genesiswooc_archive_layout' );

	// Setup cache.
	static $layout_cache = '';

	// If cache is populated, return value.
	if ( '' !== $layout_cache ) {
		return esc_attr( $layout_cache );
	}

	$site_layout = '';

	global $wp_query;

	// If home page.
	if ( is_home() ) {
		$site_layout = genesis_get_custom_field( '_genesis_layout', get_option( 'page_for_posts' ) );
		if ( ! $site_layout ) {
			$site_layout = genesis_get_option( 'layout_archive' );
		}
	}

	// If viewing a singular page, post, or CPT.
	elseif ( is_singular() ) {
		$site_layout = genesis_get_custom_field( '_genesis_layout', get_the_ID() );
		if ( ! $site_layout ) {
			$site_layout = genesis_get_option( sprintf( 'layout_%s', get_post_type() ) );
		}
	}

	// If viewing a post taxonomy archive.
	elseif ( is_category() || is_tag() || is_tax( get_object_taxonomies( 'post', 'names' ) ) ) {
		$term        = $wp_query->get_queried_object();
		$site_layout = $term ? get_term_meta( $term->term_id, 'layout', true) : '';
		$site_layout = $site_layout ? $site_layout : genesis_get_option( 'layout_archive' );
	}

	// If viewing a custom taxonomy archive.
	elseif ( is_tax() ) {
		$term        = $wp_query->get_queried_object();
		$site_layout = $term ? get_term_meta( $term->term_id, 'layout', true) : '';
		if ( ! $site_layout ) {
			$tax = get_taxonomy( $wp_query->get_queried_object()->taxonomy );
			if ( $tax ) {
				/**
				 * If we have a tax, get the first one.
				 * Changed to reset() when hit an error on a term archive that object_type array didn't start with [0]
				 */
				$post_type = reset( $tax->object_type );
				// If we have a post type and it supports genesis-cpt-archive-settings
				if ( post_type_exists( $post_type ) && genesis_has_post_type_archive_support( $post_type ) ) {
					// $site_layout = genesis_get_option( sprintf( 'layout_archive_%s', $post_type ) );
					$site_layout = genesis_get_cpt_option( 'layout', $post_type );
				}
			}
		}
		$site_layout = $site_layout ? $site_layout : genesis_get_option( 'layout_archive' );
	}

	// If viewing a supported post type.
	// elseif ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
	elseif ( is_post_type_archive() ) {
		// $site_layout = genesis_get_option( sprintf( 'layout_archive_%s', get_post_type() ) );
		$site_layout = genesis_get_cpt_option( 'layout', get_post_type() );
		$site_layout = $site_layout ? $site_layout : genesis_get_option( 'layout_archive' );
	}

	// If viewing an author archive.
	elseif ( is_author() ) {
		$site_layout = get_the_author_meta( 'layout', (int) get_query_var( 'author' ) );
		$site_layout = $site_layout ? $site_layout : genesis_get_option( 'layout_archive' );
	}

	// Pull the theme option.
	if ( ! $site_layout ) {
		$site_layout = genesis_get_option( 'site_layout' );
	}

	// Use default layout as a fallback, if necessary.
	if ( ! genesis_get_layout( $site_layout ) ) {
		$site_layout = genesis_get_default_layout();
	}
	// Push layout into cache.
	$layout_cache = $site_layout;

	// Return site layout.
	return esc_attr( $site_layout );

}

/**
 * Maybe add no-sidebars body class to the head.
 *
 * @param   array  $classes  The body classes.
 *
 * @return  array  The modified body classes.
 */
add_filter( 'body_class', 'mai_sidebars_body_class' );
function mai_sidebars_body_class( $classes ) {

	$layout = genesis_site_layout();

	$no_sidebars = array(
		'full-width-content',
		'md-content',
		'sm-content',
		'xs-content',
	);
	$has_sidebar = array(
		'sidebar-content',
		'content-sidebar',
	);
	$has_sidebars = array(
		'sidebar-content-sidebar',
		'content-sidebar-sidebar',
		'sidebar-sidebar-content',
	);
	// Add .no-sidebar body class if don't have any sidebars
	if ( in_array( $layout, $no_sidebars ) ) {
		$classes[] = 'no-sidebars';
	} elseif ( in_array( $layout, $has_sidebar ) ) {
		$classes[] = 'has-sidebar';
	} elseif ( in_array( $layout, $has_sidebars ) ) {
		$classes[] = 'has-sidebar has-sidebars';
	}
	return $classes;
}

/**
 * Use Flexington for the main content and sidebar layout.
 *
 * @return  void.
 */
add_action( 'genesis_before_content_sidebar_wrap', 'mai_do_layout' );
function mai_do_layout() {

	$layout = genesis_site_layout();

	// No sidebars
	$no_sidebars = array(
		'full-width-content',
		'md-content',
		'sm-content',
		'xs-content',
	);

	// Single sidebar
	$single_primary_first = array(
		'sidebar-content',
	);
	$single_content_first = array(
		'content-sidebar',
	);
	$single_sidebars = array_merge( $single_primary_first, $single_content_first );

	// Double sidebars
	$double_secondary_first = array(
		'sidebar-content-sidebar',
	);
	$double_secondary_last = array(
		'content-sidebar-sidebar',
	);
	$double_secondary_first_content_last = array(
		'sidebar-sidebar-content',
	);
	$double_sidebars = array_merge( $double_secondary_first, array_merge( $double_secondary_last, $double_secondary_first_content_last ) );

	$secondary_first = array_merge( $double_secondary_first, $double_secondary_first_content_last );
	$sidebars        = array_merge( $single_sidebars, $double_sidebars );

	// Remove primary sidebar
	if ( in_array( $layout, $no_sidebars ) ) {
		remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
	}

	// Reposition secondary sidebar, we'll add it back later where we need it
	remove_action( 'genesis_after_content_sidebar_wrap', 'genesis_get_sidebar_alt' );

	// Add back the secondary sidebary where flexington needs it
	if ( in_array( $layout, $double_sidebars ) ) {
		add_action( 'genesis_after_content', function() {
			get_sidebar( 'alt' );
		}, 11 );
	}

	// Add flexington row classes to the content sidebar wrap
	// add_filter( 'genesis_attr_content-sidebar-wrap', function( $attributes ) use ( $layout, $sidebars ) {
	// 	$gutter = '';
	// 	$align  = ' around-xs';
	// 	// Remove alignment and add gutter
	// 	if ( in_array( $layout, $sidebars ) ) {
	// 		$gutter = ' gutter-30';
	// 		$align  = '';
	// 	}
	// 	$attributes['class'] .= ' row' . $gutter . $align;
	// 	return $attributes;
	// });

	/**
	 * Add flexington column classes to the content
	 * The breakpoint classes here need to match with the sidebar classes and total 12
	 * to avoid flash of full-width containers on page load
	 */
	add_filter( 'genesis_attr_content', function( $attributes ) use ( $layout, $no_sidebars, $double_sidebars, $double_secondary_first_content_last ) {
		$classes = '';
		// Add .content-no-sidebar class if don't have any sidebars
		if ( in_array( $layout, $no_sidebars ) ) {
			$classes .= ' content-no-sidebars';
		}
		// $classes .= ' col col-xs-12 col-md';
		// if ( in_array( $layout, $double_sidebars ) ) {
		// 	// Break to full width earlier when there are 2 sidebars
		// 	$classes .= ' col col-xs-12 col-lg-6';
		// }
		// if ( in_array( $layout, $double_secondary_first_content_last ) ) {
		// 	$classes .= ' last-lg';
		// }
		$attributes['class'] .= $classes;
		return $attributes;
	});

	// Add flexington column classes to the primary sidebar
	// add_filter( 'genesis_attr_sidebar-primary', function( $attributes ) use ( $layout, $double_sidebars, $single_primary_first ) {
	// 	$classes = ' col col-xs-12 col-md-4';
	// 	if ( in_array( $layout, $double_sidebars ) ) {
	// 	// Break to full width earlier when there are 2 sidebars
	// 		$classes = ' col col-xs-12 col-lg-4';
	// 	}
	// 	if ( in_array( $layout, $single_primary_first ) ) {
	// 		$classes .= ' first-md';
	// 	}
	// 	$attributes['class'] .= $classes;
	// 	return $attributes;
	// });

	// Add flexington column classes to the secondary sidebar.
	// add_filter( 'genesis_attr_sidebar-secondary', function( $attributes ) use ( $layout, $secondary_first ) {
	// 	// This will only show if there are 2 sidebars, no need for the conditional above
	// 	$classes = ' col col-xs-12 col-lg-2';
	// 	if ( in_array( $layout, $secondary_first ) ) {
	// 		$classes .= ' first-lg';
	// 	}
	// 	$attributes['class'] .= $classes;
	// 	return $attributes;
	// });

}

/**
 * Filter the footer-widgets context of the genesis_structural_wrap to add a div before the closing wrap div.
 *
 * @param   string  $output             The markup to be returned.
 * @param   string  $original_output    Set to either 'open' or 'close'.
 *
 * @return  string  The footer markup
 */
add_filter( 'genesis_structural_wrap-footer-widgets', 'mai_footer_widgets_flex_row', 10, 2 );
function mai_footer_widgets_flex_row( $output, $original_output ) {
	if ( 'open' == $original_output ) {
		$output = $output . '<div class="row gutter-30">';
	}
	elseif ( 'close' == $original_output ) {
		$output = '</div>' . $output;
	}
	return $output;
}

/**
 * Filter the footer-widget markup to add flexington column classes.
 *
 * @param   array  $attributes  The array of attributes to be added to the footer widget wrap.
 *
 * @return  array  The attributes.
 */
add_filter( 'genesis_attr_footer-widget-area', 'mai_footer_widgets_flex_classes' );
function mai_footer_widgets_flex_classes( $attributes ) {
	switch ( mai_get_footer_widgets_count() ) {
		case '1':
			$classes = ' col col-xs-12 center-xs';
		break;
		case '2':
			$classes = ' col col-xs-12 col-sm-6';
		break;
		case '3':
			$classes = ' col col-xs-12 col-sm-6 col-md-4';
		break;
		case '4':
			$classes = ' col col-xs-12 col-sm-6 col-md-3';
		break;
		case '6':
			$classes = ' col col-xs-6 col-sm-4 col-md-2';
		break;
		default:
			$classes = ' col col-xs';
	}
	$attributes['class'] .= $classes;
	return $attributes;
}
