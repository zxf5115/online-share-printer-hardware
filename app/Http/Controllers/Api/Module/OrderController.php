<?php
namespace App\Http\Controllers\Api\Module;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Constant\Code;
use App\Models\Common\Module\Order;
use App\Models\Common\Module\Order\Resource;
use App\Http\Controllers\Api\BaseController;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-07-01
 *
 * 订单控制器类
 */
class OrderController extends BaseController
{
  /**
   * @api {get} /api/order/task 01. 处理打印任务
   * @apiDescription 处理指定订单的打印任务
   * @apiGroup 23. 订单模块
   *
   * @apiParam {String} order_id 订单自增编号
   * @apiParam {String} start 开始打印页码
   * @apiParam {String} end 结束打印页码
   *
   * @apiSampleRequest /api/order/task
   * @apiVersion 1.0.0
   */
  public function task(Request $request)
  {
    $messages = [
      'order_id.required' => '请您输入订单自增编号',
      'start.required' => '请您输入开始打印页码',
      'end.required' => '请您输入结束打印页码',
    ];

    $rule = [
      'order_id' => 'required',
      'start' => 'required',
      'end' => 'required',
    ];

    // 验证用户数据内容是否正确
    $validation = self::validation($request, $messages, $rule);

    if(!$validation['status'])
    {
      return $validation['message'];
    }
    else
    {
      try
      {
        $model = Resource::getRow(['order_id' => $request->order_id]);

        $response = $model->url;

        return self::success($response);
      }
      catch(\Exception $e)
      {
        // 记录异常信息
        self::record($e);

        return self::error(Code::HANDLE_FAILURE);
      }
    }
  }


  /**
   * @api {post} /api/order/result 02. 报告打印结果
   * @apiDescription 报告当前打印文件结果
   * @apiGroup 23. 订单模块
   *
   * @apiParam {String} order_id 订单自增编号
   * @apiParam {String} code 打印状态
   * @apiParam {String} reason 失败描述
   * @apiParam {String} items 打印项目
   *
   * @apiParamExample {json} Param-Example:
   * {
   *   "orderId": 1, // 上面下发打印任务时给的
   *   "code": 5, // 状态：0正常 非0异常
   *   "reason": "缺纸", // 失败原因
   *   "items": [{
   *     "id": "1",
   *     "printPages": 3 // 该文件打印了多少张
   *   }],
   * }
   *
   * @apiSampleRequest /api/order/result
   * @apiVersion 1.0.0
   */
  public function result(Request $request)
  {
    $messages = [
      'orderId.required' => '请您输入订单自增编号',
      'code.required' => '请您输入打印状态',

    $rule = [
      'orderId' => 'required',
      'code' => 'required',
    ];

    // 验证用户数据内容是否正确
    $validation = self::validation($request, $messages, $rule);

    if(!$validation['status'])
    {
      return $validation['message'];
    }
    else
    {
      try
      {\Log::error($request->orderId);
        $model = Order::getRow(['id' => $request->orderId]);

        if(0 == $request->code)
        {
          $model->order_status = 2;
        }
        else
        {
          $model->order_status = 3;
        }

        $model->error_status = $request->code;
        $model->remark       = $request->reason;
        $model->save();

        return self::success($model);
      }
      catch(\Exception $e)
      {
        // 记录异常信息
        self::record($e);

        return self::error(Code::HANDLE_FAILURE);
      }
    }
  }
}
