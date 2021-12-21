<?php
namespace App\Http\Controllers\Api\Module;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Constant\Code;
use App\Events\Common\Sms\CodeEvent;
use App\Events\Common\Message\SmsEvent;
use App\Http\Controllers\Api\BaseController;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-06-11
 *
 * 会员控制器类
 */
class MemberController extends BaseController
{
  // 模型名称
  protected $_model = 'App\Models\Api\Module\Member';

  // 排序方式
  protected $_order = [
    ['key' => 'id', 'value' => 'asc'],
  ];

  // 关联对象
  protected $_relevance = [
    'archive' => [
      'archive',
    ],
    'data' => [
      'archive',
    ],
  ];


  /**
   * @api {get} /api/member/archive 01. 当前会员档案
   * @apiDescription 获取当前会员的档案信息
   * @apiGroup 20. 会员模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiSuccess (字段说明|会员) {String} id 会员编号
   * @apiSuccess (字段说明|会员) {String} role_id 角色编号
   * @apiSuccess (字段说明|会员) {String} avatar 会员头像
   * @apiSuccess (字段说明|会员) {String} username 登录账户
   * @apiSuccess (字段说明|会员) {String} nickname 会员姓名
   * @apiSuccess (字段说明|档案) {String} sex 性别
   * @apiSuccess (字段说明|档案) {String} age 年龄
   * @apiSuccess (字段说明|档案) {String} province_id 省
   * @apiSuccess (字段说明|档案) {String} city_id 市
   * @apiSuccess (字段说明|档案) {String} region_id 县
   * @apiSuccess (字段说明|档案) {String} address 详细地址
   *
   * @apiSampleRequest /api/member/archive
   * @apiVersion 1.0.0
   */
  public function archive(Request $request)
  {
    try
    {
      // 获取当前会员基础查询条件
      $condition = self::getCurrentWhereData('id');

      $condition = array_merge($condition, $this->_where);

      // 获取关联对象
      $relevance = self::getRelevanceData($this->_relevance, 'archive');

      $response = $this->_model::getRow($condition, $relevance);

      return self::success($response);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return self::error(Code::ERROR);
    }
  }



  /**
   * @api {get} /api/member/status 02. 当前会员是否填写资料
   * @apiDescription 获取当前会员是否填写资料信息
   * @apiGroup 20. 会员模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiSuccess (字段说明) {String} data true|false
   *
   * @apiSampleRequest /api/member/status
   * @apiVersion 1.0.0
   */
  public function status(Request $request)
  {
    try
    {
      $response = false;

      // 获取当前会员基础查询条件
      $condition = self::getCurrentWhereData('id');

      $condition = array_merge($condition, $this->_where);

      $result = $this->_model::getRow($condition);

      if(!empty($result) || !empty($result->archive))
      {
        $response = true;
      }

      return self::success($response);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return self::error(Code::ERROR);
    }
  }


