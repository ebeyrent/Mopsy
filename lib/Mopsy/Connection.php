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

use Mopsy\Container;
use Mopsy\Channel\Options;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use InvalidArgumentException;

class Connection
{
    /**
     *
     * @var AMQPConnection
     */
    protected $connection;


    /**
     *
     * @var AMQPChannel
     */
    protected $channel;

    /**
     *
     * @var string
     */
    protected $consumerTag = null;

    /**
     *
     * @var string
     */
    protected $callback;

    /**
     *
     * @var string
     */
    protected $routingKey = '';

    /**
     *
     * @var Mopsy\Channel\Options
     */
    protected $exchangeOptions;

    /**
     *
     * @var Mopsy\Channel\Options
     */
    protected $queueOptions;

    /**
     * Class constructor
     *
     * @param Mopsy\Container $container
     * @param AMQPConnection $connection
     * @param AMQPChannel|null $channel
     */
    public function __construct(Container $container,
        AMQPConnection $connection,
        AMQPChannel $channel = null)
    {
        $this->connection = $connection;
        $this->channel = empty($channel) ? $this->connection->channel() : $channel;

        $container->set('AMQPConnection', $this->connection);
        $container->set('AMQPChannel', $this->channel);

        $this->exchangeOptions = $container->newInstance('Mopsy\Channel\Options');
        $this->queueOptions = $container->newInstance('Mopsy\Channel\Options');
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        /**
        try {
            $this->channel->close();
            $this->connection->close();
        }
        catch(Exception $e) {}
        **/

        $this->connection = null;
        $this->channel = null;
        $this->consumerTag = null;
    }

    /**
     * Static Initializer
     *
     * @param Mopsy\Container $container
     * @param AMQPConnection $connection
     * @param AMQPChannel $channel
     *
     * @return \Mopsy\Connection
     */
    public static function getInstance(Container $container,
        AMQPConnection $connection, AMQPChannel $channel = null)
    {
        return new self($container, $connection, $channel);
    }

    /**
     *
     * @return \Mopsy\Connection
     */
    public function enableDebug()
    {
        $this->channel->enableDebug();
        return $this;
    }

    /**
     *
     * @return \Mopsy\Connection
     */
    public function disableDebug()
    {
        $this->channel->disableDebug();
        return $this;
    }

    /**
     *
     * @param Options $options
     * @throws InvalidArgumentException
     * @return \Mopsy\Connection
     */
    public function setExchangeOptions(Options $options)
    {
        $name = $options->getName();
        $type = $options->getType();

        if(empty($name))
        {
            throw new InvalidArgumentException('You must provide an exchange name');
        }

        if(empty($type))
        {
            throw new InvalidArgumentException('You must provide an exchange type');
        }

        $this->exchangeOptions = $options;
        return $this;
    }

    /**
     *
     * @param unknown_type $options
     * @return \Mopsy\Connection
     */
    public function setQueueOptions($options)
    {
        $this->queueOptions = $options;
        return $this;
    }

    /**
     *
     * @param string $routingKey
     * @return \Mopsy\Connection
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    /**
     *
     *
     * @return string
     */
    protected function getConsumerTag()
    {
        if(empty($this->consumerTag)) {
            return sprintf("PHPPROCESS_%s_%s", gethostname(), getmypid());
        }

        return $this->consumerTag.'_'.getmypid();
    }

    /**
     *
     * @param string $consumerTag
     *
     * @return \Mopsy\Connection
     */
    public function setConsumerTag($consumerTag)
    {
        $this->consumerTag = $consumerTag;
        return $this;
    }

}