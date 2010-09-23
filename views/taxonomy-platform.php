<?php
/**
 * Template Name: Projects Page
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); 
$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
?>

		<div id="container" class="one-column">
			<div id="content" role="main">
				<h1>Projects</h1>
				
				<h3 class="left">Completed using <?php echo $term->name; ?></h3>
				<p class="right"><a href="/projects">&larr; Back to all Projects</a></p>
				<div class="clear"></div>
			<?php if (have_posts()) : ?>

			<?php 
				$sortOptions = array('meta_key' => 'sugar-date', 'orderby' => 'meta_value', 'order' => 'DESC');
				$newQueryArgs = array_merge($wp->query_vars, $sortOptions);
				$newQuery = new WP_Query($newQueryArgs);
			?>
			<?php while ( $newQuery->have_posts() ) : $newQuery->the_post(); ?>

			<?php
				$project = WPSS_Project::getProject(get_the_ID());
			?>
			
				<div class="entry-project">
<!-- 					<a href="<?php echo $project->getURL(); ?>" target="_blank"><?php echo $project->getURL(); ?></a> -->
<!-- 					<p><?php echo $project->getClient(); ?></p> -->
					<a href="<?php echo get_permalink();?>">
						<img src="<?php echo $project->getImage(); ?>" />
					</a>
				</div>
			<?php endwhile; ?>
				<div class="clear"></div>
			<?php else: ?>
				<h4>No Projects Found</h4>
			<?php endif;?>
			</div><!-- #content -->
			
		</div><!-- #container -->

<?php get_footer(); ?>
