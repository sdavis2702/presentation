<?php
/**
 * The template for displaying Archive pages.
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title">
					<?php
						if ( is_category() ) :
							single_cat_title();

						elseif ( is_tag() ) :
							single_tag_title();

						elseif ( is_author() ) :
							printf( __( 'Author: %s', 'presentation' ), '<span class="vcard">' . get_the_author() . '</span>' );

						elseif ( is_day() ) :
							printf( __( 'Day: %s', 'presentation' ), '<span>' . get_the_date() . '</span>' );

						elseif ( is_month() ) :
							printf( __( 'Month: %s', 'presentation' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'presentation' ) ) . '</span>' );

						elseif ( is_year() ) :
							printf( __( 'Year: %s', 'presentation' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'presentation' ) ) . '</span>' );

						elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
							_e( 'Asides', 'presentation' );

						elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
							_e( 'Links', 'presentation' );

						elseif ( is_tax( 'post_format', 'post-format-chat' ) ) :
							_e( 'Chats', 'presentation' );

						else :
							_e( 'Archives', 'presentation' );

						endif;
					?>
				</h1>
				<?php
					$term_description = term_description();
					$tag_description = tag_description();
					if ( is_author() && '' != get_the_author_meta( 'description' ) ) : // show author user bio if it exists ?>
						<p class="user-description"><?php echo get_the_author_meta( 'description' ); ?></p>
						<?php					
					elseif ( is_category() || is_tag() && ! empty( $tag_description ) ) : ?>
							<div class="taxonomy-description"><?php echo $term_description; ?></div>
							<?php
					endif;
				?>
			</header>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content/content', get_post_format() );
				?>

			<?php endwhile; ?>

			<?php presentation_paging_nav(); ?>

		<?php else : ?>

			<?php get_template_part( 'content/content', 'none' ); ?>

		<?php endif; ?>

		</main>
	</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
