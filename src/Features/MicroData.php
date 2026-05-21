<?php

namespace WPShop\WPCommunity\Features;

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Data\ElementAttributes\ElementAttributeContextInterface;
use WPShop\WPCommunity\Data\ElementAttributes\PostCardContext;
use WPShop\WPCommunity\Data\ElementAttributes\PostContext;
use WPShop\WPCommunity\Membership;
use function WPShop\WPCommunity\theme_container;

class MicroData {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function init() {
        // https://schema.org/BreadcrumbList
        add_filter( 'wpcommunity/tag/attributes', [ $this, '_prepare_breadcrumbs' ], 10, 2 );
        add_filter( 'wpcommunity/breadcrumbs/item_additional_content', [ $this, '_add_breadcrumbs_position' ], 10, 3 );

        // http://schema.org/Article for single post
        add_filter( 'wpcommunity/tag/attributes', [ $this, '_prepare_article' ], 10, 2 );
        add_filter( 'wp_get_attachment_image_attributes', [ $this, '_add_image_article_props' ] );
        add_action( 'wpcommunity/post/content', [ $this, '_add_article_meta' ], 100 );

        // https://schema.org/BlogPosting in posts list
        add_filter( 'wpcommunity/tag/attributes', [ $this, '_prepare_post_card' ], 10, 2 );
        add_action( 'wpcommunity/post_card/entry', [ $this, '_add_post_card_meta' ], 100 );

        // http://schema.org/SiteNavigationElement
        add_filter( 'wpcommunity/tag/attributes', [ $this, '_prepare_navigation' ], 10, 2 );

        // http://schema.org/WPHeader
        add_filter( 'wpcommunity/tag/attributes', [ $this, '_prepare_header' ], 10, 2 );

    }

    /**
     * @param array  $atts
     * @param string $context
     *
     * @return mixed
     */
    public function _prepare_breadcrumbs( $atts, $context ) {

        /**
         * Breadcrumbs
         */
        switch ( $context ) {
            case 'breadcrumbs':
                $atts['itemtype']  = 'https://schema.org/BreadcrumbList';
                $atts['itemscope'] = true;
                break;
            case 'breadcrumbs-element':
                $atts['itemprop']  = 'itemListElement';
                $atts['itemscope'] = true;
                $atts['itemtype']  = 'https://schema.org/ListItem';
                break;
            case 'breadcrumbs-item':
                $atts['itemprop'] = 'item';
                break;
            case 'breadcrumbs-item-name':
                $atts['itemprop'] = 'name';
                break;
            default:
                break;
        }

        return $atts;
    }

    /**
     * @param string $content
     * @param array  $item
     * @param int    $idx
     *
     * @return string
     */
    public function _add_breadcrumbs_position( $content, $item, $idx ) {
        return '<meta itemprop="position" content="' . ( $idx + 1 ) . '"/>';
    }

