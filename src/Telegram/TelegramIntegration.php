<?php

namespace WPShop\WPCommunity\Telegram;

use JetBrains\PhpStorm\NoReturn;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use WP_Error;
use WP_Post;
use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\TemplateFunctions;
use WPShop\WPCommunity\User;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\get_user_name;
use function WPShop\WPCommunity\theme_container;

class TelegramIntegration {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $_enabled_logs = false;

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
        add_filter( 'wpcommunity/telegram/send_post', [ $this, '_send_to_telegram' ], 10, 4 );

        add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {

            /**
             * @since 1.0
             */
            $send = apply_filters( 'wpcommunity/telegram/send_post', false, $post, $new_status, $old_status );

            if ( $send ) {
                $this->send_post_published( $post );
            }

        }, 10, 3 );

//        add_action( 'new_to_publish', [ $this, '_save_post_send_message' ] );
//        add_action( 'auto-draft_to_publish', [ $this, '_save_post_send_message' ] );
//        add_action( 'draft_to_publish ', [ $this, '_save_post_send_message' ] );
//        add_action( 'future_to_publish ', [ $this, '_save_post_send_message' ] );

        add_action( 'parse_request', function () {
            // todo add filter?
            $param = 'webhook';
            $value = 'telegram';

            if ( $value !== ( $_REQUEST[ $param ] ?? '' ) ) {
                return;
            }

            if ( get_setting( 'telegram.webhook_key' ) &&
                 ! hash_equals( get_setting( 'telegram.webhook_key' ), ( $_REQUEST['key'] ?? '' ) )
            ) {
                return;
            }
            $this->webhook();
        } );
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_set_telegram_webhook';
            add_action( "wp_ajax_$action", [ $this, '_set_webhook' ] );
        }

