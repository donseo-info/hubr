<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use function WPShop\WPCommunity\theme_container;
use function WPShop\WPCommunity\transform_markdown_link;

/**
 * @var array{'label':string} $args
 */

$settings = theme_container()->get( Settings::class );
//$settings = theme_container()->get( Settings::class );

?>

<?php if ( $settings->do_show_welcome() ): ?>
    <div class="wpshop-settings-welcome">
        <div class="wpshop-settings-welcome__close js-wpshop-settings-welcome-close"></div>
        <div class="wpshop-settings-welcome__icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M256 48c114.88 0 208 93.12 208 208s-93.12 208-208 208S48 370.88 48 256 141.12 48 256 48m0-48c-34.54 0-68.07 6.78-99.66 20.14-30.49 12.9-57.86 31.35-81.36 54.84-23.5 23.5-41.95 50.87-54.84 81.36C6.78 187.93 0 221.46 0 256s6.78 68.07 20.14 99.66c12.9 30.49 31.35 57.86 54.84 81.36 23.49 23.5 50.87 41.95 81.36 54.84C187.93 505.22 221.46 512 256 512s68.07-6.78 99.66-20.14c30.49-12.9 57.86-31.35 81.36-54.84 23.5-23.5 41.95-50.87 54.84-81.36C505.22 324.07 512 290.54 512 256s-6.78-68.07-20.14-99.66c-12.9-30.49-31.35-57.86-54.84-81.36-23.49-23.5-50.87-41.95-81.36-54.84C324.07 6.78 290.54 0 256 0Zm-23.27 361.24 149-149c9.37-9.37 9.37-24.57 0-33.94-9.37-9.37-24.57-9.37-33.94 0L215.76 310.33l-55.56-55.56c-9.37-9.37-24.57-9.37-33.94 0-9.37 9.37-9.37 24.57 0 33.94l72.53 72.53c4.69 4.69 10.83 7.03 16.97 7.03s12.28-2.34 16.97-7.03Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="wpshop-settings-welcome__header"><?php echo esc_html__( 'The theme has been successfully installed!', 'wpcommunity' ) ?></div>
        <div class="wpshop-settings-welcome__content">
            <p><?php echo esc_html__( 'You need to do a few additional actions to continue.', 'wpcommunity' ) ?></p>
            <p>1. <?php echo transform_markdown_link( esc_html__( 'Set up [standard pages](#tab-pages).', 'wpcommunity' ), 'js-wpcommunity-dashboard-link' ) ?></p>
            <p>2. <?php echo transform_markdown_link( esc_html__( 'Set up subscription [payment methods](#tab-payment).', 'wpcommunity' ), 'js-wpcommunity-dashboard-link' ) ?></p>
            <p>3. <?php echo esc_html__( 'Customize menu.', 'wpcommunity' ) ?></p>
            <p><?php echo esc_html__( 'If everything is ready, you can close this window.', 'wpcommunity' ) ?></p>
        </div>
    </div>
<?php endif ?>


