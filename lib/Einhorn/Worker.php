<?php

namespace Einhorn;

use UnexpectedValueException,
    InvalidArgumentException;

class Worker
{
    const DISCOVER_ENV = "env";
    const DISCOVER_FD = "fd";
    const DISCOVER_DIRECT = "direct";

    # Creates a client for the Einhorn Master Process.
    #
    # discovery - Discovery strategy for the connection method to the Einhorn Master (default: DISCOVER_ENV).
    #             DISCOVER_ENV    - Connects to the Unix Socket in
    #                               `EINHORN_SOCK_PATH`.
    #             DISCOVER_FD     - Opens the file descriptor in `EINHORN_FD`.
    #             DISCOVER_DIRECT - Connects to the Unix Socket passed 
    #                               as second argument.
    # arg       - Optional argument for the discovery method (default: null).
    #
    # Returns a Client instance.
    static function client($discovery = self::DISCOVER_ENV, $arg = null)
    {
        switch ($discovery) {
            case self::DISCOVER_ENV:
                $path = $_SERVER["EINHORN_SOCK_PATH"];
                $client = Client::forPath($path);
                break;
            case self::DISCOVER_FD:
                $fd = $_SERVER["EINHORN_FD"];
                $client = Client::forFd($fd);
                break;
            case self::DISCOVER_DIRECT:
                $client = Client::forPath($arg);
                break;
            default:
                throw new UnexpectedValueException("Unexpected discovery strategy '$discovery'.");
                break;
        }

        return $client;
    }

    # Returns the socket on which the server was started.
    static function socket($fd = null, $mode = "rb")
    {
        if (null === $fd) {
            if (!isset($_SERVER['argv'][1])) {
                throw new InvalidArgumentException("No file descriptor was given as argument."
                    . " Check that you gave Einhorn an address to listen on.");
            }

            $fd = $_SERVER['argv'][1];
        }

        return fopen("php://fd/$fd", $mode);
    }

    # Calls the handler when Einhorn is gracefully shutdown.
    #
    # callback - Callback
    #
    # Returns Nothing.
    static function gracefulShutdown($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException("Not a valid callback.");
        }

        pcntl_signal(SIGUSR2, $callback);
    }
}