  /**
   * @api {post} /api/member/handle 03. 当前会员填写信息
   * @apiDescription 当前会员填写信息
   * @apiGroup 20. 会员模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} avatar 会员头像
   * @apiParam {string} nickname 会员姓名
   * @apiParam {string} [sex] 会员性别
   * @apiParam {string} [age] 会员性别
   * @apiParam {string} [province_id] 省
   * @apiParam {string} [city_id] 市
   * @apiParam {string} [region_id] 县
   *
   * @apiSampleRequest /api/member/handle
   * @apiVersion 1.0.0
   */
  public function handle(Request $request)
  {
    $messages = [
      'nickname.required' => '请您输入会员姓名',
      'avatar.required'   => '请您上传会员头像',
    ];

    $rule = [
      'nickname' => 'required',
      'avatar'   => 'required',
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
        // 获取当前会员编号
        $member_id = self::getCurrentId();

        $model = $this->_model::firstOrNew(['id' => $member_id]);

        $model->avatar   = $request->avatar;
        $model->nickname = $request->nickname;
        $model->save();

        $data = [
          'sex'         => $request->sex ?? '1',
          'age'         => $request->age ?? '1',
          'province_id' => $request->province_id ?? '',
          'city_id'     => $request->city_id ?? '',
          'region_id'   => $request->region_id ?? '',
          'address'     => $request->address ?? '',
        ];

        if(!empty($data))
        {
          $model->archive()->delete();
          $model->archive()->create($data);
        }

        DB::commit();

        return self::success(Code::message(Code::HANDLE_SUCCESS));
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
   * @api {post} /api/member/change_code 04. 手机验变更证码[略]
   * @apiDescription 获取当前会员的修改验证码
   * @apiGroup 20. 会员模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} username 旧手机号码（18201018888）
   *
   * @apiSampleRequest /api/change_code
   * @apiVersion 1.0.0
   */
  public function change_code(Request $request)
  {
    $messages = [
      'username.required' => '请输入用户名称',
      'username.regex'    => '手机号码不合法',
    ];
    $rule = [
      'username' => 'required',
      'username' => 'regex:/^1[3456789][0-9]{9}$/',     //正则验证
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
        $username = $request->username;

        $condition = self::getSimpleWhereData($username, 'username');

        $result = $this->_model::getRow($condition);

        if(!empty($result) && !empty($result->username))
        {
          return self::error(Code::CURRENT_MOBILE_EMPTY);
        }

        // 发送修改验证码
        event(new SmsEvent($username, 4));

        return self::success(Code::message(Code::SEND_SUCCESS));
      }
      catch(\Exception $e)
      {
        // 记录异常信息
        self::record($e);

        return self::error(Code::ERROR);
      }
    }
  }


  /**
   * @api {post} /api/member/change_mobile 05. 变更手机号码[略]
   * @apiDescription 修改当前会员的手机号码
   * @apiGroup 20. 会员模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} username 手机号码
   * @apiParam {string} sms_code 验证码
   *
   * @apiSampleRequest /api/change_mobile
   * @apiVersion 1.0.0
   */
  public function change_mobile(Request $request)
  {
    $messages = [
      'username.required' => '请您输入手机号码',
      'username.regex'    => '手机号码不合法',
      'sms_code.required' => '请您输入验证码',
    ];

    $rule = [
      'username' => 'required',
      'username' => 'regex:/^1[3456789][0-9]{9}$/',     //正则验证
      'sms_code' => 'required',
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
        $username = $request->username;

        $sms_code = $request->sms_code;

        // 比对验证码
        $status = event(new CodeEvent($username, $sms_code, 4));

        // 验证码错误
        if(empty($status))
        {
          return self::error(Code::VERIFICATION_CODE);
        }

        $condition = self::getSimpleWhereData($username, 'username');

        $model = Member::getRow($condition);

        if(empty($model->id))
        {
          return self::error(Code::MEMBER_EMPTY);
        }

        if(!empty($model->open_id))
        {
          return self::error(Code::CURRENT_MOBILE_BIND);
        }

        $model->open_id = $request->open_id;

        $response = $model->save();

        return self::success(Code::$message[Code::HANDLE_SUCCESS]);
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
   * @api {get} /api/member/data 06. 会员数据
   * @apiDescription 根据会员编号获取会员数据
   * @apiGroup 20. 会员模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {int} id 会员编号
   *
   * @apiSuccess (字段说明|会员) {String} id 会员编号
   * @apiSuccess (字段说明|会员) {String} role_id 角色编号
   * @apiSuccess (字段说明|会员) {String} avatar 会员头像
   * @apiSuccess (字段说明|会员) {String} username 登录账户
   * @apiSuccess (字段说明|会员) {String} nickname 会员姓名
   * @apiSuccess (字段说明|档案) {String} sex 性别
   * @apiSuccess (字段说明|档案) {String} age 年龄
   * @apiSuccess (字段说明|档案) {String} province_id 省
   * @apiSuccess (字段说明|档案) {String} city_id 市
   * @apiSuccess (字段说明|档案) {String} region_id 县
   * @apiSuccess (字段说明|档案) {String} address 详细地址
   *
   * @apiSampleRequest /api/member/data
   * @apiVersion 1.0.0
   */
  public function data(Request $request)
  {
    try
    {
      $condition = self::getSimpleWhereData($request->id);

      // 获取关联对象
      $relevance = self::getRelevanceData($this->_relevance, 'data');

      $response = $this->_model::getRow($condition, $relevance);

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
