<?php
namespace App\Tasks\Socket;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Hhxsv5\LaravelS\Swoole\Task\Task;

use App\TraitClass\ToolTrait;
use App\Http\Constant\RedisKey;
use App\Models\Common\Module\Order;
use App\Models\Common\Module\Printer;


/**
 * 打印任务
 */
class PrintTask extends Task
{
  public function __construct()
  {

  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-31
   * ------------------------------------------
   * 处理打印任务
   * ------------------------------------------
   *
   * 处理任务的逻辑，运行在Task进程中，不能投递任务
   *
   * @return [type]
   */
  public function handle()
  {
    try
    {
      // 打印队列Socket消耗
      $key = RedisKey::SOCKET_PRINT_QUEUE;

      if(0 == Redis::llen($key))
      {
        Log::info('Print Queue: 队列为空');

        return false;
      }

      // 取出打印队列第一个元素
      $order_id = Redis::lpop($key);

      $model = Order::getRow(['id' => $order_id]);

      if(empty($model->id))
      {
        // 打印失败，将内容添加到打印头部，等待二次打印
        Redis::lpush($key, $order_id);

        Log::error('Print Queue: 订单不存在');

        return false;
      }

      $data = [
        'orderId' => strval($order_id),
        'items' => [
          [
            'id' => "1",
            'fileId' => strval($order_id),
            'url' => 'https://printer.vstown.cc/api/order/task',
            'pages' => "1-1",
            'copies' => $model->print_total
          ]
        ]
      ];

      // 内容转换为json
      $data = json_encode($data);

      // 将内容添加包头信息
      $message = ToolTrait::stringAddPrefix($data);

      // 获取当前订单使用的打印机
      $printer = Printer::getRow(['id' => $model->printer_id]);

      if(empty($printer->id))
      {
        // 打印失败，将内容添加到打印头部，等待二次打印
        Redis::lpush($key, $order_id);

        Log::error('Print Queue: 打印机未找到');

        return false;
      }

      // 获得打印机当前socket识别号
      $client_id = $printer->client_id;

      if(empty($client_id))
      {
        // 打印失败，将内容添加到打印头部，等待二次打印
        Redis::lpush($key, $order_id);

        Log::error('Print Queue: 打印机识别号未找到');

        return false;
      }

      // 发送打印任务消息
      app('swoole')->send($client_id, $message);

      if(0 != swoole_last_error())
      {
        // 打印失败，将内容添加到打印头部，等待二次打印
        Redis::lpush($key, $order_id);

        Log::error('Print Queue: Socket 错误 [' . swoole_last_error() . ']');

        return false;
      }
      else
      {
        Log::info('Print Queue: 发送完成');
      }
    }
    catch(\Exception $e)
    {
      // 打印失败，将内容添加到打印头部，等待二次打印
      Redis::lpush($key, $order_id);

      Log::error('Print Queue: 异常');

      record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-31
   * ------------------------------------------
   * 函数功能简述
   * ------------------------------------------
   *
   * 具体描述一些细节
   *
   * @return [type]
   */
  public function finish()
  {
    \Log::info(__CLASS__ . ':finish start', [$this->result]);
    Task::deliver(new TestTask2('task2')); // 投递其他任务
  }
}
