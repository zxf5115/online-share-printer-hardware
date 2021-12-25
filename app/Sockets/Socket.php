<?php
namespace App\Sockets;

use Swoole\Server;
use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;

/**
 * WebSocket
 */
class Socket extends TcpSocket
{
  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-25
   * ------------------------------------------
   * 连接
   * ------------------------------------------
   *
   * 客户端连接
   *
   * @param Server $server [description]
   * @param [type] $fd [description]
   * @param [type] $reactorId [description]
   * @return [type]
   */
  public function onConnect(Server $server, $fd, $reactorId)
  {
    \Log::info('New TCP connection', [$fd]);

    $server->send($fd, 'Welcome to Socket.');
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-25
   * ------------------------------------------
   * 接收消息
   * ------------------------------------------
   *
   * 接收客户端消息
   *
   * @param Server $server [description]
   * @param [type] $fd [description]
   * @param [type] $reactorId [description]
   * @param [type] $data [description]
   * @return [type]
   */
  public function onReceive(Server $server, $fd, $reactorId, $data)
  {
    \Log::info('Received data', [$fd, $data]);

    $server->send($fd, 'LaravelS: ' . $data);

    if ($data === "quit0xff0x**")
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
