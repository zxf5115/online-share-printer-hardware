z<?php
namespace App\Http\Controllers\Api\Module\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;
use App\Http\Constant\Code;
use App\TraitClass\ToolTrait;
use App\Models\Common\Module\Printer;
use App\Http\Controllers\Api\BaseController;
use Yansongda\Pay\Exceptions\GatewayException;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-12-31
 *
 * 回调控制器类
 */
class NotifyController extends BaseController
{

  /**
   * @api {post} /api/common/notify/wechat 14. 微信支付回调
   * @apiDescription 获取微信支付回调
   * @apiGroup 02. 公共模块
   *
   * @apiSampleRequest /api/common/notify/wechat
   * @apiVersion 1.0.0
   */
  public function wechat(Request $request)
  {
    DB::beginTransaction();

    try
    {
      $message = [
        'orderId' => "81",
        'items' => [
          [
            'id' => "1",
            'fileId' => "81",
            'url' => 'https://printer.vstown.cc/api/order/task',
            'pages' => "1-1",
            'copies' => null
          ]
        ]
      ];


      $message = json_encode($message);


      $message = ToolTrait::stringAddPrefix($message);

      $printer = Printer::getRow(['id' => 13]);

      $client_id = $printer->client_id;

      app('swoole')->send($client_id, $message);




      // Log::info('微信支付回调开始 <================ 支付中');

      // $config = config('pay.wechat');

      // $pay = Pay::wechat($config);

      // $data = $pay->verify(); // 验签

      // Log::debug('微信回调参数', $data->all());

      // $order_no = $data->out_trade_no;

      // Log::info('订单编号====' . $order_no);

      // $where = [
      //   'id'     => $order_no,
      //   'status' => 1
      // ];

      // $model = Money::getRow($where);

      // if(empty($model->id))
      // {
      //   return false;
      // }

      // $model->confirm_status = 1;
      // $model->save();

      // // 充值
      // event(new AssetEvent($model->member_id, $model->money));

      // Log::info('支付成功');

      // DB::commit();

      // return $pay->success()->send();
    }
    catch(\Exception $e)
    {
      DB::rollback();

      $content = '在文件 ' . $e->getFile();
      $content .= ' 的 ' . $e->getLine();
      $content .= ' 行 ' .$e->getMessage();

      Log::info('支付失败====' . $content);
    }
  }
}
