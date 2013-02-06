Mopsy
=====

Mopsy is a PHP library that implements several messaging patterns for RabbitMQ,
based on the [Thumper library](https://github.com/videlalvaro/Thumper/ "Title").

Unlike Thumper, Mopsy provides support for dead-lettering messages through 
retry cycles, and declares an extra exchange and queue for storing dead-letter
messages.

This library is PSR-0 compatible, and has been tested against RabbitMQ 3.0.1.

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

## Examples ##

Examples are in the examples directory.

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
### Queue Server ###

This example illustrates how to create a producer that will publish jobs into a 
queue. Those jobs will be processed later by a consumer –or several of them–.

## Debugging ##

If you want to know what's going on at a protocol level then add the following 
constant to your code:

    <?php
    define('AMQP_DEBUG', true);

    ... more code

    ?>
    
## Disclaimer ##

This code is experimental. Its purpose is to provide a simple interface for 
working with RabbitMQ with message failure and dead-letter functionality.

However, this code is not ready for production environments.  Use at your own
risk.

## @TODO ##
* PHPUnit tests!
* More examples

## License ##

See LICENSE.md
    