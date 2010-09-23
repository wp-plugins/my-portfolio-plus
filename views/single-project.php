<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>
	
		<div id="container" class="one-column">
			<div id="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<?php
				$project = WPSS_Project::getProject(get_the_ID());
			?>
			
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1>Project Detail</h1>
					<h3 class="left"><?php the_title(); ?></h3>
					<a href="#" class="right" onClick="history.go(-1);return false;">&larr; Back to Projects</a>
					<div class="clear"></div>
					<div class="project-info">
						<div class="project-quick">
							<a href="<?php echo $project->getURL(); ?>" id="sitePreview">
								<img src="<?php echo $project->getImage(); ?>" />
							</a>
							<br />
							<p><span class="project-attr">URL:</span> <a href="<?php echo $project->getURL(); ?>" target="_blank"><?php echo $project->getURL(); ?></a></p>
							<p><span class="project-attr">Client:</span> <strong><?php echo $project->getClient(); ?></strong></p>
							<p><span class="project-attr">Date:</span> <strong><?php echo $project->getDate(); ?></strong></p>
						</div>
						<div class="project-description">
							<?php the_content(); ?>
						</div>
					</div><!-- .entry-content -->

				</div><!-- #post-## -->

<?php endwhile; // end of the loop. ?>
			<div class="apple_overlay" id="overlay">
			
				<!-- the external content is loaded inside this tag -->
				<div class="contentWrap"></div>
			
			</div>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>