//        $this->_test();
    }

    /**
     * @param bool    $result
     * @param WP_Post $post
     * @param string  $new_status
     * @param string  $old_status
     *
     * @return bool
     */
    public function _send_to_telegram( $result, $post, $new_status, $old_status ) {
        if ( ! get_setting( 'telegram.enable_notifications' ) ) {
            return false;
        }

        $post_access = theme_container()->get( Membership::class )->get_post_access( $post->ID );
        if ( get_setting( 'telegram.notify_private_only' ) ) {
            if ( $post_access === Membership::ACCESS_PUBLIC ) {
                return false;
            }

            if ( $post_access === Membership::ACCESS_DEFAULT &&
                 get_setting( 'content.default_access.post_type--post' ) === Membership::ACCESS_PUBLIC
            ) {
                return false;
            }
        }

        if ( $post->post_type != 'post' ) {
            return false;
        }
        if ( $new_status === 'publish' ) {
            $result = true;
        }

        return $result;
    }


    /**
     * @return string|null
     */
    public function get_bot_link() {
        if ( $bot_name = get_setting( 'telegram.bot_username' ) ) {
            return 'https://t.me/' . $bot_name;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function is_bot_enabled() {
        return get_setting( 'telegram.api_key' ) && get_setting( 'telegram.bot_username' );
    }

    /**
     * @param int $user_id
     *
     * @return string|null
     */
    public function get_bot_start_link( $user_id ) {
        if ( $bot_link = $this->get_bot_link() ) {
            $user = theme_container()->get( User::class );

            return "{$bot_link}?start={$user_id}_{$user->get_bot_token( $user_id )}";
        }

        return null;
    }

    /**
     * @param int    $user_id
     * @param string $nonce
     *
     * @return bool
     */
    public function verify_nonce( $user_id, $nonce ) {
        $user = theme_container()->get( User::class );

        return hash_equals( $user->get_bot_token( $user_id ), $nonce );
    }

    /**
     * Проверка ссылки /start на токен правильный
     *
     * @param string $deep_linking_parameter
     * @param string $telegram_user_id
     * @param string $telegram_username
     *
     * @return bool
     */
    public function check_deep_linking_token( $deep_linking_parameter, $telegram_user_id, $telegram_username ) {
        if ( strpos( $deep_linking_parameter, '_' ) === false ) {
            return false;
        }
        [ $user_id, $nonce ] = explode( '_', $deep_linking_parameter );

        $user_id = (int) $user_id;

        // если пользователь не найден
        $user = get_user_by( 'ID', $user_id );
        if ( ! $user ) {
            return false;
        }

        if ( $this->verify_nonce( $user_id, $nonce ) ) {

            // todo удалять старых пользователей

            // устанавливаем пользователю
            update_user_meta( $user_id, User::USER_META_TELEGRAM_USER_ID, $telegram_user_id );
            update_user_meta( $user_id, User::USER_META_TELEGRAM_USERNAME, $telegram_username );

            return true;
        }

        return false;
    }

    /**
     * @param string $telegram_user_id
     *
     * @return \WP_User|null
     */
    public function get_user_by_telegram_user_id( $telegram_user_id ) {
        $users = get_users( [
            'meta_query' => [
                [
                    'key'   => User::USER_META_TELEGRAM_USER_ID,
                    'value' => $telegram_user_id,
                    'type'  => 'NUMERIC',
                ],
            ],
            'number'     => 1,
        ] );

        if ( ! empty( $users ) ) {
            return $users[0];
        }

        return null;
    }


    public function _test() {
        try {
            $this->enable_logs();

            if ( ! $this->get_telegram_instance() ) {
                return;
            }

            $inline_keyboard = new InlineKeyboard( [
                [ 'text' => __( 'View', 'wpcommunity' ), 'url' => get_the_permalink( 1 ) ],
            ] );

            $result = Request::sendMessage( [
                'chat_id'                  => get_setting( 'telegram.target_channel' ),
                'text'                     => 'test',
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup'             => $inline_keyboard,
            ] );

            if ( ! $result->isOk() ) {
                TelegramLog::error( $result->getDescription() );
            }

        } catch ( TelegramException $e ) {
            TelegramLog::error( $e );
        }
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function send_post_published( $post ) {
        if ( get_post_meta( $post->ID, '_sent_to_telegram', true ) ) {
            return;
        }

        try {
            $this->enable_logs();

            if ( ! $this->get_telegram_instance() ) {
                return;
            }

            $chat_id = get_setting( 'telegram.target_channel' );
            if ( ! $chat_id ) {
                return;
            }

            $message = $this->prepare_template(
                (string) get_setting( 'telegram.post_message_tpl' ),
                $post
            );

            $inline_keyboard = new InlineKeyboard( [
                [ 'text' => __( 'View', 'wpcommunity' ), 'url' => get_the_permalink( $post->ID ) ],
            ] );

            $data = [
                'chat_id'                  => $chat_id,
                'text'                     => $message,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup'             => $inline_keyboard,
            ];

            /**
             * Allows to modify message data for telegram
             *
             * @since 1.0
             */
            $data = apply_filters( 'wpcommunity/telegram/post_published_data', $data, $post );

            if ( get_setting( 'telegram.send_post_thumbnail' ) ) {
                $image = get_the_post_thumbnail_url( $post->ID, 'full' );
                if ( $image ) {
                    $data['text']                     = sprintf( '<a href="%s">&#8205;</a>', $image ) . $data['text'];
                    $data['disable_web_page_preview'] = false;
                }
            }

            $result = Request::sendMessage( $data );

            update_post_meta( $post->ID, '_sent_to_telegram', 1 );
        } catch ( TelegramException $e ) {
            TelegramLog::error( $e );
        }
    }

    /**
     * @param string  $message
     * @param WP_Post $post
     *
     * @return string
     */
    protected function prepare_template( $message, $post ) {

        add_filter( 'wpcommunity/membership/is_post_accessible', '__return_true' );

        $variables = [
            '{{post_link}}'   => function ( $post ) {
                return get_the_permalink( $post->ID );
            },
            '{{title}}'       => function ( $post ) {
                return get_the_title( $post->ID );
            },
            '{{excerpt}}'     => function ( $post ) {
                return theme_container()->get( TemplateFunctions::class )->get_the_excerpt( get_the_excerpt( $post ) );
            },
            '{{author_link}}' => function ( $post ) {
                return get_author_posts_url( $post->post_author );
            },
            '{{author}}'      => function ( $post ) {
                return get_user_name( $post->post_author );
            },
        ];

        /**
         * Allows to add additional variables for post published message
         *
         * @since 1.0
         */
        $variables = apply_filters( 'wpcommunity/telegram/post_published_variables', $variables, $post );

        foreach ( $variables as $token => $fn ) {
            $message = str_replace( $token, $fn( $post ), $message );
        }

        remove_filter( 'wpcommunity/membership/is_post_accessible', '__return_true' );

        return $message;
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _set_webhook() {
        try {
            $this->enable_logs();

            $telegram = $this->get_telegram_instance();

            if ( ! $telegram ) {
                wp_send_json_error(
                    new WP_Error( 'telegram', __( 'Unable to set telegram webhook with wrong api key or bot username', 'wpcommunity' ) )
                );
            }

            $result = $telegram->setWebhook( $this->get_webhook_url() );
            if ( $result->isOk() ) {
                wp_send_json_success( [ 'message' => $result->getDescription() ] );
            }

        } catch ( TelegramException $e ) {
            wp_send_json_error( new WP_Error( 'telegram_error', $e->getMessage() ) );
        }

        wp_send_json_error( new WP_Error( 'telegram_error', __( 'Unable to set telegram webhook', 'wpcommunity' ) ) );
    }

    /**
     * @return void
     */
    #[NoReturn]
    protected function webhook() {
        try {
            if ( ! ( $telegram = $this->get_telegram_instance() ) ) {
                return;
            }

            $telegram->enableAdmins( wp_parse_id_list( [ get_setting( 'telegram.admin_ids' ) ] ) );

            $telegram->addCommandsPath( __DIR__ . '/Commands' );

            $this->enable_logs();

            // Requests Limiter (tries to prevent reaching Telegram API limits)
            $telegram->enableLimiter( [ 'enabled' => true ] );

            $telegram->handle();
        } catch ( TelegramException $e ) {
            TelegramLog::error( $e );
        } catch ( TelegramLogException $e ) {

        }
        die;
    }

    /**
     * @return void
     */
    protected function enable_logs() {
        if ( get_setting( 'telegram.enable_logs' ) ) {
            if ( $this->_enabled_logs ) {
                return;
            }

            TelegramLog::initialize(
                new Logger( 'telegram_bot', [
                    ( new StreamHandler( WP_CONTENT_DIR . '/php-telegram-bot-debug.log', Logger::DEBUG ) )->setFormatter( new LineFormatter( null, null, true ) ),
                    ( new StreamHandler( WP_CONTENT_DIR . '/php-telegram-bot-error.log', Logger::ERROR ) )->setFormatter( new LineFormatter( null, null, true ) ),
                ] ),
                new Logger( 'telegram_bot_updates', [
                    ( new StreamHandler( WP_CONTENT_DIR . '/php-telegram-bot-update.log', Logger::INFO ) )->setFormatter( new LineFormatter( '%message%' . PHP_EOL ) ),
                ] )
            );

            $this->_enabled_logs = true;
        }
    }

    /**
     * @return string
     */
    protected function get_webhook_url() {
        // todo add filter?
        $param = 'webhook';
        $value = 'telegram';

        $url = add_query_arg( $param, $value, home_url() );
        if ( $key = get_setting( 'telegram.webhook_key' ) ) {
            $url = add_query_arg( 'key', $key, $url );
        }

        return $url;
    }

    /**
     * @return Telegram|null
     * @throws TelegramException
     */
    public function get_telegram_instance() {
        $api_key      = get_setting( 'telegram.api_key' );
        $bot_username = get_setting( 'telegram.bot_username' );

        if ( ! $api_key || ! $bot_username ) {
            return null;
        }

        return new Telegram( $api_key, $bot_username );
    }

    /**
     * @return void
     */
    public function kick_expired_users() {
        try {
            if ( ! $this->settings->get_value( 'telegram.auto_kick' ) ) {
                return;
            }

            $this->enable_logs();

            if ( ! $this->get_telegram_instance() ) {
                return;
            }

            foreach ( $this->get_expired_user_ids() as $user_id ) {
                $tg_user_id = get_user_meta( $user_id, User::USER_META_TELEGRAM_USER_ID, true );

                if ( ! $tg_user_id ) {
                    continue;
                }

                Request::kickChatMember( [
                    'chat_id' => get_setting( 'telegram.target_channel' ),
                    'user_id' => $tg_user_id,
                ] );

                // @todo delete user meta?
            }

        } catch ( TelegramException $e ) {
            TelegramLog::error( $e );
        }
    }

    /**
     * @return \Generator|int[]
     */
    protected function get_expired_user_ids() {
        global $wpdb;

        $now = current_time( 'timestamp' );

        $excluded = wp_parse_id_list( $this->settings->get_value( 'telegram.auto_kick_excluded' ) );
        $excluded = array_map( 'absint', $excluded );
        $excluded = $excluded ?: [ 0 ];
        $excluded = implode( ',', $excluded );
        $limit    = 100;
        $offset   = 0;
        $total    = $wpdb->get_var( $wpdb->prepare(
            "SELECT count(umeta_id) FROM $wpdb->usermeta WHERE meta_key = 'expired' AND CAST(meta_value AS unsigned) < %d AND umeta_id NOT IN({$excluded})",
            $now
        ) );

        while ( $offset < $total ) {
            $sql = $wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'expired' AND CAST(meta_value AS unsigned) < %d AND umeta_id NOT IN({$excluded}) LIMIT %d OFFSET %d",
                $now,
                $limit,
                $offset
            );

            $results = $wpdb->get_results( $sql, ARRAY_A );
            foreach ( $results as $result ) {
                yield $result['user_id'];
            }
            $offset += $limit;
        }
    }
}
