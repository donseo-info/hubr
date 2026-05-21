<?php

namespace WPShop\WPCommunity;

use Detection\MobileDetect;
use Psr\Container\ContainerInterface;
use WP_Query;
use WPShop\Container\Container;
use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Customizer\CustomizerHelper;
use WPShop\WPCommunity\Data\ElementAttributes\ElementAttributeContextInterface;
use WPShop\WPCommunity\Database\Subs;
use WPShop\WPCommunity\Layout\Sidebar;

/**
 * @return Container|ContainerInterface
 */
function theme_container() {
    static $container;
    if ( ! $container ) {
        $config    = require __DIR__ . '/../core/config.php';
        $init      = require __DIR__ . '/../core/container.php';
        $container = new Container( $init( $config ) );
    }

    return $container;
}

/**
 * @return string[]
 */
function get_device_edges() {
    return [
        'tablet'  => '768',
        'desktop' => '1024',
    ];
}

/**
 * @return bool
 */
function is_desktop() {
    return ! is_tablet() && ! is_mobile();
}

/**
 * @return bool
 */
function is_tablet() {
    return theme_container()->get( MobileDetect::class )->isTablet();
}

/**
 * @return bool
 */
function is_mobile() {
    return theme_container()->get( MobileDetect::class )->isMobile();
}

/**
 * @param string|ElementAttributeContextInterface $context
 * @param array                                   $attributes
 * @param array                                   $args
 *
 * @return void
 */
function the_attributes( $context, array $attributes = [], array $args = [] ) {
    echo get_the_attributes( $context, $attributes, $args );
}

/**
 * @param string|ElementAttributeContextInterface $context
 * @param array                                   $attributes
 * @param array                                   $args
 *
 * @return string
 */
function get_the_attributes( $context, array $attributes = [], array $args = [] ) {
    $classes = array_key_exists( 'classes', $attributes ) ? $attributes['classes'] : [];
    if ( is_string( $classes ) ) {
        $classes = explode( ' ', trim( $classes ) );
        $classes = array_filter( $classes );

        $attributes['classes'] = $classes;
    }

    /**
     * Allows to change tag attributes
     *
     * @since 1.0
     */
    $attributes = apply_filters( 'wpcommunity/tag/attributes', $attributes, $context, $args );

    $parts = [];
    foreach ( $attributes as $key => $value ) {

        if ( $key === 'classes' ) {
            $key   = 'class';
            $value = is_array( $value ) ? implode( ' ', $value ) : $value;
        }

        if ( is_numeric( $key ) ) {
            $parts[] = esc_html( $value ); // for case of property
        } else if ( $value === true ) {
            $parts[] = esc_html( $key ); // for case of property
        } else {
            $parts[ $key ] = esc_html( $key ) . '="' . esc_attr( $value ) . '"';
        }
    }

    return implode( ' ', $parts );
}

/**
 * @param string $name
 * @param bool   $null_default get null if value is same as default
 *
 * @return mixed|null
 */
function get_setting( $name, $null_default = false ) {
    return theme_container()->get( Settings::class )->get_value( $name, $null_default );
}

/**
 * @return string
 */
function get_settings_url() {
    return admin_url( 'options-general.php?page=' . THEME_SETTINGS_PAGE );
}

/**
 * @param string $email
 * @param bool   $generate_suffix
 *
 * @return string
 */
function generate_username( $email = '', $generate_suffix = false ) {
    $username = 'user';
    if ( $email && false !== mb_strpos( $email, '@', 1, 'UTF-8' ) ) {
        [ $username, $domain ] = explode( '@', $email, 2 );
    }

    if ( $generate_suffix ) {
        $username .= '_' . generate_random_string( 8 );
    }

    return $username;
}

/**
 * @param int $length
 *
 * @return string
 */
function generate_random_string( $length = 16 ) {
    $chars       = '0123456789abcdefghijklmnopqrstuvwxyz';
    $char_length = strlen( $chars );
    $result      = '';
    for ( $i = 0 ; $i < $length ; $i ++ ) {
        $result .= $chars[ mt_rand( 0, $char_length - 1 ) ];
    }

    return $result;
}

/**
 * Output follow button
 *
 * @param string    $type          one of User::FOLLOW_TYPE_* const
 * @param int       $target
 * @param bool|null $is_subscribed will be checked from database if null
 * @param int|null  $user_id       will be used from current session if null
 * @param string    $template_part_name
 *
 * @return void
 * @since 1.0.0
 * @see   User::FOLLOW_TYPE_CATEGORY
 * @see   User::FOLLOW_TYPE_TAG
 * @see   User::FOLLOW_TYPE_USER
 */
