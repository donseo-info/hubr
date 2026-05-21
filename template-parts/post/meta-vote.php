<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

theme_container()->get( Vote::class )->the_vote( $post ); ?>
