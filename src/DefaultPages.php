<?php

namespace WPShop\WPCommunity;

use WP_Post;

class DefaultPages {

    /**
     * @var array[]
     */
    protected $pages;

    /**
     * @var array|null
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct() {
        $this->pages = [
            'profile'   => [
                'title'    => __( 'Profile', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-account.php',
            ],
            'join'      => [
                'title'   => __( 'Join Us', 'wpcommunity' ),
                'content' => <<<'HTML'
<!-- wp:shortcode -->
[subscriptions]
<!-- /wp:shortcode -->
HTML
                ,
            ],
            'about'     => [
                'title'   => __( 'About', 'wpcommunity' ),
                'content' => '',
            ],
            'popular'   => [
                'title'    => __( 'Popular', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-popular.php',
            ],
            'subs'      => [
                'title'    => __( 'Subscriptions', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-subs.php',
            ],
            'top'       => [
                'title'    => __( 'Top', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-top.php',
            ],
            'bookmarks' => [
                'title'    => __( 'Bookmarks', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-bookmarks.php',
            ],
            'publish'   => [
                'title'    => __( 'Add Post', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-publish.php',
            ],
            'order'     => [
                'title'    => __( 'Order', 'wpcommunity' ),
                'content'  => '',
                'template' => 'template-order.php',
            ],
            'offer'     => [
                'title'   => __( 'Offer', 'wpcommunity' ),
                'content' => [ $this, 'get_page_content' ],
            ],
            'payment'   => [
                'title'   => __( 'Payment and Refund', 'wpcommunity' ),
                'content' => [ $this, 'get_page_content' ],
            ],
            'contacts'  => [
                'title'   => __( 'Contacts', 'wpcommunity' ),
                'content' => [ $this, 'get_page_content' ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'wpcommunity/main/before', [ $this, '_output_page_header' ], 15 );
    }

    /**
     * @return void
     */
    public function _output_page_header() {
        if ( ! is_page() ) {
            return;
        }
        $id = get_the_ID();

        $page_elements = (array) ( get_post_meta( $id, 'page_elements', true ) ?: [] );

        if ( in_array( 'header', $page_elements ) ) {
            get_template_part( 'template-parts/elements/page-header' );
        }
    }

    /**
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    protected function get_page_content( $name, $locale = null ) {
        if ( ! $locale ) {
            $locale = get_locale();
        }

        if ( ! $this->data ) {
            $this->data = include_once get_template_directory() . '/data/pages.php';
        }

        return $this->data[ $locale ][ $name ] ?? '';
    }

    /**
     * @return array[]
     */
    public function get_pages() {
        uasort( $this->pages, function ( $a, $b ) {
            return strcasecmp( $a['title'], $b['title'] );
        } );

        return $this->pages;
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    public function get_page( $name ) {
        return $this->pages[ $name ] ?? null;
    }

    /**
     * @param array $pages particular list of pages
     *
     * @return array
     */
    public function create_pages( $pages = [] ) {
        $created_pages = [];
        foreach ( $this->get_pages() as $page_name => $params ) {
            if ( $pages && ! in_array( $page_name, $pages ) ) {
                continue;
            }

            if ( $page = $this->get_page_by_name( $page_name ) ) {
                $created_pages[] = [
                    'name'       => $page_name,
                    'id'         => $page->ID,
                    'title'      => "{$page->post_title}",
                    'link'       => get_permalink( $page->ID ),
                    'edit_link'  => get_edit_post_link( $page->ID, '' ),
                    'is_created' => false,
                ];
                continue;
            }

            $content = is_callable( $params['content'] )
                ? call_user_func( $params['content'], $page_name )
                : $params['content'];

            $post_id = wp_insert_post( [
                'post_name'    => $page_name,
                'post_title'   => $params['title'],
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
                'post_type'    => 'page',
            ] );

            if ( ! is_wp_error( $post_id ) ) {
                if ( isset( $params['template'] ) ) {
                    update_post_meta( $post_id, '_wp_page_template', $params['template'] );
                }

                $created_pages[] = [
                    'name'       => $page_name,
                    'id'         => $post_id,
                    'title'      => "{$params['title']}",
                    'link'       => get_permalink( $post_id ),
                    'edit_link'  => get_edit_post_link( $post_id, '' ),
                    'is_created' => true,
                ];
            }
        }

        return $created_pages;
    }

    /**
     * @param string $name
     *
     * @return WP_Post|null
     */
    protected function get_page_by_name( $name ) {
        return get_page_by_name( $name );
    }
}
