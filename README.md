# [Einhorn][] Client Library for PHP

## Install

Put this into your `composer.json`:

    {
        "require": {
            "chh/einhorn": "*@dev"
        }
    }

Install it:

    % php composer.phar install

Then setup the autoloader:

    <?php
    
    require("vendor/autoload.php");

## Usage

### Manual ACK

Example:

```php
<?php

use Einhorn\Worker;

Worker::client()->ack(getmypid());
```

### Aquire a client to the Server

Example:

```php
use Einhorn\Worker;

$client = Worker::client();
$resp = $client->command(array("command" => "stat"));
```

[einhorn]: https://github.com/stripe/einhorn
