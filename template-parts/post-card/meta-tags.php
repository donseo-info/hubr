<?php


/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<div class="post-meta__tags">
    <?php
    $post_tags = get_the_tags();
    if ( $post_tags ) {
        foreach ( $post_tags as $tag ) {
            echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . $tag->name . '</a> ';
        }
    }
    ?>
</div>
