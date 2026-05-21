<?php

namespace WPShop\WPCommunity\Telegram\Commands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class NewchatmembersCommand extends SystemCommand {

    /**
     * @var string
     */
    protected $name = 'newchatmembers';

    /**
     * @var string
     */
    protected $description = 'New Chat Members';

    /**
     * @var string
     */
    protected $version = '1.3.0';

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse {
        $message = $this->getMessage();
        $members = $message->getNewChatMembers();

        if ( $message->botAddedInChat() ) {
            return $this->replyToChat( 'Hi there, you BOT!' );
        }

        $member_names = [];
        foreach ( $members as $member ) {
            $member_names[] = $member->tryMention();
        }

        return $this->replyToChat( 'Hi ' . implode( ', ', $member_names ) . '!' );
    }
}
