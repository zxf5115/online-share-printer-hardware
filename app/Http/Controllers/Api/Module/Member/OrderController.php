<?php
namespace App\Http\Controllers\Api\Module\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Constant\Code;
use App\Models\Api\Module\Order;
use App\Http\Controllers\Api\BaseController;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-07-01
 *
 * 会员订单控制器类
 */
class OrderController extends BaseController
{
  /**
   * @api {post} /api/member/order/result 03. 报告打印结果
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


  /**
   * @api {post} /api/member/order/second_step 04. 打印第二步
   * @apiDescription 当前会员打印第二步: 确认打印份数
   * @apiGroup 23. 会员订单模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {Array} order_id 订单自增编号
   * @apiParam {String} print_total 打印份数
   *
   * @apiSampleRequest /api/member/order/second_step
   * @apiVersion 1.0.0
   */
  public function second_step(Request $request)
  {
    $messages = [
      'order_id.required' => '请您输入订单自增编号',
      'print_total.required' => '请您输入打印份数',
    ];

    $rule = [
      'order_id' => 'required',
      'print_total' => 'required',
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

        $model->print_total = $request->print_total;
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


  /**
   * @api {post} /api/member/order/pay 05. 订单支付[TODO]
   * @apiDescription 当前会员订单支付
   * @apiGroup 23. 会员订单模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} order_id 订单自增编号
   *
   * @apiSampleRequest /api/member/order/pay
   * @apiVersion 1.0.0
   */
  public function pay(Request $request)
  {
    $messages = [
      'order_id.required' => '请您输入订单自增编号',
    ];

    $rule = [
      'order_id' => 'required',
    ];

    // 验证用户数据内容是否正确
    $validation = self::validation($request, $messages, $rule);

    if(!$validation['status'])
    {
      return $validation['message'];
    }
    else
    {
      DB::beginTransaction();

      try
      {
        $member_id = self::getCurrentId();

        $condition = self::getCurrentWhereData();

        $where = ['id' => $request->order_id];

        $condition = array_merge($condition, $where);

        $model = Order::getRow($condition);

        // 如果订单数据为空或者用户信息为空
        if(empty($model) || empty($member_id))
        {
          return self::error(Code::DATA_ERROR);
        }

        // 支付
        $result = event(new PayEvent($model));

        if(empty($result[0]))
        {
          return self::error(Code::PAY_ERROR);
        }

        $response = $result[0];

        DB::commit();

        return self::success($response);
      }
      catch(\Exception $e)
      {
        DB::rollback();

        // 记录异常信息
        self::record($e);

        return self::error(Code::HANDLE_FAILURE);
      }
    }
  }


  /**
   * @api {post} /api/member/order/delete 06. 删除记录
   * @apiDescription 当前会员把课程删除购物车
   * @apiGroup 23. 会员订单模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} id 订单自增编号
   *
   * @apiSampleRequest /api/member/order/delete
   * @apiVersion 1.0.0
   */
  public function delete(Request $request)
  {
    $messages = [
      'id.required' => '请您输入订单自增编号',
    ];

    $rule = [
      'id' => 'required',
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
        Order::destroy($request->id);

        return self::success(Code::message(Code::HANDLE_SUCCESS));
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
