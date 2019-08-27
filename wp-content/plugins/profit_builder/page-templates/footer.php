</div>
<?php
wp_footer();

$retargetpixel = do_shortcode(get_post_meta(get_the_ID(), 'pb_retargetpixel', true));
if (!empty($retargetpixel))
    echo $retargetpixel;
?>
</body>
</html>