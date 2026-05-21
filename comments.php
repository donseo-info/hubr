<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WPCommunity
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

use function WPShop\WPCommunity\get_setting;

if ( post_password_required() ) {
    return;
}

$post = get_post();

?>

<div id="comments" class="comments-area">
    <?php if ( get_setting( 'comments.show_by_button' ) && have_comments() ): ?>
        <div class="js-wpcommunity-comments-container">
            <button class="btn js-wpcommunity-show-comments" data-post="<?php echo $post ? $post->ID : 0 ?>"><?php echo __( 'Show Comments', 'wpcommunity' ) ?></button>
        </div>
    <?php else: ?>
        <?php get_template_part( 'template-parts/comments' ) ?>
    <?php endif ?>
</div><!-- #comments -->
