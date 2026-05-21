<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>

<?php theme_container()->get( Vote::class )->the_vote( $post ); ?>
