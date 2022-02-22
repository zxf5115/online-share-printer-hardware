<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

use App\Enum\BaseEnum;
use App\Models\Platform\System\Config;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-01-07
 *
 * 基础模型类
 */
class Base extends Model
{
  /**
   * 表名
   */
  protected $table = '';


  // 是否由模型去维护时间戳字段，如果我们想手动去维护，可以设置为 false
  public $timestamps = true;


  /**
   * mass assignment 的时候可以批量设置的属性，目的是防止用户提交我们不想更新的字段
   * 注意：
   *      和 $guarded 同时使用的时候， $guard 设置的会无效
   */
  protected $fillable = [];


  /**
   * 其中一个用法，根据现有某几个属性，计算出新属性，并在 模型 toArray 的时候显示
   * usage：
   *      模型里面定义： protected $appends = ['full_name'];
   *      public function getFullNameAttribute() { return $this->firstName . ' ' . $this->lastName; }
   */
  protected $appends = [];


  /**
   * 隐藏的属性，我们调用模型的 toArray 方法的时候不会得到该数组中的属性，
   * 如果需要也得到隐藏属性，可以通过 withHidden 方法
   */
  protected $hidden = [];


  /**
   * 默认属性值
   * @var [type]
   */
  protected $attributes = [
    'status' => 1
  ];

  /**
   * 需要进行时间格式转换的字段
   * 应用场景：
   *      一般情况我们只定义了 created_at、updated_at，我们还可能会保存用户注册时间这些，register_time，
   *      这样我们就可以定义，protected $dates = ['register_time'];
   * 好比如：
   *      我们定义的 $dateFormat 为 mysql 的 datetime 格式，我们即使把 register_time 设置为 time(),
   *      实际保存的其实是 datetime 格式的
   */
  protected $dates = [];

  /**
   * created_at、updated_at、$dates数组 进行时间格式转换的时候使用的格式
   * 默认使用 mysql 的 datetime 类型，如果需要更改为 10 位整型，可以设置 protected $dateFormat = 'U';
   */
  protected $dateFormat = 'U';

  /**
   * 创建时间戳字段名称
   */
  const CREATED_AT = 'create_time';

  /**
   * 更新时间戳字段名称
   */
  const UPDATED_AT = 'update_time';

  protected static $_with = false;

  protected static $_orwhere = false;



