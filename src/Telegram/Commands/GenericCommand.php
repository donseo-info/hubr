<?php

namespace WPShop\WPCommunity\Telegram\Commands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class GenericCommand extends SystemCommand {

    /**
     * @var string
     */
    protected $name = 'generic';

    /**
     * @var string
     */
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse {
        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();

        // To enable proper use of the /whois command.
        // If the user is an admin and the command is in the format "/whoisXYZ", call the /whois command
        if ( stripos( $command, 'whois' ) === 0 && $this->telegram->isAdmin( $user_id ) ) {
            return $this->telegram->executeCommand( 'whois' );
        }

        return $this->replyToChat( "Command /{$command} not found.. :(" );
    }
}
