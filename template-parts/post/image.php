<?php

/**
 * @version 1.1
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\theme_container;

$post_instance = theme_container()->get( Post::class );
$post_format   = $post_instance->get_post_format( $post->ID );
$is_video      = ( $post_format === 'video' );

$raw_gallery = get_post_meta( get_the_ID(), '_hubr_gallery', true );
$gallery_ids = is_array( $raw_gallery )
    ? array_values( array_filter( $raw_gallery, function( $id ) {
        $f = get_attached_file( $id );
        return ! $f || ! str_starts_with( basename( $f ), 'generic_' );
    } ) )
    : [];

?>
<?php if ( $is_video ): ?>
    <?php /* carousel with video rendered via [hubr_gallery] shortcode inside content */ ?>
<?php elseif ( count( $gallery_ids ) === 1 ): ?>
    <div class="post-card__image">
        <?php echo wp_get_attachment_image( $gallery_ids[0], 'large', false, [ 'class' => '' ] ); ?>
    </div>
<?php elseif ( count( $gallery_ids ) > 1 ): ?>
<div class="post-gallery">
    <div class="post-gallery__track">
        <?php foreach ( $gallery_ids as $img_id ): ?>
        <div class="post-gallery__slide">
            <?php echo wp_get_attachment_image( $img_id, 'large', false, [ 'class' => 'post-gallery__img' ] ); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="post-gallery__btn post-gallery__btn--prev" aria-label="Назад">&#8249;</button>
    <button class="post-gallery__btn post-gallery__btn--next" aria-label="Вперёд">&#8250;</button>
    <div class="post-gallery__dots">
        <?php foreach ( $gallery_ids as $i => $_ ): ?>
        <span class="post-gallery__dot<?= $i === 0 ? ' active' : '' ?>"></span>
        <?php endforeach; ?>
    </div>
</div>
<?php elseif ( has_post_thumbnail() ): ?>
    <div class="post-card__image">
        <?php the_post_thumbnail( 'post-thumbnail' ) ?>
    </div>
<?php elseif ( is_customize_preview() ): ?>
    <div class="post-card__image">
        <i>&lt;<?php echo __( 'image preview' ) ?>&gt;</i>
    </div>
<?php endif; ?>
