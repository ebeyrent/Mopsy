<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category Mopsy
 * @package  Mopsy
 * @author   Erich Beyrent <erich.beyrent@pearson.com>
 * @license  Pearson http://polaris.fen.com/
 * @version  $Revision$
 * @link     $HeadURL$
 *
 */

namespace Mopsy;

use PhpAmqpLib\Message\AMQPMessage;

use Mopsy\Connection;
use Mopsy\Channel\Options;

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
    private $maxRetries = 1;

    /**
     *
     * @param Container $container
     * @param AMQPConnection $connection
     * @param AMQPChannel $channel
     * @return \Mopsy\Consumer
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
     * @return \Mopsy\Consumer
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
     * @return Mopsy\Consumer - provides fluent interface
     */
    public function setExchangeOptions(Options $options)
    {
        parent::setExchangeOptions($options);
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
     * @return \Mopsy\Consumer
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
     * @return \Mopsy\Consumer
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
    public function processMessage(Mopsy\Message $msg)
    {
        try
        {
            $return = call_user_func($this->callback, $msg->getBody());
            if($return === false) {
                // Message consumption failed, handle retry logic
            }
            else {
                $msg->getChannel()->basic_ack($msg->getDeliveryTag());
                $this->consumed++;
                $this->maybeStopConsumer($msg);
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
