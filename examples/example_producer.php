<?php

require_once '/path/to/mopsy/vendor/autoload.php';

$producer = new \Mopsy\Producer(new Mopsy\Container(),
    new Mopsy\Connection\Configuration());

$content = array(
    'action' => 'foo',
    'options' => array(
        'bar' => 'baz',
        'debug' => true,
    ),
);

$producer
    ->setExchangeOptions(Mopsy\Channel\Options::getInstance()
        ->setName('responsys-exchange')
        ->setType('direct'))
    ->publish(new Mopsy\Message($content));