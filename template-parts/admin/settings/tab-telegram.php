<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;
use function WPShop\WPCommunity\transform_markdown_link;

/**
 * @var array{'label':string} $args
 */

$settings = theme_container()->get( Settings::class );

?>

<div class="wpcommunity-telegram-logo">
    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 240 240">
        <defs>
            <linearGradient id="linear-gradient" x1="120" y1="240" x2="120" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#1d93d2"/>
                <stop offset="1" stop-color="#38b0e3"/>
            </linearGradient>
        </defs>
        <circle cx="120" cy="120" r="120" fill="url(#linear-gradient)"/>
        <path d="M81.229,128.772l14.237,39.406s1.78,3.687,3.686,3.687,30.255-29.492,30.255-29.492l31.525-60.89L81.737,118.6Z" fill="#c8daea"/>
        <path d="M100.106,138.878l-2.733,29.046s-1.144,8.9,7.754,0,17.415-15.763,17.415-15.763" fill="#a9c6d8"/>
        <path d="M81.486,130.178,52.2,120.636s-3.5-1.42-2.373-4.64c.232-.664.7-1.229,2.1-2.2,6.489-4.523,120.106-45.36,120.106-45.36s3.208-1.081,5.1-.362a2.766,2.766,0,0,1,1.885,2.055,9.357,9.357,0,0,1,.254,2.585c-.009.752-.1,1.449-.169,2.542-.692,11.165-21.4,94.493-21.4,94.493s-1.239,4.876-5.678,5.043A8.13,8.13,0,0,1,146.1,172.5c-8.711-7.493-38.819-27.727-45.472-32.177a1.27,1.27,0,0,1-.546-.9c-.093-.469.417-1.05.417-1.05s52.426-46.6,53.821-51.492c.108-.379-.3-.566-.848-.4-3.482,1.281-63.844,39.4-70.506,43.607A3.21,3.21,0,0,1,81.486,130.178Z" fill="#fff"/>
    </svg>
</div>

<?php if ( ! empty( $settings->get_value( 'telegram.api_key' ) ) && ! empty( $settings->get_value( 'telegram.bot_username' ) ) ): ?>
    <div class="wpshop-settings-header">
        <?php $settings->render_header( __( 'Publishing', 'wpcommunity' ), null, $settings->doc_link( 'doc' ) . '/settings#telegram-bot' ); ?>
        <p>1. <?php echo esc_html__( 'Create a channel or group in Telegram.', 'wpcommunity' ) ?></p>
        <p>2. <?php printf(
                esc_html__( 'Add Bot (%s) as an administrator of your channel/group.', 'wpcommunity' ),
                '<code>@' . esc_html( $settings->get_value( 'telegram.bot_username' ) ) . '</code>'
            ) ?></p>
        <p>3. <?php echo transform_markdown_link( esc_html__( 'Get an ID using [@MyChatInfoBot](https://t.me/MyChatInfoBot) or [@IDBot](https://t.me/username_to_id_bot) and specify in the field below', 'wpcommunity' ) ) ?> </p>
    </div>

    <div class="wpshop-settings-form-row">
        <?php $settings->render_input( 'telegram.target_channel', __( 'Channel Or Group', 'wpcommunity' ) ); ?>
    </div>

    <div class="wpshop-settings-form-row">
        <?php
        $name  = 'telegram.post_message_tpl';
        $title = __( 'New Post Message', 'wpcommunity' );

        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        ?>
        <div class="wpshop-settings-form-row__label">
            <label for="<?php echo esc_attr( $args['id'] ) ?>"><?php echo $title ?></label>
        </div>
        <div class="wpshop-settings-form-row__body">
            <?php $settings->render_textarea_field( $name, $args ) ?>
            <div class="description">
                <p><?php echo __( 'Available tags you can find here', 'wpcommunity' ) ?> <a href="https://core.telegram.org/bots/api#html-style" target="_blank" rel="noopener">https://core.telegram.org/bots/api#html-style</a>.
                    <br><?php echo __( 'Important: If you use an illegal tag, the message will not be sent.', 'wpcommunity' ) ?>
                </p>
                <p><?php echo __( 'Available variables', 'wpcommunity' ) ?></p>
                <ul>
                    <li><code>{{post_link}}</code> <?php echo __( 'post link', 'wpcommunity' ) ?></li>
                    <li><code>{{title}}</code> <?php echo __( 'post title', 'wpcommunity' ) ?></li>
                    <li><code>{{excerpt}}</code> <?php echo __( 'post excerpt', 'wpcommunity' ) ?></li>
                    <li><code>{{author_link}}</code> <?php echo __( 'author link', 'wpcommunity' ) ?></li>
                    <li><code>{{author}}</code> <?php echo __( 'author name', 'wpcommunity' ) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="wpshop-settings-form-row">
        <?php $settings->render_checkbox( 'telegram.enable_notifications', __( 'Enable Notifications', 'wpcommunity' ) ); ?>
    </div>

    <div class="wpshop-settings-form-row">
        <?php $settings->render_checkbox( 'telegram.notify_private_only', __( 'Notify Only About Private Posts', 'wpcommunity' ) ); ?>
    </div>

    <div class="wpshop-settings-form-row">
        <?php $settings->render_checkbox( 'telegram.send_post_thumbnail', __( 'Send Post Thumbnail', 'wpcommunity' ) ); ?>
    </div>

    <div class="wpshop-settings-form-row">
        <?php $settings->render_checkbox(
            'telegram.auto_kick',
            __( 'Enable auto-kick', 'wpcommunity' ),
            [],
            __( 'Users with expired subscriptions will be automatically excluded from the group', 'wpcommunity' ) ); ?>
    </div>

    <div class="wpshop-settings-form-row">
        <?php $settings->render_input(
            'telegram.auto_kick_excluded',
            __( 'Prevent kick', 'wpcommunity' ),
            [],
            __( 'Id of users who will not be automatically excluded from the group, comma seperated values', 'wpcommunity' )
        ); ?>
    </div>
