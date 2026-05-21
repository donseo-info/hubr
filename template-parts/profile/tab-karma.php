<?php

/**
 * @version 1.0
 */

use WPShop\WPCommunity\Features\Karma;
use function WPShop\WPCommunity\theme_container;

$karma = theme_container()->get( Karma::class );

?>
    <span class="karma" style="--karma-size: 1em;">
        <svg><use xlink:href="#ico-karma"></use></svg>
        <?php echo $karma->get_karma( get_current_user_id() ) ?>
    </span>

    <h3><?php echo esc_html__( 'What karma is awarded for', 'wpcommunity' ) ?></h3>
<?php
$karma_rate = $karma->get_karma_rate();
if ( ! empty( $karma_rate ) ) {
    echo '<div class="karma-rate">';
    foreach ( $karma_rate as $item ) {
        if ( ! $item['public'] ) {
            continue;
        }
        echo '<div class="karma-rate__item">';
        echo '<div class="karma-rate__points"><span class="karma">' . $item['direction'] . $item['points'] . '</span></div>';
        echo '<div class="karma-rate__name">' . $item['name'] . '</div>';
        echo '</div>';
    }
    echo '</div>';
}

?>

<?php
$karma_history = $karma->get_karma_history_beauty( get_current_user_id() );
if ( ! empty( $karma_history ) ) {
    echo '<h3>' . __( 'The history of karma change', 'wpcommunity' ) . '</h3>';
    echo '<div class="karma-history">';

    $n = 0;
    foreach ( $karma_history as $item ) {
        $n ++;
        echo '<div class="karma-history__item">';

        echo '<div class="karma-history__date">' . date( 'd.m.Y', $item['time'] ) . '<br><small>' . date( 'H:i', $item['time'] ) . '</small></div>';
        echo '<div class="karma-history__points">' . $item['points_text'] . '</div>';
        echo '<div class="karma-history__action">' . $item['action_text'] . '</div>';
        echo '<div class="karma-history__from">' . $item['from_text'] . '</div>';

        echo '</div>';

        if ( $n == 30 ) {
            break;
        }
    }
    echo '</div>';
}
