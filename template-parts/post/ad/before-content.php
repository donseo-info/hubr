<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'ad_items':array} $args
 */
?>

<div>
    <?php foreach ( $args['ad_items'] as $item ): ?>
        <?php echo $item['content'] ?>
    <?php endforeach ?>
</div>
