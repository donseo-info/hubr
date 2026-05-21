<?php

use WPShop\WPCommunity\FrontendPublish;
use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'editor':\WPShop\WPCommunity\Features\FrontendEditor} $args
 */

$frontend_publish = theme_container()->get( FrontendPublish::class );

/**
 * Allows to change args for available topics on publish page
 *
 * [ru] Позволяет изменять параметры для доступных тем на странице публикации
 *
 * @since 1.0
 */
$publish_topics_args = apply_filters( 'wpcommunity/publish_form/topic_args', [
    'taxonomy'   => [ 'category' ],
    'hide_empty' => false,
] );

$terms = get_terms( $publish_topics_args );

/**
 * Allows to change available post topics
 *
 * [ru] Позволяет поменять доступные топики поста
 *
 * @since 1.1
 */
$terms = (array) apply_filters( 'wpcommunity/publish_form/topics', $terms );

// Готовим контент
// Если указан GET 'id', то выполняем проверки и если все ок — заполняем данными оттуда
$post_id              = 0;
$title                = '';
$excerpt              = '';
$content              = '';
$category_id          = 0;
$access               = get_setting( 'content.default_access.post_type--post' );
$format               = '';
$hidden_input_post_id = '';

$errors  = [];
$notices = [];

if ( ! empty( $_GET['id'] ) ) {
    $post_id = (int) $_GET['id'];

    $post = get_post( $post_id );

    $is_editable = $frontend_publish->is_post_editable( $post->ID, get_current_user_id() );

    if ( is_wp_error( $is_editable ) ) {
        $errors[] = $is_editable->get_error_message();
    }

    if ( empty( $errors ) ) {
        $title   = $post->post_title;
        $excerpt = $post->post_excerpt;
        $content = $post->post_content;

        $categories  = get_the_category( $post->ID );
        $category_id = $categories[0]->term_id;

        $access = get_post_meta( $post->ID, Post::POST_META_ACCESS, true );
        $format = get_post_meta( $post->ID, Post::POST_META_FORMAT, true );

        if ( 'pending' === $post->post_status ) {
            $notices[] = 'Ваш пост находится на модерации. ' .
                         sprintf(
                             'Если он соответствует нашим <a href="%s" target="_blank">Правилам</a> и <a href="%s" target="_blank">Миссии</a>, то опубликуем его в самое ближайшее время.',
                             get_permalink( 1 ),
                             get_permalink( 2 )
                         ) .
                         ' Напоминаем, что пост снимается с публикации и отправляется повторно на модерацию после каждого его изменения с вашей стороны';
        }
    }

    $hidden_input_post_id = '<input type="hidden" name="post_id" value="' . $post->ID . '" id="js-publish-form-post-id">';
} else {
    $auto_draft = get_posts( [
        'post_status' => 'auto-draft',
        'numberposts' => 1,
        'author'      => get_current_user_id(),
    ] );

    if ( $auto_draft ) {
        $post    = current( $auto_draft );
        $post_id = $post->ID;
    } else {
        $post_id = wp_insert_post( [
            'post_title'  => 'Draft',
            'post_status' => 'auto-draft',
        ] );

        $post = get_post( $post_id );
    }
}

$thumbnail_id = get_post_meta( $post ? $post->ID : null, '_thumbnail_id', true );
?>


<?php
foreach ( $notices as $notice ) {
    echo '<div class="alert alert-warning">' . $notice . '</div>';
}
foreach ( $errors as $error ) {
    echo '<div class="alert alert-danger">' . $error . '</div>';
}
?>

