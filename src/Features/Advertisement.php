<?php

namespace WPShop\WPCommunity\Features;

use Monolog\DateTimeImmutable;
use WP_Post;
use WPShop\WPCommunity\Context;
use WPShop\WPCommunity\Layout\LoopContext;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class Advertisement {

    const OPTION = 'wpcommunity_advertisement';

    protected $_context;

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp', function () {
            $this->_context = Context::createFromWpQuery();
        } );

//        add_action( 'wpcommunity/post/content', [ $this, '_insert_post_ad_before_content' ], 35 );
//        add_action( 'wpcommunity/post/content', [ $this, '_insert_post_ad_after_content' ], 45 );
        add_action( 'wpcommunity/post_content/before', [ $this, '_insert_post_ad_before_content' ] );
        add_action( 'wpcommunity/post_content/after', [ $this, '_insert_post_ad_after_content' ] );

        add_action( 'wpcommunity/comments/output', [ $this, '_insert_post_ad_before_comments' ], 15 );
        add_action( 'wpcommunity/comments/output', [ $this, '_insert_post_ad_after_comments' ], 25 );

        add_action( 'wpcommunity/posts_loop/after_card', [ $this, '_insert_ad_in_loop' ] );

        add_filter( 'wpcommunity/advertisement/item_content', [ $this, '_update_yandex_ad_block' ] );
        add_filter( 'wpcommunity/advertisement/item_content', [ $this, '_replace_variables' ] );
    }

    /**
     * @return Context
     */
    protected function get_context() {
        if ( ! $this->_context ) {
            trigger_error( 'Using a context object before the "wp" hook may cause errors', E_USER_WARNING );
            $this->_context = Context::createFromWpQuery();
        }

        return $this->_context;
    }

    /**
     * @return void
     */
    public function _insert_post_ad_before_content() {
        if ( ! $this->get_context()->is_singular( 'post' ) ) {
            return;
        }

        if ( $ad_items = $this->get_post_ad_items( 'before_content', get_post() ) ) {
            get_template_part( 'template-parts/post/ad/before-content', '', compact( 'ad_items' ) );
        }
    }

    /**
     * @return void
     */
    public function _insert_post_ad_after_content() {
        if ( ! $this->get_context()->is_singular( 'post' ) ) {
            return;
        }

        if ( $ad_items = $this->get_post_ad_items( 'after_content', get_post() ) ) {
            get_template_part( 'template-parts/post/ad/after-content', '', compact( 'ad_items' ) );
        }
    }

    /**
     * @return void
     */
    public function _insert_post_ad_before_comments() {
        if ( ! $this->get_context()->is_singular( 'post' ) ) {
            return;
        }

        if ( $ad_items = $this->get_post_ad_items( 'before_comments', get_post() ) ) {
            get_template_part( 'template-parts/post/ad/comments', '', compact( 'ad_items' ) );
        }
    }

    /**
     * @return void
     */
    public function _insert_post_ad_after_comments() {
        if ( ! $this->get_context()->is_singular( 'post' ) ) {
            return;
        }

        if ( $ad_items = $this->get_post_ad_items( 'after_comments', get_post() ) ) {
            get_template_part( 'template-parts/post/ad/comments', '', compact( 'ad_items' ) );
        }
    }

    /**
     * @param string $content
     *
     * @return array
     */
    public function _update_yandex_ad_block( $content ) {
        if ( preg_match( '/Ya\.Context/ui', $content ) ) {
            if ( preg_match( '/[\'"]?\s*renderTo\s*[\'"]?\s*:\s*[\'"]([^\'"]+)[\'"]/', $content, $match ) ) {
                if ( ! empty( $match[1] ) ) {
                    $block_id = $match[1];

                    $salt = time() . mt_rand( 0, 9999 );

                    $new_block_id = $block_id . $salt;

                    $content = str_replace( $block_id, $new_block_id, $content );

                    if ( substr_count( $content, 'pageNumber' ) ) {
                        $content = preg_replace( '/pageNumber:\s*[0-9]+(,|)/ui', '', $content );
                        $content = str_replace( 'Ya.Context.AdvManager.render({', 'Ya.Context.AdvManager.render({ pageNumber: ' . $salt . ', ', $content );
                    }
                }
            }
        }

        return $content;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function _replace_variables( $content ) {
        return str_replace( '{{unique_id}}', uniqid( current_time( 'timestamp' ) . '-' ), $content );
    }

    /***
     * @param string  $position
     * @param WP_Post $post
     *
     * @return array
     */
    protected function get_post_ad_items( $position, $post ) {
        if ( ! $post ) {
            return [];
        }

        $ad_items = $this->get_items();

        return array_filter( $ad_items, function ( $item ) use ( $position, $post ) {
            $include = wp_parse_id_list( $item['options']['include'] ?? '' );
            $exclude = wp_parse_id_list( $item['options']['exclude'] ?? '' );

            $show_by_date = true;
            if ( $after_days = absint( $item['options']['show_after_days'] ?? '' ) ) {
                $current_date = current_datetime();
                $post_date    = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $post->post_date );
                $date_diff    = $current_date->diff( $post_date );

                $show_by_date = $after_days > $date_diff->days;
            }

            return $item['place'] === 'post' && // если блок показываем в записи
                   $item['content'] && // если есть контент
                   $item['options'][ $position ] && // если соответствует требуемой позиции
                   ( $include ? in_array( $post->ID, $include ) : true ) && // если только в списке include
                   ( $exclude ? ! in_array( $post->ID, $exclude ) : true ) && // если нет в списке exclude
                   $show_by_date;
        } );
    }

    /**
     * @return void
     */
    public function _insert_ad_in_loop() {
        $loop_context = theme_container()->get( LoopContext::class );

        $context = $loop_context->get_context();
        $counter = $loop_context->get_counter();

        $ad_items = $this->get_items();

        // фильтруем по месту размещения и счетчику
        $ad_items = array_filter( $ad_items, function ( $item ) use ( $counter ) {
            return $item['place'] === 'archive' && $item['content'] &&
                   $item['options']['after_n'] &&
                   0 === $counter % $item['options']['after_n'];
        } );

        $output = function ( $type, $items ) {
            $items = array_filter( $items, function ( $item ) use ( $type ) {
                return $item['options'][ $type ];
            } );
            if ( $items ): ?>
                <div class="post-card">
                    <?php foreach ( $items as $item ):
                        echo $item['content'];
                    endforeach ?>
                </div>
            <?php endif;
        };

        /* ?>
        <div class="post-card">counter <?php echo $counter ?></div>
        <?php */

        if ( $context->is_home ) {
            $output( 'home', $ad_items );
        }

        if ( ( $page_id = get_setting( 'page.popular' ) ) && $context->is_page( $page_id ) ) {
            $output( 'popular', $ad_items );
        }
        if ( ( $page_id = get_setting( 'page.subs' ) ) && $context->is_page( $page_id ) ) {
            $output( 'subs', $ad_items );
        }
        if ( ( $page_id = get_setting( 'page.bookmarks' ) ) && $context->is_page( $page_id ) ) {
            $output( 'bookmarks', $ad_items );
        }

        if ( $context->is_category ||
             $context->is_tag
        ) {
            $place = null;
            if ( $context->is_category ) {
                $place = 'category';
            } else if ( $context->is_tag ) {
                $place = 'tag';
            }

            $ad_items = $this->get_items( $place );

            $ad_items = array_filter( $ad_items, static function ( $item ) use ( $counter, $context ) {
                $include = wp_parse_id_list( $item['options']['include'] ?? '' );
                $exclude = wp_parse_id_list( $item['options']['exclude'] ?? '' );

                if ( ! empty( $item['options']['after_n'] ) ) {
                    return 0 === $counter % $item['options']['after_n'] &&
                           ( $include ? in_array( $context->get_object_id(), $include ) : true ) && // если только в списке include
                           ( $exclude ? ! in_array( $context->get_object_id(), $exclude ) : true ); // если нет в списке exclude
                }

                return false;
            } );

            if ( $ad_items ):?>
                <div class="post-card">
                    <?php foreach ( $ad_items as $item ):
                        echo $item['content'];
                    endforeach ?>
                </div>
            <?php endif;
        }
    }

    /**
     * @return array
     */
    public static function insert_places() {
        return [
            'post'     => __( 'Post', 'wpcommunity' ),
            'archive'  => __( 'Feed', 'wpcommunity' ),
            'category' => __( 'Post Category', 'wpcommunity' ),
            'tag'      => __( 'Post Tag', 'wpcommunity' ),
        ];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function save_items( array $data ) {
        $to_save = [];
        foreach ( $data as $item ) {
            $item = wp_parse_args( $item, [
                'place'          => '',
                'content_pc'     => '',
                'content_mobile' => '',
            ] );

            switch ( $item['place'] ) {
                case 'post':
                    $item['options'] = wp_parse_args( $item['options'] ?? [], [
                        'before_content'  => 0,
                        'after_content'   => 0,
                        'before_comments' => 0,
                        'after_comments'  => 0,
                        'exclude'         => '',
                        'include'         => '',
                        'show_after_days' => '',
                    ] );
                    break;
                case 'archive':
                    $item['options'] = wp_parse_args( $item['options'] ?? [], [
                        'home'      => 0,
                        'bookmarks' => 0,
                        'popular'   => 0,
                        'subs'      => 0,
                        'after_n'   => 0,
                    ] );
                    break;
                case 'category':
                case 'tag':
                    $item['options'] = wp_parse_args( $item['options'] ?? [], [
                        'after_n' => 0,
                        'exclude' => '',
                        'include' => '',
                    ] );
                    break;
                default:
                    continue 2;
            }

            $to_save[] = $item;
        }

        update_option( self::OPTION, $to_save );
    }

    /**
     * @return array
     */
    public function get_items( $place = null ) {
        $items = (array) get_option( self::OPTION, [] );

        if ( $place ) {
            $items = array_filter( $items, static function ( $item ) use ( $place ) {
                return $item['place'] === $place;
            } );
        }

        $is_mobile = wp_is_mobile();

        // устанавливаем ключ content в зависимости от wp_is_mobile()
        $items = array_map( static function ( $item ) use ( $is_mobile ) {
            $item['content'] = $is_mobile ? $item['content_mobile'] : $item['content_pc'];

            /**
             * @since 1.0
             */
            $item['content'] = apply_filters( 'wpcommunity/advertisement/item_content', $item['content'], $item, $is_mobile, 'loop' );

            return $item;
        }, $items );

        return $items;
    }
}
