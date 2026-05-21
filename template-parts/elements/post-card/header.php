<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<header class="post-card__header">
    <div class="post-meta post-card__meta">
        <?php

        /**
         * @since 1.0
         */
        do_action( 'wpcommunity/post_card/header_meta' );
        ?>
    </div>

    <?php

    /**
     * @since 1.0
     */
    do_action( 'wpcommunity/post_card/header' );
    ?>

</header><!-- .post-card__header -->
