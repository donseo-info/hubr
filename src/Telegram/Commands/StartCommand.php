<?php

namespace WPShop\WPCommunity\Telegram\Commands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use WPShop\WPCommunity\Telegram\TelegramIntegration;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class StartCommand extends SystemCommand {

    use BlogInfoTrait;

    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @inheridoc
     * @return ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute() {
        // If you use deep-linking, get the parameter like this:
        $deep_linking_parameter = $this->getMessage()->getText( true );

        $tg           = theme_container()->get( TelegramIntegration::class );
        $user         = $tg->get_user_by_telegram_user_id( $this->getMessage()->getFrom()->getId() );
        $deep_linking = $tg->check_deep_linking_token( $deep_linking_parameter, $this->getMessage()->getFrom()->getId(), $this->getMessage()->getFrom()->getUsername() );

        $message = '';
        $message .= __( 'Hi', 'wpcommunity' ) . ' 👋' . PHP_EOL . PHP_EOL;
        $message .= __( 'I\'m a bot in a closed community', 'wpcommunity' );
        $message .= ' ' . $this->get_blogname() . '. ';
        $message .= __( 'My tasks include various notifications and messages about replies, rewards and subscription expiration.', 'wpcommunity' ) . PHP_EOL . PHP_EOL;

        //createChatInviteLink
        //$this->getTelegram()->executeCommand('createChatInviteLink');

        if ( ! empty( $user ) || $deep_linking ) {
            $invite_link_request = Request::execute( 'createChatInviteLink', [
                'chat_id'      => get_setting( 'telegram.target_channel' ),
                'name'         => sprintf( __( 'Invite for %s' ), $user->ID ),
                'expire_date'  => time() + ( 60 * 60 * 24 ),
                'member_limit' => 1,
            ] );

            if ( ! empty( $invite_link_request ) ) {
                $invite_link_decode = json_decode( $invite_link_request );
                if ( $invite_link_decode->ok ) {
                    $invite_link = $invite_link_decode->result->invite_link;
                }
            }
        }

        if ( ! empty( $user ) ) {
            $message .= '✅ ' . __( 'Your account is already linked to', 'wpcommunity' ) . ' ' . $this->get_blogname() . '.';
        } else {

            if ( $deep_linking ) {
                $message .= '✅ ' . __( 'Your account has been successfully linked.', 'wpcommunity' );

                if ( ! empty( $invite_link ) ) {
                    $message .= PHP_EOL . PHP_EOL . '➡ ' . __( 'Your invitation:', 'wpcommunity' ) . ' ' . $invite_link;
                }
            } else {
                $message .= sprintf( __( 'For me to be able to do all this I need to link your %s account and your telegram account.', 'wpcommunity' ), $this->get_blogname() ) . PHP_EOL;
                $message .= sprintf( __( 'To do this, go to <a href=“%s”>Account</a> and click on the “Link” link.', 'wpcommunity' ), $this->get_page_url( 'page.profile' ) ) . PHP_EOL;
            }

        }

        $message .= PHP_EOL . PHP_EOL . '/help ' . __( 'to view all commands.', 'wpcommunity' );

        return $this->replyToChat( $message );
    }
}
