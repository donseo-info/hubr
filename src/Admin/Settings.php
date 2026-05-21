<?php

namespace WPShop\WPCommunity\Admin;

use Wpshop\Settings\AbstractSettings;
use Wpshop\Settings\MaintenanceInterface;
use WPShop\WPCommunity\Features\RelatedProducts;
use WPShop\WPCommunity\Vote;

class Settings extends AbstractSettings {

    const TEXT_DOMAIN = 'wpcommunity';

    /**
     * @var string[]
     */
    protected $_defaults = [
        'subscription.1_day'           => 49,
        'subscription.1_day.enabled'   => 0,
        'subscription.1_month'         => 490,
        'subscription.1_month.enabled' => 1,
        'subscription.1_year'          => 4990,
        'subscription.1_year.enabled'  => 1,

        'payment.recurring.description'           => '',
        'payment.recurring.errors_limit'          => 5,
        'payment.recurring.enable_logs'           => false,
        'payment.recurring.enable_fake_recurring' => false,

        'payment.yookassa.is_enabled'   => false,
        'payment.yookassa.recurring'    => false,
        'payment.yookassa.enable_logs'  => false,
        'payment.yookassa.shop_id'      => '',
        'payment.yookassa.api_key'      => '',
        'payment.yookassa.send_receipt' => 0,

        'payment.robokassa.is_enabled'   => false,
        'payment.robokassa.recurring'    => false,
        'payment.robokassa.enable_logs'  => false,
        'payment.robokassa.is_test'      => false,
        'payment.robokassa.merchant'     => '',
        'payment.robokassa.county'       => 'ru',
        'payment.robokassa.currency'     => 'RUB',
        'payment.robokassa.pass1'        => '',
        'payment.robokassa.pass2'        => '',
        'payment.robokassa.hash_algo'    => 'md5',
        'payment.robokassa.send_receipt' => 0,

        'payment.prodamus.is_enabled'  => '',
        'payment.prodamus.recurring'   => '',
        'payment.prodamus.enable_logs' => '',
        'payment.prodamus.key'         => '',
        'payment.prodamus.payment_url' => '',
        'payment.prodamus.is_debug'    => 0,
        'payment.prodamus.currency'    => 'rub',

        'content.default_access.post_type--post' => 'private',
        'content.default_access.post_type--page' => 'public',

        'publish.default_status'     => 'publish',
        'publish.can_create_tags'    => 0,
        'publish.edit_time'          => 900, // 60 * 15
        'publish.exclude_categories' => '',

        'related.enabled'            => 0,
        'related.count'              => 4,
        'related.search_by'          => 'tag_and_categories', // categories, tags
        'related.exclude_categories' => '',
        'related.exclude_tags'       => '',
        'related.exclude_posts'      => '',
        'related.order'              => 'date_desc', // date_asc, rand, karma

        'comments.enabled_for_guests' => 0,
        'comments.for_members_only'   => 0,
        'comments.show_by_button'     => 0,

        'content.infinite_scroll.home'      => 0,
        'content.infinite_scroll.popular'   => 0,
        'content.infinite_scroll.subs'      => 0,
        'content.infinite_scroll.bookmarks' => 0,
        'content.full.home'                 => 0,
        'content.full.popular'              => 0,
        'content.full.subs'                 => 0,
        'content.full.bookmarks'            => 0,

        'likes.vote_method'   => 'users',
        'likes.icon'          => 'chevron',
        'likes.show_score'    => true,
        'likes.show_dislikes' => true,

        'telegram.api_key'              => '',
        'telegram.bot_username'         => '',
        'telegram.webhook_key'          => '',
        'telegram.enable_logs'          => 0,
        'telegram.admin_ids'            => '',
        'telegram.target_channel'       => '',
        'telegram.enable_notifications' => 1,
        'telegram.notify_private_only'  => 0,
        'telegram.send_post_thumbnail'  => 0,
        'telegram.auto_kick'            => 0,
        'telegram.auto_kick_excluded'   => '',

        'registration.require_invite' => 0,

        'breadcrumbs.enabled'     => 1,
        'breadcrumbs.use_plugins' => 1,
        'breadcrumbs.home_title'  => '',
        'breadcrumbs.home_link'   => '/',
        'breadcrumbs.separator'   => '»',
        'breadcrumbs.show_paged'  => 0,

        'karma.post_publish'        => 100,
        'karma.comment_publish'     => 5,
        'karma.comment_spam'        => 5,
        'karma.post_vote_change'    => 5,
        'karma.comment_vote_change' => 1,

        'account.pro.enable_expire_notification' => 0,

        'account.not_pro.create_posts' => 1,
        'account.not_pro.add_comments' => 1,

        'grecaptcha.site_key'        => '',
        'grecaptcha.secret_key'      => '',
        'grecaptcha.sign_up.enabled' => 0,

        'advanced.use_localized_settings' => 0,
        'advanced.code.head'              => '',
        'advanced.code.body'              => '',
        'advanced.enable_microdata'       => 0,
        'advanced.disable_illegal_logins' => 1,
    ];

