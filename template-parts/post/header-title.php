<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\ElementAttributes\PostContext;
use function WPShop\WPCommunity\the_attributes;

?>
<h1 <?php the_attributes( new PostContext( 'div.post-card__title' ), [ 'classes' => 'post-card__title' ] ); ?>><?php the_title() ?></h1>
