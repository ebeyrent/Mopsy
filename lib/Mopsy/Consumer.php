<?php

/**
 * The MIT License
 *
 * Copyright (c) 2013 Erich Beyrent
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 * @category Mopsy
 * @package Mopsy
 */

namespace Mopsy;

use Mopsy\Connection;
use Mopsy\Message;
use Mopsy\Channel\Options;
use Mopsy\Producer;

class Consumer extends Connection
{
    /**
     * Callback function to execute when processing messages
     * @var callable|string|array
     */
    protected $callback;

    /**
     * Tracks the number of messages processed
     * @var int
     */
    protected $consumed = 0;

    /**
     * Defines how many messages to process before shutting down
     * @var int
     */
    protected $target = 0;

    /**
     * The name of the dead-letter exchange
     * @var string
     */
    private $deadLetterExchange = null;

    /**
     * The name of the dead-letter routing key
     * @var string
     */
    private $deadLetterRoutingKey = null;

    /**
     * The maximum number of times a failed message will be retried before being
     * dead-lettered
     *
     * @var int
     */
    private $maxRetries = 1;

    /**
     *
     * @param Container $container
     * @param AMQPConnection $connection
     * @param AMQPChannel $channel
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public static function getInstance(
        Container $container,
        AMQPConnection $connection,
        AMQPChannel $channel = null
    ) {
        return new self($container, $connection, $channel);
    }

    /**
     * This method declares the exchange and queue and binds them together.
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    protected function initialize()
    {
        // Declare the exchange
        $this->channel->declareExchange($this->exchangeOptions);

        // Declare the queue
        $queueName = $this->channel->declareQueue($this->queueOptions);

        // Bind the queue to the exchange
        $this->channel->queue_bind(
            $queueName,
            $this->exchangeOptions->getName(),
            $this->routingKey
        );

        $this->initializeDeadLetterQueue();

        // Start consuming messages from the queue
        $this->channel->basic_consume(
            $queueName,
            $this->getConsumerTag(),
            false,
            false,
            false,
            false,
            array($this, 'processMessage')
        );

        return $this;
    }

    /**
     * This method declares the dead-letter exchange and queue and binds them
     * together.
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    protected function initializeDeadLetterQueue()
    {
        // If a dead-letter exchange has been set, declare the exchange
        if (!empty($this->deadLetterExchange)) {

            /* @var $options Options */
            $options = $this->container->newInstance('Mopsy\Channel\Options');
            $options->setName($this->deadLetterExchange)
                ->setType('topic')
                ->setDurable(true)
                ->setPassive(false)
                ->setAutoDelete(false);
            $this->channel->declareExchange($options);

            // If the dead-letter queue has been set, declare the queue
            if (!empty($this->deadLetterRoutingKey)) {

                /* @var $options Options */
                $options = $this->container->newInstance('Mopsy\Channel\Options');
                $options->setName($this->deadLetterRoutingKey)
                    ->setDurable(true)
                    ->setPassive(false)
                    ->setAutoDelete(false);
                $this->channel->declareQueue($options);
            }

