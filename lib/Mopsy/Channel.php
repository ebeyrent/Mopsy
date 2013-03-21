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

use Mopsy\Channel\Options;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Helper\MiscHelper;

class Channel extends AMQPChannel
{
    /**
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     *
     * @var Mopsy\Connection
     */
    protected $connection;

    /**
     *
     * @var Mopsy\Container
     */
    protected $container;

    /**
     *
     * @var boolean
     */
    protected $auto_decode = true;

    /**
     *
     * @var Mopsy\Channel\Options
     */
    protected $exchangeOptions;

    /**
     * Class constructor
     *
     * @param Mopsy\Connection $connection
     * @param int $channel_id
     * @param boolean $auto_decode
     */
    public function __construct(Connection $connection,
        $channel_id = 0,
        $auto_decode = true)
    {
        parent::__construct($connection->getAMQPConnection(), $channel_id, $auto_decode);
        $this->container = $connection->getContainer();
        $this->auto_decode = $auto_decode;
    }

    /**
     * Static initializer
     *
     * @param Mopsy\Connection $connection
     * @param int $channel_id
     * @param boolean $auto_decode
     *
     * @return \Mopsy\Channel - Provides fluent interface
     */
    public static function getInstance(Connection $connection,
        $channel_id = 0,
        $auto_decode = true)
    {
        return new self($connection, $channel_id, $auto_decode);
    }

    /**
     *
     * @return Mopsy\Connection - Provides fluent interface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     *
     * @return boolean
     */
    public function getDebugState()
    {
        return $this->debug;
    }

    /**
     *
     * @return \Mopsy\Channel - Provides fluent interface
     */
    public function enableDebug()
    {
        $this->debug = true;
        return $this;
    }

    /**
     *
     * @return \Mopsy\Channel - Provides fluent interface
     */
    public function disableDebug()
    {
        $this->debug = false;
        return $this;
    }

    /**
     * This method declares a new exchange
     *
     * @param Options $options
     *
     * @return \Mopsy\Channel - Provides fluent interface
     */
    public function declareExchange(Options $options)
    {
        $this->exchangeOptions = $options;

        $this->exchange_declare($this->exchangeOptions->getName(),
            $this->exchangeOptions->getType(),
            $this->exchangeOptions->getPassive(),
            $this->exchangeOptions->getDurable(),
            $this->exchangeOptions->getAutoDelete(),
            $this->exchangeOptions->getInternal(),
            $this->exchangeOptions->getNowait(),
            $this->exchangeOptions->getArguments(),
            $this->exchangeOptions->getTicket());

        return $this;
    }

    /**
     * This method declares a new queue
     *
     * @param Options $options
     *
     * @return string
     */
    public function declareQueue(Options $options)
    {
        $queue = $this->queue_declare($options->getName(),
            $options->getPassive(),
            $options->getDurable(),
            $options->getExclusive(),
            $options->getAutoDelete(),
            $options->getNowait(),
            $options->getArguments(),
            $options->getTicket());

        if(!empty($queue)) {
            $queueName = array_shift($queue);
        }
        else {
            $queueName = $options->getName();
        }

        return $queueName;
    }

    /**
     *
     * @return \Mopsy\Channel\Options - Provides fluent interface
     */
    public function getExchangeOptions()
    {
        return $this->exchangeOptions;
    }

    /**
     * Overrides parent method and creates messages of type Mopsy\Message
     * instead of AMQPMessage to gain extra functionality
     *
     * (non-PHPdoc)
     * @see \PhpAmqpLib\Channel\AbstractChannel::wait_content()
     *
     * @return Mopsy\Message
     */
    public function wait_content()
    {
        $frame = $this->next_frame();
        $frame_type = $frame[0];
        $payload = $frame[1];

        if ($frame_type != 2) {
            throw new AMQPRuntimeException("Expecting Content header");
        }

        /* @var $payload_reader AMQPReader */
        $payload_reader = $this->container
            ->newInstance('PhpAmqpLib\Wire\AMQPReader', array(
                substr($payload,0,12),
            ));

        $class_id = $payload_reader->read_short();
        $weight = $payload_reader->read_short();

        $body_size = $payload_reader->read_longlong();

        /* @var $msg Message */
        $msg = $this->container->newInstance('Mopsy\Message', array(array()));
        $msg->load_properties(substr($payload,12));

        $body_parts = array();
        $body_received = 0;

        while (bccomp($body_size,$body_received) == 1) {
            $frame = $this->next_frame();
            $frame_type = $frame[0];
            $payload = $frame[1];

            if ($frame_type != 3) {
                throw new AMQPRuntimeException("Expecting Content body, received frame type $frame_type ("
                    .self::$FRAME_TYPES[$frame_type].")");
            }

            $body_parts[] = $payload;
            $body_received = bcadd($body_received, strlen($payload));
        }

        $msg->body = implode("",$body_parts);

        if ($this->auto_decode && isset($msg->content_encoding)) {
            try {
                $msg->body = $msg->body->decode($msg->content_encoding);
            }
            catch (\Exception $e) {
                if ($this->debug) {
                    MiscHelper::debug_msg("Ignoring body decoding exception: " . $e->getMessage());
                }
            }
        }

        return $msg;
    }
}