<?php
namespace App\Sockets;

use Swoole\Server;
use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;

use App\Events\Common\StatusEvent;

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
   * @param Server $server 服务器对象
   * @param [type] $client_id 客户端编号
   * @param [type] $data 客户端数据
   * @return [type]
   */
  public function onConnect(Server $server, $client_id, $from_id)
  {
    \Log::info('New TCP connection', [$client_id]);

    $server->send($client_id, 'Welcome to Socket.');
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
   * @param Server $server 服务器对象
   * @param [type] $client_id 客户端编号
   * @param [type] $from_id 来源线程
   * @param [type] $data 客户端数据
   * @return [type]
   */
  public function onReceive(Server $server, $client_id, $from_id, $data)
  {
    $data = rtrim($data, "0xff0x**");
\Log::error($data);
    $result = json_decode($data, true);
\Log::error($result);
    if(empty($result['type']))
    {
      $server->send($client_id, 'Socket: bye' . PHP_EOL);

      $server->close($client_id);
    }

    // 心跳包
    if(106 == $result['type'])
    {
      $printer_id = $result['terminalHeartbeat']['terminalId'];
      $client_time = $result['terminalHeartbeat']['terminalTime'];
\Log::error($printer_id);
\Log::error($client_time);
      $timestamp = bcsub(time(), $client_time);

      $message = 'heart beat delay:' . $timestamp . PHP_EOL;
\Log::error($message);
      $server->send($client_id, $message);
    }

    // 上报打印机状态
    if(103 == $result['type'])
    {
      event(new StatusEvent($result['printerMonitoring']));
    }


    // $result = explode("0xff0x**", $data);

    // foreach($result as $item)
    // {
    //   $server->send($client_id, $item);
    // }



    // if ($data === "quit0xff0x**")
    // {
    //   $server->send($client_id, 'LaravelS: bye' . PHP_EOL);
    //   $server->close($client_id);
    // }
  }






  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-25
   * ------------------------------------------
   * 关闭
   * ------------------------------------------
   *
   * 服务关闭
   *
   * @param Server $server 服务器对象
   * @param [type] $client_id 客户端编号
   * @param [type] $data 客户端数据
   * @return [type]
   */
  public function onClose(Server $server, $client_id, $from_id)
  {
    \Log::info('Close TCP connection', [$client_id]);

    $server->send($client_id, 'Goodbye');
  }
}
