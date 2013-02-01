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

use PhpAmqpLib\Message\AMQPMessage;

use Mopsy\Connection;
use Mopsy\Message;
use Mopsy\Channel\Options;
use Mopsy\Producer;

use InvalidArgumentException;

class Consumer extends Connection
{
    /**
     *
     * @var string|array
     */
    protected $callback;

    /**
     *
     * @var int
     */
    protected $consumed = 0;

    /**
     *
     * @var int
     */
    protected $target = 0;

    /**
     *
     * @var string
     */
    private $deadLetterExchange = null;

    /**
     *
     * @var string
     */
    private $deadLetterRoutingKey = null;

    /**
     * The maximum number of times a failed message will be retried before being
     * dead-lettered
     *
     * @var int
     */
    private $maxRetries = 5;

    /**
     *
     * @param Container $container
     * @param AMQPConnection $connection
     * @param AMQPChannel $channel
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public static function getInstance(Container $container,
        AMQPConnection $connection, AMQPChannel $channel = null)
    {
        return new self($container, $connection, $channel);
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    protected function initialize()
    {
        $this->channel->exchange_declare($this->exchangeOptions->getName(),
            $this->exchangeOptions->getType(),
            $this->exchangeOptions->getPassive(),
            $this->exchangeOptions->getDurable(),
            $this->exchangeOptions->getAutoDelete(),
            $this->exchangeOptions->getInternal(),
            $this->exchangeOptions->getNowait(),
            $this->exchangeOptions->getArguments(),
            $this->exchangeOptions->getTicket());

        $queue = $this->channel->queue_declare($this->queueOptions->getName(),
            $this->queueOptions->getPassive(),
            $this->queueOptions->getDurable(),
            $this->queueOptions->getExclusive(),
            $this->queueOptions->getAutoDelete(),
            $this->queueOptions->getNowait(),
            $this->queueOptions->getArguments(),
            $this->queueOptions->getTicket());

        if(!empty($queue)) {
            $queueName = array_shift($queue);
        }
        else {
            $queueName = $this->queueOptions->getName();
        }

        $this->channel->queue_bind($queueName, $this->exchangeOptions->getName(),
            $this->routingKey);

        $this->channel->basic_consume($queueName,
            $this->getConsumerTag(),
            false,
            false,
            false,
            false,
            array($this, 'processMessage'));

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
     *
     * @return number
     */
    public function getConsumed()
    {
        return $this->consumed;
    }

    /**
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
            'x-dead-letter-exchange' => $exchangeName,
        );

        $newArgs = array_intersect($this->queueOptions->getArguments(), $args);
        $this->queueOptions->setArguments($newArgs);
        return $this;
    }

    /**
     * Sets the routing key to be used when dead-lettering messages.
     *
     * @internal If this is not set, the message's own routing keys will be used.
     *
     * @param string $routingKey
     *
     * @return \Mopsy\Consumer - Provides fluent interface
     */
    public function setDeadLetterRoutingKey($routingKey)
    {
        $this->deadLetterRoutingKey = $routingKey;

        $args = array(
            'x-dead-letter-routing-key' => $routingKey,
        );

        $newArgs = array_intersect($this->queueOptions->getArguments(), $args);
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
    public function consume($msgAmount)
    {
        $this->target = $msgAmount;

        $this->initialize();

        while(count($this->channel->callbacks))
        {
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
            try {
                // Retrieve the retry count from the message
                $headers = $msg->get('application_headers');
                $retryCount = $headers['x-retry_count'][1];
            }
            catch(\OutOfBoundsException $e) {
                $retryCount = 0;
            }

            // Increment the retry count
            $retryCount++;

            // Update the message headers with the new retry count
            $msg->set('application_headers', array(
                'x-retry_count' => array('I', $retryCount),
            ));

            // Execute the callback function
            $return = call_user_func($this->callback, $msg);

            if($return === false) {
                // Message consumption failed, handle retry logic
                if($retryCount > $this->maxRetries) {
                    // TODO - Dead letter the message
                    die('TIME TO DEAD-LETTER THIS MESSAGE, retry count = '.$retryCount);
                }
                else {

                    // Acknowledge the message
                    $msg->getChannel()->basic_ack($msg->getDeliveryTag());

                    /*
                     * Republish the message to the exchange
                     */
                    $producer = new Producer($this->getContainer(),
                        $this->getConfiguration(),
                        $msg->getChannel());
                    $producer->setExchangeOptions($msg->getConsumer()->getExchangeOptions());
                    $producer->publish($msg);

                    /*
                     * Rejecting is not what we want, because the message stays
                     * where it was in the queue, and we can't modify the
                     * message to update the retry count.  We want to republish
                     * the message so it gets appended to the end of the queue
                     */
                }

            }
            else {
                $msg->getChannel()->basic_ack($msg->getDeliveryTag());
                $this->consumed++;
                //$this->maybeStopConsumer($msg);
            }

        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    /**
     *
     * @param Mopsy\Message $msg
     */
    protected function maybeStopConsumer(Mopsy\Message $msg)
    {
        if($this->consumed == $this->target)
        {
            $msg->getChannel()->basic_cancel($msg->getConsumerTag());
        }
    }
}
