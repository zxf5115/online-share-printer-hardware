<?php
namespace App\Services;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Illuminate\Support\Facades\Log;

use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;

/**
 * WebSocket
 */
class WebSocketService implements WebSocketHandlerInterface
{
  public function __construct()
  {}


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-20
   * ------------------------------------------
   * 连接建立时触发
   * ------------------------------------------
   *
   * 具体描述一些细节
   *
   * @param Server $server [description]
   * @param Request $request [description]
   * @return [type]
   */
  public function onOpen(Server $server, Request $request)
  {
    // 在触发 WebSocket 连接建立事件之前，Laravel 应用初始化的生命周期已经结束，你可以在这里获取 Laravel 请求和会话数据
    // 调用 push 方法向客户端推送数据，fd 是客户端连接标识字段
    Log::info('WebSocket 连接建立');

    $server->push($request->fd, 'Welcome to WebSocket Server built on LaravelS');
  }

  // 收到消息时触发
  public function onMessage(Server $server, Frame $frame)
  {
    // 调用 push 方法向客户端推送数据
    $server->push($frame->fd, 'This is a message sent from WebSocket Server at ' . date('Y-m-d H:i:s'));
  }


  // public function onRequest(Server $server, $fd, $reactorId)
  // {

  // }




  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-20
   * ------------------------------------------
   * 连接断开
   * ------------------------------------------
   *
   * 连接断开销毁资源
   *
   * @param Server $server [description]
   * @param [type] $fd [description]
   * @param [type] $reactorId [description]
   * @return [type]
   */
  public function onClose(Server $server, $fd, $reactorId)
  {
    Log::info('WebSocket 连接关闭');
  }
}
