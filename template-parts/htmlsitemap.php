<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}


/**
 * @version 1.1
 */

/**
 * @var array{'related_posts':WP_Post[], 'post':WP_Post} $args
 */
?>
<div class="sitemap-list">
    <ul>
        <li>

        </li>

        <li class="sitemap-list__header"><h2>header</h2></li>
        <li class="sitemap-list__block">
            <ul>
                <?php foreach ( $posts as $post ): ?>
                    <li>
                        <a href="<?php echo esc_attr( get_permalink( $post->ID ) ) ?>" target="_blank"><?php echo get_the_title( $post->ID ) ?></a>
                    </li>
                <?php endforeach ?>
            </ul>
        </li>
    </ul>
</div>
