<?php

namespace App\Event;

use Symfony\Component\RemoteEvent\RemoteEvent;

class TaskStatusTransitionEvent extends RemoteEvent
{
    public function __construct(
        public readonly int $messageId,
        public readonly int $taskId,
        public readonly string $transition
    ) {
        parent::__construct('telegram.status_transition.event', "{$taskId}_$transition", []);
    }

}