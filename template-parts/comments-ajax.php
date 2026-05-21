<?php

/**
 * @version 1.0
 */

if ( post_password_required() ) {
    return;
}


?>
<div>

    <div class="comments-title"><?php echo __( 'Comments:', 'wpcommunity' ) ?><?php echo get_comments_number() ?></div>
    <!-- .comments-title -->

    <?php
    $user                 = wp_get_current_user();
    $user_identity        = $user->exists() ? $user->display_name : '';
    $comment_field_avatar = '';

    if ( is_user_logged_in() && $user_identity ) {
        $comment_field_avatar = '<div class="comment-form-comment__avatar">' . get_avatar( $user->ID, 48 ) . '</div>';
    }

    $post = get_post( $post );

    $comment_form_args = [
        'title_reply'        => '',
        'title_reply_before' => '',
        'title_reply_after'  => '',
        'title_reply_to'     => 'KEK',

        'cancel_reply_before' => '<span class="cancel-comment-reply-link">',
        'cancel_reply_after'  => '</span>',
        'cancel_reply_link'   => __( 'Cancel' ),

        'comment_notes_before' => '',

        'comment_field' => '<div class="comment-form-comment">' . $comment_field_avatar . '<textarea id="comment" name="comment" cols="45" rows="4" aria-required="true" placeholder="' . _x( 'Comment', 'noun' ) . '" required></textarea></div>',

        // если залогинен -- ничего не показываем, тк мы добавляем аватар к текстареа
        'logged_in_as'  => '',

        'submit_field' => '<div class="comment-form-submit">%1$s %2$s</div>',
        'class_submit' => 'btn',

        'must_log_in' => sprintf(
            '<p class="must-log-in">%s</p>',
            sprintf(
            /* translators: %s: Login URL. */
                __( 'You must be <a href="%s">logged in</a> to post a comment.' ),
                /** This filter is documented in wp-includes/link-template.php */
                add_query_arg(
                    'redirect_to',
                    get_permalink( $post->id ),
                    get_permalink( \WPShop\WPCommunity\get_setting( 'page.profile' ) )
                )
            )
        ),
    ];


    $commenter = wp_get_current_commenter();
    $req       = get_option( 'require_name_email' );
    $html5     = true;

    // Define attributes in HTML5 or XHTML syntax.
    $required_attribute = ( $html5 ? ' required' : ' required="required"' );
    $checked_attribute  = ( $html5 ? ' checked' : ' checked="checked"' );

    // Identify required fields visually.
    $required_indicator = ' <span class="required" aria-hidden="true">*</span>';

    $fields = [
        'author' => sprintf(
            '<div class="comment-form-author">%s %s</div>',
            sprintf(
                '<label for="author">%s%s</label>',
                __( 'Name' ),
                ( $req ? $required_indicator : '' )
            ),
            sprintf(
                '<input id="author" name="author" type="text" value="%s" size="30" maxlength="245"%s />',
                esc_attr( $commenter['comment_author'] ),
                ( $req ? $required_attribute : '' )
            )
        ),
        'email'  => sprintf(
            '<div class="comment-form-email">%s %s</div>',
            sprintf(
                '<label for="email">%s%s</label>',
                __( 'Email' ),
                ( $req ? $required_indicator : '' )
            ),
            sprintf(
                '<input id="email" name="email" %s value="%s" size="30" maxlength="100" aria-describedby="email-notes"%s />',
                ( $html5 ? 'type="email"' : 'type="text"' ),
                esc_attr( $commenter['comment_author_email'] ),
                ( $req ? $required_attribute : '' )
            )
        ),
        'url'    => sprintf(
            '<div class="comment-form-url">%s %s</div>',
            sprintf(
                '<label for="url">%s</label>',
                __( 'Website' )
            ),
            sprintf(
                '<input id="url" name="url" %s value="%s" size="30" maxlength="200" />',
                ( $html5 ? 'type="url"' : 'type="text"' ),
                esc_attr( $commenter['comment_author_url'] )
            )
        ),

    ];

    if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
        $consent = empty( $commenter['comment_author_email'] ) ? '' : $checked_attribute;

        $fields['cookies'] = sprintf(
            '<div class="comment-form-cookies-consent">%s %s</div>',
            sprintf(
                '<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%s />',
                $consent
            ),
            sprintf(
                '<label for="wp-comment-cookies-consent">%s</label>',
                __( 'Save my name, email, and website in this browser for the next time I comment.' )
            )
        );

        // Ensure that the passed fields include cookies consent.
        if ( isset( $args['fields'] ) && ! isset( $args['fields']['cookies'] ) ) {
            $args['fields']['cookies'] = $fields['cookies'];
        }
    }

    $comment_form_args['fields'] = $fields;

    $comment_form_args = apply_filters( 'wpcommunity/comments/comment_form_args', $comment_form_args );

    comment_form( $comment_form_args );
    ?>

    <?php
    // You can start editing here -- including this comment!
    if ( have_comments() ) :
        ?>

        <?php the_comments_navigation(); ?>

        <div class="comment-list">
            <?php
            wp_list_comments( [
                'walker'      => new WPCommunity_Walker_Comment(),
                'avatar_size' => 64,
                'style'       => 'div',
            ] );
            ?>
        </div><!-- .comment-list -->

        <?php
        the_comments_navigation();

        // If comments are closed and there are comments, let's leave a little note, shall we?
        if ( ! comments_open() ) :
            ?>
            <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'wpcommunity' ); ?></p>
        <?php
        endif;

    endif; // Check for have_comments().

    ?>
</div>
