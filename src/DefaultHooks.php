<?php

namespace WPShop\WPCommunity;

use WPShop\WPCommunity\Customizer\Customizer;

class DefaultHooks {

    /**
     * @var Customizer
     */
    protected $customizer;

    /**
     * @param Customizer $customizer
     */
    public function __construct( Customizer $customizer ) {
        $this->customizer = $customizer;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_head', [ $this, '_insert_code_before_head_closed' ], 1000 );
        add_action( 'wp_footer', [ $this, '_insert_code_before_body_closed' ], 1000 );

        add_filter( 'wpcommunity/user/attachment_blog_id', [ $this, '_set_default_attachment_blog_id' ] );

        add_filter( 'wpcommunity/template_functions/excerpt_length', [ $this, '_set_excerpt_length' ] );
        //add_filter( 'wpcommunity/post_card/show_full_text', [ $this, '_set_post_card_user_full_text' ] );

        add_filter( 'wpcommunity/recurring_payment/subscription_expire_dates', [
            $this,
            '_add_fake_recurring_date',
        ], 10, 2 );

        add_filter( 'wpcommunity/views_counter/post_views', [ $this, '_round_views_count' ] );

        add_filter( 'wpcommunity/search/show_form', [ $this, '_show_search_form' ] );

        add_filter( 'wpcommunity/publish_form/topics', [ $this, '_set_available_publish_topics' ] );

        add_action( 'wpcommunity/main/before', [ $this, '_output_homepage_h1' ] );

        add_filter( 'the_date', [ $this, '_fix_the_date' ], 10, 4 );
    }

    /**
     * @return void
     */
    public function _insert_code_before_head_closed() {
        if ( $code = get_setting( 'advanced.code.head' ) ) {
            echo $code;
        }
    }

    /**
     * @return void
     */
    public function _insert_code_before_body_closed() {
        if ( $code = get_setting( 'advanced.code.body' ) ) {
            echo $code;
        }
    }

    /**
     * @param int|null $attachment_blog_id
     *
     * @return int|null
     */
    public function _set_default_attachment_blog_id( $attachment_blog_id ) {
        if ( null === $attachment_blog_id ) {
            $attachment_blog_id = 1;
        }

        return $attachment_blog_id;
    }

    /**
     * @param int $length
     *
     * @return int
     */
    public function _set_excerpt_length( $length ) {
        return (int) $this->customizer->get_option( 'post_card.excerpt_length' );
    }

    /**
     * @return bool
     * @deprecated
     */
    public function _set_post_card_user_full_text() {
        return (bool) $this->customizer->get_option( 'post_card.use_full_text' );
    }

    /**
     * @param array $dates
     * @param int   $user_id
     *
     * @return array
     * @throws \Exception
     */
    public function _add_fake_recurring_date( $dates, $user_id ) {
        if ( ! get_setting( 'payment.recurring.enable_fake_recurring' ) ) {
            return $dates;
        }

        if ( $user_id != get_current_user_id() ) {
            return $dates;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return $dates;
        }

        if ( empty( $dates ) ) {
            $membership = theme_container()->get( Membership::class );
            if ( $expired_timestamp = $membership->get_expired( $user_id ) ) {
                $datetime = new \DateTimeImmutable( '@' . $expired_timestamp, wp_timezone() );
            } else {
                $datetime = ( new \DateTimeImmutable( 'now', wp_timezone() ) )->modify( '+10 days' );
            }

            $dates[] = [
                0,
                date_i18n(
                    get_option( 'date_format' ),
                    $datetime->getTimestamp() + $datetime->getOffset()
                ),
            ];
        }

        return $dates;
    }

    /**
     * @param int $views_count
     *
     * @return string
     */
    public function _round_views_count( $views_count ) {
        if ( $views_count > 1000000 ) {
            return round( ( $views_count / 1000000 ), 1 ) . __( 'm.', 'wpcommunity' );
        }
        if ( $views_count > 100000 ) {
            return round( ( $views_count / 1000 ) ) . __( 'k.', 'wpcommunity' );
        }
        if ( $views_count > 1000 ) {
            return round( ( $views_count / 1000 ), 1 ) . __( 'k.', 'wpcommunity' );
        }

        return $views_count;
    }

    /**
     * @return bool
     */
    public function _show_search_form() {
        if ( $this->customizer->get_option( 'search.show_form' ) ) {
            if ( wp_is_mobile() ) {
                return (bool) $this->customizer->get_option( 'search.show_form_mobile' );
            }

            return true;
        }

        return false;
    }

    /**
     * @param \WP_Term[] $terms
     *
     * @return \WP_Term[]
     */
    public function _set_available_publish_topics( $terms ) {
        $excluded_categories = wp_parse_id_list( get_setting( 'publish.exclude_categories' ) );
        if ( $excluded_categories ) {
            return array_filter( $terms, function ( $term ) use ( $excluded_categories ) {
                return ! in_array( $term->term_id, $excluded_categories );
            } );
        }

        return $terms;
    }

    /**
     * @return void
     */
    public function _output_homepage_h1() {
        if ( ! is_home() ) {
            return;
        }
        $h1 = $this->customizer->get_option( 'homepage_h1' );
        if ( $h1 && ! get_bloginfo( 'name', 'display' ) ) {
            echo '<h1 style="display: none">' . $h1 . '</h1>';
        }
    }

    /**
     * @param string $the_date
     * @param string $format
     * @param string $before
     * @param string $after
     *
     * @return string
     *
     * @see the_date()
     */
    public function _fix_the_date( $the_date, $format, $before, $after ) {
        if ( ! $the_date ) {
            $the_date = $before . get_the_date( $format ) . $after;
        }

        return $the_date;
    }
}