    /**
     * @var callable[]
     */
    protected $sanitizers = [
        'payment.yookassa.is_enabled'   => 'absint',
        'payment.yookassa.recurring'    => 'absint',
        'payment.yookassa.enable_logs'  => 'absint',
        'payment.yookassa.description'  => 'wp_kses_post',
        'payment.yookassa.shop_id'      => 'sanitize_text_field',
        'payment.yookassa.api_key'      => 'sanitize_text_field',
        'payment.yookassa.send_receipt' => 'absint',

        'payment.general.acceptance_text' => 'sanitize_text_field',

        'payment.recurring.description'   => 'wp_kses_post',
        'payment.recurring.checkbox_text' => 'wp_kses_post',
        'payment.recurring.errors_limit'  => 'absint',
        'payment.recurring.enable_logs'   => 'absint',

        'payment.robokassa.is_enabled'   => 'absint',
        'payment.robokassa.recurring'    => 'absint',
        'payment.robokassa.enable_logs'  => 'absint',
        'payment.robokassa.description'  => 'wp_kses_post',
        'payment.robokassa.is_test'      => 'absint',
        'payment.robokassa.merchant'     => 'sanitize_text_field',
        'payment.robokassa.currency'     => 'sanitize_text_field',
        'payment.robokassa.country'      => 'sanitize_text_field',
        'payment.robokassa.pass1'        => 'sanitize_text_field',
        'payment.robokassa.pass2'        => 'sanitize_text_field',
        'payment.robokassa.hash_algo'    => 'sanitize_text_field',
        'payment.robokassa.send_receipt' => 'absint',

        'payment.prodamus.is_enabled'  => 'absint',
        'payment.prodamus.recurring'   => 'absint',
        'payment.prodamus.enable_logs' => 'absint',
        'payment.prodamus.description' => 'sanitize_text_field',
        'payment.prodamus.key'         => 'sanitize_text_field',
        'payment.prodamus.is_debug'    => 'absint',
        'payment.prodamus.currency'    => 'sanitize_text_field',

        'content.default_access.post_type--post' => 'sanitize_text_field',
        'content.default_access.post_type--page' => 'sanitize_text_field',

        'publish.default_status'     => 'sanitize_text_field',
        'publish.can_create_tags'    => 'absint',
        'publish.edit_time'          => 'absint',
        'publish.exclude_categories' => 'sanitize_text_field',

        'related.enabled'            => 'absint',
        'related.title'              => 'sanitize_text_field',
        'related.count'              => 'absint',
        'related.exclude_categories' => [ __CLASS__, 'sanitize_id_list' ],
        'related.exclude_tags'       => [ __CLASS__, 'sanitize_id_list' ],
        'related.exclude_posts'      => [ __CLASS__, 'sanitize_id_list' ],

        'comments.enabled_for_guests' => 'absint',
        'comments.for_members_only'   => 'absint',
        'comments.show_by_button'     => 'absint',

        'content.infinite_scroll.home'      => 'absint',
        'content.infinite_scroll.popular'   => 'absint',
        'content.infinite_scroll.subs'      => 'absint',
        'content.infinite_scroll.bookmarks' => 'absint',
        'content.full.home'                 => 'absint',
        'content.full.popular'              => 'absint',
        'content.full.subs'                 => 'absint',
        'content.full.bookmarks'            => 'absint',

        'likes.icon'          => 'sanitize_text_field',
        'likes.show_score'    => 'absint',
        'likes.show_dislikes' => 'absint',

        'telegram.api_key'              => 'sanitize_text_field',
        'telegram.bot_username'         => 'sanitize_text_field',
        'telegram.webhook_key'          => 'sanitize_text_field',
        'telegram.enable_logs'          => 'absint',
        'telegram.target_channel'       => 'sanitize_text_field',
        'telegram.enable_notifications' => 'absint',
        'telegram.notify_private_only'  => 'absint',
        'telegram.send_post_thumbnail'  => 'absint',
        'telegram.auto_kick'            => 'absint',

        'page.about'     => 'absint',
        'page.bookmarks' => 'absint',
        'page.join'      => 'absint',
        'page.order'     => 'absint',
        'page.popular'   => 'absint',
        'page.profile'   => 'absint',
        'page.publish'   => 'absint',
        'page.subs'      => 'absint',
        'page.top'       => 'absint',
        'page.offer'     => 'absint',
        'page.payment'   => 'absint',
        'page.contacts'  => 'absint',

        'breadcrumbs.enabled'     => 'absint',
        'breadcrumbs.use_plugins' => 'absint',
        'breadcrumbs.home_title'  => 'sanitize_text_field',
        'breadcrumbs.home_link'   => 'sanitize_url',
        'breadcrumbs.separator'   => 'sanitize_text_field',
        'breadcrumbs.show_paged'  => 'absint',

        'karma.post_publish'        => 'absint',
        'karma.comment_publish'     => 'absint',
        'karma.comment_spam'        => 'absint',
        'karma.post_vote_change'    => 'absint',
        'karma.comment_vote_change' => 'absint',

        'account.pro.enable_expire_notification' => 'absint',

        'account.not_pro.create_posts' => 'absint',
        'account.not_pro.add_comments' => 'absint',

        'grecaptcha.site_key'        => 'sanitize_text_field',
        'grecaptcha.secret_key'      => 'sanitize_text_field',
        'grecaptcha.sign_up.enabled' => 'absint',

        'advanced.use_localized_settings' => 'absint',
        'advanced.code.head'              => 'trim',
        'advanced.code.body'              => 'trim',
        'advanced.enable_microdata'       => 'absint',
        'advanced.disable_illegal_logins' => 'absint',

        'site.privacy_policy.link_text' => 'sanitize_text_field',
    ];

