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
    #                               `EINHORN_SOCKET_PATH`.
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
                $path = $_SERVER["EINHORN_SOCKET_PATH"];
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

    # Public: Opens the connection to the FD passed to the script
    # by Einhorn when in socket server mode.
    #
    # fd   - File descriptor as number (default: $_SERVER['argv'][1]).
    # mode - Mode passed to `fopen` (default: 'rb').
    #
    # Returns a Stream.
    static function open($fd = null, $mode = "rb")
    {
        if (null === $fd) {
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
