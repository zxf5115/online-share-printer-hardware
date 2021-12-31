<?php
namespace App\Tasks\Socket;

use Hhxsv5\LaravelS\Swoole\Task\Task;

/**
 * 打印任务
 */
class PrintTask extends Task
{
  private $data;
  private $result;

  public function __construct($data)
  {
    $this->data = $data;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-31
   * ------------------------------------------
   * 函数功能简述
   * ------------------------------------------
   *
   * 处理任务的逻辑，运行在Task进程中，不能投递任务
   *
   * @return [type]
   */
  public function handle()
  {
    \Log::info(__CLASS__ . ':handle start', [$this->data]);
    sleep(2);// 模拟一些慢速的事件处理
    // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
    $this->result = 'the result of ' . $this->data;
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
