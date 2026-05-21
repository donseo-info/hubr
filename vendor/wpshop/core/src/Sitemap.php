<?php

namespace Wpshop\Core;

/**
 * Class Sitemap
 *
 * 2022-02-04       Add filter 'wpshop_sitemap_pages_header' for header pages
 * 2021-07-09       Fix get_the_title on pages
 * 2021-06-02       Add attr taxonomies and taxonomies_show_posts
 * 2020-06-04       Add filter 'wpshop_sitemap_posts_exclude' for posts exclude
 *                  Add filter 'wpshop_sitemap_category_exclude' for categories exclude
 *                  Add filter 'wpshop_sitemap_pages_exclude' for pages exclude
 * 2019-10-18       Add attribute post_types to show custom post_types
 */
class Sitemap {

    protected $options;

    /**
     * Contact_Form constructor.
     *
     * @param ThemeOptions $options
     */
    public function __construct( ThemeOptions $options ) {
        $this->options = $options;
        add_shortcode( 'htmlsitemap', array( $this, 'sitemap_shortcode' ) );
    }

    public function sitemap_shortcode( $atts , $content = null ) {
        $atts = shortcode_atts( array(
            'post_types' => '',
            'taxonomies' => '',
            'taxonomies_show_posts' => false,
        ), $atts );

        global $post;

        $out = '<div class="sitemap-list">' . PHP_EOL;

        $out .= '<ul>' . PHP_EOL;
        $out .= $this->get_posts_list();
        $out .= '</ul>' . PHP_EOL;

        if ( ! empty( $atts['post_types'] ) ) {
            $out .= '<ul>' . PHP_EOL;
            $out .= $this->get_posts_by_post_type( $atts['post_types'] );
            $out .= '</ul>' . PHP_EOL;
        }

        if ( ! empty( $atts['taxonomies'] ) ) {


            $taxonomies = explode(',', $atts['taxonomies']);
            foreach ( $taxonomies as $taxonomy ) {
                $taxonomy = trim( $taxonomy );

                if ( ! taxonomy_exists( $taxonomy ) ) continue;

                $taxonomy_info = get_taxonomy( $taxonomy );
                if ( ! empty( $taxonomy_info ) ) {
                    $out .= '<h2>' . $taxonomy_info->label . '</h2>';
                }

                $terms = get_terms( [
                    'taxonomy' => $taxonomy,
                    'hide_empty' => true,
                ] );

                if ( ! empty( $terms ) ) {
                    $out .= '<ul>' . PHP_EOL;
                    foreach ( $terms as $term ) {
                        $out .= '<li>';
                        if ( $atts['taxonomies_show_posts'] ) $out .= '<h3>';
                        $out .= '<a href="' . get_term_link( $term->term_id, $taxonomy ) . '">' . $term->name . '</a>';
                        if ( $atts['taxonomies_show_posts'] ) $out .= '</h3>';

                        if ( $atts['taxonomies_show_posts'] ) {
                            $out .= '<ul>' . PHP_EOL;
                            $out .= $this->get_posts_by_taxonomy( $taxonomy, $term->term_id );
                            $out .= '</ul>' . PHP_EOL;
                        }

                        $out .= '</li>';
                    }
                    $out .= '</ul>' . PHP_EOL;
                }

            }


        }

        if ( apply_filters( 'wpshop_sitemap_show_pages', true ) ) :
            $out .= '<h2>' . apply_filters( 'wpshop_sitemap_pages_header', __( 'Pages', $this->options->text_domain ) ) . '</h2>' . PHP_EOL;
            $out .= $this->get_pages_list();
        endif;

        $out .= '</div>';

        return $out;
    }


