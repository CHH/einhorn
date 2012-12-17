<?php

declare(ticks = 1);

require(dirname(__DIR__) . "/vendor/autoload.php");

use Einhorn\Worker;

Worker::gracefulShutdown(function() {
    printf("Goodbye from %d\n", getmypid());
    exit(0);
});

$socket = Worker::socket();
$client = Worker::client();

# Send manual ack.
$client->ack();

for (;;) {
    pcntl_signal_dispatch();

    $accepted = @stream_socket_accept($socket);

    if (false === $accepted) {
        continue;
    }

    $date = new \DateTime;

    fprintf($accepted, "[%d]: The current time is %s!\n", getmypid(), $date->format(\DateTime::RFC1123));
    fclose($accepted);
}

