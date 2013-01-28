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
 * @category Thumper
 * @package Thumper
 */
namespace Mopsy;
use Mopsy\Consumer;
/**
 *
 *
 *
 * @category Mopsy
 * @package Mopsy
 */
class AnonymousConsumer extends Consumer
{
    public function __construct($host, $port, $user, $pass, $vhost)
    {
        parent::__construct(HOST, PORT, USER, PASS, VHOST);

        $this->setQueueOptions(array('name' => '', 'passive' => false, 'durable' => false,
            'exclusive' => true, 'auto_delete' => true, 'nowait' => false,
            'arguments' => null, 'ticket' => null));
    }

    /**
     *
     * @param unknown_type $host
     * @param unknown_type $port
     * @param unknown_type $user
     * @param unknown_type $pass
     * @param unknown_type $vhost
     * @return \Mopsy\AnonymousConsumer
     */
    public static function getInstance($host, $port, $user, $pass, $vhost) {
        return new self($host, $port, $user, $pass, $vhost);
    }
}