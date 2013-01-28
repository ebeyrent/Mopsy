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

namespace Mopsy\AMQP;

use Mopsy\Connection\Configuration;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Service
{
    /**
     *
     * @param Mopsy\Connection\Configuration $configuration
     *
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    public static function createAMQPConnection(
        Configuration $configuration)
    {
        return new AMQPConnection($configuration->getHost(),
            $configuration->getPort(),
            $configuration->getUser(),
            $configuration->getPass());
    }

    /**
     *
     * @param string $body
     * @param array $options
     *
     * @return \Mopsy\AMQP\AMQPMessage
     */
    public static function createAMQPMessage($body, $options = array())
    {
        $defaultOptions = array(
            'content_type' => 'text/plain',
            'delivery_mode' => 2,
        );
        $options = array_merge($defaultOptions, $options);
        return new AMQPMessage($body, $options);
    }
}