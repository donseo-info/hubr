<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Detection\MobileDetect;
use WPShop\Container\ServiceRegistry;
use Wpshop\Core2\BreadcrumbsBuilder;
use Wpshop\Settings\Maintenance;
use Wpshop\Settings\MaintenanceInterface;
use WPShop\WPCommunity\Achievements;
use WPShop\WPCommunity\Actions\Admin\SaveAdOptionsAction;
use WPShop\WPCommunity\Actions\Admin\SetupAssistantAction;
use WPShop\WPCommunity\Api\CommentsEndpoint;
use WPShop\WPCommunity\Api\PublishEndpoint;
use WPShop\WPCommunity\Actions\AuthAction;
use WPShop\WPCommunity\Actions\AvatarAction;
use WPShop\WPCommunity\Actions\BookmarkAction;
use WPShop\WPCommunity\Actions\CommentAction;
use WPShop\WPCommunity\Actions\FollowAction;
use WPShop\WPCommunity\Actions\InfiniteScrollAction;
use WPShop\WPCommunity\Actions\InviteAction;
use WPShop\WPCommunity\Actions\OrderAction;
use WPShop\WPCommunity\Actions\ProfileAction;
use WPShop\WPCommunity\Actions\PublishAction;
use WPShop\WPCommunity\Actions\SubscribeAction;
use WPShop\WPCommunity\Admin\MenuPage;
use WPShop\WPCommunity\Admin\Notices;
use WPShop\WPCommunity\Admin\SetupAssistant;
use WPShop\WPCommunity\AssetProvider;
use WPShop\WPCommunity\Auth;
use WPShop\WPCommunity\Bookmark;
use WPShop\WPCommunity\Cron;
use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Customizer\CustomizerSettingsBridge;
use WPShop\WPCommunity\DefaultHooks;
use WPShop\WPCommunity\Features\Advertisement;
use WPShop\WPCommunity\Features\Breadcrumbs;
use WPShop\WPCommunity\Features\Feeds;
use WPShop\WPCommunity\Features\FrontendEditor;
use WPShop\WPCommunity\Features\GoogleReCaptcha;
use WPShop\WPCommunity\Features\InfiniteScroll;
use WPShop\WPCommunity\Features\Karma;
use WPShop\WPCommunity\Comments;
use WPShop\WPCommunity\Database\DbInstall;
use WPShop\WPCommunity\Database\Subs;
use WPShop\WPCommunity\DefaultPages;
use WPShop\WPCommunity\Features\MenuIcons;
use WPShop\WPCommunity\Features\MicroData;
use WPShop\WPCommunity\Features\RelatedProducts;
use WPShop\WPCommunity\Features\TableOfContents;
use WPShop\WPCommunity\FrontendPublish;
use WPShop\WPCommunity\Helper;
use WPShop\WPCommunity\Invite;
use WPShop\WPCommunity\Layout\Layout;
use WPShop\WPCommunity\Layout\LoopContext;
use WPShop\WPCommunity\Layout\PostCard;
use WPShop\WPCommunity\Layout\Profile\ProfileTabs;
use WPShop\WPCommunity\Layout\Profile\UserComments;
use WPShop\WPCommunity\Layout\Sidebar;
use WPShop\WPCommunity\Layout\SinglePost;
use WPShop\WPCommunity\Linkify;
use WPShop\WPCommunity\Logger;
use WPShop\WPCommunity\Mail;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Metaboxes\MetaboxPostElements;
use WPShop\WPCommunity\Metaboxes\MetaboxPostSettings;
use WPShop\WPCommunity\Metaboxes\MetaboxSeo;
use WPShop\WPCommunity\Metaboxes\CategorySeo;
use WPShop\WPCommunity\MobileMenu;
use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\OrderStatus;
use WPShop\WPCommunity\PaymentProviders;
use WPShop\WPCommunity\PaymentProviders\Prodamus;
use WPShop\WPCommunity\PaymentProviders\RecurringPayments;
use WPShop\WPCommunity\PaymentProviders\RoboKassa;
use WPShop\WPCommunity\PaymentProviders\YooKassa;
use WPShop\WPCommunity\Post;
use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\PaidSubscriptions;
use WPShop\WPCommunity\Features\Shortcodes;
use WPShop\WPCommunity\Social;
use WPShop\WPCommunity\Support\Clearfy;
use WPShop\WPCommunity\Support\ExpertReview;
use WPShop\WPCommunity\Support\OmniVideo;
use WPShop\WPCommunity\Support\Polylang;
use WPShop\WPCommunity\Support\WPRemark;
use WPShop\WPCommunity\Support\WPStories;
use WPShop\WPCommunity\Telegram\TelegramCron;
use WPShop\WPCommunity\Telegram\TelegramIntegration;
use WPShop\WPCommunity\TemplateFunctions;
use WPShop\WPCommunity\User;
use WPShop\WPCommunity\Features\UserSubscriptions;
use WPShop\WPCommunity\Features\ViewsCounter;
use WPShop\WPCommunity\Vote;

