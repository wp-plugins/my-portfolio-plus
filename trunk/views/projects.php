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

get_header(); ?>

		<div id="container" <?php echo wpss_isSidebarOn() ? '' : 'class="one-column"'; ?>>
			<div id="content" role="main">
				<h1>Projects</h1>
			<?php $loop = new WP_Query( array( 'post_type' => 'project', 'posts_per_page' => 10, 
			'meta_key' => 'sugar-date', 'orderby' => 'meta_value', 'order' => 'DESC' ) ); ?>
				
			<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
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
			</div><!-- #content -->
			
		</div><!-- #container -->
		
			<?php if( wpss_isSidebarOn()): ?>
				<div id="primary" class="widget-area" role="complementary">
					<h3>by Platform</h3>
					<?php wp_tag_cloud( array( 'taxonomy' => 'platform', 'number' => 45 ) ); ?>
				</div>
			<?php endif; ?>
			
<?php get_footer(); ?>
