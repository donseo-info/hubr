<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<div class="post-meta__comments">
    <a href="<?php echo esc_url( get_permalink() ) ?>#comments">
        <svg width="20" height="20">
            <use xlink:href="#ico-comment"></use>
        </svg>
        <?php echo get_comments_number() ?>
    </a>
</div>
