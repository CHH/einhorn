# [Einhorn][] Client Library for PHP

_A simple utility belt for using PHP with [Einhorn][]._

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

### Communicate with the Master Process

A `Einhorn\Client` is used to communicate with the Einhorn Master
Process. The simplest way to retrieve a client is through the worker's
`client` method.

```php
<?php

use Einhorn\Worker;

$client = Worker::client();
```

The `client` method takes the discovery method for the control socket as
first argument and an optional second argument, which can be used by the
specific discovery strategy.

Following discovery strategies are supported out-of-the-box:

 * `DISCOVER_ENV`: Looks for an environment variable named
   `EINHORN_SOCK_PATH` which contains the path to the master's
   control Unix Socket.
 * `DISCOVER_FD`: Looks for an environment variable named `EINHORN_FD`
   which contains the file descriptor number for the master's control socket.
   This is mainly useful with Einhorn's `-b` switch.
 * `DISCOVER_DIRECT`: Opens the path passed as second argument as Unix
   Socket. Useful when opening an connection the control socket from
   outside of a worker script for monitoring etc.

Example:

```php
<?php

use Einhorn\Worker;

$client = Worker::client(Worker::DISCOVER_FD);
$client->ack();
```

The client also features a `command` method to send arbitary commands to
the master process.

    Client::command(array $command)

The `command` argument is an array which contains at least an `command`
key which contains the name of the command to invoke on the master.

For commands which return a response, the `recv` method should be called
after sending the command.

Example:

```php
<?php

user Einhorn\Worker;

$client = Worker::client();

$client->command(array("command" => "state"));

$state = $client->recv();
```

### Manual ACK

The client features a `ack` method which sends an `worker:ack` command with the
current process' PID to the Einhorn Master Process, in case you've
launched `einhorn` with `-m manual`.

Example:

```php
<?php

use Einhorn\Worker;

$client = Worker::client();
$client->ack();
```

### File Descriptor Number to PHP Stream

The `Worker` class features an utility method which returns a PHP stream
for the file descriptor number passed in by Einhorn. This stream can
then be used with `stream_select` or `stream_socket_accept`.

Example:

```php
<?php

use Einhorn\Worker;

# Uses $argv[1] as FD number.
$socket = Worker::socket();

# When the FD number is passed by other means:
$socket = Worker::socket($fd);
```

## License

Copyright (c) 2012 Christoph Hochstrasser

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

[einhorn]: https://github.com/stripe/einhorn

