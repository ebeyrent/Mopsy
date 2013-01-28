<?php

require_once '/path/to/mopsy/vendor/autoload.php';

$connection = Mopsy\AMQP\Service::createAMQPConnection(
    new Mopsy\Connection\Configuration());

$producer = new Mopsy\Producer(new Mopsy\Container(), $connection);
$producer->setConsumerTag('rabbits')
    ->setRoutingKey('rabbits')
    ->setExchangeOptions(Mopsy\Channel\Options::getInstance()
        ->setName('rabbits-exchange')
        ->setType('direct'))
    ->publish(Mopsy\AMQP\Service::createAMQPMessage('foo'));