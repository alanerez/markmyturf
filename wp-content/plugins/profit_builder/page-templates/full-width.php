<?php
/**
* Template Name: ProfitBuilder Full Width Page
*/
//echo $site_url;
include_once "header.php";//IMSCPB_DIR.DIRECTORY_SEPARATOR."page-templates".DIRECTORY_SEPARATOR."header.php";

$pb_page_width = get_post_meta(get_the_ID(), 'pb_page_width', true );
?>
    <div id="pb_content">
		<div class="pb_container" style="<?php if ($pb_page_width != '') echo ' max-width: '.$pb_page_width.'; ';?>">
		<?php if ( have_posts() ) : ?>
    		<div id="content" <?php post_class(); ?>>
    			<?php the_post(); ?>
    			<?php the_content(); ?>
    		</div>
		<?php endif; ?>
		</div>
	</div>
<?php
include_once "footer.php";//IMSCPB_DIR.DIRECTORY_SEPARATOR."page-templates".DIRECTORY_SEPARATOR."footer.php";
