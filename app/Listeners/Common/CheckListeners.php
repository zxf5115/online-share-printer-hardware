<?php
namespace App\Listeners\Common;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\Common\CheckEvent;
use App\Models\Common\Module\Printer;

/**
 * 打印机校验监听器
 */
class CheckListeners
{
  /**
   * Create the event listener.
   *
   * @return void
   */
  public function __construct()
  {
      //
  }

  /**
   * Handle the event.
   *
   * @param  CheckEvent  $event
   * @return void
   */
  public function handle(CheckEvent $event)
  {
    try
    {
      // 客户端数据
      $printer_id = $event->printer_id;

      // 客户端编号
      $client_id = $event->client_id;

      $printer = Printer::getRow(['client_id' => $client_id]);

      // 如果打印机存在当前识别码，取消以前的
      if(!empty($printer->id))
      {
        if($printer_id != $printer->id)
        {
          $printer->client_id = 0;
          $printer->save();
        }
      }

      $printer = Printer::getRow(['id' => $printer_id]);

      // 如果打印机不存在取消以前的
      if(empty($printer->id))
      {
        return false;
      }

      $printer->client_id = $client_id;
      $printer->save();
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return false;
    }
  }
}
