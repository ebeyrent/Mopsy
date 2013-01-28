<?php

/**
 * The MIT License
 *
 * Copyright (c) 2010 Alvaro Videla
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
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Producer extends Connection
{
    /**
     *
     * @var boolean
     */
    protected $exchangeReady = false;

    /**
    public function __construct(AMQPConnection $connection,
        AMQPChannel $channel = null, $consumerTag = null) {
        parent::__construct($connection, $channel, $consumerTag);
    }
    **/

    /**
     *
     * @param Container $container
     * @param AMQPConnection $connection
     * @param AMQPChannel $channel
     * @return \Mopsy\Producer
     */
    public static function getInstance(Container $container,
        AMQPConnection $connection, AMQPChannel $channel = null)
    {
        return new self($container, $connection, $channel);
    }

    /**
     * Publish a message to RabbitMQ
     *
     * @param AMQPMessage $message
     *
     * @return \Mopsy\Producer
     */
    public function publish(Message $message)
    {
        if (!$this->exchangeReady) {
            $this->channel->exchange_declare($this->exchangeOptions->getName(),
                $this->exchangeOptions->getType(),
                $this->exchangeOptions->getPassive(),
                $this->exchangeOptions->getDurable(),
                $this->exchangeOptions->getAutoDelete(),
                $this->exchangeOptions->getInternal(),
                $this->exchangeOptions->getNowait(),
                $this->exchangeOptions->getArguments(),
                $this->exchangeOptions->getTicket());

            $this->exchangeReady = true;
        }

        $this->channel->basic_publish($message,
            $this->exchangeOptions->getName(), $this->routingKey,$mandatory,
                $immediate, $ticket);
        return $this;
    }
}