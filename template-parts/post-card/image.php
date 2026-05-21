<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use function WPShop\WPCommunity\_ob_get_content;

$image = _ob_get_content( 'the_post_thumbnail' );
if ( ! $image ) {
    return;
}

?>

<div class="post-card__image">
    <a href="<?php echo esc_url( get_permalink() ) ?>">
        <?php echo $image ?>
    </a>
</div>