function the_follow_button( $type, $target, $is_subscribed = null, $user_id = null, $template_part_name = '' ) {
    $is_logged_in = is_user_logged_in();

    if ( ! $is_logged_in ) {
        get_template_part( 'template-parts/elements/follow-button', $template_part_name, compact( 'is_logged_in' ) );

        return;
    }


    if ( null === $is_subscribed ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }
        $is_subscribed = (bool) theme_container()->get( Subs::class )->get_row( $user_id, $type, $target );
    }

    get_template_part(
        'template-parts/elements/follow-button',
        $template_part_name,
        compact( 'type', 'target', 'is_subscribed', 'is_logged_in' )
    );
}

/**
 * @param string $currency
 *
 * @return string
 */
function get_currency_beauty( $currency = null ) {
    if ( $currency == 'RUB' ) {
        $result = '₽';
    } elseif ( $currency == 'USD' ) {
        $result = '$';
    } elseif ( $currency == 'EUR' ) {
        $result = '€';
    } else {
        $result = $currency;
    }

    /**
     * @hook Allows to change symbol of currency
     */
    $result = apply_filters( 'wpcommunity/functions/get_currency_beauty', $result, $currency );

    return $result;
}

/**
 * @param float  $price
 * @param string $currency
 *
 * @return string
 */
function format_price( $price, $currency ) {
    return apply_filters(
        'wpcommunity/functions/format_price',
        $price . '&nbsp;' . get_currency_beauty( $currency ),
        [ $price, $currency ]
    );
}

/**
 * @param int|\WP_User $user_id
 *
 * @return string|null
 */
function get_user_name( $user_id = null ) {
    if ( null === $user_id ) {
        if ( ! is_user_logged_in() ) {
            return null;
        }
        $user_id = wp_get_current_user();
    }

    if ( $user_id instanceof \WP_User ) {
        $user = $user_id;
    } else {
        $user = get_user_by( 'ID', $user_id );
    }

    if ( ! $user ) {
        return null;
    }

    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name  = get_user_meta( $user->ID, 'last_name', true );


    $name = $first_name;
    // если имя есть и есть фамилия -- ставим пробел между ними
    if ( ! empty( $name ) && ! empty( $last_name ) ) {
        $name .= ' ';
    }
    $name .= $last_name;

    // если имя пустое -- выводим логин
    if ( empty( $name ) ) {
        $name = $user->user_login;
    }

    return $name;
}

/**
 * @param string $name
 *
 * @return \WP_Post|null
 */
function get_page_by_name( $name ) {
    $query = new WP_Query( [
        'post_type'      => 'page',
        'name'           => $name,
        'posts_per_page' => 1,
    ] );

    return $query->have_posts() ? reset( $query->posts ) : null;
}

/**
 * @return bool
 */
function is_login_page() {
    return in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ] );
}

/**
 * @param string $type
 *
 * @return string
 */
function doc_link( $type ) {
    if ( function_exists( 'Wpbox\Core\doc_link' ) ) {
        return \WPBox\Core\doc_link( $type );
    }

    $args = func_get_args();
    array_unshift( $args, 'WPBox\WpboxTheme\doc_link' );

    return call_user_func_array( 'apply_filters', $args );
}

/**
 * @return string
 */
function get_share_buttons() {
    $social = theme_container()->get( Social::class );

    $enabled_services = [];
    foreach ( CustomizerHelper::get_json_option_values( 'social_share.services' ) as $device => $services ) {
        foreach ( $services as $service ) {
            if ( $service['enabled'] ?? false ) {
                $enabled_services[ $service['name'] ] = true;
            }
        }
    }

    $buttons = array_keys( $social->get_share_services() );
    $buttons = array_filter( $buttons, function ( $button ) use ( $enabled_services ) {
        return array_key_exists( $button, $enabled_services );
    } );

    return $social->get_share_buttons( $buttons );
}

/**
 * @param string $string
 * @param int    $length
 * @param string $del
 * @param string $ellipses
 *
 * @return mixed|string
 */
function substring_by_word( $string, $length = 200, $del = ' ', $ellipses = '' ) {

    if ( $length < 100 ) {
        $offset = ceil( $length * 0.2 );
    } else {
        $offset = ceil( $length * 0.1 );
    }

    if ( mb_strlen( $string ) > $length ) {
        $search = mb_strpos( $string, $del, $length );
        if ( $search ) {
            $substr = mb_substr( $string, 0, $search );

            // ищем конец предложения
            $substr_with_offset = mb_substr( $string, 0, $search + $offset );

            $symbols = [ '.', '!', '?', ';' ];
            foreach ( $symbols as $symbol ) {
                $search_end = mb_strpos( $substr_with_offset, $symbol, $length - $offset );
                if ( $search_end ) {
                    $substr = mb_substr( $string, 0, $search_end + 1 );
                }
            }

            $substr = rtrim( $substr, ',:' );

            return $substr . $ellipses;
        }
    }

    return $string;
}

