<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Http\Constant\Code;
use App\Http\Constant\Status;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-12-26
 *
 * 基础接口控制器类
 */
class BaseController extends Controller
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  /**
   * 当前用户
   */
  protected $user = null;

  /**
   * 模型名称
   */
  protected $_model = '';


  /**
   * 关联表
   */
  protected $_relevance = false;


  /**
   * 查询条件
   */
  protected $_where = [];


  /**
   * 搜索条件
   */
  protected $_params = [
    'title',
    'username',
    'realname',
  ];

  /**
   * 附加搜索条件
   */
  protected $_addition = [];


  /**
   * 关联查询条件
   */
  protected $_with = [];

  /**
   * 或者查询条件
   */
  protected $_orwhere = [];


  /**
   * 可查询字段与查理类型
   */
  protected $_filter = [
    // 模糊查询
    'like' => [
      'name',
      'code',
      'title',
      'mobile',
      'content',
      'nickname',
      'username',
      'realname',
      'order_no',
      'weixin',
      'operator',
    ],
    // 时间查询
    'time' => [
      'add_time',
      'valid_time',
      'create_time',
      'audit_time',
    ],
    // 小于查询
    'less' => [],
    // 大于查询
    'greater' => []
  ];


  /**
   * 排序
   */
  protected $_order = [
    ['key' => 'create_time', 'value' => 'desc'],
  ];

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-04-16
   * ------------------------------------------
   * 系统默认首页
   * ------------------------------------------
   *
   * 系统默认首页
   *
   * @return [type]
   */
  public function index()
  {
    try
    {
      return self::success();
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-12-28
   * ------------------------------------------
   * 对请求参数信息进行过滤与组装
   * ------------------------------------------
   *
   * 对请求参数信息进行过滤与组装
   *
   * @param [array] $request [请求参数]
   * @return [过滤与组装后的请求参数]
   */
  public function filter($request)
  {
    $condition = $relevance_condition = $with_condition = [];

    $params = [];
    $relevance_params = [];
    $with_params = [];

    if(!empty($_orwhere))
    {
      $condition['orwhere'] = $__orwhere;
    }

    foreach($request as $key => $value)
    {
      if(empty($value) && $value != 0)
      {
        continue;
      }

      if(in_array($key, $this->_params))
      {
        $params[$key] = $value;
      }

      foreach($this->_addition as $k => $v)
      {
        if(false !== strpos($key, $k))
        {
          $field = $k . '_';
          $field = str_replace($field, '', $key);

          if(in_array($field, $v))
          {
            $relevance_params[$k][$field] = $value;
          }
        }
        else if(in_array($key, $v) && false !== strpos($key, '_'))
        {
          $relevance_params[$k][$key] = $value;
        }
      }

      foreach($this->_with as $k => $v)
      {
        if(in_array($key, $v))
        {
          $with_params[$k][$key] = $value;
        }
      }
    }

    if(!empty($params))
    {
      // 请求过滤操作
      foreach($params as $key => $param)
      {
        if($param == '')
        {
          unset($params[$key]);
          continue;
        }

        // 模糊查询
        if(in_array($key, $this->_filter['like']))
        {
          $condition[] = [$key, 'like', '%'.$param.'%'];
        }
        // 时间查询
        else if(in_array($key, $this->_filter['time']))
        {
          if(is_array($param))
          {
            $start_time = strtotime($param[0]);
            $end_time = strtotime($param[1]);

            $condition[] = [$key, '>=', $start_time];
            $condition[] = [$key, '<=', $end_time];
          }
          else
          {
            $start_time = strtotime($param);
            $end_time = $start_time + 86400;

            $condition[] = [$key, '>=', $start_time];
            $condition[] = [$key, '<=', $end_time];
          }

        }
        // 小于查询
        else if(in_array($key, $this->_filter['less']))
        {
          $condition[] = [$key, '<', $param];
        }
        // 大于查询
        else if(in_array($key, $this->_filter['greater']))
        {
          $condition[] = [$key, '>', $param];
        }
        // 等于查询
        else
        {
          $condition = array_merge($condition, [$key => $param]);
        }
      }

      if(!empty($where))
      {
        $condition = array_merge($condition, $where);
      }
    }


    if(!empty($relevance_params))
    {
      foreach($relevance_params as $k => $params)
      {
        $condition[$k] = [];

        // 请求过滤操作
        foreach($params as $key => $param)
        {
          if($param == '')
          {
            unset($params[$key]);
            continue;
          }

          if(in_array($key, $this->_filter['like']))
          {
            $condition[$k][] = [$key, 'like', '%'.$param.'%'];
          }
          else if(in_array($key, $this->_filter['time']))
          {
            $start_time = strtotime($param[0]);
            $end_time = strtotime($param[1]);

            $condition[$k][] = [$key, '>=', $start_time];
            $condition[$k][] = [$key, '<=', $end_time];
          }
          // 小于查询
          else if(in_array($key, $this->_filter['less']))
          {
            $condition[$k][] = [$key, '<', $param];
          }
          // 大于查询
          else if(in_array($key, $this->_filter['greater']))
          {
            $condition[$k][] = [$key, '>', $param];
          }
          else
          {
            $condition[$k] = array_merge($condition[$k], [$key => $param]);
          }
        }

        if(empty($condition[$k]))
        {
          unset($condition[$k]);
        }
      }
    }

    if(!empty($with_params))
    {
      foreach($with_params as $k => $params)
      {
        $condition[$k] = [];

        // 请求过滤操作
        foreach($params as $key => $param)
        {
          if($param == '')
          {
            unset($params[$key]);
            continue;
          }

          if(in_array($key, $this->_filter['like']))
          {
            $condition['with'][$k][] = [$key, 'like', '%'.$param.'%'];
          }
          else if(in_array($key, $this->_filter['time']))
          {
            $start_time = strtotime($param[0]);
            $end_time = strtotime($param[1]);

            $condition['with'][$k][] = [$key, '>=', $start_time];
            $condition['with'][$k][] = [$key, '<=', $end_time];
          }
          else
          {
            if(empty($condition['with']))
            {
              $condition['with'][$k] = [$key => $param];
            }
            else
            {
              $condition['with'][$k] = array_merge($condition['with'][$k], [$key => $param]);
            }
          }
        }
      }
    }

    return $condition;
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
      ['status', '>', Status::DELETE]
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
      ['status', '>', Status::DELETE]
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
      ['status', '>', Status::DELETE],
      $field => $member_id,
      'organization_id' => $organization_id,
    ];

    return $condition;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-11-23
   * ------------------------------------------
   * 获得查询当前用户字段
   * ------------------------------------------
   *
   * 获得查询当前用户字段
   *
   * @return [type]
   */
  public static function getCurrentUserQueryField()
  {
    $response = 'member_id';

    $level = auth('api')->user()->level;

    $role_id = auth('api')->user()->role_id;

    if(2 == $role_id)
    {
      $response = 'manager_id';
    }
    else if(3 == $role_id)
    {
      if(1 == $level)
      {
        $response = 'first_level_agent_id';
      }
      else if(2 == $level)
      {
        $response = 'second_level_agent_id';
      }
    }

    return $response;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-12-07
   * ------------------------------------------
   * 获取关联查询数据
   * ------------------------------------------
   *
   * 获取关联查询数据
   *
   * @param [type] $relevance 关联数组
   * @param string $type [description]
   * @return [type]
   */
  public static function getRelevanceData($relevance, $type = 'list')
  {
    $response = false;

    if(isset($relevance[$type]))
    {
      $response = $relevance[$type];
    }
    else
    {
      $response = $relevance;
    }

    return $response;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-09-29
   * ------------------------------------------
   * 组装关联数据
   * ------------------------------------------
   *
   * 组装关联数据
   *
   * @param array $request 关联数据数组
   * @param string $field 关联字段
   * @return [type]
   */
  public static function packRelevanceData($request, $field, $is_json = false)
  {
    $response = [];

    $result = $request->$field;

    if($is_json)
    {
      $result = json_decode($request->$field, true);
    }

    if(empty($result))
    {
      return $response;
    }

    foreach($result as $key => $item)
    {
      $response[$key][$field]        = $item;
      $response[$key]['organization_id'] = static::getOrganizationId();
    }

    return $response;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-12-26
   * ------------------------------------------
   * 数据验证
   * ------------------------------------------
   *
   * 验证数据用户提交的数据内容
   *
   * @param [type] $request 用户请求数据
   * @param [type] $messages 数据错误返回信息
   * @param [type] $rule 验证规则
   * @return [type]
   */
  public static function validation($request, $messages, $rule)
  {
    $response = [
      'status' => true,
      'message' => ''
    ];

    $validator = Validator::make($request->all(), $rule, $messages);

    if($validator->fails())
    {
      $error = $validator->getMessageBag()->toArray();
      $error = array_values($error);
      $message = $error[0][0] ?? Code::$message[Code::ERROR];

      $response = [
        'status' => false,
        'message' => self::message($message)
      ];
    }

    return $response;
  }





  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 成功信息
   * ------------------------------------------
   *
   * 返回成功信息
   *
   * @param array $data [数据信息]
   * @return 成功信息
   */
  public static function success($data = [], $message = '')
  {
    $code = Code::SUCCESS;

    $headers = ['content-type' => 'application/json'];

    $response = \Response::json($data);

    return $response->withHeaders($headers);
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 失败信息
   * ------------------------------------------
   *
   * 返回错误信息
   *
   * @param integer $code 错误代码
   * @return 错误信息
   */
  public static function error($code = 1000)
  {
    $headers = ['content-type' => 'application/json'];

    $response = \Response::json(['status' => $code, 'message' => Code::message($code)]);

    return $response->withHeaders($headers);
  }

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 返回信息
   * ------------------------------------------
   *
   * 返回信息
   *
   * @param integer $code 错误代码
   * @return 错误信息
   */
  public static function message($message)
  {
    $headers = ['content-type' => 'application/json'];

    $response = \Response::json(['status' => 0, 'message' => $message]);
    return $response->withHeaders($headers);
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-12-28
   *
   * 本地环境下进行日志输出
   *
   * @param    [object]     $exception    [异常对象]
   *
   * @return   [false|错误]
   */
  public static function record(\Exception $exception)
  {
    if('local' == config('app.debug'))
    {
      dd($exception);
      // dd($exception->getLine());
      // dd($exception->getFile());
      // dd($exception->getMessage());
    }
    else
    {
      \Log::error($e);
    }
  }
}