<?php
$drafts = $frontend_publish->get_drafts( get_current_user_id() );
if ( ! empty( $drafts ) ) {
    echo '<div class="publish-drafts js-publish-drafts">';
    echo '<div class="publish-drafts__header js-publish-drafts-header">';
    echo __( 'Drafts', 'wpcommunity' );
    echo '<span class="badge js-publish-drafts-header-badge">' . count( $drafts ) . '</span>';
    echo '</div>';

    echo '<div class="publish-drafts__body js-publish-drafts-body">';
    foreach ( $drafts as $draft ) {

        $edit_link = $frontend_publish->get_edit_link( $draft->ID );

        echo '<div class="publish-draft js-publish-draft" data-post-id="' . $draft->ID . '">';
        if ( $post_id == $draft->ID ) {
            echo '&rarr; ';
        }
        echo get_the_title( $draft->ID );
        echo '<div class="publish-draft__actions">';
        if ( $post_id == $draft->ID ) {
            echo '  ' . __( 'You are currently editing this post.', 'wpcommunity' );
        } else {
            echo '  <a href="' . get_the_permalink( $draft->ID ) . '" class="publish-draft__action" target="_blank">' . __( 'View', 'wpcommunity' ) . '</a>';
            echo '  <a href="' . $edit_link . '" class="publish-draft__action">' . __( 'Edit', 'wpcommunity' ) . '</a>';
            echo '  <span class="publish-draft__action js-publish-draft-delete">' . __( 'Delete', 'wpcommunity' ) . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div><!--.publish-drafts__body-->';
    echo '</div><!--.publish-drafts-->';
}
?>

<form action="" method="post" class="publish-form js-publish-form">
    <div class="publish-form__title">
        <label for="title"><?php _e( 'Title', 'wpcommunity' ) ?></label>
        <input name="title" id="title" type="text" class="input" value="<?php esc_attr_e( $title ) ?>" required>
    </div>
    <div class="publish-form__excerpt">
        <label for="excerpt"><?php _e( 'Excerpt', 'wpcommunity' ) ?></label>
        <textarea name="excerpt" id="excerpt" rows="2" class="input"><?php echo $excerpt ?></textarea>
        <p class="publish-form__description"><?php _e( 'An excerpt is a short description of a post in two sentences. Usually motivating to go in and read the text. The excerpt will appear below the title and will always be visible, regardless of your privacy settings.', 'wpcommunity' ) ?></p>
    </div>
    <div class="publish_form__thumbnail" id="postimagediv">
        <div class="inside">
            <?php echo $frontend_publish->get_post_thumbnail_html( $thumbnail_id, $post ) ?>
        </div>
    </div>
    <div class="publish-form__text">
        <label for="text"><?php _e( 'Text', 'wpcommunity' ) ?></label>
        <?php $args['editor']->wp_editor( $content, 'text' ); ?>
    </div>
    <div class="publish-form__row">
        <div class="publish-form__topic">
            <label for="topic"><?php _e( 'Topic', 'wpcommunity' ) ?></label>
            <select name="topic" id="topic" class="select">
                <!--                <option value="">Choose topic</option>-->
                <?php
                foreach ( $terms as $term ) {
                    $selected = selected( $category_id, $term->term_id, false );
                    echo '<option value="' . $term->term_id . '"' . $selected . '>' . $term->name . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="publish-form__format">
            <label for="format"><?php _e( 'Format', 'wpcommunity' ) ?></label>
            <select name="format" id="format" class="select">
                <?php
                foreach ( $frontend_publish->get_formats() as $format_id => $format_name ) {
                    echo '<option value="' . $format_id . '"' . selected( $format, $format_id ) . '>' . $format_name . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="publish-form__row">
        <div class="publish-form__topic">
            <label for="tags"><?php _e( 'Tags', 'wpcommunity' ) ?></label>
            <?php
            $tags         = get_tags();
            $current_tags = wp_list_pluck( wp_get_post_tags( $post_id ), 'name' );
            ?>

            <input type="text" id="publish_tags" class="input">
            <input type="hidden" name="tags" value="<?php echo esc_attr( implode( ',', $current_tags ) ) ?>">
            <div class="post-tag-suggest" style="display: none">
                <div class="post-tag-suggest__item">tag 1</div>
                <div class="post-tag-suggest__item">tag 2</div>
            </div>
            <div class="post-tag-pills js-wpcommunity-tag-pills"></div>

            <template type="text/html" id="tag_pill_template">
                <span class="post-tag-pill__text">{{text}}</span>
                <span class="post-tag-pill__remove">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512">
                        <path d="M441 407a24 24 0 11-34 34L256 290 105 441a24 24 0 01-34-34l151-151L71 105a24 24 0 0134-34l151 151L407 71a24 24 0 0134 34L290 256z" fill="currentColor"></path>
                    </svg>
                </span>
            </template>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function () {
                    const container = document.querySelector('.js-wpcommunity-tag-pills');
                    const tagContainer = document.querySelector('.post-tag-suggest');
                    const tagsInput = document.querySelector('input[name="tags"]');

                    const canCreate = <?php echo get_setting( 'publish.can_create_tags' ) ? 'true' : 'false' ?>;

                    tagsInput.value && tagsInput.value.split(',').forEach(tag => {
                        container.appendChild(createPill(tag, getRemoveTagFn(tag)));
                    });

                    let suggestTimeout;

                    document.getElementById('publish_tags').addEventListener('keydown', function (e) {
                        const text = e.target.value.trim();

                        clearTimeout(suggestTimeout);
                        suggestTimeout = setTimeout(function () {
                            const form = new FormData();
                            form.append('action', 'wpcommunity_suggest_tags');
                            form.append('text', text);
                            fetch(wpsc_globals.url, {
                                method: 'POST',
                                body: form
                            })
                                .then(response => response.json())
                                .then(response => {
                                    if (!response.success || !response.data.length) {
                                        return;
                                    }
                                    tagContainer.innerHTML = '';
                                    tagContainer.style = 'display:block';
                                    response.data.forEach(tag => {
                                        const tagEl = document.createElement('div');
                                        tagEl.classList.add('post-tag-suggest__item');
                                        tagEl.innerText = tag;
                                        tagContainer.appendChild(tagEl);
                                        tagEl.addEventListener('click', e => {
                                            if (appendValue(tag)) {
                                                container.appendChild(createPill(tag, getRemoveTagFn(tag)));
                                            }
                                            tagContainer.style = 'display:none';
                                        });
                                    });
                                });
                        }, 300);

                        if (e.key !== 'Enter') {
                            return;
                        }
                        e.preventDefault();

                        if (!canCreate) {
                            return;
                        }

                        clearTimeout(suggestTimeout);
                        tagContainer.style = 'display:none';

                        if (text) {
                            if (appendValue(text, e.target)) {
                                container.appendChild(createPill(text, getRemoveTagFn(text)));
                            }
                        }
                    });

                    document.addEventListener('click', e => {
                        if (!e.target.classList.contains('post-tag-suggest__item')) {
                            document.querySelector('.post-tag-suggest').style = 'display:none';
                        }
                    });

                    function appendValue(text, inp) {
                        const values = tagsInput.value.split(',');
                        if (values.includes(text)) {
                            return false;
                        }
                        values.push(text);
                        tagsInput.value = values.join(',');
                        if (inp) {
                            inp.value = '';
                        }
                        return true;
                    }

                    function createPill(text, onRemove) {
                        const pill = document.createElement('div');
                        pill.classList.add('post-tag-pill');
                        pill.appendChild(document.getElementById('tag_pill_template').content.cloneNode(true));
                        pill.querySelector('.post-tag-pill__text').innerText = text;
                        pill.querySelector('.post-tag-pill__remove').addEventListener('click', function () {
                            if (typeof onRemove === 'function') {
                                onRemove();
                            }
                            pill.remove();

                        });
                        return pill;
                    }

                    function getRemoveTagFn(text) {
                        return function () {
                            tagsInput.value = tagsInput.value.split(',').filter(tag => {
                                return tag !== text;
                            }).join(',');
                        };
                    }

                    function suggestTags() {

                    }
                });
            </script>
        </div>
    </div>
    <div class="publish-form__row">
        <div class="publish-form__access">
            <label for="access"><?php _e( 'Access', 'wpcommunity' ) ?></label>
            <select name="access" id="access" class="select">
                <option value="private"<?php selected( $access, 'private' ) ?>><?php _e( 'Private', 'wpcommunity' ) ?></option>
                <option value="public"<?php selected( $access, 'public' ) ?>><?php _e( 'Public', 'wpcommunity' ) ?></option>
            </select>
        </div>

    </div>
    <div class="publish-form__actions">
        <button type="submit" class="js-publish-form-save"><?php _e( 'Save', 'wpcommunity' ) ?></button>
        <button class="btn-secondary js-publish-form-preview"><?php _e( 'View', 'wpcommunity' ) ?></button>
        <span class="publish-form__action-saved js-publish-form-saved"></span>
        <button class="btn--green js-publish-form-publish"><?php _e( 'Publish', 'wpcommunity' ) ?></button>
    </div>
    <?php echo $hidden_input_post_id ?>
</form>