    public function get_posts_by_taxonomy( $taxonomy, $term ) {
        $out = '';

        $args = [
            'post_type'       => 'any',
            'numberposts'     => 1000,
            'orderby'         => 'post_date',
            'order'           => 'DESC',
            'tax_query'       => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'id',
                    'terms'    => [ $term ],
                ],
            ],
        ];

        $posts = get_posts( $args );

        if ( ! empty( $posts ) ) {
            foreach( $posts as $post ) {
                $out .= '<li><a href="' . get_the_title( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></li>';
            }
        }

        return $out;
    }


    /**
     * @param string|array $post_type
     *
     * @return string
     */
    public function get_posts_by_post_type( $post_type ) {
        $out = '';

        $post_type_obj = get_post_type_object( $post_type );

        $out .= '<li class="sitemap-list__header"><h2>' . $post_type_obj->label . '</h2></li>' . PHP_EOL;
        $out .= '<li class="sitemap-list__block"><ul>' . PHP_EOL;

        $posts = get_posts( array(
            'numberposts'     => 1000,
            'orderby'         => 'post_date',
            'order'           => 'DESC',
            'post_type'       => $post_type,
        ) );
        if ( ! empty( $posts ) ) {
            $posts_exclude = apply_filters( 'wpshop_sitemap_posts_exclude', '' );

            foreach( $posts as $post ) {
                if ( empty( $posts_exclude ) || ( ! empty( $posts_exclude ) && ! in_array( $post->ID, $posts_exclude ) ) ) {
                    $out .= '  <li><a href="' . get_the_permalink( $post->ID ) . '" target="_blank">'. get_the_title( $post->ID ) . '</a></li>' . PHP_EOL;
                }
            }
        }

        $out .= '</ul></li>' . PHP_EOL;
        return $out;
    }


    public function get_posts_list( $category_ID = 0 ) {
        $out = '';

        $next = get_categories( 'orderby=name&order=ASC&parent=' . $category_ID );

        if ( $next ) {
            $category_exclude = apply_filters( 'wpshop_sitemap_category_exclude', '' );

            foreach ( $next as $cat ) {
                if ( empty( $category_exclude ) || ( ! empty( $category_exclude ) && ! in_array( $cat->cat_ID, $category_exclude ) ) ) {
                    $out .= '<li class="sitemap-list__header category-' . $cat->cat_ID . '"><h3><a href="' . get_term_link( $cat->cat_ID ) . '" target="_blank">' . $cat->name . '</a></h3></li>' . PHP_EOL;

                    $out .= '<li class="sitemap-list__block category-' . $cat->cat_ID . '"><ul>' . PHP_EOL;

                    $posts = get_posts( array(
                        'numberposts'     => -1,
                        'category__in'    => array( $cat->cat_ID ),
                        'orderby'         => 'post_date',
                        'order'           => 'DESC',
                        'exclude'         => ''
                    ) );

                    if ( ! empty( $posts ) ) {
                        $posts_exclude = apply_filters( 'wpshop_sitemap_posts_exclude', '' );

                        foreach( $posts as $post ) {
                            if ( empty( $posts_exclude ) || ( ! empty( $posts_exclude ) && ! in_array( $post->ID, $posts_exclude ) ) ) {
                                $out .= '  <li><a href="' . get_the_permalink( $post->ID ) . '" target="_blank">'. get_the_title( $post->ID ) . '</a></li>' . PHP_EOL;
                            }
                        }
                    }

                    $out .= $this->get_posts_list( $cat->cat_ID );

                    $out .= '</ul></li>' . PHP_EOL;
                }
            }
        }

        return $out;

    }


    public function get_pages_list( $page_id = 0 ) {
        global $post;
        $out = '';

        $pages = get_pages( array(
            'exclude'   => array( $post->ID ),
            'parent'    => $page_id,
        ) );

        if ( ! empty( $pages ) ) {
            $pages_exclude = apply_filters( 'wpshop_sitemap_pages_exclude', '' );

            $out .= '<ul>' . PHP_EOL;
            foreach ( $pages as $page ) {
                if ( empty( $pages_exclude ) || ( ! empty( $pages_exclude ) && ! in_array( $page->ID, $pages_exclude ) ) ) {
                    $out .= '<li><a href="' . get_the_permalink( $page->ID ) . '" target="_blank">' . get_the_title( $page->ID ) . '</a>';

                    $subpages = $this->get_pages_list( $page->ID );
                    if ( ! empty( $subpages ) ) $out .= $subpages;

                    $out .= '</li>';
                }
            }
            $out .= '</ul>' . PHP_EOL;
        }

        return $out;
    }

}
