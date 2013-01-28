<?php

require_once '/path/to/mopsy/vendor/autoload.php';

$callback = function($message)
{
    // Do something with $message
};

$connection = Mopsy\AMQP\Service::createAMQPConnection(
    new Mopsy\Connection\Configuration());

$consumer = new Mopsy\Consumer(new Mopsy\Container(), $connection);
$consumer->setMaxRetries(5)
    ->setExchangeOptions(Mopsy\Channel\Options::getInstance()
    ->setName('rabbits-exchange')
    ->setType('direct'))
    ->setQueueOptions(Mopsy\Channel\Options::getInstance()
        ->setName('rabbits-queue'))
    ->setDeadLetterExchange('dead-rabbits-exchange')
    ->setCallback($callback)
    ->consume(1);
