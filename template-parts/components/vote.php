<?php
use WPShop\WPCommunity\Vote;
use function WPShop\WPCommunity\theme_container;

$vote = theme_container()->get( Vote::class );

$vote_attributes = '';
$vote_classes = '';

$can_user_vote = $vote->can_user_vote( $post->post_author );
if ( is_wp_error( $can_user_vote ) ) {
    if ( $can_user_vote->get_error_code() == 'login_required' ) {
	    $vote_attributes = ' data-tooltip="' . $can_user_vote->get_error_message() . '"';
    }
	$vote_classes = ' disabled';
}
$post_vote = $vote->get_vote_data( 'post', $post->ID );

$method = $vote->get_method();
$method_item = $vote->get_method_item();
?>
<div class="vote post-meta__vote js-vote-container<?php echo $vote_classes ?>" data-post-id="<?php the_ID() ?>" <?php echo $vote_attributes ?>>
	<div class="vote__minus js-vote-minus<?php if ( isset( $post_vote[ $method ][ $method_item ] ) && $post_vote[ $method ][ $method_item ] < 0 ) echo ' active' ?>">
        <svg width="18" height="18"><use xlink:href="#ico-vote-minus"></use></svg>
	</div>
	<div class="vote__score js-vote-score">
		<?php echo $vote->get_vote_score( 'post', $post->ID ) ?>
	</div>
	<div class="vote__plus js-vote-plus<?php if ( isset( $post_vote[ $method ][ $method_item ] ) && $post_vote[ $method ][ $method_item ] > 0 ) echo ' active' ?>">
        <svg width="18" height="18"><use xlink:href="#ico-vote-plus"></use></svg>
	</div>
</div>