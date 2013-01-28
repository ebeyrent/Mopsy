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
    
## Examples ##


```php
<?php
require_once '/path/to/mopsy/vendor/autoload.php';

define('AMQP_DEBUG', true);

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