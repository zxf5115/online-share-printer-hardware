<?php
namespace App\Sockets;

use Swoole\Server;
use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;

/**
 * WebSocket
 */
class Socket extends TcpSocket
{
  public function onConnect(Server $server, $fd, $reactorId)
  {
    \Log::info('New TCP connection', [$fd]);

    $server->send($fd, 'Welcome to Socket.');
  }

  public function onReceive(Server $server, $fd, $reactorId, $data)
  {
    \Log::info('Received data', [$fd, $data]);

    $server->send($fd, 'LaravelS: ' . $data);

    if ($data === "quit\r\n")
    {
      $server->send($fd, 'LaravelS: bye' . PHP_EOL);
      $server->close($fd);
    }
  }

  public function onClose(Server $server, $fd, $reactorId)
  {
    \Log::info('Close TCP connection', [$fd]);
    $server->send($fd, 'Goodbye');
  }
}
