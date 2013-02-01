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

namespace Mopsy\Connection;

class Configuration
{
    /**
     *
     * @var string
     */
    private $host;

    /**
     *
     * @var int
     */
    private $port;

    /**
     *
     * @var string
     */
    private $user;

    /**
     *
     * @var string
     */
    private $pass;

    /**
     *
     * @var string
     */
    private $vhost = '/';

    /**
     *
     * @var boolean
     */
    private $insist = false;

    /**
     *
     * @var string
     */
    private $loginMethod = 'AMQPLAIN';

    /**
     *
     * @var string
     */
    private $loginResponse = null;

    /**
     *
     * @var string
     */
    private $locale = 'en_US';

    /**
     *
     * @var int
     */
    private $connectionTimeout = 3;

    /**
     *
     * @var int
     */
    private $readWriteTimeout = 3;

    /**
     *
     * @var resource
     */
    private $context = null;

    /**
     * Class constructor
     */
    public function __construct()
    {

    }

    /**
     * Static initializer
     *
     * @return \Mopsy\Connection\Configuration
     */
    public static function getInstance()
    {
        return new self();
    }

	/**
     * Getter method for the $host data member
     *
     * @return string $host
     */
    public function getHost()
    {
        return $this->host;
    }

	/**
     * Getter method for the $port data member
     *
     * @return number $port
     */
    public function getPort()
    {
        return $this->port;
    }

	/**
     * Getter method for the $user data member
     *
     * @return string $user
     */
    public function getUser()
    {
        return $this->user;
    }

	/**
     * Getter method for the $pass data member
     *
     * @return string $pass
     */
    public function getPass()
    {
        return $this->pass;
    }

	/**
     * Getter method for the $vhost data member
     *
     * @return string $vhost
     */
    public function getVhost()
    {
        return $this->vhost;
    }

	/**
     * Getter method for the $insist data member
     *
     * @return boolean $insist
     */
    public function getInsist()
    {
        return $this->insist;
    }

	/**
     * Getter method for the $loginMethod data member
     *
     * @return string $loginMethod
     */
    public function getLoginMethod()
    {
        return $this->loginMethod;
    }

	/**
     * Getter method for the $loginResponse data member
     *
     * @return string $loginResponse
     */
    public function getLoginResponse()
    {
        return $this->loginResponse;
    }

	/**
     * Getter method for the $locale data member
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

	/**
     * Getter method for the $connectionTimeout data member
     *
     * @return number $connectionTimeout
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

	/**
     * Getter method for the $readWriteTimeout data member
     *
     * @return number $readWriteTimeout
     */
    public function getReadWriteTimeout()
    {
        return $this->readWriteTimeout;
    }

	/**
     * Getter method for the $context data member
     *
     * @return resource $context
     */
    public function getContext()
    {
        return $this->context;
    }

	/**
     * Setter method for the $host data member
     *
     * @param string $host
     * @return Configuration - Provides fluent interface
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

	/**
     * Setter method for the $port data member
     *
     * @param number $port
     * @return Configuration - Provides fluent interface
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

	/**
     * Setter method for the $user data member
     *
     * @param string $user
     * @return Configuration - Provides fluent interface
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

	/**
     * Setter method for the $pass data member
     *
     * @param string $pass
     * @return Configuration - Provides fluent interface
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

	/**
     * Setter method for the $vhost data member
     *
     * @param string $vhost
     * @return Configuration - Provides fluent interface
     */
    public function setVhost($vhost)
    {
        $this->vhost = $vhost;
        return $this;
    }

	/**
     * Setter method for the $insist data member
     *
     * @param boolean $insist
     * @return Configuration - Provides fluent interface
     */
    public function setInsist($insist)
    {
        $this->insist = $insist;
        return $this;
    }

	/**
     * Setter method for the $loginMethod data member
     *
     * @param string $loginMethod
     * @return Configuration - Provides fluent interface
     */
    public function setLoginMethod($loginMethod)
    {
        $this->loginMethod = $loginMethod;
        return $this;
    }

	/**
     * Setter method for the $loginResponse data member
     *
     * @param string $loginResponse
     * @return Configuration - Provides fluent interface
     */
    public function setLoginResponse($loginResponse)
    {
        $this->loginResponse = $loginResponse;
        return $this;
    }

	/**
     * Setter method for the $locale data member
     *
     * @param string $locale
     * @return Configuration - Provides fluent interface
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

	/**
     * Setter method for the $connectionTimeout data member
     *
     * @param number $connectionTimeout
     * @return Configuration - Provides fluent interface
     */
    public function setConnectionTimeout($connectionTimeout)
    {
        $this->connectionTimeout = $connectionTimeout;
        return $this;
    }

	/**
     * Setter method for the $readWriteTimeout data member
     *
     * @param number $readWriteTimeout
     * @return Configuration - Provides fluent interface
     */
    public function setReadWriteTimeout($readWriteTimeout)
    {
        $this->readWriteTimeout = $readWriteTimeout;
        return $this;
    }

	/**
     * Setter method for the $context data member
     *
     * @param resource $context
     * @return Configuration - Provides fluent interface
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }
}