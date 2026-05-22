<?php

use WPShop\WPCommunity\Achievements;
use WPShop\WPCommunity\Actions\Admin\SaveAdOptionsAction;
use WPShop\WPCommunity\Actions\Admin\SetupAssistantAction;
use WPShop\WPCommunity\Api\BoostScoreEndpoint;
use WPShop\WPCommunity\Api\CommentsEndpoint;
use WPShop\WPCommunity\Api\PublishEndpoint;
use WPShop\WPCommunity\Actions\AvatarAction;
use WPShop\WPCommunity\Actions\BookmarkAction;
use WPShop\WPCommunity\Actions\CommentAction;
use WPShop\WPCommunity\Actions\InfiniteScrollAction;
use WPShop\WPCommunity\Actions\InviteAction;
use WPShop\WPCommunity\Actions\OrderAction;
use WPShop\WPCommunity\Actions\FollowAction;
use WPShop\WPCommunity\Actions\SubscribeAction;
use WPShop\WPCommunity\Admin\MenuPage;
use WPShop\WPCommunity\Admin\Notices;
use WPShop\WPCommunity\AssetProvider;
use WPShop\WPCommunity\Actions\AuthAction;
use WPShop\WPCommunity\Auth;
use WPShop\WPCommunity\Bookmark;
use WPShop\WPCommunity\Cron;
use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\DefaultHooks;
use WPShop\WPCommunity\DefaultPages;
use WPShop\WPCommunity\Features\Advertisement;
use WPShop\WPCommunity\Features\Breadcrumbs;
use WPShop\WPCommunity\Comments;
use WPShop\WPCommunity\Database\DbInstall;
use WPShop\WPCommunity\Actions\PublishAction;
use WPShop\WPCommunity\Features\Feeds;
use WPShop\WPCommunity\Features\FrontendEditor;
use WPShop\WPCommunity\Features\GoogleReCaptcha;
use WPShop\WPCommunity\Features\InfiniteScroll;
use WPShop\WPCommunity\Features\MenuIcons;
use WPShop\WPCommunity\Features\RelatedProducts;
use WPShop\WPCommunity\Features\TableOfContents;
use WPShop\WPCommunity\FrontendPublish;
use WPShop\WPCommunity\Helper;
use WPShop\WPCommunity\Invite;
use WPShop\WPCommunity\Features\Karma;
use WPShop\WPCommunity\Layout\Layout;
use WPShop\WPCommunity\Layout\Sidebar;
use WPShop\WPCommunity\Mail;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Metaboxes\MetaboxPostElements;
use WPShop\WPCommunity\Metaboxes\MetaboxPostSettings;
use WPShop\WPCommunity\Metaboxes\MetaboxSeo;
use WPShop\WPCommunity\Metaboxes\CategorySeo;
use WPShop\WPCommunity\Features\MicroData;
use WPShop\WPCommunity\MobileMenu;
use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\PaidSubscriptions;
use WPShop\WPCommunity\PaymentProviders;
use WPShop\WPCommunity\Actions\ProfileAction;
use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Post;
use WPShop\WPCommunity\Features\Shortcodes;
use WPShop\WPCommunity\Support\Clearfy;
use WPShop\WPCommunity\Support\ExpertReview;
use WPShop\WPCommunity\Support\OmniVideo;
use WPShop\WPCommunity\Support\Polylang;
use WPShop\WPCommunity\Support\WPRemark;
use WPShop\WPCommunity\Support\WPStories;
use WPShop\WPCommunity\Telegram\TelegramIntegration;
use WPShop\WPCommunity\TemplateFunctions;
use WPShop\WPCommunity\User;

//use function WPShop\WPCommunity\core_container;
use WPShop\WPCommunity\Features\UserSubscriptions;
use WPShop\WPCommunity\Features\ViewsCounter;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_filter( 'doing_it_wrong_trigger_error', function ( $trigger, $function_name, $message, $version ) {
    if ( '_load_textdomain_just_in_time' === $function_name && false !== strpos( $message, '<code>wpcommunity</code>' ) ) {
        return false;
    }

    return (bool) $trigger;
}, 10, 4 );

//add_action( 'after_setup_theme', function () {
load_theme_textdomain( 'wpcommunity', get_template_directory() . '/languages' );
//} );


