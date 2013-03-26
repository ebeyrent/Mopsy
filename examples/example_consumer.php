<?php

require_once '/path/to/mopsy/vendor/autoload.php';

$callback = function (Mopsy\Message $message) {
    $body = $message->getPayload();

    /*
     * $body is the message body that was published.  Your callback should
     * return true or false.  If false is returned, the message will go through
     * a retry cycle until it is dead-lettered.
     */

    if (is_string($body)) {
        echo $body;
    }
};

$consumer = new Mopsy\Consumer(
    new Mopsy\Container(),
    new Mopsy\Connection\Configuration()
);

$exchangeOptions = Mopsy\Channel\Options::getInstance()
    ->setName('rabbits-exchange')
    ->setType('direct');

$queueOptions = Mopsy\Channel\Options::getInstance()
    ->setName('rabbits-queue');

$consumer->setExchangeOptions($exchangeOptions)
    ->setQueueOptions($queueOptions)
    ->setMaxRetries(5)
    ->setDeadLetterExchange('rabbits-exchange-dead')
    ->setDeadLetterRoutingKey('rabbits-dead')
    ->setCallback($callback)
    ->consume(1);
