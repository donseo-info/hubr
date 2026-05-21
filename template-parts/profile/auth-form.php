<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Auth;
use function WPShop\WPCommunity\get_privacy_policy_text;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

$user_login = isset( $user_login ) ? $user_login : '';
$user_email = isset( $user_email ) ? $user_email : '';

$auth     = theme_container()->get( Auth::class );
$settings = theme_container()->get( Settings::class );

$page_profile = get_the_permalink( get_setting( 'page.profile' ) );
$redirect_to  = get_the_permalink();
$errors       = [];
$messages     = [];

if ( ! empty( $_GET['redirect_to'] ) ) {
	$redirect_to = urldecode( $_GET['redirect_to'] );
}

if ( ! empty( $_GET['action'] ) && $_GET['action'] == 'reset_password' ) {

	$get_key   = ! empty( $_GET['key'] ) ? $_GET['key'] : '';
	$get_login = ! empty( $_GET['login'] ) ? $_GET['login'] : '';

	// проверяем ключ
	$check_reset_key = $auth->check_reset_key( $get_key, $get_login );
	if ( is_wp_error( $check_reset_key ) ) {
		$messages[] = $check_reset_key->get_error_message();
	}

}

if ( ! empty( $_GET['reset_password'] ) && $_GET['reset_password'] == 'success' ) {
    $messages[] = __( 'Password has been successfully updated.', 'wpcommunity' );
}
?>

<div class="post-card__header">
    <h1 class="post-card__title"><?php _e( 'Profile', 'wpcommunity' ) ?></h1>
</div>

