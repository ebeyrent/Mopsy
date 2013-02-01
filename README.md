Mopsy
=====

PHP Library that implements several messaging patterns for RabbitMQ

**Requirements: PHP 5.3** due to the use of `namespaces`.

## Setup ##

Get the library source code:

```bash
$ git clone git://github.com/ebeyrent/Mopsy.git
```

Class autoloading and dependencies are managed by `composer` so install it:

```bash
$ curl --silent https://getcomposer.org/installer | php
```

And then install the library dependencies and generate the `autoload.php` file:

    $ php composer.phar install

## Queue Server ##

This example illustrates how to create a producer that will publish jobs into a 
queue. Those jobs will be processed later by a consumer –or several of them–.

## Examples ##


```php
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
?>

```    

## Debugging ##

If you want to know what's going on at a protocol level then add the following 
constant to your code:

    <?php
    define('AMQP_DEBUG', true);

    ... more code

    ?>
    
# Disclaimer #

This code is experimental. Its purpose is to provide a simple interface for 
working with RabbitMQ with message failure and dead-letter functionality.

However, this code is not ready for production environments.  Use at your own
risk.

# License #

See LICENSE.md
    