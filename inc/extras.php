<?php
/**
 * Custom functions that act independently of the theme templates
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function presentation_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'presentation_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function presentation_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) :
		$classes[] = 'group-blog';
	endif;
	
	if ( is_page_template( 'edd_templates/edd-store-front.php' ) ) :		
		$classes[] = 'edd-store-front-template';
	elseif ( is_page_template( 'edd_templates/edd-checkout.php' ) ) :		
		$classes[] = 'edd-checkout-template';	
	elseif ( is_page_template( 'edd_templates/edd-confirmation.php' ) ) :		
		$classes[] = 'edd-confirmation-template';
	elseif ( is_page_template( 'edd_templates/edd-history.php' ) ) :		
		$classes[] = 'edd-history-template';
	elseif ( is_page_template( 'edd_templates/edd-members.php' ) ) :		
		$classes[] = 'edd-members-template';
	elseif ( is_page_template( 'edd_templates/edd-failed.php' ) ) :		
		$classes[] = 'edd-failed-template';				
	endif;

	return $classes;
}
add_filter( 'body_class', 'presentation_body_classes' );

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function presentation_setup_author() {
	global $wp_query;

	if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
		$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
	}
}
add_action( 'wp', 'presentation_setup_author' );
