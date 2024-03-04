<?php

namespace App\Command;

use App\WebSocket\ChatWebSocket;
use App\WebSocket\NotificationWebSocket;
use Ratchet\Server\IoServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'websocket:serve',
    description: 'Commande pour demarer le websocket',
)]
class WebSocketServerCommand extends Command
{
    private $chatWebSocket;

    public function __construct(
        ChatWebSocket $chatWebSocket
    ) {
        parent::__construct();
        $this->chatWebSocket = $chatWebSocket;
    }

    protected function configure(): void
    {
        $this->setName('websocket:serve')
            ->setDescription('Start the WebSocket server');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        $server = IoServer::factory($this->chatWebSocket, 8080);
        $output->writeln('WebSocket server started on port 8080.');
        $server->run();

        return Command::SUCCESS;
    }
}
