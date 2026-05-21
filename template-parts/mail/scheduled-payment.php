<?php

/**
 * @version 1.2
 */

use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\transform_markdown_link;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'date':\DateTimeInterface} $args
 */

$date = $args['date'];
?>
<p style="margin: 0 0 16px;">
    <?php printf(
        __( 'On %s the subscription renewal fee will be automatically charged.', 'wpcommunity' ),
        wp_date( 'j F Y', $date->getTimestamp() )
    ) ?>
</p>
<p style="margin: 0 0 16px;">
    <?php echo transform_markdown_link( sprintf( __( 'You can cancel the autopayment in your [profile](%s).', 'wpcommunity' ),
        get_the_permalink( get_setting( 'page.profile' ) )
    ) ) ?>
</p>
