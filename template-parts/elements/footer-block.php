<?php

/**
 * @version 1.0.1
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'content':string, 'block':int} $args
 */

?>

<div class="site-footer__block site-footer__block--<?php echo $args['block'] ?>">
    <?php echo $args['content'] ?>
</div>
