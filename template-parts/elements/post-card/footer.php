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
    do_action( 'wpcommunity/post_card/footer' );

    ?>
    <div class="post-meta">
        <?php

        /**
         * @since 1.0
         */
        do_action( 'wpcommunity/post_card/footer_meta' );
        ?>

    </div>
</footer><!-- .post-card__footer -->
