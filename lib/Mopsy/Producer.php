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

use \Mopsy\Channel\Options;
use \Mopsy\Connection\Configuration;

class Producer extends Connection
{
    /**
     *
     * @var boolean
     */
    protected $exchangeReady = false;

    /**
     * Static initializer
     *
     * @param Container $container
     * @param Configuration $configuration
     * @param Channel $channel
     *
     * @return Connection
     */
    public static function getInstance(
        Container $container,
        Configuration $configuration,
        Channel $channel = null
    ) {
        return new self($container, $configuration, $channel);
    }

    /**
     * Sets up the exchange options for the connection
     *
     * @see Connection::setExchangeOptions()
     *
     * @param Options $options
     *
     * @return $this - Producer - Provides fluent interface
     */
    public function setExchangeOptions(Options $options)
    {
        parent::setExchangeOptions($options);
        return $this;
    }

    /**
     * Publish a message to RabbitMQ.
     *
     * @param Message $message
     *
     * @return $this - Producer - Provides fluent interface
     */
    public function publish(Message $message)
    {
        // Declare the exchange if it hasn't already been declared
        if (!$this->exchangeReady) {
            $this->channel->declareExchange($this->exchangeOptions);
            $this->exchangeReady = true;
        }

        // Publish the message
        $this->channel->basic_publish(
            $message,
            $this->exchangeOptions->getName(),
            $this->routingKey
        );

        return $this;
    }
}
