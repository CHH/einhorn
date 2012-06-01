<?php

namespace Einhorn;

class Client
{
    protected
        $socket;

    static function forPath($path)
    {
        $socket = stream_socket_client("unix://$path");

        if (false === $socket) {
            throw new \UnexpectedValueException("Could not open connection to Unix Socket '$path'.");
        }

        return new static($socket);
    }

    # Creates a Client from a File descriptor number.
    #
    # fd - File Descriptor number.
    #
    # Returns a Client instance.
    static function forFd($fd)
    {
        $socket = fopen("php://fd/$fd", "rb");

        if (false === $socket) {
            throw new \UnexpectedValueException("Could not open FD '$fd'.");
        }

        return new static($socket);
    }

    function __construct($socket)
    {
        if (!is_resource($socket)) {
            throw new \InvalidArgumentException("Socket is not a valid resource.");
        }

        $this->socket = $socket;
    }

    # Sends a manual ACK to the Einhorn Master when Einhorn is started
    # with `-m manual`.
    #
    # pid - PID of the current process (default: null).
    #
    # Returns Nothing.
    function ack($pid = null)
    {
        if (null === $pid) $pid = getmypid();

        $req = array(
            "command" => "worker:ack",
            "pid" => $pid
        );

        $this->command($req);
    }

    # Sends a command to the Einhorn Master. For commands which return
    # a response (e.g. "state") you must call `recv()` afterwards to get
    # the message.
    #
    # command - An array describing the command and its arguments.
    #
    # Returns Nothing or the decoded Response as Array.
    function command($command)
    {
        $req = json_encode($command) . "\n";
        $this->write($req);
    }

    # Writes bytes to the Einhorn Master.
    #
    # data - Bytes to write as String.
    #
    # Returns the Number of Bytes written.
    function write($data)
    {
        return fwrite($this->socket, $data);
    }

    # Reads a response message from the Einhorn Master. Note: This blocks until
    # a message is available!
    #
    # Returns the message as Array.
    function recv()
    {
        $line = fgets($this->socket);
        return json_decode($line, true);
    }
}
