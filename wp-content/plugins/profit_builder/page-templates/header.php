<?php
require_once 'Mobile_Detect.php';
$detect = new Mobile_Detect;
?>
<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!--[if lt IE 9]>
<script>
	document.createElement('header');
	document.createElement('nav');
	document.createElement('section');
	document.createElement('article');
	document.createElement('aside');
	document.createElement('footer');
	document.createElement('video');
	document.createElement('audio');
</script>
<![endif]-->
<?php
$pb_class = 'pb-layout pb_wide';//div-nobgvideo
$page_bg = get_post_meta(get_the_ID(), 'pb_page_bg', true );
if ( $page_bg == '' || $page_bg == 'none' ) {
    $pb_class .= ' div-nobgvideo';
}
wp_head();
?>
</head>
<body <?php body_class( $pb_class ); ?> >
<div id="pb_wrapper">
    <?php
	if ( $page_bg !== '' && $page_bg !== 'none' ) {
        switch ($page_bg) :
            case 'bgimage' :
                $image = get_post_meta(get_the_ID(),'pb_page_image',true);
                $size = @getimagesize($image);
                printf('<div id="pb_page_bg" class="pb_page_bg"><div id="pb_page_bg_inner"><img src="'.$image.'" id="bg" alt="" style="min-width:'.$size['width'].';min-height:'.$size['height'].';" /></div></div>' );
            break;
            case 'videoembed' :
                if($detect->isMobile() || $detect->isTablet()){
                    $image = get_post_meta(get_the_ID(),'pb_page_image',true);
                    if(!empty($image) && $image != ""){
                        $size = @getimagesize($image);
                        printf('<div id="pb_page_bg" class="pb_page_bg"><div id="pb_page_bg_inner"><img src="'.$image.'" id="bg" alt="" style="min-width:'.$size['width'].';min-height:'.$size['height'].';" /></div></div>' );
                    }
                }else{
                    printf('<div id="pb_page_bg" class="pb_page_bg"><div id="pb_page_bg_inner"></div></div>' );
                }
            break;
			case 'html5video' :
                $add_poster = ( has_post_thumbnail() ? wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID() ), 'full' ) : '' );
                $mp4 = get_post_meta(get_the_ID(),'pb_pagevideo_mp4',true);
                $ogv = get_post_meta(get_the_ID(),'pb_pagevideo_ogv',true);
                $entry = sprintf('
                    <video class="fullwidth block" preload="auto" loop="loop" autoplay%4$s>
				        <source src="%1$s" type="video/mp4">
						<source src="%2$s" type="video/ogg">
						%3$s
					</video>', $mp4, $ogv, __( 'Your browser does not support the video tag.', 'pb' ), ( $add_poster !== '' ? ' poster="'.$add_poster[0].'"  data-image-replacement="'.$add_poster[0].'"' : '') );
                
                if($detect->isMobile() || $detect->isTablet()){
                    $image = get_post_meta(get_the_ID(),'pb_page_image',true);
                    if(!empty($image) && $image != ""){
                        $size = @getimagesize($image);
                        printf('<div id="pb_page_bg" class="pb_page_bg"><div id="pb_page_bg_inner"><img src="'.$image.'" id="bg" alt="" style="min-width:'.$size['width'].';min-height:'.$size['height'].';" /></div></div>' );
                    }
                }else{
                    printf('<div id="pb_page_bg" class="pb_page_bg">%1$s</div>', $entry );
                }
            break;
	   endswitch;		
	}
?>