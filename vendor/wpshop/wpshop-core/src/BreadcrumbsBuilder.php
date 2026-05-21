<?php

namespace Wpshop\Core2;

use WP_Post;
use WP_Term;
use WP_User;

/**
 * @version 1.0
 */
class BreadcrumbsBuilder {

    const TYPE_HOMEPAGE = 'homepage';

    /**
     * @var string
     */
    protected $home_link;

    /**
     * @var string
     */
    protected $home_text;

    /**
     * @var string
     */
    protected $page_search_text;

    /**
     * @var string
     */
    protected $page_404_text;

    /**
     * @var string
     */
    protected $paged_format;

    /**
     * @var string
     */
    protected $archive_format;

    /**
     * @var bool
     */
    protected $show_paged = false;

    /**
     * @var bool
     */
    protected $show_term_ancestors = true;

    /**
     * @var bool
     */
    protected $hide_tax_name = true;

    /**
     * @var string
     */
    protected $separator = '<span class="breadcrumbs-separator">»</span>';

    /**
     * @var bool
     */
    protected $clear_last_link = true;

    /**
     * @var array|null
     */
    protected $crumbs;

    /**
     * @var callable|null
     */
    protected $item_render_fn;

    /**
     * Constructor
     */
    public function __construct() {
        $this->home_link        = '/';
        $this->home_text        = 'Home';
        $this->page_search_text = 'Search';
        $this->page_404_text    = 'Not Found';
        $this->paged_format     = 'Page %s';
        $this->archive_format   = 'Archive for %s';
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function set_home_link( $link ) {
        $this->home_link = $link;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function set_home_text( $text ) {
        $this->home_text = $text;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function set_page_search_text( $text ) {
        $this->page_search_text = $text;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function set_page_404_text( $text ) {
        $this->page_404_text = $text;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function set_paged_format( $format ) {
        $this->paged_format = $format;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function set_archive_format( $format ) {
        $this->archive_format = $format;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function set_show_paged( $flag ) {
        $this->show_paged = $flag;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function set_show_term_ancestors( $flag ) {
        $this->show_term_ancestors = $flag;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function set_hide_tax_name( $flag ) {
        $this->hide_tax_name = $flag;

        return $this;
    }

    /**
     * @param string $separator
     *
     * @return $this
     */
    public function set_separator( $separator ) {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function set_clear_last_link( $flag ) {
        $this->clear_last_link = $flag;

        return $this;
    }

    /**
     * Can accept function like this
     * <pre>
     * function ( $crumb, $idx, $count) {}
     * </pre>
     *
     * @param callable $item_render_fn
     *
     * @return $this
     */
    public function set_item_render_fn( $item_render_fn ) {
        if ( ! is_callable( $item_render_fn ) ) {
            throw new \BadMethodCallException( 'Unable to set not callable' );
        }


        $this->item_render_fn = $item_render_fn;

        return $this;
    }

    /**
     * @param string $text
     * @param string $link
     * @param string $type
     *
     * @return $this
     */
    public function add_crumb( $text, $link = '', $type = '' ) {
        $text = wp_strip_all_tags( $text );

        $this->crumbs[] = compact( 'text', 'link', 'type' );

        return $this;
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function get_string( array $args = [] ) {
        $this->build();

        if ( count( $this->crumbs ) <= 1 ) {
            return null;
        }

        $args = wp_parse_args( $args, [
            'before'    => '<div class="breadcrumbs">',
            'after'     => '</div>',
            'separator' => $this->separator,
        ] );

        $item_renderer = $this->item_render_fn
            ?: function ( $item ) {
                return sprintf(
                    '<div class="breadcrumbs-item">%s</div>',
                    ! empty( $item['link'] )
                        ? sprintf( '<a href="%s" class="breadcrumbs-item__link">%s</a>', $item['link'], $item['text'] ?? '' )
                        : sprintf( '<span class="breadcrumbs-item__text">%s</span>', $item['text'] ?? '' )
                );
            };


        $html_parts = [];

        $count = count( $this->crumbs );
        foreach ( $this->crumbs as $idx => $crumb ) {
            if ( $this->clear_last_link &&
                 $idx > 0 &&
                 $idx + 1 == $count
            ) {
                unset( $crumb['link'] );
            }

            if ( is_callable( $item_renderer ) ) {
                $html_parts[] = call_user_func( $item_renderer, $crumb, $idx, $count );
            }
        }

        if ( $html_parts ) {
            return $args['before'] . implode( $args['separator'], $html_parts ) . $args['after'];
        }

        return '';
    }

    /**
     * @return void
     */
    public function build() {
        if ( null !== $this->crumbs ) {
            return;
        }

        $creators = [
            'is_home'              => [ $this, 'create_home_crumbs' ],
            'is_404'               => [ $this, 'create_404_crumbs' ],
            'is_search'            => [ $this, 'create_search_crumbs' ],
            'is_attachment'        => [ $this, 'create_attachment_crumbs' ],
            'is_singular'          => [ $this, 'create_singular_crumbs' ],
            'is_post_type_archive' => [ $this, 'create_post_type_archive_crumbs' ],
            'is_category'          => [ $this, 'create_category_crumbs' ],
            'is_tag'               => [ $this, 'create_tag_crumbs' ],
            'is_tax'               => [ $this, 'create_tax_crumbs' ],
            'is_date'              => [ $this, 'create_date_crumbs' ],
            'is_author'            => [ $this, 'create_author_crumbs' ],
        ];

        /**
         * Allows to add custom generation function
         *
         * @since 1.0
         */
        $creators = apply_filters( 'wpshop_core/breadcrumbs/creators', $creators );

        $this->create_homepage_crumb();

        foreach ( $creators as $conditional => $callable ) {
            if ( is_callable( $conditional ) &&
                 is_callable( $callable ) &&
                 call_user_func( $conditional )
            ) {
                //if ( $callable instanceof \Closure ) {
                //    $callable = $callable->bindTo( $this );
                //}
                call_user_func( $callable );
                break;
            }
        }

        $this->create_paged_crumbs();

        /**
         * Allows to change created items
         *
         * @since 1.0
         */
        $this->crumbs = apply_filters( 'wpshop_core/breadcrumbs/items', $this->crumbs, $this );
    }

    /**
     * @return void
     */
    protected function create_homepage_crumb() {
        $this->add_crumb( $this->home_text, $this->home_link, 'homepage' );
    }

    /**
     * @return void
     */
    protected function create_paged_crumbs() {
        if ( ! $this->show_paged || ! is_paged() ) {
            return;
        }

        $current_page = get_query_var( 'paged', 1 );
        if ( $current_page <= 1 ) {
            return;
        }

        $this->add_crumb(
            sprintf( $this->paged_format, $current_page ),
            $current_page,
            'paged'
        );
    }

    /**
     * @return void
     */
    protected function create_home_crumbs() {
//        $this->add_crumb(
//            single_post_title( '', false ) ?: $this->home_text
//        );
    }

    /**
     * @return void
     */
    protected function create_404_crumbs() {
        $this->add_crumb(
            $this->page_404_text,
            null,
            '404'
        );
    }

    /**
     * @return void
     */
    protected function create_search_crumbs() {
        $this->add_crumb(
            $this->page_search_text,
            null,
            'search'
        );
    }

    /**
     * @return void
     */
    protected function create_attachment_crumbs() {
        global $post;

        $this->create_singular_crumbs( $post->post_parent, get_permalink( $post->post_parent ) );
        $this->add_crumb(
            $this->get_breadcrumb_title( 'post', get_the_ID(), get_the_title() ),
            get_permalink(),
            'attachment'
        );
    }

    /**
     * @param int    $post_id
     * @param string $permalink
     *
     * @return void
     */
    protected function create_singular_crumbs( $post_id = 0, $permalink = '' ) {
        $post      = ! $post_id ? $GLOBALS['post'] : get_post( $post_id );
        $post_type = get_post_type( $post );
        $permalink = $permalink ?: get_permalink( $post );

        $this->create_post_type_archive_crumbs( $post_type );

        if ( ! isset( $post->ID ) || empty( $post->ID ) ) {
            return;
        }

        $this->maybe_create_blog_crumb();

//        $this->maybe_create_primary_term();
//
        if ( isset( $post->post_parent ) && 0 !== $post->post_parent ) {
            $this->create_post_ancestors( $post );
        }

        $this->add_crumb(
            $this->get_breadcrumb_title( 'post', $post->ID, get_the_title( $post ) ),
            $permalink,
            'singular'
        );
    }

    /**
     * @return void
     */
    protected function maybe_create_blog_crumb() {
        if ( ! is_singular( 'post' ) && ! is_category() && ! is_tag() ) {
            return;
        }

        if ( 'page' !== get_option( 'show_on_front' ) ) {
            return;
        }

        $blog_id = get_option( 'page_for_posts' );
        if ( ! $blog_id ) {
            return;
        }

        $this->add_crumb(
            $this->get_breadcrumb_title( 'post', $blog_id, get_the_title( $blog_id ) ),
            get_permalink( $blog_id ),
            'blog'
        );
    }

    protected function maybe_create_primary_term() {
        // todo implement logic of primary term
    }

    /**
     * @param string|null $post_type
     *
     * @return void
     */
    protected function create_post_type_archive_crumbs( $post_type = null ) {
        if ( ! $post_type ) {
            $post_type = $GLOBALS['wp_query']->get( 'post_type' );
        }

        if ( 'post' === $post_type ) {
            return;
        }

        $type_object = get_post_type_object( $post_type );
        if ( ! empty( $type_object->has_archive ) ) {
            $this->add_crumb(
                $type_object->labels->name,
                get_post_type_archive_link( $post_type ),
                'archive'
            );
        }
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    protected function create_post_ancestors( $post ) {
        $ancestors = [];
        if ( isset( $post->ancestors ) ) {
            $ancestors = is_array( $post->ancestors ) ? array_values( $post->ancestors ) : [ $post->ancestors ];
        } elseif ( isset( $post->post_parent ) ) {
            $ancestors = [ $post->post_parent ];
        }

        if ( ! is_array( $ancestors ) ) {
            return;
        }

        $ancestors = array_reverse( $ancestors );
        foreach ( $ancestors as $ancestor ) {
            $this->add_crumb(
                $this->get_breadcrumb_title( 'post', $ancestor, get_the_title( $ancestor ) ),
                get_permalink( $ancestor ),
                'post-ancestor'
            );
        }
    }

    /**
     * @return void
     */
    protected function create_category_crumbs() {
        $this->maybe_create_blog_crumb();

        $term = $GLOBALS['wp_query']->get_queried_object();

        $this->maybe_create_term_ancestors( $term );

        $this->add_crumb(
            $this->get_breadcrumb_title( 'term', $term, $term->name ),
            get_term_link( $term ),
            'category'
        );
    }

    /**
     * @param WP_Term $term Term data object.
     */
    protected function maybe_create_term_ancestors( $term ) {
        if ( ! $term->parent ||
             ! $this->show_term_ancestors ||
             ! is_taxonomy_hierarchical( $term->taxonomy )
        ) {
            return;
        }

        $ancestors = get_ancestors( $term->term_id, $term->taxonomy );
        $ancestors = array_reverse( $ancestors );

        foreach ( $ancestors as $ancestor ) {
            $ancestor = get_term( $ancestor, $term->taxonomy );

            if ( is_wp_error( $ancestor ) || ! $ancestor ) {
                continue;
            }

            $this->add_crumb(
                $this->get_breadcrumb_title( 'term', $ancestor, $ancestor->name ),
                get_term_link( $ancestor ),
                'term-ancestor'
            );
        }
    }

    /**
     * @return void
     */
    protected function create_tag_crumbs() {
        $this->maybe_create_blog_crumb();

        $term = $GLOBALS['wp_query']->get_queried_object();

        $this->add_crumb(
            $this->get_breadcrumb_title( 'term', $term, $term->name ),
            get_term_link( $term )
        );
    }

    /**
     * @return void
     */
    protected function create_tax_crumbs() {
        $term = $GLOBALS['wp_query']->get_queried_object();

        if ( ! $this->hide_tax_name ) {
            $taxonomy = get_taxonomy( $term->taxonomy );
            $this->add_crumb( $taxonomy->labels->name );
        }

        $this->maybe_create_term_ancestors( $term );

        $this->add_crumb(
            $this->get_breadcrumb_title( 'term', $term, $term->name ),
            get_term_link( $term )
        );
    }

    /**
     * @return void
     */
    protected function create_date_crumbs() {
        if ( is_year() || is_month() || is_day() ) {
            $this->add_crumb(
                sprintf( $this->archive_format, get_the_time( 'Y' ) ),
                get_year_link( get_the_time( 'Y' ) )
            );
        }
        if ( is_month() || is_day() ) {
            $this->add_crumb(
                sprintf( $this->archive_format, get_the_time( 'F' ) ),
                get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) );
        }
        if ( is_day() ) {
            $this->add_crumb(
                sprintf( $this->archive_format, get_the_time( 'd' ) )
            );
        }
    }

    /**
     * @return void
     */
    protected function create_author_crumbs() {
        global $author;
        if ( $userdata = get_userdata( $author ) ) {
            $this->add_crumb( $this->get_breadcrumb_title( 'user', $userdata->ID, $userdata->display_name ) );
        }
    }

    /**
     * @param string            $object_type
     * @param int|string|object $object_id
     * @param string            $default
     *
     * @return string
     */
    public function get_breadcrumb_title( $object_type, $object_id, $default ) {
        $title = $default;
        if ( 'post' === $object_type ) {
            $post = $this->get_post( $object_id );
            if ( $post && 'auto-draft' !== $post->post_status ) {

                /**
                 * Allows to change title of a post for breadcrumbs
                 *
                 * @since 1.0
                 */
                $title = apply_filters( 'wpshop_core/breadcrumbs/post_title', $default, $post );
            }
        } elseif ( 'term' === $object_type ) {
            $term = $this->get_term( $object_id );
            if ( $term ) {

                /**
                 * Allows to change title of term for breadcrumbs
                 *
                 * @since 1.0
                 */
                $title = apply_filters( 'wpshop_core/breadcrumbs/term_title', $default, $term );
            }
        } elseif ( 'user' === $object_type ) {
            $user = $this->get_user( $object_id );
            if ( $user ) {

                /**
                 * Allows to change title of user for breadcrumbs
                 *
                 * @since 1.0
                 */
                $title = apply_filters( 'wpshop_core/breadcrumbs/user_title', $default, $user );
            }
        }

        return $title;
    }

    /**
     * @param int|WP_Post $post
     *
     * @return WP_Post|null
     */
    protected function get_post( $post = 0 ) {
        if ( is_object( $post ) && isset( $post->ID ) ) {
            return $post;
        }

        $post = absint( $post );
        if ( $post >= 0 ) {
            return get_post( $post );
        }

        return null;
    }


    /**
     * @param int|string|WP_Term $term slug or id
     * @param string|null        $taxonomy
     *
     * @return WP_Term|null
     */
    protected function get_term( $term, $taxonomy = null ) {
        if ( is_string( $term ) ) {
            $term = get_term_by( 'slug', $term, $taxonomy );
        } elseif ( is_int( $term ) && 0 === absint( $term ) ) {
            $term = $GLOBALS['wp_query']->get_queried_object();
        }

        if ( is_object( $term ) && isset( $term->term_id ) ) {
            return $term;
        }

        return null;
    }

    /**
     * @param int|WP_User $user_id
     *
     * @return WP_User|null
     */
    protected function get_user( $user_id = 0 ) {
        if ( is_int( $user_id ) && 0 === absint( $user_id ) ) {
            $user_id = $GLOBALS['wp_query']->get_queried_object();
        }
        if ( is_object( $user_id ) && isset( $user_id->ID ) ) {
            $user_id = $user_id->ID;
        }
        if ( empty( $user_id ) ) {
            return null;
        }

        if ( $user = get_user_by( 'id', $user_id ) ) {
            return $user;
        }

        return null;
    }
}
