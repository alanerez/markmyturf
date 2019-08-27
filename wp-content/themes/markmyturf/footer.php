<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
?>

		</div><!-- #main -->
		<aside id="after-content-container">
			<?php dynamic_sidebar( 'after-content-widget' ); ?>
		</aside>
		<aside id="footer-images-container">
			<?php dynamic_sidebar( 'footer-images-widget' ); ?>
		</aside>
		<footer id="colophon" class="site-footer" role="contentinfo">
			<div class="container">
				<div class="site-info">
					<img src="<?php bloginfo('stylesheet_directory'); ?>/images/logo.png" width="193" height="52" alt="<?php bloginfo( 'name' ); ?>" />
					<?php dynamic_sidebar( 'footer-widget' ); ?>
				</div><!-- .site-info -->

				<div class="ewp-credits">
					Webdesign <a href="http://everythingwebperth.com.au" title="Everything Web Perth">Everything Web Perth</a>
				</div><!-- .ewp-credits -->
			</div>
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
	
	<script>
		smoothScroll.init();
	</script>
	
</body>
</html>