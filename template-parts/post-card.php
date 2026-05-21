<?php

defined( 'WPINC' ) || die;

use WPShop\WPCommunity\Data\ElementAttributes\PostCardContext;
use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\the_attributes;
use function WPShop\WPCommunity\theme_container;

$post_card_tag = apply_filters( 'wpcommunity/post-card/tag', 'article' );

$post_instance = theme_container()->get( Post::class );
$post_format   = $post_instance->get_post_format( $post->ID );

?>

<<?php echo $post_card_tag ?> id="post-<?php the_ID(); ?>" <?php the_attributes( new PostCardContext( 'article.post-card' ), [
    'id'      => 'post-' . get_the_ID(),
    'classes' => get_post_class( 'post-card post-card--format-' . $post_format ),
] ); ?>>
<?php

/**
 * @since 1.1
 *
 * @hooked \WPShop\WPCommunity\Layout\PostCard::_output_header()
 * @hooked \WPShop\WPCommunity\Layout\PostCard::_output_image(), 20
 * @hooked \WPShop\WPCommunity\Layout\PostCard::_output_content(), 30
 * @hooked \WPShop\WPCommunity\Layout\PostCard::_output_content(), 40
 * @hooked \WPShop\WPCommunity\Layout\PostCard::_output_footer(), 50
 * @hooked \WPShop\WPCommunity\Features\MicroData::_add_post_card_meta(), 100
 */
do_action( 'wpcommunity/post_card/entry' );
?>
</<?php echo $post_card_tag ?>>
