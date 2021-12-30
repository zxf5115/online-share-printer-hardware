<?php
namespace App\Sockets;

use Swoole\Server;
use Illuminate\Support\Facades\Log;
use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;

use App\TraitClass\ToolTrait;
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
    Log::info('New TCP connection', [$client_id]);
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
    // Log::info('original data: ' . $data);

    $result = ToolTrait::parseData($data);

    Log::info('parsed data: start');
    Log::info($result);
    Log::info('parsed data: end');

    // 如果数据不存在 type, 为无效数据
    if(empty($result['type']))
    {
      Log::warning('Invalid TCP connection', [$client_id]);
    }

    // 心跳包
    if(106 == $result['type'])
    {
      // 获取打印机编号
      $printer_id = $result['terminalHeartbeat']['terminalId'];

      // 获取客户端发送时间
      $client_time = $result['terminalHeartbeat']['terminalTime'];

      // 计算当前时间与客户端发送时间的时间差, 客户端需要
      $timestamp = bcsub(ToolTrait::msectime(), $client_time);

      // 拼装发送给下游的消息数据
      $message = 'heart beat delay:' . $timestamp;

      // 将字符串添加前缀
      $message = ToolTrait::stringAddPrefix($message);

      Log::info('heart beat data: ' . $message);

      $server->send($client_id, $message);
    }


    // 上报打印机状态
    if(103 == $result['type'])
    {
      event(new StatusEvent($result['printerMonitoring']));
    }
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
    Log::info('Close TCP connection', [$client_id]);
  }
}
