<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Telegram\TelegramIntegration;
use WPShop\WPCommunity\User;
use function WPShop\WPCommunity\theme_container;

$membership        = theme_container()->get( Membership::class );
$telegram_bot      = theme_container()->get( TelegramIntegration::class );
$telegram_username = get_user_meta( get_current_user_id(), User::USER_META_TELEGRAM_USERNAME, true );
$telegram_user_id  = get_user_meta( get_current_user_id(), User::USER_META_TELEGRAM_USER_ID, true );
?>

<div class="profile-form__row">
    <div class="profile-form__label">
        <?php echo __( 'Telegram bot', 'wpcommunity' ) ?>
    </div>
    <div class="profile-form__body">
        <div class="form-text">
            <?php if ( $bot_start_link = $telegram_bot->get_bot_start_link( get_current_user_id() ) ): ?>
                <?php
                if ( $membership->is_member( get_current_user_id() ) ) {
                    if ( ! empty( $telegram_user_id ) ) {
                        echo '✅ ' . __( 'Bot is bound to @', 'wpcommunity' ) . ( $telegram_username ?: '-- not found --' );
                        ?>
                        <p><?php printf(
                                __( 'If for some reason you are unable to access the chat, try <a href=“%s” target=“_blank”>bind</a> the bot again.', 'wpcommunity' ),
                                esc_attr( $bot_start_link )
                            ) ?></p>
                        <?php
                    } else {
                        echo '<a href="' . esc_attr( $bot_start_link ) . '" target="_blank">' . __( 'Bind', 'wpcommunity' ) . '</a>';
                    }
                } else {
                    echo __( 'Without subscription the bot does not work', 'wpcommunity' );
                }
                ?>
                <div>
                    <?php echo __( 'To get access to the closed Telegram chat - bind the bot. It will give you a link to join.', 'wpcommunity' ) ?>
                </div>
            <?php else: ?>
                <div><?php echo __( 'Closed Telegram chat has not been created yet', 'wpcommunity' ) ?></div>
            <?php endif ?>
        </div>
    </div>
</div>
