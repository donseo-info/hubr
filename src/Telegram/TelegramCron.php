<?php

namespace WPShop\WPCommunity\Telegram;

/**
 * @deprecated
 */
class TelegramCron {

    /**
     * @var TelegramIntegration
     */
    protected $telegram_integration;

    /**
     * @param TelegramIntegration $telegram_integration
     */
    public function __construct( TelegramIntegration $telegram_integration ) {
        $this->telegram_integration = $telegram_integration;
    }

    /**
     * @return void
     */
    public function init() {
        add_filter( 'cron_schedules', function ( $schedules ) {
            $schedules['five_min'] = [
                'interval' => 5 * 60,
                'display'  => __( 'Every 5 minutes', 'wpcommunity' ),
            ];

            return $schedules;
        } );

        if ( ! wp_next_scheduled( 'wpcommunity/cron/check_telegram_users' ) ) {
            wp_schedule_event( time(), 'five_min', 'wpcommunity/cron/check_telegram_users' );
        }

        add_action( 'wpcommunity/cron/check_telegram_users', [ $this, 'check_users' ] );
    }

    /**
     * @return void
     */
    public function check_users() {
//        $this->telegram_integration->kick_expired_users();
    }
}