<?php endif ?>


<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Bot Settings', 'wpcommunity' ), null, $settings->doc_link( 'doc' ) . '/settings#telegram-bot' ); ?>
    <h3><?php echo esc_html__( 'Instruction', 'wpcommunity' ) ?></h3>
    <p>1. <?php printf(
            transform_markdown_link( esc_html__( ' Create a bot by sending %s command to [@BotFather](https://t.me/BotFather)', 'wpcommunity' ) ),
            '<code>/newbot</code>'
        )?></p>
    <p>2. <?php printf(
            esc_html__( 'After completing the %s steps, you will receive a token and a bot name. Copy these values and paste them into appropriate fields.', 'wpcommunity' ),
            '<code>@BotFather</code>'
        ) ?></p>
    <p>3. <?php echo esc_html__( 'Generate a webhook key. It is needed for secure processing of requests from telegram bot.', 'wpcommunity' ) ?></p>
    <p>4. <?php echo esc_html__( 'Save the settings.', 'wpcommunity' ) ?></p>
    <p>5. <?php printf( esc_html__( 'After saving the settings, if the %s and %s fields are filled in, the %s button will become available. Press it to activate the bot commands.', 'wpcommunity' ),
            '<code>' . esc_html__( 'Bot Token', 'wpcommunity' ) . '</code>',
            '<code>' . esc_html__( 'Bot Username', 'wpcommunity' ) . '</code>',
            '<code>' . esc_html__( 'Set Webhook', 'wpcommunity' ) . '</code>'
        ) ?></p>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'telegram.api_key', __( 'Bot Token', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'telegram.bot_username', __( 'Bot Username', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_webhook_input( 'telegram.webhook_key', __( 'Webhook Key', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <div>
        <button class="wpshop-settings-button js-wpcommunity-set-telegram-webhook"
            <?php disabled( empty( $settings->get_value( 'telegram.api_key' ) ) || empty( $settings->get_value( 'telegram.bot_username' ) ) ) ?>>
            <?php echo __( 'Set Webhook', 'wpcommunity' ) ?>
        </button>
    </div>
</div>


<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'telegram.enable_logs', __( 'Enable Logs', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'telegram.admin_ids', __( 'Admin IDs', 'wpcommunity' ) ); ?>
</div>


