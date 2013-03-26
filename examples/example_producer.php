<?php

require_once '/path/to/mopsy/vendor/autoload.php';

$producer = new \Mopsy\Producer(
    new Mopsy\Container(),
    new Mopsy\Connection\Configuration()
);

$content = array(
    'action' => 'foo',
    'options' => array(
        'bar' => 'baz',
        'debug' => true,
    ),
);

$exchangeOptions = Mopsy\Channel\Options::getInstance()
    ->setName('responsys-exchange')
    ->setType('direct');

$producer->setExchangeOptions($exchangeOptions)
    ->publish(new Mopsy\Message($content));
