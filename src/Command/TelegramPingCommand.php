<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

#[AsCommand(
    name: 'telegram:ping',
    description: 'Ping telegram',
)]
class TelegramPingCommand extends Command
{
    public function __construct(private readonly NotifierInterface $notifier)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('text', InputArgument::REQUIRED, 'Text to send')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notification = new Notification($input->getArgument('text'), ['chat/telegram']);
        $this->notifier->send($notification);
        return Command::SUCCESS;
    }
}