<div class="post-card__content">
    <div class="auth-form-message js-auth-form-message">
	    <?php
	    foreach ( $messages as $message ) {
		    echo '<div>' . $message . '</div>';
	    }
	    ?>
    </div>

    <?php if ( ! empty( $_GET['action'] ) && $_GET['action'] == 'reset_password' ) : ?>

    <div class="auth-form js-auth-form-login">
        <h3><?php _e( 'Password Recovery', 'wpcommunity' ) ?></h3>

        <?php if ( empty( $errors ) ): ?>
        <form action="" method="post">
            <div class="auth-form__row">
                <label for="pass"><?php _e( 'Enter new password', 'wpcommunity' ) ?></label>
                <input type="password" name="pass" id="pass" class="input" value="" required>
            </div>

            <div class="auth-form__row">
                <button class="btn" type="submit"><?php _e( 'Set password', 'wpcommunity' ) ?></button>
                <a href="<?php echo $page_profile ?>" class="pseudo-link"><?php _e( 'Log in', 'wpcommunity' ) ?></a>
            </div>

            <input type="hidden" name="key" value="<?php esc_attr_e( $_GET['key'] ); ?>">
            <input type="hidden" name="login" value="<?php esc_attr_e( $_GET['login'] ); ?>">

            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ) ?>">
            <input type="hidden" name="action" value="new_password">
        </form>
        <?php endif; ?>
    </div>

    <?php else: ?>

    <div class="auth-form js-auth-form-login">
        <h3><?php _e( 'Log in', 'wpcommunity' ) ?></h3>
        <form action="" method="post">
            <div class="auth-form__row">
                <label for="email"><?php _e( 'Email', 'wpcommunity' ); ?></label>
                <input type="email" name="email" id="email" class="input" value="<?php echo esc_attr( wp_unslash( $user_email ) ); ?>" required>
            </div>
            <div class="auth-form__row">
                <label for="pass"><?php _e( 'Password', 'wpcommunity' ) ?></label>
                <span class="pseudo-link js-auth-form-open-forget"><?php _e( 'Forget?', 'wpcommunity' ) ?></span>
                <input type="password" name="pass" id="pass" class="input" value="" required>
            </div>
            <div class="auth-form__row">
                <label>
                    <input type="checkbox" name="rememberme" value="forever">
                    <?php esc_html_e( 'Remember Me' ) ?>
                </label>
            </div>

            <?php
            /**
             * @since 1.2
             */
            do_action('wpcommunity/auth_form/login');
            ?>

            <div class="auth-form__row">
                <button class="btn" type="submit"><?php _e( 'Log in', 'wpcommunity' ) ?></button>
                <span class="pseudo-link js-auth-form-open-register"><?php _e( 'Sign up', 'wpcommunity' ) ?></span>
            </div>


            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ) ?>">
            <input type="hidden" name="action" value="login">
        </form>
    </div>

    <div class="auth-form js-auth-form-register" style="display: none;">
        <h3><?php _e( 'Sign up', 'wpcommunity' ) ?></h3>
        <form action="" method="post" data-name="sign_up">
            <div class="auth-form__row">
                <label for="username"><?php _e( 'User name', 'wpcommunity' ); ?></label>
                <input type="text" name="username" id="username" class="input" value="<?php echo esc_attr( wp_unslash( $user_login ) ); ?>" required>
                <div class="auth-form__description">
                    <?php _e( 'Used in your profile address and @user_name mentions. Only:', 'wpcommunity' ) ?> <code>a-z._-</code>
                </div>
            </div>
            <div class="auth-form__row">
                <label for="email"><?php _e( 'Email', 'wpcommunity' ); ?></label>
                <input type="email" name="email" id="email" class="input" value="<?php echo esc_attr( wp_unslash( $user_email ) ); ?>" required>
            </div>
            <div class="auth-form__row">
                <label for="pass"><?php _e( 'Password', 'wpcommunity' ) ?></label>
                <input type="password" name="pass" id="pass" class="input" value="" required>
                <div class="auth-form__description"><?php _e( 'Password must be at least 8 characters long.', 'wpcommunity' ) ?></div>
            </div>
            <?php if ( $settings->get_value( 'registration.require_invite' ) ): ?>
                <div class="auth-form__row">
                    <label for="invite"><?php _e( 'Invite', 'wpcommunity' ) ?></label>
                    <?php /*<em>(<?php _e( 'optional', 'wpcommunity' ) ?>)</em>*/ ?>
                    <input type="text" name="invite" id="invite" class="input" value="" required>
                </div>
            <?php endif ?>

            <div class="auth-form__row js-wpcommunity-register-acceptance">
                <label>
                    <input type="checkbox" name="acceptance" value="1">
                    <?php if ( $privacy_policy = get_privacy_policy_text() ): ?>
                        <?php echo $privacy_policy ?>
                    <?php else: ?>
                        <?php echo __( 'Accept privacy policy', 'wpcommunity' ) ?>
                    <?php endif ?>
                </label>

                <?php
                /**
                 * @since 1.2
                 */
                do_action('wpcommunity/auth_form/after_acceptance');
                ?>
            </div>

            <?php
            /**
             * @since 1.2
             */
            do_action('wpcommunity/auth_form/register');
            ?>

            <div class="auth-form__row">
                <button class="btn js-wpcommunity-sign-up" type="submit" disabled><?php _e( 'Sign up', 'wpcommunity' ) ?></button>
                <span class="pseudo-link js-auth-form-open-login"><?php _e( 'Log in', 'wpcommunity' ) ?></span>
            </div>

            <script>
                (function () {
                    var inputs = [];
                    function updateDisabled() {
                        var disabled = !!inputs.length;
                        document.querySelectorAll('.js-wpcommunity-sign-up').forEach(function (el) {
                            el.disabled = disabled;
                        });
                    }
                    document.querySelectorAll('.js-wpcommunity-register-acceptance input[type="checkbox"]').forEach(function (input) {
                        input.addEventListener('change', function () {
                            if (!input.checked) {
                                inputs.push({name: input.name})
                            } else {
                                inputs = inputs.filter(function (item) {
                                    return item.name !== input.name;
                                });
                            }

                            updateDisabled();
                        });
                    });
                }());
            </script>

            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ) ?>">
            <input type="hidden" name="action" value="register">
        </form>
    </div>

    <div class="auth-form js-auth-form-forget" style="display: none;">
        <h3><?php _e( 'Forget password?', 'wpcommunity' ) ?></h3>
        <form action="" method="post">
            <div class="auth-form__row">
                <label for="email"><?php _e( 'Email', 'wpcommunity' ); ?></label>
                <input type="email" name="email" id="email" class="input" value="<?php echo esc_attr( wp_unslash( $user_email ) ); ?>">
            </div>

            <div class="auth-form__row">
                <button class="btn" type="submit"><?php _e( 'Reset', 'wpcommunity' ) ?></button>
                <span class="pseudo-link js-auth-form-open-login"><?php _e( 'Log in', 'wpcommunity' ) ?></span>
                <span class="pseudo-link js-auth-form-open-register"><?php _e( 'Sign up', 'wpcommunity' ) ?></span>
            </div>

            <input type="hidden" name="action" value="forget">
        </form>
    </div>

    <?php endif; ?>

</div>
