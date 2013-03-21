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

use PhpAmqpLib\Wire\GenericContent;

/**
 * A Message for use with the Channnel.basic_* methods.
 */
class Message extends GenericContent
{
    /**
     * The message payload to be processed by consumers
     * @var string
     */
    public $body;

    /**
     * The value of the expiration field describes the message TTL period in
     * milliseconds.
     * @var int
     */
    private $expiration = 0;

    /**
     * Defines RabbitMQ-supported fields
     * @var array
     */
    protected static $PROPERTIES = array(
        "content_type" => "shortstr",
        "content_encoding" => "shortstr",
        "application_headers" => "table",
        "delivery_mode" => "octet",
        "priority" => "octet",
        "correlation_id" => "shortstr",
        "reply_to" => "shortstr",
        "expiration" => "shortstr",
        "message_id" => "shortstr",
        "timestamp" => "timestamp",
        "type" => "shortstr",
        "user_id" => "shortstr",
        "app_id" => "shortstr",
        "cluster_id" => "shortstr",
    );

    /**
     * This flag tells the server how to react if the message cannot be routed
     * to a queue. If this flag is set, the server will return an unroutable
     * message with a Return method. If this flag is zero, the server silently
     * drops the message.
     * @var boolean
     */
    private $mandatory = false;

    /**
     * This flag tells the server how to react if the message cannot be routed
     * to a queue consumer immediately. If this flag is set, the server will
     * return an undeliverable message with a Return method. If this flag is
     * zero, the server will queue the message, but with no guarantee that it
     * will ever be consumed.
     *
     * @deprecated
     * @var boolean
     */
    private $immediate = false;

    /**
     * AMQP 0.8 originally introduced the idea of a ticket as a token generated
     * by the server representing a cached set of permissions.
     *
     * @deprecated
     * @var int
     */
    private $ticket = 0;

    /**
     * Class constructor
     *
     * @param string|array|object $body
     */
    public function __construct($messageBody, array $properties = array())
    {
        // JSON-encode all messages; getPayload() will decode the messages
        $this->body = json_encode($messageBody, JSON_FORCE_OBJECT);

        // Set default properties
        if(empty($properties)) {
            $properties = array(
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
            );
        }

        // Call the parent class constructor
        parent::__construct($properties, static::$PROPERTIES);

        // Discard this message after 60 seconds if not acknowledged
        $this->set('expiration', $this->expiration);

        $this->set('application_headers', array(
            'x-retry_count' => array('I', 0),
        ));
    }

    /**
     * Static initializer
     *
     * @param string|array|object $body
     *
     * @return \Mopsy\Message - Provides fluent interface
     */
    public static function getInstance($body, array $properties = array())
    {
        return new self($body, $properties);
    }

    /**
     * Getter method for the class data member $body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     *
     * @return \stdClass
     */
    public function getPayload()
    {
        return json_decode($this->body, false);
    }

    /**
     *
     * @return array
     */
    public function getDeliveryInfo()
    {
        return $this->delivery_info;
    }

    /**
     *
     * @return string
     */
    public function getConsumerTag()
    {
        return $this->delivery_info['consumer_tag'];
    }

    /**
     *
     * @return string
     */
    public function getDeliveryTag()
    {
        return $this->delivery_info['delivery_tag'];
    }

    /**
     * Gets the stored Channel object from the message
     *
     * @return Mopsy\Channel
     */
    public function getChannel()
    {
        return $this->delivery_info['channel'];
    }

    /**
     * Gets the stored Consumer object from the message
     *
     * @return Mopsy\Consumer
     */
    public function getConsumer()
    {
        return $this->getChannel()->callbacks[$this->getConsumerTag()][0];
    }

    /**
     * Getter method for the class data member $mandatory
     *
     * @return boolean
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Sets the message time-to-live.  RabbitMQ will drop the message if
     * unacknowledged after this value, expressed in milliseconds.
     *
     * @param int $expiration
     *
     * @return \Mopsy\Message - Provides fluent interface
     */
    public function setTTL($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * Setter method for the class data member $mandatory
     *
     * @param boolean $mandatory
     *
     * @return \Mopsy\Message - Provides fluent interface
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    /**
     *
     * @param array $properties
     *
     * @return \Mopsy\Message
     */
    public function addProperties(array $properties)
    {
        foreach($properties as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

}