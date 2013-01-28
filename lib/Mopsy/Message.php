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
     *
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
        "cluster_id" => "shortstr"
    );

    /**
     *
     * @var boolean
     */
    private $mandatory = false;

    /**
     *
     * @var boolean
     */
    private $immediate = false;

    /**
     *
     * @var int
     */
    private $ticket = null;

    /**
     * Class constructor
     *
     * @param string|array|object $body
     */
    public function __construct($messageBody)
    {
        if(is_array($messageBody) || is_object($messageBody)) {
            $body = json_encode($body);
        }
        $this->body = $body;
        parent::__construct(array(), static::$PROPERTIES);

    }

    /**
     * Static initializer
     *
     * @param string|array|object $body
     *
     * @return \Mopsy\Message - Provides fluent interface
     */
    public static function getInstance($body)
    {
        return new self($body);
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
     *
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->delivery_info['channel'];
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
     * Getter method for the class data member $immediate
     *
     * @return boolean
     */
    public function getImmediate()
    {
        return $this->immediate;
    }

    /**
     * Getter method for the class data member $ticket
     *
     * @return int
     */
    public function getTicket()
    {
        return $this->ticket;
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
     * Setter method for the class data member $immediate
     *
     * @param boolean $immediate
     *
     * @return \Mopsy\Message - Provides fluent interface
     */
    public function setImmediate($immediate)
    {
        $this->immediate = $immediate;
        return $this;
    }

    /**
     * Setter method for the class data member $ticket
     *
     * @param int $ticket
     *
     * @return \Mopsy\Message - Provides fluent interface
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function addProperties(array $properties)
    {
        foreach($properties as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

}