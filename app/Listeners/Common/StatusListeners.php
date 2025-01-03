<?php
namespace App\Listeners\Common;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\Common\StatusEvent;
use App\Models\Common\Module\Printer;
use App\Models\Common\Module\Printer\Log;

/**
 * 打印机状态监听器
 */
class StatusListeners
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
   * @param  StatusEvent  $event
   * @return void
   */
  public function handle(StatusEvent $event)
  {
    DB::beginTransaction();

    try
    {
      // 客户端数据
      $data = $event->data;

      // 客户端编号
      $client_id = $event->client_id;

      $printer_id = $data['terminalId'];

      $type = $data['status'];

      $content = $data['display'];

      $paper_quantity = $data['totalEnginePageCount'];

      $ink_quantity = $data['inkKLeft'];

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

      if(empty($printer->id))
      {
        return false;
      }

      if(0 != $type)
      {
        $printer->increment('failure_number');
      }

      $printer->client_id = $client_id;
      $printer->paper_quantity = $paper_quantity;
      $printer->ink_quantity = $ink_quantity;
      $printer->save();

      if(0 != $type)
      {
        $log = new Log();

        $log->printer_id = $printer_id;
        $log->type = $type;
        $log->content = $content;
        $log->operator = '客户端上报';
        $log->save();
      }

      DB::commit();
    }
    catch(\Exception $e)
    {
      DB::rollback();

      // 记录异常信息
      record($e);

      return false;
    }
  }
}
