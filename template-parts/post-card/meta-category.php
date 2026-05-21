<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\ElementAttributes\PostCardContext;
use function WPShop\WPCommunity\the_attributes;

?>

<div <?php the_attributes( new PostCardContext( 'div.post-meta__category' ), [ 'classes' => 'post-meta__category' ] ); ?>>
    <?php wpcommunity_the_category() ?>
</div>
