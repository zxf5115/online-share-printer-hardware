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
  protected $_model = 'App\Models\Api\Module\Member';

  /**
   * @api {post} /api/weixin_login 01. 微信登录
   * @apiDescription 通过第三方软件-微信，进行登录
   * @apiGroup 01. 登录模块
   * @apiParam {string} open_id 微信OpenID
   *
   * @apiSuccess (字段说明|令牌) {String} token 身份令牌
   * @apiSuccess (字段说明|用户) {Number} id 会员编号
   * @apiSuccess (字段说明|用户) {Number} role_id 角色编号 1会员 2店长 3分销商
   * @apiSuccess (字段说明|用户) {Number} open_id 微信编号
   * @apiSuccess (字段说明|用户) {Number} parent_id 上级分销商编号
   * @apiSuccess (字段说明|用户) {Number} level 分销商级别
   * @apiSuccess (字段说明|用户) {String} another_name 分销商别称
   * @apiSuccess (字段说明|用户) {String} avatar 会员头像
   * @apiSuccess (字段说明|用户) {String} username 登录账户
   * @apiSuccess (字段说明|用户) {String} nickname 会员昵称
   *
   * @apiSampleRequest /api/weixin_login
   * @apiVersion 1.0.0
   */
  public function weixin_login(Request $request)
  {
    $messages = [
      'open_id.required' => '请输入微信编号',
    ];

    $rule = [
      'open_id' => 'required',
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
        $condition = self::getSimpleWhereData($request->open_id, 'open_id');

        $response = $this->_model::getRow($condition);

        // 用户不存在
        if(is_null($response))
        {
          return self::error(Code::MEMBER_EMPTY);
        }

        // 用户已禁用
        if(2 == $response->status['value'])
        {
          return self::error(Code::MEMBER_DISABLE);
        }

        // 在特定时间内访问次数过多，就触发访问限制
        if($this->_model::AccessRestrictions($response))
        {
          return self::error(Code::ACCESS_RESTRICTIONS);
        }

        // 认证用户密码是否可以登录
        if (! $token = auth('api')->tokenById($response->id))
        {
          return self::error(Code::MEMBER_EMPTY);
        }

        // 获取客户端ip地址
        $response->last_login_ip = $request->getClientIp();

        $old_token = $response->remember_token;

        if(!empty($old_token))
        {
          \JWTAuth::setToken($old_token)->invalidate();
        }

        // 记录登录信息
        $this->_model::login($response, $request);

        return self::success([
          'code' => 200,
          'token' => $token,
          'token_type' => 'bearer',
          'expires_in' => auth('api')->factory()->getTTL() * 60,
          'user_info' => $response
        ]);
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
   * @api {post} /api/register 02. 用户注册
   * @apiDescription 注册用户信息
   * @apiGroup 01. 登录模块
   *
   * @apiParam {string} open_id 微信小程序编号
   * @apiParam {string} avatar 会员头像
   * @apiParam {string} nickname 会员姓名
   * @apiParam {string} [sex] 会员性别
   * @apiParam {string} [age] 会员性别
   * @apiParam {string} [province_id] 省
   * @apiParam {string} [city_id] 市
   * @apiParam {string} [region_id] 县
   * @apiParam {string} [address] 详细地址
   *
   * @apiSampleRequest /api/register
   * @apiVersion 1.0.0
   */
  public function register(Request $request)
  {
    $messages = [
      'open_id.required'  => '请您输入微信小程序编号',
      'nickname.required' => '请您输入会员姓名',
      'avatar.required'   => '请您上传会员头像',
    ];

    $rule = [
      'open_id'  => 'required',
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
        $model = $this->_model::firstOrNew([
          'open_id' => $request->open_id,
          'status' => 1
        ]);

        $model->open_id  = $request->open_id ?? '';
        $model->role_id  = 1;
        $model->avatar   = $request->avatar;
        $model->username = '';
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

        $data = [
          'money' => 0.00,
        ];

        if(!empty($data))
        {
          $model->asset()->delete();
          $model->asset()->create($data);
        }

        DB::commit();

        return self::success(Code::message(Code::REGISTER_SUCCESS));
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
   * @api {post} /api/bind_mobile 03. 绑定手机号码
   * @apiDescription 绑定用的的手机号码
   * @apiGroup 01. 登录模块
   *
   * @apiParam {string} open_id 微信小程序编号
   * @apiParam {string} username 手机号码
   *
   * @apiSampleRequest /api/bind_mobile
   * @apiVersion 1.0.0
   */
  public function bind_mobile(Request $request)
  {
    $messages = [
      'open_id.required'  => '请您输入微信小程序编号',
      'username.required' => '请您输入登录账户',
      'username.regex'    => '手机号码不合法',
    ];

    $rule = [
      'open_id'  => 'required',
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
        $condition = self::getSimpleWhereData($request->open_id, 'open_id');

        $model = $this->_model::getRow($condition);

        if(empty($model->id))
        {
          return self::error(Code::MEMBER_EMPTY);
        }

        $model->username = $request->username;
        $model->save();

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


  /**
   * @api {get} /api/logout 04. 退出
   * @apiDescription 退出登录状态
   * @apiGroup 01. 登录模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmN"
   * }
   *
   * @apiSampleRequest /api/logout
   * @apiVersion 1.0.0
   */
  public function logout()
  {
    try
    {
      auth('api')->logout();

      return self::success([], '退出成功');
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return self::error(Code::ERROR);
    }
  }
}