            // Bind the dead letter queue to the exchange
            $this->channel->queue_bind(
                $this->deadLetterRoutingKey,
                $this->deadLetterExchange,
                $this->deadLetterRoutingKey
            );
        }
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Mopsy\Connection::setExchangeOptions()
     *
     * @return Mopsy\Consumer - Provides fluent interface
     */
    public function setExchangeOptions(Options $options)
    {
        parent::setExchangeOptions($options);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Mopsy\Connection::setQueueOptions()
     *
     * @return Mopsy\Consumer - Provides fluent interface
     */
    public function setQueueOptions(Options $options)
    {
        parent::setQueueOptions($options);
        return $this;
    }

    /**
     * Gets the number of messages that have been consumed
     *
     * @return number
     */
    public function getConsumed()
    {
        return $this->consumed;
    }

    /**
     * Gets the dead-letter routing key
     *
     * @return string
     */
    public function getDeadLetterRoutingKey()
    {
        return $this->deadLetterRoutingKey;
    }

    /**
     * Sets the callback function to execute when processing a message
     *
     * @param callable|string|array $callback
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Set the name of the exchange to use for the dead letter exchange
     *
     * @param string $exchangeName
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function setDeadLetterExchange($exchangeName)
    {
        $this->deadLetterExchange = $exchangeName;

        $args = array(
            'x-dead-letter-exchange' => array('S', $exchangeName),
        );

        $newArgs = array_merge($this->queueOptions->getArguments(), $args);
        $this->queueOptions->setArguments($newArgs);
        return $this;
    }

    /**
     * Sets the routing key to be used when dead-lettering messages. If this is
     * not set, the message's own routing keys will be used.
     *
     * @param string $routingKey
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function setDeadLetterRoutingKey($routingKey)
    {
        $this->deadLetterRoutingKey = $routingKey;

        $args = array(
            'x-dead-letter-routing-key' => array('S', $routingKey),
        );

        $newArgs = array_merge($this->queueOptions->getArguments(), $args);
        $this->queueOptions->setArguments($newArgs);
        return $this;
    }

    /**
     * Get the maximum number of times a failed message will be retried before
     * being dead-lettered
     *
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    /**
     * Set the maximum number of times a failed message will be retried before
     * being dead-lettered.
     *
     * @param int $maxRetries
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function setMaxRetries($maxRetries)
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    /**
     *
     * @param int $msgAmount
     */
    public function consume($msgAmount = null)
    {
        $this->target = $msgAmount;

        $this->initialize();

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * Uses defined callback functions to process a given message.
     *
     * @param Mopsy\Message $msg
     *
     * @throws Exception
     */
    public function processMessage(Message $msg)
    {
        try {
            
            // Retrieve the retry count from the message
            try {
                $headers = $msg->get('application_headers');
                $retryCount = $headers['x-retry_count'][1];
            } catch (\OutOfBoundsException $e) {
                $retryCount = 0;
            }

            // Increment the retry count
            $retryCount++;

            // Update the message headers with the new retry count
            $msg->set(
                'application_headers',
                array('x-retry_count' => array('I', $retryCount))
            );

            // Execute the callback function
            $return = call_user_func($this->callback, $msg);

            // Message consumption failed, handle retry logic
            if ($return === false) {
                if ($retryCount > $this->maxRetries) {
                    if (! empty($this->deadLetterExchange)) {
                        $this->deadLetterMessage($msg);
                    } else {
                        /*
                         *  Dead letter routing hasn't been configured, so just
                         *  reject the message.
                         */
                        $msg->getChannel()
                            ->basic_reject($msg->getDeliveryTag(), false);
                    }
                } else {
                    $this->republishMessage($msg);
                }
            } else {
                // Acknowledge the message as received
                $msg->getChannel()->basic_ack($msg->getDeliveryTag());
                $this->consumed++;

                /*
                 * If the consumer has processed the amount of messages
                 * alotted, shut it down.
                 */
                if ($this->consumed == $this->target) {
                    $msg->getChannel()->basic_cancel($msg->getConsumerTag());
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * This method takes a Message object and republishes it to the message's
     * exchange so that it can be reprocessed by the same or another consumer.
     *
     * @param Message $msg
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function republishMessage(Message $msg)
    {
        // Acknowledge the message
        $msg->getChannel()->basic_ack($msg->getDeliveryTag());
        $msg->delivery_info['redelivered'] = 1;

        /*
         * Republish the message to the exchange
         */
        $producer = new Producer(
            $this->getContainer(),
            $this->getConfiguration(),
            $msg->getChannel()
        );
        $producer->setExchangeOptions($msg->getConsumer()->getExchangeOptions());
        $producer->publish($msg);

        return $this;
    }

    /**
     * This method takes a Message object and acks it before publishing it to
     * the specified dead-letter exchange.  Simply rejecting the message does
     * not allow the message to be modified.
     *
     * @param Message $msg
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function deadLetterMessage(Message $msg)
    {
        $routingKey = $msg->getConsumer()->getRoutingKey();
        $deadLetterRoutingKey = $msg->getConsumer()
            ->getDeadLetterRoutingKey();

        if (!empty($deadLetterRoutingKey)) {
            $routingKey = $deadLetterRoutingKey;
        }

        /*
         * Add details to the message to explain why it was
         * dead-lettered
         */
        $headers = array(
            'queue' => array(
                'S',
                $msg->getConsumer()->getRoutingKey(),
            ),
            'reason' => array('S', 'rejected'),
            'time' => array('T', time()),
            'exchange' => array(
                'S',
                $msg->getConsumer()->getExchangeOptions()->getName(),
            ),
            'routing-keys' => array(
                'A',
                array(
                    $msg->getConsumer()->getRoutingKey(),
                    $deadLetterRoutingKey,
                ),
            ),
        );
        $msg->set(
            'application_headers',
            array('x-death' => array('A', array($headers)))
        );

        /*
         * Override the message expiration so that it doesn't
         * get dropped from the dead-letter exchange until it
         * has been processed.
         */
        $msg->set('expiration', 0);

        // Ack the message so it gets dropped from its queue
        $msg->getChannel()->basic_ack($msg->getDeliveryTag());

        // Publish the message to the dead-letter exchange
        $msg->getChannel()->basic_publish(
            $msg,
            $this->deadLetterExchange,
            $routingKey
        );

        return $this;
    }
}
