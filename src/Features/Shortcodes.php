<?php

namespace WPShop\WPCommunity\Features;

class Shortcodes {

    /**
     * @return void
     */
    public function init() {
        $list = [
            'post_link' => [ $this, '_post_link' ],
            'year'      => [ $this, '_year' ],
        ];

        foreach ( $list as $shortcode => $callback ) {
            if ( ! shortcode_exists( $shortcode ) ) {
                add_shortcode( $shortcode, $callback );
            }
        }

        $options = new class extends \Wpshop\Core\ThemeOptions {
            public function __construct() {
                $this->text_domain = 'wpcommunity';
                $this->theme_slug  = 'wpcommunity';
            }
        };

        $core_sitemap      = new \Wpshop\Core\Sitemap( $options );
        $core_contact_form = new \Wpshop\Core\ContactForm( $options );

        $this->init_contact_form( $core_contact_form );

        $core_shortcodes = new \Wpshop\Core\Shortcodes( $options );

        $core_shortcode_list = [
            'current_year',
            'previous_year',
            'button',
            'spoiler',
            'mask_link',
            //'social_profiles',
            //'helper',
        ];

        foreach ( $core_shortcode_list as $shortcode ) {
            $core_shortcodes->init_shortcode( $shortcode );
        }

        /**
         * Allows you to control the operation of classes from core
         *
         * [ru] Позволяет влиять на работу классов из core
         *
         * @since 1.1
         */
        do_action(
            'wpcommunity/shortcodes/init_core',
            compact( 'core_sitemap', 'core_contact_form', 'core_shortcodes' )
        );
    }

    /**
     * @param \Wpshop\Core\ContactForm $contact_form
     *
     * @return void
     */
    protected function init_contact_form( $contact_form ) {
        $fields = [
            [
                'name'        => 'contact-name',
                'placeholder' => __( 'Your name', 'wpcommunity' ),
                'required'    => 'required',
            ],
            [
                'name'        => 'contact-email',
                'type'        => 'email',
                'placeholder' => __( 'Your e-mail', 'wpcommunity' ),
                'required'    => 'required',
            ],
            [
                'name'        => 'contact-subject',
                'placeholder' => __( 'Your subject', 'wpcommunity' ),
            ],
            [
                'tag'         => 'textarea',
                'name'        => 'contact-message',
                'placeholder' => __( 'Message', 'wpcommunity' ),
                'required'    => 'required',
            ],
        ];

        /**
         * Allows you to customize contact form fields
         *
         * [ru] Позволяет изменить поля контактной формы
         *
         * @since 1.1
         */
        $fields = apply_filters( 'wpcommunity/contact_form/fields', $fields );

        $contact_form->create_fields( $fields );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _post_link( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'id' => '',
        ], $atts, $shortcode );

        $post = get_post( $atts['id'] );

        if ( ! $post ) {
            return '';
        }

        $text = $content ?: get_the_title( $post );

        return sprintf( '<a href="%s">%s</a>', get_permalink( $post ), $text );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     * @throws \Exception
     */
    public function _year( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'format' => 'Y',
        ], $atts, $shortcode );

        $datetime = new \DateTime( 'now', wp_timezone() );

        return wp_date( $atts['format'], $datetime->getTimestamp() );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return void
     */
    public function _htmlsitemap( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'post_types'            => '',
            'taxonomies'            => '',
            'taxonomies_show_posts' => false,
        ], $atts );
    }
}
