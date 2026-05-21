<?php

namespace WPShop\WPCommunity;

use WPShop\WPCommunity\PaymentProviders\RecurringPayments;
use WPShop\WPCommunity\Telegram\TelegramIntegration;

class Cron {

    const EVERY_5_MINUTES = 'every_5_minutes';

    /**
     * @var \string[][]
     */
    protected $events = [
        [ self::EVERY_5_MINUTES, 'wpcommunity/cron/notify_expired_members' ],
        [ 'hourly', 'wpcommunity/cron/handle_recurring_payments' ],
        [ 'hourly', 'wpcommunity/cron/notify_recurring_payments' ],
        [ self::EVERY_5_MINUTES, 'wpcommunity/cron/check_telegram_users' ],
    ];

    /**
     * @return void
     */
    public function init() {
        add_filter( 'cron_schedules', [ $this, '_add_schedules' ] );
        add_action( 'wp', [ $this, '_schedule_cron_job' ] );

        add_action( 'switch_theme', [ $this, 'clear' ] );

        $this->register_scheduled_actions();

//        add_action( 'init', function () {
//            wp_clear_scheduled_hook( 'wpcommunity/subscription/notify_expired' );
//        } );
    }

    /**
     * @return void
     */
    protected function register_scheduled_actions() {
        add_action( 'wpcommunity/cron/notify_expired_members', [
            theme_container()->get( Membership::class ),
            'notify_expired',
        ] );

        add_action( 'wpcommunity/cron/handle_recurring_payments', [
            theme_container()->get( RecurringPayments::class ),
            'create_scheduled_payments',
        ] );

        add_action( 'wpcommunity/cron/notify_recurring_payments', [
            theme_container()->get( RecurringPayments::class ),
            'notify_scheduled_payments',
        ] );

        add_action( 'wpcommunity/cron/check_telegram_users', [
            theme_container()->get( TelegramIntegration::class ),
            'kick_expired_users',
        ] );
    }

    /**
     * @return void
     */
    public function _schedule_cron_job() {
        foreach ( $this->events as $event ) {
            [ $recurrence, $hook ] = $event;
            if ( ! wp_next_scheduled( $hook ) ) {
                wp_schedule_event( time(), $recurrence, $hook );
            }
        }
    }

    /**
     * @param array $schedules
     *
     * @return array
     */
    public function _add_schedules( $schedules ) {
        $schedules[ self::EVERY_5_MINUTES ] = [
            'interval' => MINUTE_IN_SECONDS * 5,
            'display'  => __( 'Every 5 minutes', 'wpcommunity' ),
        ];

        return $schedules;
    }

    /**
     * @return void
     */
    public function clear() {
        foreach ( $this->events as $event ) {
            [ $recurrence, $hook ] = $event;
            if ( $timestamp = wp_next_scheduled( $hook ) ) {
                wp_unschedule_event( $timestamp, $hook );
            }
        }
    }
}