    /**
     * @return bool
     */
    protected function is_article_enabled() {
        if ( $this->is_enabled() ) {
            $membership = theme_container()->get( Membership::class );

            return $membership->is_user_post_access();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function is_enabled() {
        return (bool) $this->settings->get_value( 'advanced.enable_microdata' );
    }

    /**
     * @param array  $attributes
     * @param string $context
     *
     * @return array
     */
    public function _prepare_article( $attributes, $context ) {
        if ( ! $this->is_article_enabled() ) {
            return $attributes;
        }

        if ( $context instanceof PostContext ) {
            switch ( $context->get_name() ) {
                case 'main.site-main':
                    //case 'article.post-card':
                    $attributes['itemscope'] = true;
                    $attributes['itemtype']  = 'http://schema.org/Article';
                    break;
                case 'div.post-card__title':
                    $attributes['itemprop'] = 'headline';
                    break;
                case 'div.entry-content':
                    $attributes['itemprop'] = 'articleBody';
                    break;
                default:
                    break;
            }
        }


        return $attributes;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    public function _add_image_article_props( $attributes ) {
        if ( ! $this->is_article_enabled() ) {
            return $attributes;
        }

        if ( doing_action( 'wpcommunity/post/content' ) ) {
            $attributes['itemprop'] = 'image';
        }

        return $attributes;
    }

    /**
     * @return void
     */
    public function _add_article_meta() {
        if ( ! $this->is_article_enabled() ) {
            return;
        }

        $show_author   = apply_filters( 'wpcommunity/microdata/show_author', true );
        $show_date     = apply_filters( 'wpcommunity/microdata/show_date', true );
        $show_modified = apply_filters( 'wpcommunity/microdata/show_modified', true );

        $publisher_phone   = apply_filters( 'wpcommunity/microdata/publisher_telephone', get_bloginfo( 'name' ) );
        $publisher_address = apply_filters( 'wpcommunity/microdata/publisher_address', get_bloginfo( 'url' ) );

        $thumb_url = get_the_post_thumbnail_url();

        $logotype_image = $this->get_logo_image_src();


        /*?>
        <meta itemscope itemprop="mainEntityOfPage" itemtype="https://schema.org/WebPage" itemid="<?php echo esc_attr( get_permalink() ) ?>" content="<?php echo esc_attr( get_the_title() ) ?>">
        <?php */
        ?>
        <meta itemprop="image" content="<?php echo esc_attr( $thumb_url ) ?>">
        <link itemprop="mainEntityOfPage" href="<?php echo esc_attr( get_permalink() ) ?>">
        <?php if ( $show_author ): ?>
            <meta itemprop="author" content="<?php echo esc_attr( get_the_author() ); ?>"/>
        <?php endif ?>
        <?php if ( $show_date ): ?>
            <meta itemprop="datePublished" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"/>
        <?php endif ?>
        <?php if ( $show_modified ): ?>
            <meta itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"/>
        <?php endif ?>

        <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="display: none;">
            <?php if ( $logotype_image ): ?>
                <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                    <img itemprop="url image" src="<?php echo esc_attr( $logotype_image ) ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
                </div>
            <?php endif ?>
            <meta itemprop="name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
            <meta itemprop="telephone" content="<?php echo esc_attr( $publisher_phone ) ?>">
            <meta itemprop="address" content="<?php echo esc_attr( $publisher_address ) ?>">
        </div>
        <?php
    }

    /**
     * @param array                                   $attributes
     * @param string|ElementAttributeContextInterface $context
     *
     * @return array
     */
    public function _prepare_post_card( $attributes, $context ) {
        if ( ! $this->is_article_enabled() ) {
            return $attributes;
        }

        if ( $context instanceof PostCardContext ) {
            switch ( $context->get_name() ) {
                case 'div.post-meta__category':
                case 'a.article-category-link':
                    $attributes['itemprop'] = 'articleSection';
                    break;
                case 'h2.post-card__title':
                    $attributes['itemprop'] = 'name';
                    break;
                case 'h2.post-card__title-link-wrap':
                    $attributes['itemprop'] = 'headline';
                    break;
                case 'div.post-card__content':
                    $attributes['itemprop'] = 'articleBody';
                    break;
                case 'article.post-card':
                    $attributes['itemscope'] = true;
                    $attributes['itemtype']  = 'https://schema.org/BlogPosting';
                    break;
                default:
                    break;
            }
        }

        return $attributes;
    }

    /**
     * @return void
     */
    public function _add_post_card_meta() {
        if ( ! $this->is_article_enabled() ) {
            return;
        }

        $show_author   = apply_filters( 'wpcommunity/microdata/show_author', true );
        $show_date     = apply_filters( 'wpcommunity/microdata/show_date', true );
        $show_modified = apply_filters( 'wpcommunity/microdata/show_modified', true );

        $publisher_phone   = apply_filters( 'wpcommunity/microdata/publisher_telephone', get_bloginfo( 'name' ) );
        $publisher_address = apply_filters( 'wpcommunity/microdata/publisher_address', get_bloginfo( 'url' ) );

        $logotype_image = $this->get_logo_image_src();
        ?>
        <meta itemscope itemprop="mainEntityOfPage" itemtype="https://schema.org/WebPage" itemid="<?php echo esc_attr( get_permalink() ) ?>" content="<?php echo esc_attr( get_the_title() ) ?>">
        <?php if ( $show_author ): ?>
            <meta itemprop="author" content="<?php echo esc_attr( get_the_author() ); ?>"/>
        <?php endif ?>
        <?php if ( $show_date ): ?>
            <meta itemprop="datePublished" content="2023-09-18T12:00:00+03:00">
        <?php endif ?>
        <?php if ( $show_modified ): ?>
            <meta itemprop="dateModified" content="2023-09-18T12:00:00+03:00">
        <?php endif ?>
        <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="display: none;">
            <?php if ( $logotype_image ): ?>
                <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                    <img itemprop="url image" src="<?php echo esc_attr( $logotype_image ) ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
                </div>
            <?php endif ?>
            <meta itemprop="name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
            <meta itemprop="telephone" content="<?php echo esc_attr( $publisher_phone ) ?>">
            <meta itemprop="address" content="<?php echo esc_attr( $publisher_address ) ?>">
        </div>
        <?php
    }

    /**
     * @return array|false
     */
    protected function get_logo_image_src() {
        $custom_logo_id = get_theme_mod( 'custom_logo' );

        //$custom_logo_dark_id = get_theme_mod( 'custom_logo_dark' );

        [ $src ] = wp_get_attachment_image_src( $custom_logo_id );

        return $src;
    }

    /**
     * @param array                                   $attributes
     * @param string|ElementAttributeContextInterface $context
     *
     * @return array
     */
    public function _prepare_header( $attributes, $context ) {
        if ( ! $this->is_enabled() ) {
            return $attributes;
        }

        switch ( $context ) {
            case 'nav.main-navigation':
                $attributes['itemscope'] = true;
                $attributes['itemtype']  = 'http://schema.org/WPHeader';
                break;
            default:
                break;
        }

        return $attributes;
    }

    /**
     * @param array                                   $attributes
     * @param string|ElementAttributeContextInterface $context
     *
     * @return array
     */
    public function _prepare_navigation( $attributes, $context ) {
        if ( ! $this->is_enabled() ) {
            return $attributes;
        }

        switch ( $context ) {
            case 'nav.main-navigation':
                $attributes['itemscope'] = true;
                $attributes['itemtype']  = 'http://schema.org/SiteNavigationElement';
                break;
            default:
                break;
        }

        return $attributes;
    }
}
