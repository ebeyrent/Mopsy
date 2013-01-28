<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Erich Beyrent <erich.beyrent@pearson.com>
 * @license  Pearson http://polaris.fen.com/
 * @version  $Revision$
 * @link     $HeadURL$
 *
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