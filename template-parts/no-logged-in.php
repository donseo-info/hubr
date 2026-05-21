<?php

use function WPShop\WPCommunity\get_setting;

$profile_link = add_query_arg([
    'redirect_to' => urlencode( get_the_permalink() ),
], get_the_permalink( get_setting( 'page.profile' ) ));

?>
<div class="no-access">
	<div class="no-access__icon">🔎</div>
    <div class="no-access__title"><?php echo __( 'Authorization required', 'wpcommunity' ) ?></div>
    <p><?php _e( 'Sorry, you dont have permission to this part.', 'wpcommunity' ) ?></p>
    <a href="<?php echo $profile_link ?>"><?php _e( 'Log in', 'wpcommunity' ) ?></a>
</div>
