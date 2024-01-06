<?php

namespace App\Command;

use App\Entity\Status;
use App\Repository\TaskRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsCommand(
    name: 'app:send-tasks',
    description: 'Sends tasks to the telegram',
)]
#[AsPeriodicTask('1 day', schedule: 'defult', from: '08:00 Europe/Kyiv')]
class SendTasksCommand extends Command
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly ChatterInterface $chatter
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tasks = $this->taskRepository->findBy(['status' => Status::New], ['created_at' => 'ASC'], 3);
        foreach ($tasks as $task) {
            $options = (new TelegramOptions())
                ->replyMarkup((new InlineKeyboardMarkup())
                    ->inlineKeyboard([
                        (new InlineKeyboardButton('To Done'))
                            ->callbackData(json_encode(['id' => $task->getId(), 'transition' => 'to_done'])),
                        (new InlineKeyboardButton('To Rejected'))
                            ->callbackData(json_encode(['id' => $task->getId(), 'transition' => 'to_rejected'])),
                    ])
                );
            $this->chatter->send(new ChatMessage($task->getTitle(), $options));
        }
        return Command::SUCCESS;
    }
}
