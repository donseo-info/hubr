<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>

<footer class="post-card__footer">

    <?php

    /**
     * @since 1.0
     */
    do_action( 'wpcommunity/post/footer' );
    ?>

    <div class="post-meta post-card__meta">
        <?php

        /**
         * @since 1.0
         */
        do_action( 'wpcommunity/post/footer_meta' );
        ?>
    </div>

</footer>
