<?php
/**
 * WPCommunity functions and definitions
 *
 * @link    https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WPCommunity
 */


use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\get_the_attributes;

defined( 'WPINC' ) || die;

if ( ! defined( '_S_VERSION' ) ) {
    // Replace the version number of the theme on each release.
    define( '_S_VERSION', '1.0.0' );
}

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/core/constants.php';
require __DIR__ . '/core/init.php';
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function wpcommunity_setup() {
    /*
        * Make theme available for translation.
        * Translations can be filed in the /languages/ directory.
        * If you're building a theme based on WPCommunity, use a find and replace
        * to change 'wpcommunity' to the name of your theme in all the template files.
        */
    load_theme_textdomain( 'wpcommunity', get_template_directory() . '/languages' );

    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    /*
        * Let WordPress manage the document title.
        * By adding theme support, we declare that this theme does not use a
        * hard-coded <title> tag in the document head, and expect WordPress to
        * provide it for us.
        */
    add_theme_support( 'title-tag' );

    /*
        * Enable support for Post Thumbnails on posts and pages.
        *
        * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
        */
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 1024, 576, true );

    add_theme_support( 'post-formats', [ 'video', 'image', 'gallery', 'audio', 'link', 'quote', 'status', 'aside', 'chat' ] );

    // This theme uses wp_nav_menu() in one location.
    // todo: move to dedicated class
    register_nav_menus(
        [
            'primary-menu'   => __( 'Primary Menu', 'wpcommunity' ),
            'sidebar-bottom' => __( 'Sidebar Bottom', 'wpcommunity' ),
        ]
    );

    /*
        * Switch default core markup for search form, comment form, and comments
        * to output valid HTML5.
        */
    add_theme_support(
        'html5',
        [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ]
    );

    // Set up the WordPress core custom background feature.
//    add_theme_support(
//        'custom-background',
//        apply_filters(
//            'wpcommunity_custom_background_args',
//            [
//                'default-color' => 'ffffff',
//                'default-image' => '',
//            ]
//        )
//    );

    // Add theme support for selective refresh for widgets.
    add_theme_support( 'customize-selective-refresh-widgets' );

    /**
     * Add support for core custom logo.
     *
     * @link https://codex.wordpress.org/Theme_Logo
     */
    add_theme_support(
        'custom-logo',
        [
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        ]
    );

    add_theme_support( 'rank-math-breadcrumbs' );

    // стили для блочного редактора в админке
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/admin/css/style-editor.min.css' );
}

add_action( 'after_setup_theme', 'wpcommunity_setup' );



/**
 * @param WP_Query $query
 *
 * @return WP_Query
 */
function wp_community_search_filter( $query ) {
    if ( ! is_admin() ) {
        if ( $query->is_search ) {
            $query->set( 'post_type', 'post' );
        }
    }

    return $query;
}