<div class="wpshop-settings-top">
    <a class="wpshop-settings-box wpshop-settings-top__item wpshop-settings-top__item--blue" href="<?php echo $settings->doc_link( 'doc' ) ?>" target="_blank" rel="noopener">
        <div class="wpshop-settings-top__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 512 512">
                <path d="M474.1 100.7h-30.2v-43c0-4.9-4-8.9-8.9-8.9H277.6c-.1 0-.3 0-.4.1-.1 0-.3.1-.4.1-.1 0-.3.1-.4.1-.1 0-.3.1-.4.1-.1 0-.3.1-.4.1-.1.1-.3.1-.4.2-.1.1-.3.1-.4.2-.1.1-.3.1-.4.2-.1.1-.3.2-.4.2-.1.1-.2.1-.3.2-.1.1-.2.2-.4.3l-.3.3-.3.3-.3.3-.1.1L256 69.3l-16.2-17.6c-1.7-1.8-4.1-2.9-6.6-2.9H77c-4.9 0-8.9 4-8.9 8.9v43H37.9c-4.9 0-8.9 4-8.9 8.9v264c0 4.9 4 8.9 8.9 8.9h173c-.1.6-.2 1.2-.2 1.8v61h-60.9c-4.9 0-8.9 4-8.9 8.9s4 8.9 8.9 8.9h212.3c4.9 0 8.9-4 8.9-8.9s-4-8.9-8.9-8.9h-60.9v-61c0-.6-.1-1.2-.2-1.8h173c4.9 0 8.9-4 8.9-8.9v-264c.1-4.9-3.9-8.9-8.8-8.9zM85.9 306.6h143.5l18.1 19.7H85.9v-19.7zm196.8 0h143.5v19.7H264.6l18.1-19.7zm143.4-17.8H277.7c-.1 0-.3 0-.4.1-.2 0-.3.1-.5.1-.1 0-.3.1-.4.1-.2 0-.3.1-.5.1-.1 0-.3.1-.4.1-.1.1-.3.1-.4.2-.1.1-.3.1-.4.2-.1.1-.3.1-.4.2-.1.1-.3.2-.4.2-.1.1-.2.1-.3.2-.1.1-.2.2-.4.3l-.3.3-.3.3-.3.3-.1.1-8.4 9.1V87.2l18.8-20.5H426v222.1zM85.9 66.6h143.4L246 84.8v213.6l-6.2-6.7c-1.7-1.8-4.1-2.9-6.6-2.9H85.9V66.6zm197.5 317.7v61h-54.8v-61c0-.6-.1-1.2-.2-1.8h55.2c-.1.6-.2 1.2-.2 1.8zm181.8-19.6H46.8V118.5h21.3v180.1c-.1.4-.1.9-.1 1.4v35.2c0 4.9 4 8.9 8.9 8.9H435c4.9 0 8.9-4 8.9-8.9v-34.5c0-.6-.1-1.2-.2-1.8.1-.4.1-.8.1-1.3V118.5h21.3v246.2zM220.4 105.9c0 4.9-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9s4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9zm0 47.5c0 4.9-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9s4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9zm0 47.5c0 4.9-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9s4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9zm0 47.5c0 4.9-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9s4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9zm71.2-142.5c0-4.9 4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9s-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9zm0 47.5c0-4.9 4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9s-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9zm108.9 47.5c0 4.9-4 8.9-8.9 8.9h-91.1c-4.9 0-8.9-4-8.9-8.9s4-8.9 8.9-8.9h91.1c4.9 0 8.9 4 8.9 8.9z" tabindex="0" fill="#2d72a5"/>
            </svg>
        </div>
        <div class="wpshop-settings-top__body">
            <p><?php esc_html_e( 'Documentation', 'wpcommunity' ) ?></p>
            <?php echo __( 'If you have a question about our product, perhaps the answer is already in our documentation.', 'wpcommunity' ) ?>
        </div>
    </a>
    <a class="wpshop-settings-box wpshop-settings-top__item wpshop-settings-top__item--purple" href="<?php echo $settings->doc_link( 'faq' ) ?>" target="_blank" rel="noopener">
        <div class="wpshop-settings-top__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="50px" height="50px" viewBox="0 0 512 512">
                <path d="M223.5 143.6c0 16-9.9 24.2-17.8 30.7-7.7 6.4-13.8 11.4-13.8 23.2 0 4.8-3.9 8.7-8.7 8.7s-8.7-3.9-8.7-8.7c0-20 11.6-29.6 20.1-36.6 7.6-6.2 11.5-9.9 11.5-17.4 0-7.3-1.9-16.4-11.2-18.8-8.7-2.2-21.7 2.6-28.3 14.6-2.3 4.2-7.6 5.7-11.8 3.4-4.2-2.3-5.7-7.6-3.4-11.8 9.7-17.7 30.3-27.6 47.8-23.1 15.2 4 24.3 17.4 24.3 35.8zM184 225.1c-4.8-.5-9 2.9-9.5 7.7-.2 1.7-.3 3.4-.3 5.2 0 4.8 3.9 8.7 8.7 8.7s8.7-3.9 8.7-8.7c0-1.2.1-2.3.2-3.4.4-4.7-3-9-7.8-9.5zm191 16.4c-4.8-.5-9 2.9-9.6 7.7l-9.1 81.6c-.5 4.8 2.9 9 7.7 9.6.3 0 .6.1 1 .1 4.4 0 8.1-3.3 8.6-7.7l9.1-81.6c.5-4.9-3-9.2-7.7-9.7zm-10.9 117.7c-4.7-.8-9.2 2.4-10 7.1-.3 1.7-.5 3.4-.6 5.2-.3 4.8 3.3 8.9 8.1 9.2h.6c4.5 0 8.4-3.5 8.6-8.1.1-1.2.2-2.3.4-3.4.8-4.7-2.3-9.2-7.1-10zm110.4-53.6c0 75.6-65.1 137-145.2 137-13.8 0-27.5-1.8-40.6-5.4-13.9 13.3-23.8 19.4-49.7 33.4h-.1c-.3.1-.6.3-.9.4-.1 0-.2.1-.3.1-.2.1-.4.2-.7.2-.1 0-.2.1-.3.1-.2.1-.4.1-.7.1h-.3c-.3 0-.6.1-.9.1-.3 0-.7 0-1-.1h-.3c-.3 0-.5-.1-.8-.2-.1 0-.2 0-.2-.1-.7-.2-1.3-.5-1.9-.8-.1 0-.2-.1-.2-.1-.2-.1-.4-.3-.6-.4-.1-.1-.2-.1-.2-.2-.3-.2-.5-.4-.7-.7l-.2-.2c-.2-.2-.4-.4-.5-.6-.1-.1-.1-.2-.2-.2l-.6-.9v-.1c-.1-.2-.2-.5-.3-.7 0-.1-.1-.2-.1-.2-.1-.2-.1-.4-.2-.6 0-.1-.1-.3-.1-.4 0-.1-.1-.3-.1-.4 0-.2-.1-.4-.1-.6v-1.2l-.3-61.3c-24.7-23.7-39.2-54.2-41.4-87.2H183c-80.1 0-145.2-61.5-145.2-137s65.1-137 145.2-137c77 0 140.1 56.8 144.9 128.3.6-.1 1.2-.2 1.8-.2 79.7 0 144.8 61.5 144.8 137.1zm-291.8-8.5c13.8 0 27.5-2.1 40.5-6.1.8-.3 1.7-.4 2.6-.4 2.3 0 4.5.9 6.1 2.5 12.2 12.1 19.6 17.5 36.8 27.1l.2-50.6c0-2.4 1-4.7 2.8-6.4 25-22.7 38.8-53.2 38.8-85.9 0-66-57.3-119.7-127.8-119.7S54.9 111.3 54.9 177.3s57.3 119.8 127.8 119.8zm274.5 8.5c0-66-57.3-119.7-127.8-119.7-.6 0-1.2-.1-1.7-.2-2.1 33.2-16.6 64-41.4 87.8l-.3 61.3c0 3-1.6 5.9-4.2 7.4-1.4.8-2.9 1.2-4.4 1.2-1.4 0-2.8-.3-4.1-1-26-14-35.9-20.2-49.8-33.4-7.1 1.9-14.3 3.3-21.6 4.3 2 29.7 15.5 57.2 38.5 78.2 1.8 1.6 2.8 3.9 2.8 6.4l.2 50.5c17.2-9.6 24.6-15 36.8-27.1 2.3-2.3 5.6-3.1 8.7-2.1 13 4.1 26.6 6.1 40.5 6.1 70.4-.1 127.8-53.8 127.8-119.7zm-129.5 53.6c-4.7-.8-9.2 2.4-10 7.1-.3 1.7-.5 3.4-.6 5.2-.3 4.8 3.3 8.9 8.1 9.2h.5c4.5 0 8.4-3.5 8.6-8.1.1-1.2.2-2.3.4-3.3.9-4.8-2.3-9.3-7-10.1zm-36.5 0c-4.7-.8-9.2 2.4-10 7.1-.3 1.7-.5 3.4-.6 5.2-.3 4.8 3.3 8.9 8.1 9.2h.5c4.5 0 8.4-3.5 8.6-8.1.1-1.2.2-2.3.4-3.3.9-4.8-2.3-9.3-7-10.1z" fill="#4900b8"/>
            </svg>
        </div>
        <div class="wpshop-settings-top__body">
            <p><?php echo __( 'Questions and answers', 'wpcommunity' ) ?></p>
            <?php echo __( 'Section of frequently asked questions and answers to them. You can quickly find the answer to a question.', 'wpcommunity' ) ?>
        </div>
    </a>

</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'License', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-license">
    <div class="wpshop-settings-license-info">
        <div class="wpshop-settings-license-info__key">
            <div class="wpshop-settings-license__success">
                <?php echo __( 'The license is successfully activated.', 'wpcommunity' ) ?>
            </div>
            <?php echo __( 'The license key is hidden for security purposes.', 'wpcommunity' ) ?>
        </div>
        <div class="wpshop-settings-license-info__dashboard">
            <a href="https://wpshop.ru/dashboard" target="_blank" class="wpshop-settings-button"><?php echo __( 'Personal account', 'wpcommunity' ) ?></a>
            <?php if ( current_user_can( 'administrator' ) ): ?>
                <a href="#" class="js-wpshop-settings-remove-license" data-message="<?php echo esc_attr( __( 'Are you sure you want to remove the license key?', 'wpcommunity' ) ) ?>"><?php echo __( 'Remove License', 'wpcommunity' ) ?></a>
            <?php endif ?>
        </div>
    </div>
</div>