    /**
     * @var string[]
     */
    protected $password_fields = [
        'payment.yookassa.api_key',
        'payment.robokassa.pass1',
        'payment.robokassa.pass2',
        'payment.prodamus.key',
        'grecaptcha.secret_key',
    ];

    /**
     * @return void
     */
    protected function init_defaults() {
        $this->_defaults['payment.general.acceptance_text'] = __( 'By clicking "Pay" you agree to the processing of [privacy_policy_link]personal data[/privacy_policy_link] and accept [offer_link]he offer[/offer_link].', 'wpcommunity' );

        $this->_defaults['payment.recurring.checkbox_text'] = __( 'Save for recurring autopayments.', 'wpcommunity' );

        $this->_defaults['payment.yookassa.description']  = __( 'Russian and some foreign cards, YooMoneu, QIWI and bank clients are accepted for payment.', 'wpcommunity' );
        $this->_defaults['payment.robokassa.description'] = __( 'Bank cards of the Russian Federation, foreign bank cards (MasterCard, Union Pay), SBP, QIWI, Yandex Pay.', 'wpcommunity' );
        $this->_defaults['payment.prodamus.description']  = __( 'Bank cards of the Russian Federation, foreign bank cards (MasterCard, Union Pay), SBP, QIWI, Yandex Pay.', 'wpcommunity' );

        $this->_defaults['related.title'] = __( 'Related Posts', 'wpcommunity' );

        $this->_defaults['breadcrumbs.home_title'] = __( 'Home', 'wpcommunity' );

        $this->_defaults['site.privacy_policy.link_text'] = __( 'By clicking the "Register" button, You accept the rules and [privacy_policy_link]privacy policy[/privacy_policy_link].', 'wpcommunity' );

        $_new_post = _x( 'New post', 'heredoc', 'wpcommunity' );
        $_author   = _x( 'Author', 'heredoc', 'wpcommunity' );

        $this->_defaults['telegram.post_message_tpl'] = <<<TXT
📝 $_new_post: <b><a href="{{post_link}}">{{title}}</a></b>

{{excerpt}}

$_author <a href="{{author_link}}">{{author}}</a>
TXT;
    }

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        $this->sanitizers['likes.vote_method']            = [ $this, 'sanitize_vote_method' ];
        $this->sanitizers['telegram.bot_username']        = [ $this, 'sanitize_tg_bot_name' ];
        $this->sanitizers['telegram.auto_kick_excluded']  = static function ( $value ) {
            $value = wp_parse_id_list( $value );
            $value = array_map( 'absint', $value );
            $value = implode( ',', $value );

            return $value;
        };
        $this->sanitizers['payment.prodamus.payment_url'] = static function ( $value ) {
            $parts = parse_url( $value );
            if ( empty( $parts['scheme'] ) ) {
                $value = 'https://' . $value;
            } else if ( $parts['scheme'] !== 'https' ) {
                $value = 'https' . substr( $value, strlen( $parts['scheme'] ) );
            }

            return sanitize_url( $value, [ 'https' ] );
        };