return function ( $config ) {
    global $wpdb;

    $container = new ServiceRegistry( [
        'config'                        => $config,
        Customizer::class               => function () {
            return new Customizer( 'wpcommunity_customize' );
        },
        Settings::class                 => function ( $c ) {
            return new Settings(
                $c[ MaintenanceInterface::class ],
                '_wpcommunity-r',
                '_wpcommunity-settings'
            );
        },
        CustomizerSettingsBridge::class => function () {
            return new CustomizerSettingsBridge(
                '_wpcommunity-settings',
                'wpcommunity_customize'
            );
        },

        Advertisement::class        => function () {
            return new Advertisement();
        },
        AssetProvider::class        => function () {
            return new AssetProvider();
        },
        Auth::class                 => function () {
            return new Auth();
        },
        Membership::class           => function () {
            return new Membership();
        },
        User::class                 => function () {
            return new User();
        },
        UserSubscriptions::class    => function ( $c ) {
            return new UserSubscriptions( $c[ Subs::class ] );
        },
        Invite::class               => function () {
            return new Invite();
        },
        TemplateFunctions::class    => function () {
            return new TemplateFunctions();
        },
        Vote::class                 => function () {
            return new Vote();
        },
        ViewsCounter::class         => function () {
            return new ViewsCounter();
        },
        Bookmark::class             => function () {
            return new Bookmark();
        },
        Post::class                 => function () {
            return new Post();
        },
        Comments::class             => function () {
            return new Comments();
        },
        FrontendEditor::class       => function () {
            return new FrontendEditor();
        },
        FrontendPublish::class      => function () {
            return new FrontendPublish();
        },
        Karma::class                => function () {
            return new Karma();
        },
        Achievements::class         => function () {
            return new Achievements();
        },
        PaidSubscriptions::class    => function () {
            return new PaidSubscriptions();
        },
        PaymentProviders::class     => function () {
            return new PaymentProviders();
        },
        Mail::class                 => function () {
            return new Mail();
        },
        MobileDetect::class         => function () {
            return new MobileDetect();
        },
        Orders::class               => function ( $c ) {
            return new Orders( $c[ OrderStatus::class ] );
        },
        Linkify::class              => function () {
            return new Linkify();
        },
        Helper::class               => function () {
            return new Helper();
        },
        MaintenanceInterface::class => function ( $c ) {
            return new Maintenance(
                $c['config'],
                'theme',
                THEME_SLUG,
                get_template_directory(),
                'wpcommunity'
            );
        },
        MenuPage::class             => function () {
            return new MenuPage();
        },
        YooKassa::class             => function ( $c ) {
            /** @var Settings $settings */
            $settings = $c[ Settings::class ];

            $obj = new YooKassa(
                $settings->get_value( 'payment.yookassa.shop_id' ),
                $settings->get_value( 'payment.yookassa.api_key' ),
                $logger = new Logger( 'yookassa' )
            );
            $obj->set_description( $settings->get_value( 'payment.yookassa.description' ) );
            $obj->is_enabled( $settings->get_value( 'payment.yookassa.is_enabled' ) );

            $logger->set_enabled( $settings->get_value( 'payment.yookassa.enable_logs' ) );

            return $obj;
        },
        RoboKassa::class            => function ( $c ) {
            /** @var Settings $settings */
            $settings = $c[ Settings::class ];

            $obj = new RoboKassa(
                $settings->get_value( 'payment.robokassa.merchant' ),
                $settings->get_value( 'payment.robokassa.pass1' ),
                $settings->get_value( 'payment.robokassa.pass2' ),
                $settings->get_value( 'payment.robokassa.hash_algo' ),
                $settings->get_value( 'payment.robokassa.currency' ),
                $logger = new Logger( 'robokassa' )
            );
            $obj->set_description( $settings->get_value( 'payment.robokassa.description' ) );
            $obj->is_enabled( $settings->get_value( 'payment.robokassa.is_enabled' ) );
            $obj->is_test( $settings->get_value( 'payment.robokassa.is_test' ) );

            $logger->set_enabled( $settings->get_value( 'payment.robokassa.enable_logs' ) );

            return $obj;
        },
        Prodamus::class             => function ( $c ) {
            /** @var Settings $settings */
            $settings = $c[ Settings::class ];

            $obj = new Prodamus(
                $settings->get_value( 'payment.prodamus.key' ),
                $settings->get_value( 'payment.prodamus.payment_url' ),
                $logger = new Logger( 'prodamus' )
            );
            $obj->is_enabled( $settings->get_value( 'payment.prodamus.is_enabled' ) );
            $obj->is_demo_mode( $settings->get_value( 'payment.prodamus.is_debug' ) );
            $obj->set_description( $settings->get_value( 'payment.prodamus.description' ) );

            $logger->set_enabled( $settings->get_value( 'payment.prodamus.enable_logs' ) );

            return $obj;
        },
        RecurringPayments::class    => function ( $c ) {
            /** @var Settings $settings */
            $settings = $c[ Settings::class ];

            $obj = new RecurringPayments(
                $c[ Orders::class ],
                $c[ PaymentProviders::class ],
                $logger = new Logger( 'recurring-payment' )
            );

            $logger->set_enabled( $settings->get_value( 'payment.recurring.enable_logs' ) );

            return $obj;
        },
        DefaultPages::class         => function () {
            return new DefaultPages();
        },
        Notices::class              => function ( $c ) {
            return new Notices( $c[ Settings::class ] );
        },
        TelegramCron::class         => function ( $c ) {
            return new TelegramCron( $c[ TelegramIntegration::class ] );
        },
        TelegramIntegration::class  => function ( $c ) {
            return new TelegramIntegration( $c[ Settings::class ] );
        },
        DbInstall::class            => function () use ( $wpdb ) {
            return new DbInstall( $wpdb );
        },
        OrderStatus::class          => function () {
            return new OrderStatus();
        },
        SetupAssistant::class       => function ( $c ) {
            return new SetupAssistant( $c[ Settings::class ] );
        },
        MobileMenu::class           => function () {
            return new MobileMenu();
        },
        Social::class               => function ( $c ) {
            return new Social( $c[ Customizer::class ] );
        },
        Breadcrumbs::class          => function ( $c ) {
            return new Breadcrumbs(
                $c[ BreadcrumbsBuilder::class ],
                $c[ Settings::class ]
            );
        },
        BreadcrumbsBuilder::class   => function () {
            return new BreadcrumbsBuilder();
        },
        GoogleReCaptcha::class      => function () {
            return new GoogleReCaptcha();
        },
        MicroData::class            => function ( $c ) {
            return new MicroData( $c[ Settings::class ] );
        },
        InfiniteScroll::class       => function () {
            return new InfiniteScroll();
        },
        MenuIcons::class            => function () {
            return new MenuIcons();
        },
        ProfileTabs::class          => function () {
            return new ProfileTabs();
        },
        UserComments::class         => function () {
            return new UserComments();
        },
        DefaultHooks::class         => function ( $c ) {
            return new DefaultHooks( $c[ Customizer::class ] );
        },
        LoopContext::class          => function () {
            return new LoopContext();
        },
        RelatedProducts::class      => function () {
            return new RelatedProducts();
        },
        Shortcodes::class           => function () {
            return new Shortcodes();
        },
        TableOfContents::class      => function ( $c ) {
            return new TableOfContents( $c[ Customizer::class ] );
        },


        Clearfy::class      => function () {
            return new Clearfy();
        },
        ExpertReview::class => function () {
            return new ExpertReview();
        },
        OmniVideo::class    => function () {
            return new OmniVideo();
        },
        Polylang::class     => function () {
            return new Polylang();
        },
        WPRemark::class     => function () {
            return new WPRemark();
        },
        WPStories::class    => function () {
            return new WPStories();
        },
        Feeds::class        => function ( $c ) {
            return new Feeds(
                $c[ Subs::class ],
                $c[ Bookmark::class ],
                $c[ LoopContext::class ]
            );
        },

        // actions

        AuthAction::class           => function ( $c ) {
            return new AuthAction(
                $c[ Auth::class ],
                $c[ Invite::class ],
                $c[ Mail::class ],
                $c[ Settings::class ],
                $c[ GoogleReCaptcha::class ]
            );
        },
        AvatarAction::class         => function () {
            return new AvatarAction();
        },
        BookmarkAction::class       => function ( $c ) {
            return new BookmarkAction( $c[ Bookmark::class ] );
        },
        CommentAction::class        => function ( $c ) {
            return new CommentAction( $c[ Comments::class ] );
        },
        FollowAction::class         => function ( $c ) {
            return new FollowAction( $c[ Subs::class ] );
        },
        InfiniteScrollAction::class => function ( $c ) {
            return new InfiniteScrollAction( $c[ Feeds::class ] );
        },
        InviteAction::class         => function ( $c ) {
            return new InviteAction( $c[ Invite::class ] );
        },
        OrderAction::class          => function ( $c ) {
            return new OrderAction(
                $c[ Orders::class ],
                $c[ PaymentProviders::class ],
                $c[ OrderStatus::class ],
                $c[ Membership::class ],
                $c[ RecurringPayments::class ]
            );
        },
        ProfileAction::class        => function () {
            return new ProfileAction();
        },
        PublishAction::class        => function ( $c ) {
            return new PublishAction(
                $c[ FrontendPublish::class ],
                $c[ Membership::class ]
            );
        },
        SaveAdOptionsAction::class  => function ( $c ) {
            return new SaveAdOptionsAction( $c[ Advertisement::class ] );
        },
        SubscribeAction::class      => function ( $c ) {
            return new SubscribeAction(
                $c[ PaidSubscriptions::class ],
                $c[ Orders::class ]
            );
        },
        SetupAssistantAction::class => function ( $c ) {
            return new SetupAssistantAction(
                $c[ DefaultPages::class ],
                $c[ Settings::class ]
            );
        },
        PublishEndpoint::class      => function () {
            return new PublishEndpoint();
        },
        CommentsEndpoint::class     => function () {
            return new CommentsEndpoint();
        },

        MetaboxPostElements::class => function () {
            return new MetaboxPostElements();
        },
        MetaboxPostSettings::class => function () {
            return new MetaboxPostSettings();
        },
        MetaboxSeo::class          => function () {
            return new MetaboxSeo();
        },
        CategorySeo::class         => function () {
            return new CategorySeo();
        },
        Layout::class              => function ( $c ) {
            return new Layout(
                $c[ Customizer::class ],
                $c[ SinglePost::class ],
                $c[ PostCard::class ],
            );
        },
        PostCard::class            => function ( $c ) {
            return new PostCard( $c[ Customizer::class ] );
        },
        SinglePost::class          => function ( $c ) {
            return new SinglePost( $c[ Customizer::class ] );
        },
        Sidebar::class             => function ( $c ) {
            return new Sidebar( $c[ Customizer::class ] );
        },
        Cron::class                => function () {
            return new Cron();
        },
    ] );

    $container[ Subs::class ] = $container->factory( function ( $c ) use ( $wpdb ) {
        return new Subs( $wpdb );
    } );

    return $container;
};