  /**
   * 转换属性类型
   */
  protected $casts = [
    'status' => 'array',
    'create_time' => 'datetime:Y-m-d H:i:s',
    'update_time' => 'datetime:Y-m-d H:i:s',
  ];


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-20
   * ------------------------------------------
   * 状态封装
   * ------------------------------------------
   *
   * 状态封装
   *
   * @param int $value 状态值
   * @return 状态信息
   */
  public function getStatusAttribute($value)
  {
    return BaseEnum::getStatus($value);
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-13
   * ------------------------------------------
   * 新增数据
   * ------------------------------------------
   *
   * 公有，新增数据
   *
   * @param [array] $request [请求数据]
   */
  public static function add($request)
  {
    try
    {
      return self::create($request);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-13
   * ------------------------------------------
   * 更新数据
   * ------------------------------------------
   *
   * 公有，更新数据
   *
   * @param [array] $request [请求数据]
   */
  public static function edit($request)
  {
    try
    {
      return self::where(['id' => $request['id']])->update($request);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }



  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-２8
   * ------------------------------------------
   * 批量更新数据
   * ------------------------------------------
   *
   * 批量更新数据
   *
   * @param [array] $request [请求数据]
   */
  public static function batchEdit($id, $data)
  {
    try
    {
      return self::whereIn('id', $id)->update($data);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }



  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-13
   * ------------------------------------------
   * 删除数据
   * ------------------------------------------
   *
   * 公有，删除数据
   *
   * @param [string] $id [请求id]
   */
  public static function remove($id)
  {
    try
    {
      if(is_array($id))
      {
        foreach($id as $item)
        {
          $model = self::find($item);
          $model->status = -1;
          $model->save();
        }

        // 不会触发updated
        // return self::whereIn('id', $id)->update(['status' => -1]);
      }
      else
      {
        $model = self::find($id);
        $model->status = -1;
        $model->save();

        // 不会触发updated
        // return self::where(['id' => $id])->update(['status' => -1]);
      }
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }



  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 单行查询
   * ------------------------------------------
   *
   * 根据查询条件获取一行数据
   *
   * @param array $condition [查询条件]
   * @param boolean $is_array 是否返回数组
   * @return [对象|数组]
   */
  public static function getRow($condition, $relevance = false, $is_array = false, $orders = false)
  {
    try
    {
      $model = self::buildWhere($condition, $relevance);

      $model = self::getRelevance($model, $relevance);

      // 判断是否存在排序
      if(is_array($orders))
      {
        foreach($orders as $order)
        {
          $model = $model->orderBy($order['key'], $order['value']);
        }
      }

      $response = $model->first();

      if($is_array)
      {
        $response = $response->toArray();
      }

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 多行查询
   * ------------------------------------------
   *
   * 根据查询条件获取多行数据
   *
   * @param array $condition [查询条件]
   * @param boolean $is_array 是否返回数组
   * @return [type]
   */
  public static function getList($condition, $relevance = false, $orders = false, $is_array = false, $limit = false)
  {
    try
    {
      // DB::connection()->enableQueryLog();#开启执行日志

      $model = self::buildWhere($condition, $relevance);

      $model = self::getRelevance($model, $relevance);

      // 判断是否存在排序
      if(is_array($orders))
      {
        foreach($orders as $order)
        {
          $model = $model->orderBy($order['key'], $order['value']);
        }
      }

      if($limit)
      {
        $model = $model->limit($limit);
      }
// DB::connection()->enableQueryLog();#开启执行日志
      $response = $model->get();
// dd(DB::getQueryLog()); //获取查询语句、参数和执行时间
      if($is_array)
      {
        $response = $response->toArray();
      }

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 查询一列数据
   * ------------------------------------------
   *
   * 根据查询条件获取多行数据
   *
   * @param array $condition [查询条件]
   * @param boolean $is_array 是否返回数组
   * @return [type]
   */
  public static function getPluck($field, $condition, $relevance = false, $orders = false, $is_array = false)
  {
    try
    {
      $model = self::buildWhere($condition, $relevance);

      $model = self::getRelevance($model, $relevance);

      // 判断是否存在排序
      if(is_array($orders))
      {
        foreach($orders as $order)
        {
          $model = $model->orderBy($order['key'], $order['value']);
        }
      }

      if(is_array($field))
      {
        $response = $model->pluck($field[0], $field[1]);
      }
      else
      {
        $response = $model->pluck($field);
      }

      if($is_array)
      {
        $response = $response->toArray();
      }

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2022-02-22
   * ------------------------------------------
   * 查询单个字段的值
   * ------------------------------------------
   *
   * 根据查询单个字段的值
   *
   * @param array $condition [查询条件]
   * @return [type]
   */
  public static function getValue($field, $condition)
  {
    try
    {
      $model = self::buildWhere($condition, false);

      $model = self::getRelevance($model, false);

      $response = $model->value($field);

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }



  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 查询条数
   * ------------------------------------------
   *
   * 根据查询条件获取条数
   *
   * @param array $condition [查询条件]
   * @param boolean $is_array 是否返回数组
   * @return [type]
   */
  public static function getCount($condition, $relevance = false)
  {
    try
    {
      $model = self::buildWhere($condition, $relevance);

      $response = $model->count();

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-01-19
   * ------------------------------------------
   * 分页查询
   * ------------------------------------------
   *
   * 根据查询条件获取分页数据
   *
   * @param array $condition 查询条件
   * @param array $relevance 关联查询对象
   * @param boolean $is_array 是否返回数组
   * @param integer $pageSize 分页数量
   * @return 分页数据
   */
  public static function getPaging($condition, $relevance = false, $orders = false, $is_array = false, $pageSize = 10)
  {
    try
    {
      $model = self::buildWhere($condition, $relevance);

      $model = self::getRelevance($model, $relevance);

      // 判断是否存在排序
      if(is_array($orders))
      {
        foreach($orders as $order)
        {
          $model = $model->orderBy($order['key'], $order['value']);
        }
      }
 // DB::connection()->enableQueryLog();#开启执行日志
      $response = $model->paginate($pageSize);
// dd(DB::getQueryLog()); //获取查询语句、参数和执行时间
      if($is_array)
      {
        $response = $response->toArray();
      }

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-08-21
   * ------------------------------------------
   * 组建主表查询条件
   * ------------------------------------------
   *
   * 组建主表查询条件
   *
   * @param [type] $condition 查询条件
   * @param [type] $relevance 关联表数组
   * @return [type]
   */
  public static function buildWhere($condition, $relevance)
  {
    $model = new static();

    $where = $whereIn = [];

    if(!empty($condition['with']))
    {
      self::$_with = $condition['with'];

      unset($condition['with']);
    }

    if(!empty($condition['orwhere']))
    {
      self::$_orwhere = $condition['orwhere'];

      unset($condition['orwhere']);
    }

    foreach($condition as $key => $item)
    {
      if(is_string($key) && is_array($relevance) && in_array($key, $relevance))
      {
        $where[$key] = $condition[$key];

        unset($condition[$key]);
      }
      else if(is_array($item) && count($item) != count($item, true))
      {
        $whereIn[$key] = $item;

        unset($condition[$key]);
      }
      else if(is_string($key) && is_array($item))
      {
        $where[$key] = $item;
      }
    }

    if(!empty($condition))
    {
      $model = self::where($condition);
    }

    if(!empty($where))
    {
      foreach($where as $key => $item)
      {
        $model = $model->whereHasIn($key, function($query) use ($item) {
                  $query->where($item);
                 });
      }
    }

    if(!empty($whereIn))
    {
      foreach($whereIn as $key => $item)
      {
        $model = $model->whereIn($item[0], $item[1]);
      }
    }

    if(!empty(self::$_orwhere))
    {
      $model = $model->orWhere(self::$_orwhere);
    }

    return $model;
  }




  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-14
   * ------------------------------------------
   * 组装关联关系信息
   * ------------------------------------------
   *
   * 组装关联关系信息
   *
   * @param [type] $model 操作模型
   * @param [type] $relevance 关联关系数组或者字符串
   * @param [type] $with 关联关系数组或者字符串的查询条件
   * @return [type]
   */
  public static function getRelevance($model, $relevance = false)
  {
    if(is_string($relevance))
    {
      if(!empty(self::$_with[$relevance]))
      {
        $model = $model->with([$relevance => function($query) use ($relevance)
                  {
                    return $query->where(self::$_with[$relevance]);
                  }]);
      }
      else
      {
        $model = $model->with($relevance);
      }
    }
    else if(is_array($relevance))
    {
      foreach($relevance as $item)
      {
        if(!empty(self::$_with[$item]))
        {
          $model = $model->with([$item => function($query) use ($item)
                    {
                      $query->where(self::$_with[$item]);
                    }]);
        }
        else
        {
          $model = $model->with($item);
        }
      }
    }

    return $model;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-06-11
   * ------------------------------------------
   * 已存在数据删除，不存在数据保存
   * ------------------------------------------
   *
   * 已存在数据删除，不存在数据保存
   *
   * @return [type]
   */
  public static function createOrDelete($where)
  {
    try
    {
      $model = static::firstOrNew($where);

      // 如果数据不存在，保存数据
      if(empty($model->id))
      {
        $model->save();

        return true;
      }
      // 如果数据存在，删除数据
      else
      {
        $model->delete();

        return false;
      }
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return false;
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-07-02
   * ------------------------------------------
   * 验证内容是否唯一
   * ------------------------------------------
   *
   * 验证内容是否唯一
   *
   * @param [type] $field 内容名称
   * @param [type] $value 内容信息
   * @return [type]
   */
  public static function validationOnly($field, $value)
  {
    try
    {
      $response = false;

      $id = $request->id ?? 0;

      $symbol_id = $request->symbol_id;

      $model = static::getRow([$field => $symbol_id]);

      if(!empty($model->id) && $model->id != $id)
      {
        $response = true;
      }

      return $response;
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return false;
    }
  }




  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-23
   * ------------------------------------------
   * 获取服务器域名
   * ------------------------------------------
   *
   * 获取服务器域名
   *
   * @return [type]
   */
  public static function getServerUrl()
  {
    try
    {
      $response = Config::where(['title' => 'web_url'])->first();

      return $response['value'] ?: '';
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return false;
    }
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-10-31
   * ------------------------------------------
   * 验证是否存在
   * ------------------------------------------
   *
   * 验证是否存在
   *
   * @param [type] $username [description]
   * @return boolean
   */
  public static function isExistField($value, $status, $field = 'username')
  {
    if(!empty($status))
    {
      return false;
    }

    $result = static::getRow([$field => $value, 'status' => 1]);

    if(!empty($result->id))
    {
      return true;
    }

    return false;
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
