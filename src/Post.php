<?php

namespace WPShop\WPCommunity;

use WP_Error;

class Post {

    const POST_META_FORMAT = 'format';
    const POST_META_ACCESS = 'access';

    public $formats;

    public function __construct() {
        $this->formats = [
            'post'     => _x( 'Post', 'post format', 'wpcommunity' ),
            'question' => _x( 'Question', 'post format', 'wpcommunity' ),
            'video'    => _x( 'Video', 'post format', 'wpcommunity' ),
            'link'     => _x( 'Link', 'post format', 'wpcommunity' ),
            'event'    => _x( 'Event', 'post format', 'wpcommunity' ),
        ];
    }


    /**
     * @return void
     */
    public function init() {

//		$action = 'wpcommunity_save_post';
//		add_action( "wp_ajax_{$action}", [ $this, 'ajax_save_post' ] );
//		add_action( "wp_ajax_nopriv_{$action}", [ $this, 'ajax_save_post' ] );


        add_action( 'wpcommunity/post_content/after', [ $this, '_output_source_link' ], 5 );
        add_action( 'wpcommunity/post_content/after', [ $this, '_output_edit_link' ], 5 );

        add_action( 'save_post', [ $this, 'save_video_thumbnail' ] );
    }

    /**
     * @return void
     */
    public function _output_source_link() {
        global $post;

        if ( ! $post ) {
            return;
        }

        $url = get_post_meta( $post->ID, 'source_link', true );
        if ( ! $url ) {
            return;
        }
        ?>
        <div class="meta-source">
            <a href="<?php echo esc_attr( $url ) ?>" target="_blank" rel="noopener"><?php echo esc_html__( 'Source', 'wpcommunity' ) ?></a>
        </div>
        <?php
    }

    /**
     * @return void
     */
    public function _output_edit_link() {
        global $post;
        if ( ! $post || $post->post_author != get_current_user_id() ) {
            return;
        }

        $frontend_publish = theme_container()->get( FrontendPublish::class );
        ?>
        <div class="meta-edit">
            <a href="<?php echo $frontend_publish->get_edit_link( get_the_ID() ) ?>">(<?php echo __( 'edit', 'wpcommunity' ) ?>)</a>
        </div>
        <?php
    }


    public function get_formats() {
        return $this->formats;
    }

    public function get_post_format( $post_id ) {
        $format = get_post_meta( $post_id, self::POST_META_FORMAT, true );
        if ( empty( $format ) ) {
            $format = 'post';
        }

        return $format;
    }

    public function get_format_title( $format ) {
        if ( isset( $this->formats[ $format ] ) ) {
            return $this->formats[ $format ];
        }

        return '';
    }


    public function save_video_thumbnail( $post_id ) {

        // если автосохранение -- выходим
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // если статус поста не опублированный -- выходим
        if ( get_post_status( $post_id ) != 'publish' ) {
            return;
        }

        // если у поста уже есть миниатюра
        if ( has_post_thumbnail() ) {
            return;
        }

        // если формат не видео -- выходим
        $format = get_post_meta( $post_id, self::POST_META_FORMAT, true );
        if ( $format != 'video' ) {
            return;
        }

        $post = get_post( $post_id );

        // ищем youtube

        $regexes = [
            '#(?:https?:)?//www\.youtube(?:\-nocookie)?\.com/(?:v|e|embed)/([A-Za-z0-9\-_]+)#',
            '#(?:https?(?:a|vh?)?://)?(?:www\.)?youtube(?:\-nocookie)?\.com/watch\?.*v=([A-Za-z0-9\-_]+)#',
            '#(?:https?(?:a|vh?)?://)?youtu\.be/([A-Za-z0-9\-_]+)#',
        ];

//		$regexes = [
//			'#(//(?:www\.)?vk\.com/video_ext\.php\?oid=\-?[0-9]+(?:&|&\#038;|&amp;)id=\-?[0-9]+(?:&|&\#038;|&amp;)hash=[0-9a-zA-Z]+)#',
//			// URL
//		];

//		$regexes = [
//			'#(?:www\.)?twitch\.tv/(?:[A-Za-z0-9_]+)/c/([0-9]+)#',
//			// Video URL
//			'#<object[^>]+>.+?http://www\.twitch\.tv/widgets/archive_embed_player\.swf.+?chapter_id=([0-9]+).+?</object>#s',
//			// Flash embed
//			'#<object[^>]+>.+?http://www\.twitch\.tv/swflibs/TwitchPlayer\.swf.+?videoId=c([0-9]+).+?</object>#s',
//			// Newer Flash embed
//		];
//		$request = "https://api.twitch.tv/kraken/videos/c$id";

        $youtube_id = '';

        foreach ( $regexes as $regex ) {
            if ( preg_match( $regex, $post->post_content, $matches ) ) {
                $youtube_id = $matches[1];
            }
        }

        // если нашли
        if ( ! empty( $youtube_id ) ) {
            $image_url = 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg';

            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attach_id = media_sideload_image( $image_url, $post_id, '', 'id' );

            if ( $attach_id ) {
                update_post_meta( $post_id, '_thumbnail_id', $attach_id );
            }
        }

    }


}
