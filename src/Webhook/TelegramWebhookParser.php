<?php

namespace App\Webhook;



use App\Event\TaskStatusTransitionEvent;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;
use function PHPUnit\Framework\throwException;

class TelegramWebhookParser extends AbstractRequestParser
{

    protected function getRequestMatcher(): RequestMatcherInterface
    {
        // these define the conditions that the incoming webhook request
        // must match in order to be handled by this parser
        return new ChainRequestMatcher([
            new IsJsonRequestMatcher(),
            new MethodRequestMatcher('POST'),
        ]);
    }

    protected function doParse(Request $request, string $secret): ?TaskStatusTransitionEvent
    {
        try {
            $callback = $request->toArray()['callback_query'];
            $callbackData = json_decode($callback['data'], true);
            $messageId = $callback['message']['message_id'];

            return new TaskStatusTransitionEvent($messageId, $callbackData['id'], $callbackData['transition']);
        } catch (\Throwable) {
            throw new RejectWebhookException(Response::HTTP_NOT_ACCEPTABLE, 'Invalid payload');
        }

        return null;
    }
}