add_action( 'wpcommunity/theme/init', function () {
    // Init features
    theme_container()->get( Achievements::class )->init();
    theme_container()->get( Advertisement::class )->init();
    theme_container()->get( AssetProvider::class )->init();
    theme_container()->get( Auth::class )->init();
    theme_container()->get( Breadcrumbs::class )->init();
    theme_container()->get( GoogleReCaptcha::class )->init();
    theme_container()->get( Comments::class )->init();
    theme_container()->get( Cron::class )->init();
    theme_container()->get( Customizer::class )->init();
    theme_container()->get( DbInstall::class )->init();
    theme_container()->get( DefaultPages::class )->init();
    theme_container()->get( Feeds::class )->init();
    theme_container()->get( FrontendEditor::class )->init();
    theme_container()->get( FrontendPublish::class )->init();
    theme_container()->get( Helper::class )->init();
    theme_container()->get( Invite::class )->init();
    theme_container()->get( Karma::class )->init();
    theme_container()->get( Layout::class )->init();
    theme_container()->get( Sidebar::class )->init();
    theme_container()->get( Mail::class )->init();
    theme_container()->get( Membership::class )->init();
//theme_container()->get( MenuIcons::class )->init();
    theme_container()->get( MenuPage::class )->init();
    theme_container()->get( MicroData::class )->init();
    theme_container()->get( MobileMenu::class )->init();
    theme_container()->get( Notices::class )->init();
    theme_container()->get( Orders::class )->init();
    theme_container()->get( PaidSubscriptions::class )->init();
    theme_container()->get( PaymentProviders::class )->init();
    theme_container()->get( Post::class )->init();
    theme_container()->get( Settings::class )->init();
    theme_container()->get( TelegramIntegration::class )->init();
    theme_container()->get( User::class )->init();
    theme_container()->get( ViewsCounter::class )->init();
    theme_container()->get( Vote::class )->init();
    theme_container()->get( DefaultHooks::class )->init();
    theme_container()->get( RelatedProducts::class )->init();
    theme_container()->get( Shortcodes::class )->init();
    theme_container()->get( TableOfContents::class )->init();

    theme_container()->get( Clearfy::class )->init();
    theme_container()->get( ExpertReview::class )->init();
    theme_container()->get( OmniVideo::class )->init();
    theme_container()->get( Polylang::class )->init();
    theme_container()->get( WPRemark::class )->init();
    theme_container()->get( WPStories::class )->init();

    theme_container()->get( AuthAction::class )->init();
    theme_container()->get( AvatarAction::class )->init();
    theme_container()->get( BookmarkAction::class )->init();
    theme_container()->get( CommentAction::class )->init();
    theme_container()->get( FollowAction::class )->init();
    theme_container()->get( InfiniteScrollAction::class )->init();
    theme_container()->get( InviteAction::class )->init();
    theme_container()->get( OrderAction::class )->init();
    theme_container()->get( ProfileAction::class )->init();
    theme_container()->get( PublishAction::class )->init();
    theme_container()->get( SubscribeAction::class )->init();
    theme_container()->get( SaveAdOptionsAction::class )->init();
    theme_container()->get( SetupAssistantAction::class )->init();
    theme_container()->get( PublishEndpoint::class )->init();
    theme_container()->get( CommentsEndpoint::class )->init();
    theme_container()->get( BoostScoreEndpoint::class )->init();

    theme_container()->get( MetaboxSeo::class )->init();
    theme_container()->get( CategorySeo::class )->init();

    if ( is_admin() ) {
        theme_container()->get( MetaboxPostElements::class )->init();
        theme_container()->get( MetaboxPostSettings::class )->init();
    }
} );

/**
 * The theme initialization hook
 *
 * [ru] Хук инициализации теты
 *
 * @since 1.0
 */
do_action( 'wpcommunity/theme/init' );





//    theme_container()->get( ThemeSupport::class )->init();
//    theme_container()->get( Structure::class )->init();
//    theme_container()->get( Css::class )->init();
//    theme_container()->get( JavaScript::class )->init();
//    theme_container()->get( Sidebar::class )->init();
//    theme_container()->get( Footer::class )->init();
//    theme_container()->get( Navigation::class )->init();
//    theme_container()->get( Comments::class )->init();

// Init core
//theme_container()->get( AssetProvider::class )->init();
//theme_container()->get( ThemeProvider::class )->init();
//theme_container()->get( SettingsManager::class )->init();
//theme_container()->get( MenuPage::class )->init(
//    [ theme_container()->get( ModuleManager::class ), 'init' ],
//    [ theme_container()->get( SettingsManager::class ), 'init' ]
//);

