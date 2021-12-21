<?php
namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use App\Http\Constant\Code;
use App\Http\Constant\RedisKey;
use App\Http\Controllers\Api\BaseController;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-10-24
 *
 * 系统控制器类
 */
class SystemController extends BaseController
{
  // 模型名称
  protected $_model = 'App\Models\Api\System\Config';


  /**
   * @api {get} /api/system/kernel 01. 系统信息
   * @apiDescription 获取系统配置内容信息
   * @apiGroup 02. 公共模块
   *
   * @apiSuccess (字段说明) {String} web_chinese_name 网站中文名称
   * @apiSuccess (字段说明) {String} web_english_name 网站英文名字
   * @apiSuccess (字段说明) {String} web_url 站点链接
   * @apiSuccess (字段说明) {String} keywords 网站关键字
   * @apiSuccess (字段说明) {String} description 网站描述
   * @apiSuccess (字段说明) {String} logo 网站logo
   * @apiSuccess (字段说明) {String} mobile 公司电话
   * @apiSuccess (字段说明) {String} email 公司邮箱
   * @apiSuccess (字段说明) {String} copyright 备案号
   * @apiSuccess (字段说明) {String} web_status 站点状态
   * @apiSuccess (字段说明) {String} web_close_info 站点关闭原因
   *
   * @apiSampleRequest /api/system/kernel
   * @apiVersion 1.0.0
   */
  public function kernel(Request $request)
  {
    try
    {
      // 平台核心数据Reids Key
      $key = RedisKey::KERNEL;

      if(Redis::exists($key))
      {
        $data = Redis::get($key);

        $response = unserialize($data);
      }
      else
      {
        $where = self::getSimpleWhereData(1, 'category_id');

        $field = ['value', 'title'];

        $response = $this->_model::getPluck($field, $where);

        $data = serialize($response);

        Redis::set($key, $data);
      }

      return self::success($response);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return self::error(Code::ERROR);
    }
  }
}