add_filter( 'pre_get_posts', 'wp_community_search_filter' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function wpcommunity_widgets_init() {
    register_widget( \WPShop\WPCommunity\Widgets\MenuWidget::class );
    register_widget( \WPShop\WPCommunity\Widgets\DarkModeSwitcherWidget::class );
    register_widget( \WPShop\WPCommunity\Widgets\CommentWidget::class );

    register_sidebar( [
        'name'          => esc_html__( 'Sidebar Top', 'wpcommunity' ),
        'id'            => 'sidebar-top',
        'description'   => esc_html__( 'Add widgets here.', 'wpcommunity' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="widget-title">',
        'after_title'   => '</div>',
    ] );
    register_sidebar( [
        'name'          => esc_html__( 'Sidebar Bottom', 'wpcommunity' ),
        'id'            => 'sidebar-bottom',
        'description'   => esc_html__( 'Add widgets here.', 'wpcommunity' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="widget-title">',
        'after_title'   => '</div>',
    ] );

    register_sidebar(
        [
            'name'          => esc_html__( 'Sidebar 2', 'wpcommunity' ),
            'id'            => 'sidebar-2',
            'description'   => esc_html__( 'Add widgets here.', 'wpcommunity' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="widget-title">',
            'after_title'   => '</div>',
        ]
    );
}

add_action( 'widgets_init', 'wpcommunity_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function wpcommunity_scripts() {
//	wp_enqueue_style( 'wpcommunity-style', get_stylesheet_uri(), array(), _S_VERSION );
//	wp_style_add_data( 'wpcommunity-style', 'rtl', 'replace' );

    wp_enqueue_style(
        'wpcommunity-style',
        get_template_directory_uri() . '/assets/public/css/style.min.css',
        [],
        _S_VERSION
    );

    wp_enqueue_script(
        'wpcommunity-scripts',
        get_template_directory_uri() . '/assets/public/js/scripts.min.js',
        [ 'jquery' ],
        _S_VERSION,
        true
    );

    wp_localize_script( 'wpcommunity-scripts', 'wpsc_globals', [
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wpcommunity-nonce' ),
        'i18n'  => [
            'saved'   => __( 'Saved', 'wpcommunity' ),
            'confirm' => __( 'Are you sure?', 'wpcommunity' ),
        ],
    ] );

    wp_enqueue_script( 'wpcommunity-navigation', get_template_directory_uri() . '/js/navigation.js', [], _S_VERSION, true );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

//add_action( 'wp_enqueue_scripts', 'wpcommunity_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';


require_once get_template_directory() . '/inc/class-walker-comment.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
    require get_template_directory() . '/inc/jetpack.php';
}


/**
 * @param $args
 *
 * @return void
 */
function wpcommunity_the_category( $args = [] ) {

    // Args prefixed with an underscore are reserved for internal use.
    $args = wp_parse_args( $args, [
        'post'      => '',
        'classes'   => '',
        'micro'     => true,
        'micro_out' => ' itemprop="articleSection"',
        'link'      => true,
    ] );

    if ( is_array( $args ) ) {
        $post = $args['post'];
    } else {
        $post = null;
    }

    if ( ! $post = get_post( $post ) ) {
        return;
    }

    $classes_out = '';
    if ( ! empty( $args['classes'] ) ) {
        $classes_out = ' class="' . $args['classes'] . '"';
    }

    $categories = get_the_category( $post->ID );

    $links = [];
    foreach ( $categories as $category ) {
        $url = get_category_link( $category->term_id );

        $attributes = [
            'href'    => $url,
            'classes' => 'article-category-link',
        ];

        $links[] = '<a ' . get_the_attributes( 'a.article-category-link', $attributes ) . '>' . esc_html( get_cat_name( $category->term_id ) ) . '</a>';

        // todo можно здесь завершить, чтобы оставить одну рубрику
    }

    echo implode( ', ', $links );

//	if ( class_exists( '\WPSEO_Primary_Term' ) ) {
//		$primary_cat = new \WPSEO_Primary_Term( 'category', $post->ID );
//		$primary_cat = $primary_cat->get_primary_term();
//		if ( $primary_cat ) {
//			$cat_id = $primary_cat;
//		}
//	}

}


// удаляем префикс в архивах в тайтле
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );


// wrap comment fields in div
add_action( 'comment_form_before_fields', function () {
    echo '<div class="comment-fields">';
} );

add_action( 'comment_form_after_fields', function () {
    echo '</div><!--.comment-fields-->';
} );


// добавляем отрывок для страниц
add_post_type_support( 'page', 'excerpt' );


new \WPShop\WPCommunity\Metaboxes\MetaboxMembership();


//$karma = \WPShop\WPCommunity\theme_container()->get( \WPShop\WPCommunity\Karma::class );
//$karma->reset_karma( 1 );


//add_filter( 'comments_pre_query', 'wp_kama_comments_pre_query_filter', 10, 2 );
//function wp_kama_comments_pre_query_filter( $comment_data, $query ){
//
//
//	// filter...
//	return $comment_data;
//}

/**
 * @param string $login_url
 * @param string $redirect
 * @param bool   $force_reauth
 *
 * @return false|string
 */
function wpcommunity_login_url( $login_url, $redirect, $force_reauth ) {
    if ( $profile_page_id = get_setting( 'page.profile' ) ) {
        $login_url = get_permalink( $profile_page_id );
        if ( ! empty( $redirect ) ) {
            $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
        }
    }

    return $login_url;
}

add_filter( 'login_url', 'wpcommunity_login_url', 10, 4 );

add_shortcode( 'hubr_gallery', function( $atts ) {
    $atts      = shortcode_atts( [ 'ids' => '', 'video' => '' ], $atts );
    $ids       = array_values( array_filter( array_map( 'intval', explode( ',', $atts['ids'] ) ) ) );
    $video_url = esc_url( $atts['video'] );

    // Build slides list: video first (if any), then images — skip generic_ (AI-generated)
    $slides = [];
    if ( $video_url ) {
        $slides[] = [ 'type' => 'video', 'url' => $video_url ];
    }
    foreach ( $ids as $id ) {
        $file = get_attached_file( $id );
        if ( $file && str_starts_with( basename( $file ), 'generic_' ) ) continue;
        $slides[] = [ 'type' => 'image', 'id' => $id ];
    }

    if ( empty( $slides ) ) return '';

    ob_start();

    // Single image, no video — simple display
    if ( count( $slides ) === 1 && $slides[0]['type'] === 'image' ) {
        echo '<div class="post-card__image">';
        echo wp_get_attachment_image( $slides[0]['id'], 'large', false, [ 'class' => 'post-gallery__img' ] );
        echo '</div>';
        return ob_get_clean();
    }

    $multi = count( $slides ) > 1;
    echo '<div class="post-gallery">';
    echo '<div class="post-gallery__track">';
    foreach ( $slides as $slide ) {
        echo '<div class="post-gallery__slide">';
        if ( $slide['type'] === 'video' ) {
            echo '<video class="post-gallery__img" controls playsinline preload="metadata" style="width:100%;max-height:400px;">';
            echo '<source src="' . esc_url( $slide['url'] ) . '" type="video/mp4">';
            echo '</video>';
        } else {
            echo wp_get_attachment_image( $slide['id'], 'large', false, [ 'class' => 'post-gallery__img' ] );
        }
        echo '</div>';
    }
    echo '</div>';
    if ( $multi ) {
        echo '<button class="post-gallery__btn post-gallery__btn--prev" aria-label="Назад">&#8249;</button>';
        echo '<button class="post-gallery__btn post-gallery__btn--next" aria-label="Вперёд">&#8250;</button>';
        echo '<div class="post-gallery__dots">';
        foreach ( $slides as $i => $_ ) {
            echo '<span class="post-gallery__dot' . ( $i === 0 ? ' active' : '' ) . '"></span>';
        }
        echo '</div>';
    }
    echo '</div>';

    return ob_get_clean();
} );


// Custom RSS feed for Dzen — /feed/dzen/
add_feed( 'dzen', function () {
    require get_template_directory() . '/rss-dzen.php';
} );
