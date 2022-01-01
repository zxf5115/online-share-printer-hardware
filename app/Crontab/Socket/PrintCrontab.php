<?php
namespace App\Crontab\Socket;

use Swoole\Coroutine;
use Illuminate\Support\Facades\Log;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

use App\Tasks\Socket\PrintTask;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-12-31
 *
 * 打印定时任务
 */
class PrintCrontab extends CronJob
{
  protected $i = 0;

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-31
   * ------------------------------------------
   * 每%s秒运行一次
   * ------------------------------------------
   *
   * 每%s秒运行一次
   *
   * @return [type]
   */
  public function interval()
  {
    return 1000;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-31
   * ------------------------------------------
   * 是否立即执行第一次
   * ------------------------------------------
   *
   * 是否立即执行第一次，false则等待间隔时间后执行第一次
   *
   * @return boolean
   */
  public function isImmediate()
  {
    return false;
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
  public function run()
  {
    $result = Task::deliver(new PrintTask());


  }
}
