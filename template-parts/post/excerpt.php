<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>

<?php if ( has_excerpt() ) : ?>
    <div class="post-card__excerpt">
        <?php echo get_the_excerpt() ?>
    </div>
<?php elseif ( is_customize_preview() ): ?>
    <div class="post-card__excerpt">
        <i>&lt;<?php echo __( 'excerpt preview' ) ?>&gt;</i>
    </div>
<?php endif ?>
