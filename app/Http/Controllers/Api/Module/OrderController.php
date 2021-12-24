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
   * @api {get} /api/member/order/task 01. 处理打印任务
   * @apiDescription 处理指定订单的打印任务
   * @apiGroup 23. 订单模块
   *
   * @apiParam {String} id 订单自增编号
   * @apiParam {String} start 开始打印页码
   * @apiParam {String} end 结束打印页码
   *
   * @apiSampleRequest /api/member/order/task
   * @apiVersion 1.0.0
   */
  public function task(Request $request)
  {
    $messages = [
      'id.required' => '请您输入订单自增编号',
      'start.required' => '请您输入开始打印页码',
      'end.required' => '请您输入结束打印页码',

    $rule = [
      'id' => 'required',
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
        $model = Resource::getRow(['order_id' => $request->id]);

        $model->organization_id = self::getOrganizationId();
        $model->first_level_agent_id = $request->first_level_agent_id;
        $model->second_level_agent_id = $request->second_level_agent_id;
        $model->manager_id = $request->manager_id;
        $model->member_id = self::getCurrentId();
        $model->printer_id = $request->printer_id;
        $model->title = $request->filename;
        $model->page_total = $request->page_total;
        $model->save();

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
   * @api {post} /api/member/order/result 02. 报告打印结果
   * @apiDescription 报告当前会员打印文件结果
   * @apiGroup 23. 会员订单模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {String} order_id 订单自增编号
   * @apiParam {String} code 打印状态
   * @apiParam {String} reason 失败描述
   * @apiParam {String} url 打印文件地址
   *
   * @apiParamExample {json} Param-Example:
   * {
   *   "id": 1,
   *   "sign": "sfdsfsfqwecvsefsdff@asdfsdfd2f",
   *   "version": "0.01",
   *   "printerSn": "M88XB7BN",
   *   "deviceNameAndModel": "QX616"
   * }
   *
   * @apiSampleRequest /api/member/order/result
   * @apiVersion 1.0.0
   */
  public function result(Request $request)
  {
    $messages = [
      'order_id.required' => '请您输入订单自增编号',
      'code.required' => '请您输入打印状态',

    $rule = [
      'order_id' => 'required',
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
      {
        $model = Order::getRow(['id' => $request->order_id]);

        $model->organization_id = self::getOrganizationId();
        $model->first_level_agent_id = $request->first_level_agent_id;
        $model->second_level_agent_id = $request->second_level_agent_id;
        $model->manager_id = $request->manager_id;
        $model->member_id = self::getCurrentId();
        $model->printer_id = $request->printer_id;
        $model->title = $request->filename;
        $model->page_total = $request->page_total;
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
