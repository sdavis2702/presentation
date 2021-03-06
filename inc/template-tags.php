<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 */

if ( ! function_exists( 'presentation_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
function presentation_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}
	?>
	<nav class="navigation paging-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'presentation' ); ?></h1>
		<div class="nav-links">
			<?php if ( get_next_posts_link() ) : ?>
				<div class="nav-previous"><?php next_posts_link( sprintf( __( '%sOlder posts', 'presentation' ), '<i class="fa fa-arrow-circle-left"></i>' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
				<div class="nav-next"><?php previous_posts_link( sprintf( __( 'Newer posts%s', 'presentation' ), '<i class="fa fa-arrow-circle-right"></i>' ) ); ?></div>
			<?php endif; ?>
		</div>
	</nav>
	<?php
}
endif;

if ( ! function_exists( 'presentation_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @return void
 */
function presentation_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}
	?>
	<nav class="navigation post-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'presentation' ); ?></h1>
		<div class="nav-links">
			<?php
				previous_post_link( '<div class="nav-previous">%link</div>', '<i class="fa fa-arrow-circle-left"></i>' . _x( '%title', 'Previous post link', 'presentation' ) );
				next_post_link( '<div class="nav-next">%link</div>', _x( '%title ', 'Next post link', 'presentation' ) . '<i class="fa fa-arrow-circle-right"></i>' );
			?>
		</div>
	</nav>
	<?php
}
endif;

if ( ! function_exists( 'presentation_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function presentation_posted_on() {
	$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);
	?>
	<span class="byline">
		<i class="fa fa-pencil"></i>
		<?php
			printf( '<span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span>',
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				esc_html( get_the_author() )
			);
		?>
	</span>
	<span class="posted-on">
		<i class="fa fa-calendar"></i>
		<?php
			printf( '<a href="%1$s" rel="bookmark">%2$s</a>',
				esc_url( get_permalink() ),
				$time_string
			);
		?>
	</span>
	<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
		<span class="comments-link"><i class="fa fa-comments"></i><?php comments_popup_link( __( 'Comments', 'presentation' ), __( '1 Comment', 'presentation' ), __( '% Comments', 'presentation' ) ); ?></span>
	<?php endif;
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 */
function presentation_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) :
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	endif;

	if ( '1' != $all_the_cool_cats ) :
		// This blog has more than 1 category so presentation_categorized_blog should return true.
		return true;
	else :
		// This blog has only 1 category so presentation_categorized_blog should return false.
		return false;
	endif;
}

/**
 * Flush out the transients used in presentation_categorized_blog.
 */
function presentation_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'presentation_category_transient_flusher' );
add_action( 'save_post',     'presentation_category_transient_flusher' );