        $this->sanitizers['related.search_by'] = static function ( $value ) {
            return in_array( $value, array_keys( RelatedProducts::search_by_options() ), true ) ? $value : 'both';
        };
        $this->sanitizers['related.order']     = static function ( $value ) {
            return in_array( $value, array_keys( RelatedProducts::order_options() ), true ) ? $value : 'date_desc';
        };

        add_filter( 'wpsc/settings/use_localized_settings', [ $this, '_check_localized_settings' ] );
    }

    /**
     * @param bool $result
     *
     * @return bool
     */
    public function _check_localized_settings( $result ) {
        if ( $this->get_value( 'advanced.use_localized_settings' ) ) {
            $result = true;
        } elseif ( $this->detect_multi_lang_plugins() ) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function sanitize_id_list( $value ) {
        $value = wp_parse_id_list( $value );

        return implode( ',', $value );
    }

    /**
     * @return bool
     */
    public function detect_multi_lang_plugins() {
        $plugins = [
            'sitepress-multilingual-cms/sitepress.php' => function () { // WPML
                //see https://wpml.org/forums/topic/check-if-wpml-is-active/
                return defined( 'ICL_SITEPRESS_VERSION' );
            },
            'polylang/polylang.php'                    => function () {
                return defined( 'POLYLANG_VERSION' );
            }, // Polylang
            'qtranslate-x/qtranslate.php', // qTranslate X
            'translatepress-multilingual/index.php', // TranslatePress
            'weglot/weglot.php', // Weglot
            'multilingualpress/multilingualpress.php', // MultilingualPress
            'gtranslate/gtranslate.php', // GTranslate
            'loco-translate/loco.php', // Loco Translate
        ];

        foreach ( $plugins as $plugin => $check_fn ) {
            if ( is_numeric( $plugin ) ) {
                $plugin   = $check_fn;
                $check_fn = 'is_plugin_active';
            }

            if ( $check_fn( $plugin ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
//    public function update_option( $name, $value ) {
//        $this->get_value( $name ); // trigger load option values
//        if ( array_key_exists( $name, $this->_options ) ) {
//            if ( array_key_exists( $name, $this->sanitizers ) && is_callable( $this->sanitizers[ $name ] ) ) {
//                $value = call_user_func( $this->sanitizers[ $name ], $value );
//            }
//            $this->_options[ $name ] = $value;
//            update_option( $this->option, $this->_options );
//        }
//    }

    /**
     * @inheridoc
     */
    public function setup_tabs() {
        $this->add_tab( 'dashboard', __( 'Dashboard', 'wpcommunity' ) );
        $this->add_tab( 'pages', __( 'Pages', 'wpcommunity' ) );
        $this->add_tab( 'payment', __( 'Payment', 'wpcommunity' ) );
        $this->add_tab( 'settings', __( 'Settings', 'wpcommunity' ) );
        $this->add_tab( 'telegram', __( 'Telegram', 'wpcommunity' ) );
        $this->add_tab( 'karma', __( 'Karma', 'wpcommunity' ) );
        $this->add_tab( 'pro', __( 'Pro Account', 'wpcommunity' ) );
        $this->add_tab( 'advanced', __( 'Advanced', 'wpcommunity' ) );
    }

    /**
     * @inheridoc
     */
    public function get_tab_icons() {
        return array_merge( parent::get_tab_icons(), [
            'settings' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256.01 209.36c12.96 0 25.24 4.27 33.69 11.7 9.07 7.98 13.87 19.73 14.29 34.93-.42 15.21-5.23 26.96-14.3 34.94-8.46 7.44-20.74 11.7-33.69 11.7s-25.24-4.27-33.7-11.7c-9.07-7.98-13.87-19.73-14.29-34.93.42-15.21 5.23-26.96 14.3-34.93 8.46-7.44 20.74-11.7 33.7-11.7M332.28 0H179.72v78.23a189.922 189.922 0 0 0-36.88 21.69l-66.6-39.13L0 195.17l66.58 39.12a198.125 198.125 0 0 0 0 43.41L0 316.83l76.24 134.39 66.6-39.13a189.419 189.419 0 0 0 36.88 21.69v78.23h152.56v-78.23c13.05-5.8 25.41-13.08 36.88-21.69l66.6 39.13L512 316.83l-66.58-39.12c.79-7.23 1.19-14.5 1.19-21.71s-.4-14.48-1.19-21.71L512 195.17 435.76 60.79l-66.6 39.13a189.419 189.419 0 0 0-36.88-21.69V0ZM146.65 155.93c16.34-13.16 35.37-29.23 55.09-36.62l23.83-9.82V46.55h60.88v62.95l23.83 9.82c19.67 7.36 38.8 23.5 55.09 36.62l53.61-31.5 30.48 53.72-53.59 31.49c1.62 12.88 5.23 33.6 4.93 46.36.31 12.67-3.32 33.6-4.93 46.36l53.59 31.49-30.48 53.72-53.61-31.5c-16.34 13.16-35.37 29.23-55.09 36.62l-23.83 9.82v62.95h-60.88v-62.95l-23.83-9.82c-19.67-7.36-38.8-23.5-55.09-36.62l-53.61 31.5-30.48-53.72 53.59-31.49c-1.62-12.88-5.23-33.6-4.93-46.36-.31-12.67 3.32-33.6 4.93-46.36l-53.59-31.49 30.48-53.72 53.61 31.5ZM256 161.36c-47.46 0-94.92 31.55-96 94.63 1.07 63.1 48.53 94.64 96 94.64s94.92-31.55 96-94.64c-1.07-63.09-48.53-94.64-96-94.64Z" fill="currentColor"></path></svg>',
            'payment'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M392 208.4V135C391.45 60.47 330.66 0 256 0S120.55 60.47 120 135v73.4A80.11 80.11 0 0048 288v144a80.09 80.09 0 0080 80h256a80.09 80.09 0 0080-80V288a80.11 80.11 0 00-72-79.6zM256 48a88.1 88.1 0 0188 88v72H168v-72a88.1 88.1 0 0188-88zm160 384a32 32 0 01-32 32H128a32 32 0 01-32-32V288a32 32 0 0132-32h256a32 32 0 0132 32z" fill="currentColor"></path></svg>',
            'pages'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M363.35 250.41a24 24 0 010 33.94L255.08 392.62a24 24 0 01-33.94 0l-53.49-53.49a24 24 0 1133.94-33.94l36.52 36.52 91.3-91.3a24 24 0 0133.94 0zM448 157.25V456a56.11 56.11 0 01-56.1 56H120.1A56.11 56.11 0 0164 456V56a56.11 56.11 0 0156.1-56h170.25A55.78 55.78 0 01330 16.38l101.54 101.25A55.55 55.55 0 01448 157.25zM304 136a8 8 0 008 8h78l-86-85.75zm96 320V192h-88a56.06 56.06 0 01-56-56V48H120.1a8.06 8.06 0 00-8.1 8v400a8.06 8.06 0 008.1 8h271.8a8.06 8.06 0 008.1-8z" fill="currentColor"></path></svg>',
            'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="m13.12 16.435-2.927 2.423-.827.111-1.852-6.303L1 10.552 21.054 3l.946.02L18.84 20l-5.72-3.565Zm5.016-10.244-9.19 5.442 1.059 4.46.29-.066 1.104-3.44 7.344-6.325-.607-.071Z" clip-rule="evenodd"/></svg>',
            'karma'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M56.6 480A24.63 24.63 0 0132 455.4V216.6A24.63 24.63 0 0156.6 192h46.9a24.41 24.41 0 0124.5 24.69V455.4a24.63 24.63 0 01-24.6 24.6zm129.9 0a26.53 26.53 0 01-26.5-26.5V218.6a26.6 26.6 0 013.44-13c9.28-20.6 85.11-140.11 91.93-150.85C259.89 44.09 271.09 32 288.4 32H335c9.83 0 17.11 3 21.64 8.76 5.31 6.82 6.2 16.84 2.71 30.65l-.05.21-.07.21c-.08.26-8.06 26-13.93 54.06-10.45 50-5 62.47-3 65.09a2 2 0 001.93 1H433c15 0 27.34 4.78 35.64 13.82 8.45 9.2 12.4 22.5 11.13 37.46L465 398.94v.16c-6.14 48.71-40.77 78.95-92.63 80.89H186.5zm184.68-48c27.29-1.23 42.79-14.12 46.05-38.32L431.87 240H344.2a49.92 49.92 0 01-40.56-20.55c-4.63-6.28-10.17-15.75-11.92-33.4-1.72-17.35.44-40.19 6.61-69.8v-.18L307.67 80h-13.18l-1.09 1.79C226.49 191.4 211.2 217.73 208 223.72V432z" fill="currentColor"></path></svg>',
            'pro'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M257.94 48a1.1 1.1 0 011.06 1.27l-22 135A48 48 0 00284.38 240h71.86L239.16 463.53a.87.87 0 01-.77.47.87.87 0 01-.85-1.06l29.82-132.39A48 48 0 00220.53 272h-75.17c-2.05-2.51-1.56-5.83-.07-8.15.81-1.27 1.57-2.57 2.26-3.91L257 48.59a1.11 1.11 0 011-.59m0-48a49.09 49.09 0 00-43.6 26.52L104.93 237.87c-14.22 22.1-11.32 51.07 7.77 69.38C120.78 315 130.84 320 141.89 320h78.64l-29.82 132.39a48.87 48.87 0 0091 33.41L410.43 240c13.33-21.33 1.73-48-24-48H284.38l22-135a49.09 49.09 0 00-48.44-57z" fill="currentColor"></path></svg>',
            'advanced' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M13.06 19.94l-2.12 2.12L6.88 18l4.06-4.06 2.12 2.12L11.12 18zm5.88-3.88L20.88 18l-1.94 1.94 2.12 2.12L25.12 18l-4.06-4.06zM16 12.14l-3 12 2.92.72 3-12zM30 5v22a3 3 0 01-3 3H5a3 3 0 01-3-3V5a3 3 0 013-3h22a3 3 0 013 3zM5 5v2h22V5zm22 22V10H5v17z" fill="currentColor"></path></svg>',
        ] );
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function doc_link( $type ) {
        switch ( $type ) {
            case 'doc':
                return 'https://support.wpshop.ru/docs/themes/' . THEME_SLUG;
            case 'faq':
                return 'https://support.wpshop.ru/fag_tag/' . THEME_SLUG . '/';
            default:
                return null;
        }
    }

    /**
     * @param string $name input name
     * @param string $title
     * @param array  $args
     *
     * @return void
     */
    public function render_webhook_input( $name, $title, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <div class="wpshop-settings-form-row__label">
            <label for="<?php echo esc_attr( $args['id'] ) ?>"><?php echo $title ?></label>
        </div>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_input_field( $name, $args ); ?>
            <a href="#" class="js-wpcommunity-generate-webhook"><?php echo __( 'Generate Key', 'wpcommunity' ) ?></a>
        </div>
        <?php
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitize_vote_method( $value ) {
        if ( array_key_exists( $value, Vote::options() ) ) {
            return $value;
        }

        return $this->_defaults['vote_method'];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitize_tg_bot_name( $value ) {
        $value = ltrim( $value, '@' );

        return $value;
    }


    /**
     * @inheridoc
     */
    protected static function get_template_parts_root() {
        return get_template_directory() . '/template-parts/';
    }

    /**
     * @inheritDoc
     */
    public static function product_prefix() {
        return 'wpshop_wpcommunity_';
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $force_create
     *
     * @return bool true if option was updated
     */
    public function update_value( $key, $value, $force_create = false ) {
        $this->get_value( $key );
        if ( $force_create || array_key_exists( $key, $this->_options ) ) {
            if ( array_key_exists( $key, $this->sanitizers ) ) {
                $value = call_user_func( $this->sanitizers[ $key ], $value );
            }
            $this->_options[ $key ] = $value;
            update_option( $this->option, $this->_options );

//            $this->_options = null;

            return true;
        }

        return false;
    }
}
