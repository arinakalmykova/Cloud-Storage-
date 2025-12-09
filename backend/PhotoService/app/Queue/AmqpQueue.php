<?php

namespace App\Queue;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue as AmqpQueueInterface;
use Interop\Amqp\AmqpMessage;

class AmqpQueue extends Queue implements QueueContract
{
    public function __construct(
        protected AmqpContext $context,
        protected string $queueName
    ) {}

    public function size($queue = null)
    {
        return 0;
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $queue = $this->context->createQueue($queue ?: $this->queueName);

        $message = $this->context->createMessage($payload);
        $message->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);

        $this->context->createProducer()->send($queue, $message);

        return $message->getMessageId();
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->push($job, $data, $queue);
    }

    public function pop($queue = null)
    {
        $queue = $this->context->createQueue($queue ?: $this->queueName);

        $consumer = $this->context->createConsumer($queue);
        $message = $consumer->receive(1000); // 1 second timeout

        if ($message) {
          return new \App\Queue\Jobs\AmqpJob(
                $this->container,
                $this,
                $consumer,
                $message
            );
        }

        return null;
    }
}