/**
 * @param callable $callback
 *
 * @return false|string
 */
function _ob_get_content( $callback ) {
    $ob_level = ob_get_level();
    try {
        ob_start();
        ob_implicit_flush( false );

        $args = func_get_args();
        call_user_func_array( $callback, array_slice( $args, 1 ) );

        return ob_get_clean();
    } catch ( \Exception $e ) {
        while ( ob_get_level() > $ob_level ) {
            if ( ! @ob_end_clean() ) {
                ob_clean();
            }
        }
        throw $e;
    }
}

function the_main_loop() {
    while ( have_posts() ) {
        the_post();
        get_template_part( 'template-parts/post-card' );
    }
    wp_reset_postdata();
}

/**
 * @param string $name
 *
 * @return bool
 * @deprecated
 */
function is_sidebar_hidden( $name ) {
    return theme_container()->get( Sidebar::class )->is_sidebar_hidden( $name );
}

/**
 * @param string $element
 * @param int    $post_id
 *
 * @return bool
 */
function is_post_element_hidden( $element, $post_id = null ) {
    if ( is_singular( [ 'post', 'page' ] ) ) {
        if ( null === $post_id ) {
            $post_id = get_the_ID();
        }

        $hide_elements = (array) get_post_meta( $post_id, 'hide_elements', true );

        return in_array( $element, $hide_elements );
    }

    return false;
}

/**
 * Transforms link from markdown to html
 * <pre>
 * [link text](http://example.com/)
 * </pre>
 * <pre>
 * <a href="http://example.com/" target="_blank" rel="noopener">link text</a>
 * </pre>
 *
 * @param string $text
 *
 * @return string
 */
function transform_markdown_link( $text, $classes = '' ) {
    return preg_replace(
        '/\[(.*?)\]\((.*?)\)/',
        '<a href="$2" target="_blank" rel="noopener" class="' . $classes . '">$1</a>',
        $text
    );
}

/**
 * @return string|null
 */
function get_privacy_policy_text() {
    $privacy_url = get_privacy_policy_url();

    if ( ! $privacy_url ) {
        return null;
    }

    global $shortcode_tags;
    $shortcode_tags_stash = $shortcode_tags;
    $shortcode_tags       = [];

    add_shortcode( 'privacy_policy_link', function ( $atts, $content, $shortcode ) use ( $privacy_url ) {
        $atts = shortcode_atts( [
            'target' => '_blank',
        ], $atts, $shortcode );

        return '<a href="' . esc_attr( $privacy_url ) . '" target="' . esc_attr( $atts['target'] ) . '">' . esc_html( $content ) . '</a>';
    } );

    $result = do_shortcode( get_setting( 'site.privacy_policy.link_text' ) );

    $shortcode_tags = $shortcode_tags_stash;

    /**
     * @since 1.3.0
     */
    $result = apply_filters( 'wpcommunity/functions/get_privacy_policy_text', $result );

    return $result;
}

/**
 * @return string
 */
function get_payment_acceptance_text() {
    global $shortcode_tags;
    $shortcode_tags_stash = $shortcode_tags;
    $shortcode_tags       = [];

    $privacy_url = get_privacy_policy_url();

    add_shortcode( 'privacy_policy_link', function ( $atts, $content, $shortcode ) use ( $privacy_url ) {
        if ( ! $privacy_url ) {
            return '';
        }
        $atts = shortcode_atts( [
            'target' => '_blank',
        ], $atts, $shortcode );

        return '<a href="' . esc_attr( $privacy_url ) . '" target="' . esc_attr( $atts['target'] ) . '">' . esc_html( $content ) . '</a>';
    } );

    add_shortcode( 'offer_link', function ( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'target' => '_blank',
        ], $atts, $shortcode );

        $url = get_permalink( get_setting( 'page.offer' ) );

        return '<a href="' . esc_attr( $url ) . '" target="' . esc_attr( $atts['target'] ) . '">' . esc_html( $content ) . '</a>';
    } );


    $result = do_shortcode( get_setting( 'payment.general.acceptance_text' ) );

    $shortcode_tags = $shortcode_tags_stash;

    /**
     * @since 1.3.0
     */
    $result = apply_filters( 'wpcommunity/functions/get_payment_acceptance_text', $result );

    return $result;
}
