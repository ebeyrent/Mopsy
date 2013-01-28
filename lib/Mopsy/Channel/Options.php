<?php

/**
 * Encapsulates exchange and queue options, passed to exchange_declare and
 * queue_declare.
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

namespace Mopsy\Channel;

class Options
{
    /**
     *
     * @var string
     */
    protected $name = '';

    /**
     *
     * @var string
     */
    protected $type = '';

    /**
     *
     * @var boolean
     */
    protected $passive = false;

    /**
     *
     * @var boolean
     */
    protected $durable = true;

    /**
     *
     * @var boolean
     */
    protected $auto_delete = true;

    /**
     *
     * @var boolean
     */
    protected $internal = false;

    /**
     *
     * @var boolean
     */
    protected $nowait = false;

    /**
     *
     * @var array
     */
    protected $arguments = array();

    /**
     *
     * @var boolean
     */
    protected $exclusive = false;

    /**
     *
     * @var int
     */
    protected $ticket = 0;

    /**
     * Class constructor
     *
     *
     * @param string $name
     * @param string $type
     */
    public function __construct($name='', $type='')
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Static initializer
     *
     * @param string $name
     * @param string $type
     *
     * @return \Mopsy\Channel\Options
     */
    public static function getInstance($name='', $type='')
    {
        return new self($name, $type);
    }

    /**
     * Getter method for the $exclusive data member
     *
     * @return boolean
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

	/**
     * Getter method for the $name data member
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

	/**
     * Getter method for the $type data member
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

	/**
     * Getter method for the $passive data member
     *
     * @return boolean $passive
     */
    public function getPassive()
    {
        return $this->passive;
    }

	/**
     * Getter method for the $durable data member
     *
     * @return boolean $durable
     */
    public function getDurable()
    {
        return $this->durable;
    }

	/**
     * Getter method for the $auto_delete data member
     *
     * @return boolean $auto_delete
     */
    public function getAutoDelete()
    {
        return $this->auto_delete;
    }

	/**
     * Getter method for the $internal data member
     *
     * @return boolean $internal
     */
    public function getInternal()
    {
        return $this->internal;
    }

	/**
     * Getter method for the $nowait data member
     *
     * @return boolean $nowait
     */
    public function getNowait()
    {
        return $this->nowait;
    }

	/**
     * Getter method for the $arguments data member
     *
     * @return multitype: $arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

	/**
     * Getter method for the $ticket data member
     *
     * @return number $ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Setter method for the $exclusive data member
     *
     * @param boolean $exclusive
     *
     * @return Options - Provides fluent interface
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
        return $this;
    }

	/**
     * Setter method for the $name data member
     *
     * @param string $name
     * @return Options - Provides fluent interface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

	/**
     * Setter method for the $type data member
     *
     * @param string $type
     * @return Options - Provides fluent interface
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

	/**
     * Setter method for the $passive data member
     *
     * @param boolean $passive
     * @return Options - Provides fluent interface
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
        return $this;
    }

	/**
     * Setter method for the $durable data member
     *
     * @param boolean $durable
     * @return Options - Provides fluent interface
     */
    public function setDurable($durable)
    {
        $this->durable = $durable;
        return $this;
    }

	/**
     * Setter method for the $auto_delete data member
     *
     * @param boolean $auto_delete
     * @return Options - Provides fluent interface
     */
    public function setAutoDelete($auto_delete)
    {
        $this->auto_delete = $auto_delete;
        return $this;
    }

	/**
     * Setter method for the $internal data member
     *
     * @param boolean $internal
     * @return Options - Provides fluent interface
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;
        return $this;
    }

	/**
     * Setter method for the $nowait data member
     *
     * @param boolean $nowait
     * @return Options - Provides fluent interface
     */
    public function setNowait($nowait)
    {
        $this->nowait = $nowait;
        return $this;
    }

	/**
     * Setter method for the $arguments data member
     *
     * @param multitype: $arguments
     * @return Options - Provides fluent interface
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

	/**
     * Setter method for the $ticket data member
     *
     * @param number $ticket
     * @return Options - Provides fluent interface
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }



}