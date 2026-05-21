<?php

use WPShop\WPCommunity\Bookmark;
use function WPShop\WPCommunity\theme_container;

$bookmark = theme_container()->get( Bookmark::class );

$can_user_bookmark = $bookmark->can_user_bookmark();
$bookmark_attributes = ( ! $can_user_bookmark ) ? ' data-tooltip="' . __( 'Sign in to add to bookmarks', 'wpcommunity' ) . '" data-tooltip-pos="bottom"' : '';

$bookmark_classes = [ 'post-bookmark', 'js-post-bookmark' ];
if ( ! $can_user_bookmark ) {
    $bookmark_classes[] = 'disabled';
}
?>
<span class="<?php echo implode( ' ', $bookmark_classes ) ?>" data-post-id="<?php the_ID() ?>" <?php echo $bookmark_attributes ?>>
    <span class="post-bookmark__btn js-post-bookmark-btn <?php echo ( $bookmark->is_user_bookmarked_post( get_the_ID() ) ) ? 'active' : '' ?>">
        <svg width="20" height="20"><use xlink:href="#ico-bookmark"></use></svg>
    </span>
    <span class="post-bookmark__count js-post-bookmark-count"><?php echo $bookmark->get_post_bookmarks_count( get_the_ID() ) ?></span>
</span>
