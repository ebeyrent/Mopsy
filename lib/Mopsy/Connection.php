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

use Mopsy\Connection\Configuration;

use Mopsy\Container;
use Mopsy\Channel;
use Mopsy\Channel\Options;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use InvalidArgumentException;

class Connection
{
    /**
     *
     * @var Mopsy\Container
     */
    protected $container;

    /**
     *
     * @var AMQPConnection
     */
    protected $connection;

    /**
     *
     * @var Mopsy\Connection\Configuration
     */
    protected $configuration;

    /**
     *
     * @var Mopsy\Channel
     */
    protected $channel;

    /**
     *
     * @var array
     */
    protected $channels = array();

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
        Configuration $configuration,
        Channel $channel = null)
    {
        $this->container = $container;
        $this->configuration = $configuration;

        // Create a new instance of the AMQPConnection object
        $this->connection = $container->newInstance('PhpAmqpLib\Connection\AMQPConnection', array(
            $configuration->getHost(),
            $configuration->getPort(),
            $configuration->getUser(),
            $configuration->getPass(),
        ));

        if($channel === null) {
            //$this->channel = $this->connection->channel();
            $this->channel = $container->newInstance('Mopsy\Channel', array(
                $this,
                $this->connection->get_free_channel_id(),
                true,
            ));
        }
        else {
            $this->channel = $channel;
        }

        $this->channels[$this->channel->getChannelId()] = $this->channel;
        $this->connection->channels[$this->channel->getChannelId()] = $this->channel;

        $this->exchangeOptions = $container->newInstance('Mopsy\Channel\Options');
        $this->queueOptions = $container->newInstance('Mopsy\Channel\Options');

        /*
         * Queues will expire after 30 minutes of being unused, meaning it has
         * no consumers, has not been redeclared, and basic.get has not been
         * invoked.
         *
         * Messages will be discard from this queue if they haven't been
         * acknowledged after 60 seconds.
         */
        $this->queueOptions->setArguments(array(
            'x-message-ttl', 60000,
            'x-expires' => 1800000,
        ));
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
     * Static initializer
     *
     * @param Container $container
     * @param Configuration $configuration
     * @param Mopsy\Channel $channel
     *
     * @return \Mopsy\Connection
     */
    public static function getInstance(Container $container,
        Configuration $configuration,
        Channel $channel = null)
    {
        return new self($container, $configuration, $channel);
    }

    /**
     *
     * @param int $channelId
     *
     * @return \Mopsy\Channel
     */
    public function getChannel($channelId = null)
    {
        if($channelId === null) {
            return $this->channel;
        }
        return $this->channels[$channelId];
    }

    /**
     *
     * @return \Mopsy\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     *
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    public function getAMQPConnection()
    {
        return $this->connection;
    }

    /**
     *
     * @return \Mopsy\Connection\Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     *
     * @return \Mopsy\Connection - Provides fluent interface
     */
    public function enableDebug()
    {
        $this->channel->enableDebug();
        return $this;
    }

    /**
     *
     * @return \Mopsy\Connection - Provides fluent interface
     */
    public function disableDebug()
    {
        $this->channel->disableDebug();
        return $this;
    }

    /**
     *
     * @return \Mopsy\Channel\Options
     */
    public function getExchangeOptions()
    {
        return $this->exchangeOptions;
    }

    /**
     *
     * @param Options $options
     *
     * @throws InvalidArgumentException
     *
     * @return \Mopsy\Connection - Provides fluent interface
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
     * @param Options $options
     *
     * @return \Mopsy\Connection - Provides fluent interface
     */
    public function setQueueOptions(Options $options)
    {
        $this->queueOptions = $options;
        return $this;
    }

    /**
     *
     * @param string $routingKey
     *
     * @return \Mopsy\Connection - Provides fluent interface
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
     * @return \Mopsy\Connection - Provides fluent interface
     */
    public function setConsumerTag($consumerTag)
    {
        $this->consumerTag = $consumerTag;
        return $this;
    }

}