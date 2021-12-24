<?php
namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Constant\Code;
use App\Http\Controllers\Api\BaseController;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-06-09
 *
 * 登录控制器
 */
class LoginController extends BaseController
{
  // 模型名称
  protected $_model = 'App\Models\Common\Module\Printer';

  /**
   * @api {post} /api/certification 01. 打印机认证
   * @apiDescription 认证打印机
   * @apiGroup 01. 登录模块
   * @apiParam {string} [id] 设备ID
   * @apiParam {string} [sign] 设备签名
   * @apiParam {string} [version] 版本
   * @apiParam {string} [printerSn] 打印机序列号
   * @apiParam {string} [deviceNameAndModel] 设备名称和型号
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
   * @apiSuccess (字段说明) {String} fid 设备编号
   * @apiSuccess (字段说明) {String} id 设备签名
   *
   * @apiSampleRequest /api/certification
   * @apiVersion 1.0.0
   */
  public function certification(Request $request)
  {
    $messages = [
      'printerSn.required' => '请输入打印机序列号',
    ];

    $rule = [
      'printerSn' => 'required',
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
        $condition = self::getSimpleWhereData($request->printerSn, 'code');

        $response = $this->_model::getRow($condition);

        // 用户不存在
        if(is_null($response))
        {
          return self::error(Code::PRINTER_EMPTY);
        }
\Log::error(time());
        $response = [
          'fid' => $response->id,
          'sign' => 'unkown'
        ];

        return self::success($response);
      }
      catch(\Exception $e)
      {
        // 记录异常信息
        self::record($e);

        return self::error(Code::ERROR);
      }
    }
  }
}
