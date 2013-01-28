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

use PhpAmqpLib\Channel\AMQPChannel;

class Channel extends AMQPChannel
{
    protected $debug = false;

    public function __construct($connection,
        $channel_id=null,
        $auto_decode=true)
    {
        parent::__construct($connection, $channel_id, $auto_decode);
        $this->debug = false;
    }

    public function getDebugState()
    {
        return $this->debug;
    }

    public function enableDebug()
    {
        $this->debug = true;
        return $this;
    }

    public function disableDebug()
    {
        $this->debug = false;
        return $this;
    }

}