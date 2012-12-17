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
                $fd = $_SERVER["EINHORN_SOCK_FD"];
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

    # Returns the first socket specified by the -b flag as a PHP stream.
    #
    # fd - Optional file descriptor number to convert to a stream.
    # mode - Mode string passed to fopen().
    #
    # Returns a stream.
    static function socket($fd = null, $mode = "rb")
    {
        if ($fd === null) {
            $fds = static::sockets($mode);

            if (!$fds or !is_array($fds)) {
                throw new \UnexpectedValueException(sprintf(
                    'No open sockets were found. Check that you have started
                    Einhorn with the -b flag (EINHORN_FDS=%s)', $_SERVER['EINHORN_FDS']
                ));
            }

            return $fds[0];
        }

        return fopen("php://fd/$fd", $mode);
    }

    # Returns a list of all bound sockets passed by the -b flag to Einhorn.
    #
    # mode - Mode string passed to fopen()
    #
    # Returns an Array
    static function sockets($mode = 'rb')
    {
        $fds = explode(' ', $_SERVER['EINHORN_FDS']);

        return array_map(
            function($fd) use ($mode) {
                return fopen("php://fd/$fd", $mode);
            },
            $fds
        );
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
