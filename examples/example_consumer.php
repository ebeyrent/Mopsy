<?php

require_once '/path/to/mopsy/vendor/autoload.php';

$callback = function(Mopsy\Message $message)
{
    $body = $message->getPayload();

    /*
     * $body is the message body that was published.  Your callback should
     * return true or false.  If false is returned, the message will go through
     * a retry cycle until it is dead-lettered.
     */
};

$consumer = new Mopsy\Consumer(new Mopsy\Container(),
    new Mopsy\Connection\Configuration());

$consumer
    ->setExchangeOptions(Mopsy\Channel\Options::getInstance()
        ->setName('rabbits-exchange')
        ->setType('direct'))
    ->setQueueOptions(Mopsy\Channel\Options::getInstance()
        ->setName('rabbits-queue'))
    ->setMaxRetries(5)
    ->setDeadLetterExchange('rabbits-exchange-dead')
    ->setDeadLetterRoutingKey('rabbits-dead')
    ->setCallback($callback)
    ->consume(1);
