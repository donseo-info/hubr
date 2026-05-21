<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\PaymentProviders\RecurringPayments;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

$membership        = theme_container()->get( Membership::class );
$recurring_payment = theme_container()->get( RecurringPayments::class );

$page_join = get_setting( 'page.join' );

?>

<div class="profile-form__row">
    <div class="profile-form__label">
        <?php echo __( 'Active for up', 'wpcommunity' ) ?>
    </div>
    <div class="profile-form__body">
        <div class="form-text">
            <?php
            $expired_date = $membership->get_expired_date( get_current_user_id() );
            $expired_days = $membership->get_expired_days( get_current_user_id() );
            //                var_dump($expired_date);
            //                var_dump($expired_days);

            // если уже была подписка
            if ( $expired_date ) {

                $date = $membership->get_expired_date( get_current_user_id() );
                $days = $membership->get_expired_days( get_current_user_id() );
                // если активна
                if ( $membership->is_member( get_current_user_id() ) ) {
                    echo sprintf( _n( 'to %s, day: %d', 'to %s, days: %d', $days, 'wpcommunity' ), $date, $days );
                } else {
                    echo __( 'Expired', 'wpcommunity' ) . ' ' . $expired_date . '. ';
                    if ( ! empty( $page_join ) ) {
                        echo '<a href="' . get_the_permalink( $page_join ) . '">' . __( 'Renew', 'wpcommunity' ) . '</a>';
                    }
                }

            } else {
                echo __( 'Not active.', 'wpcommunity' ) . ' ';

                // todo тут написать: Без подписки вы лишаетесь ... вот преимущества подписки

                if ( ! empty( $page_join ) ) {
                    echo '<a href="' . get_the_permalink( $page_join ) . '">' . __( 'Buy', 'wpcommunity' ) . '</a>';
                }
            }


            //			    if ( $membership->is_member( get_current_user_id() ) ) {
            //				    echo 'до ';
            //				    echo $membership->get_expired_date( get_current_user_id() );
            //				    echo ', дней: ';
            //				    echo $membership->get_expired_days( get_current_user_id() );
            //			    } else {
            //				    echo 'Не активна. ';
            //				    if ( ! empty( $page_join ) ) {
            //					    echo '<a href="' . get_the_permalink( $page_join ) . '">Купить</a>';
            //				    }
            //			    }
            ?>
        </div>
    </div>
</div>

<form action="" method="post" class="js-profile-invite-form">
    <div class="profile-form__row">
        <div class="profile-form__label">
            <?php echo esc_html__( 'Invite', 'wpcommunity' ) ?>
        </div>
        <div class="profile-form__body">
            <input type="text" class="input js-profile-invite" value="" required>
        </div>
    </div>

    <div class="profile-form__row">
        <div class="profile-form__label">

        </div>
        <div class="profile-form__body">
            <button type="submit" class="btn"><?php echo esc_html__( 'Activate Invite', 'wpcommunity' ) ?></button>
        </div>
    </div>
</form>


<?php if ( $recurring_dates = $recurring_payment->get_recurring_dates( get_current_user_id() ) ): ?>
    <div class="profile-form__row">
        <div class="profile-form__label"><?php echo __( 'Autopayment', 'wpcommunity' ) ?></div>
        <div class="profile-form__body">
            <div class="form-text">
                <?php if ( count( $recurring_dates ) > 1 ): ?>
                    <div><?php echo esc_html__( 'Dates of the next automatic debit', 'wpcommunity' ) ?>:</div>
                    <ul class="">
                        <?php foreach ( $recurring_dates as $row ): ?>
                            <?php [ $order_id, $date ] = $row ?>
                            <li class="js-wpcommunity-recurring-item">
                                <?php echo $date ?>
                                <a href="#" data-order_id="<?php echo esc_attr( $order_id ) ?>" class="js-wpcommunity-recurring-cancel">
                                    <?php echo esc_html__( 'Cancel', 'wpcommunity' ) ?>
                                </a>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php else: ?>
                    <div><?php echo esc_html__( 'Date of the next automatic debit', 'wpcommunity' ) ?>:</div>
                    <?php [ $order_id, $date ] = $recurring_dates[0] ?>
                    <div class="js-wpcommunity-recurring-item">
                        <?php echo $date ?>
                        <a href="#" data-order_id="<?php echo esc_attr( $order_id ) ?>" class="js-wpcommunity-recurring-cancel">
                            <?php echo esc_html__( 'Cancel', 'wpcommunity' ) ?>
                        </a>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
<?php endif ?>
