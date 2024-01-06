<?php

namespace App\Consumer;


use App\Event\TaskStatusTransitionEvent;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsRemoteEventConsumer(name: 'telegram')]
class TaskStatusTransitionEventConsumer implements ConsumerInterface
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private WorkflowInterface $taskStateMachine,
        private ChatterInterface $chatter,
    )
    {

    }
    public function consume(RemoteEvent $event): void
    {
        if (!$event instanceof TaskStatusTransitionEvent) {
            throw new RuntimeException('Not supported event type');
        }

        $task = $this->taskRepository->find($event->taskId);

        if (!$this->taskStateMachine->can($task, $event->transition)) {
            $this->logger->error(
                'Status transition is not allowed',
                [
                    'id' => $event->taskId,
                    'transition' => $event->transition,
                ],
            );

            return;
        }

        $this->taskStateMachine->apply($task, $event->transition);
        $this->em->flush();

        $telegramOptions = (new TelegramOptions())
            ->edit($event->messageId);
        $message = new ChatMessage("{$task->getTitle()} - {$task->getStatusAsString()}", $telegramOptions);

        $this->chatter->send($message);
    }
}