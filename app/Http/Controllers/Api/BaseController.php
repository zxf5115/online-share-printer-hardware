<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Constant\Code;
use App\Http\Constant\Status;
use App\Http\Controllers\BaseController as Common;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-12-28
 *
 * 基础接口控制器类
 */
class BaseController extends Common
{
  /**
   * 当前用户
   */
  protected $user = null;

  /**
   * 搜索条件
   */
  protected $_params = [
    'title',
    'username',
    'realname',
  ];

  /**
   * 可查询字段与查理类型
   */
  protected $_filter = [
    // 模糊查询
    'like' => [
      'title',
      'nickname',
      'content',
      'username',
      'realname',
    ],
    // 时间查询
    'time' => [
      'create_time',
      'valid_time',
      'add_time',
    ],
    // 小于查询
    'less' => [],
    // 大于查询
    'greater' => []
  ];


  // 判断是否拥有权限
  public function __construct()
  {
  	// 这里额外注意了：官方文档样例中只除外了『login』
    // 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
    // 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
    // 不过刷新一次作废
    // 另外关于上面的中间件，官方文档写的是『auth:api』
    // 但是我推荐用 『jwt.auth』，效果是一样的，但是有更加丰富的报错信息返回
    // $this->middleware();

    $this->user = auth('api')->user();
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-02-12
   * ------------------------------------------
   * 获取列表信息
   * ------------------------------------------
   *
   * 获取列表信息
   *
   * @param Request $request [请求参数]
   * @return [type]
   */
  public function list(Request $request)
  {
    try
    {
      $condition = self::getBaseWhereData();

      $page = $request->limit ?? 10;

      // 对用户请求进行过滤
      $filter = $this->filter($request->all());

      $condition = array_merge($condition, $this->_where, $filter);

      $relevance = self::getRelevanceData($this->_relevance, 'list');

      $response = $this->_model::getPaging($condition, $relevance, $this->_order, false, $page);

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
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-02-12
   * ------------------------------------------
   * 获取列表信息
   * ------------------------------------------
   *
   * 获取列表信息
   *
   * @param Request $request [请求参数]
   * @return [type]
   */
  public function select(Request $request)
  {
    try
    {
      $condition = self::getBaseWhereData();

      // 对用户请求进行过滤
      $filter = $this->filter($request->all());

      $condition = array_merge($condition, $this->_where, $filter);

      $relevance = self::getRelevanceData($this->_relevance, 'select');

      $response = $this->_model::getList($condition, $relevance, $this->_order);

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
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-19
   * ------------------------------------------
   * 获取数据详情
   * ------------------------------------------
   *
   * 获取数据详情
   *
   * @param Request $request 请求参数
   * @param [type] $id 数据编号
   * @return [type]
   */
  public function view(Request $request, $id)
  {
    try
    {
      $condition = self::getBaseWhereData();

      $where = ['id' => $id];

      $condition = array_merge($condition, $where);

      $relevance = self::getRelevanceData($this->_relevance, 'view');

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
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-02-12
   * ------------------------------------------
   * 删除信息
   * ------------------------------------------
   *
   * 删除信息
   *
   * @param Request $request [请求参数]
   * @return [type]
   */
  public function delete(Request $request)
  {
    try
    {
      $id = json_decode($request->id) ?? [0];

      $response = $this->_model::remove($id);

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
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-03-07
   * ------------------------------------------
   * 获取当前登录用户的机构编号
   * ------------------------------------------
   *
   * 获取当前登录用户的机构编号
   *
   * @return 机构编号
   */
  public static function getOrganizationId()
  {
    try
    {
      return auth('api')->user()->organization_id ?? 0;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return 0;
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-03-07
   * ------------------------------------------
   * 获取当前用户编号
   * ------------------------------------------
   *
   * 获取当前用户的用户编号
   *
   * @return 用户编号
   */
  public static function getCurrentId()
  {
    try
    {
      return auth('api')->user()->id ?? 0;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return 0;
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-03-07
   * ------------------------------------------
   * 获取当前用户昵称
   * ------------------------------------------
   *
   * 获取当前用户的用户昵称
   *
   * @return 用户编号
   */
  public static function getCurrentNickname()
  {
    try
    {
      return auth('api')->user()->nickname ?? '';
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return 0;
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-03-07
   * ------------------------------------------
   * 获取当前用户角色编号
   * ------------------------------------------
   *
   * 获取当前用户角色编号
   *
   * @return 用户编号
   */
  public static function getCurrentRoleId()
  {
    try
    {
      return auth('api')->user()->role_id;
    }
    catch(\Exception $e)
    {

      return 0;
    }
  }

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-08
   * ------------------------------------------
   * 获取基础查询条件数据
   * ------------------------------------------
   *
   * 获取基础查询条件数据，状态为正常并且属于当前商户
   *
   * @return [type]
   */
  public static function getBaseWhereData($id = '', $field = 'id')
  {
    $organization_id = static::getOrganizationId();

    $condition = [
      ['status', '=', Status::ENABLE],
    ];

    if($organization_id)
    {
      $condition['organization_id'] = $organization_id;
    }

    if($id)
    {
      $condition[$field] = $id;
    }

    return $condition;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-08
   * ------------------------------------------
   * 根据ID组装查询条件数据
   * ------------------------------------------
   *
   * 根据用户传递过来的ID，组装查询条件数据
   *
   * @return [type]
   */
  public static function getSimpleWhereData($id = '', $field = 'id')
  {
    $condition = [
      ['status', '=', Status::ENABLE],
    ];

    if($id)
    {
      $condition[$field] = $id;
    }

    return $condition;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-08
   * ------------------------------------------
   * 获取当前会员查询条件数据
   * ------------------------------------------
   *
   * 获取当前会员查询条件数据，状态为正常并且属于当前商户
   *
   * @return [type]
   */
  public static function getCurrentWhereData($field = 'member_id')
  {
    $member_id       = static::getCurrentId();
    $organization_id = static::getOrganizationId();

    $condition = [
      ['status', '=', Status::ENABLE],
      $field => $member_id,
      'organization_id' => $organization_id,
    ];

    return $condition;
  }
}
