<?php if (!empty($results)): ?>

	<?php if($results->have_posts() ) : while ($results->have_posts()) : $results->the_post(); ?>
		
		
		<div class="post-single" id="post-<?php the_ID(); ?>">
			<h2 class="blogtitle"><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			<div class="post-meta">
				<p><?php _e('Published on '); the_time('F j, Y'); _e(' at '); the_time(); _e(', by '); coauthors(); ?></p>
				<p><?php _e('Categories: '); the_category(', ') ?></p>
				<p><?php if (the_tags('Tags: ', ', ', ' ')); ?></p>
			</div><!--.postMeta-->
			<div class="post-content">
				<?php the_excerpt(__('Read more'));?>
			</div>
		</div><!--.post-single-->
		
	<?php endwhile; ?>
	
<?php else : ?>
	
		<p><?php echo $this->options['no_results_msg']; ?></p>
		
<?php endif; ?>

<div class="oldernewer">

		<?php wp_pagenavi(array( 'query' => $results )); ?>

	</div><!--.oldernewer-->
	<?php endif; ?>
	