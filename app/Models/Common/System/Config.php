<?php
namespace App\Models\Common\System;

use App\Models\Base;
use App\Models\Common\System\Config\Category;


/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-07-08
 *
 * 配置模型类
 */
class Config extends Base
{
  // 表名
  public $table = "system_config";

  // 隐藏的属性
  public $hidden = [
    'update_time'
  ];

  // 追加到模型数组表单的访问器
  public $appends = [];

  // 批量添加
  public $fillable = ['id'];



  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-12
   * ------------------------------------------
   * 状态属性类型转换函数
   * ------------------------------------------
   *
   * 状态属性类型转换函数
   *
   * @param int $value [数据库存在的值]
   * @return 状态值
   */
  public function getParamsAttribute($value)
  {
    $response = json_decode($value);

    if(empty($response))
    {
      return $value;
    }

    return $response;
  }



  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-14
   * ------------------------------------------
   * 判断当前使用的存储方式
   * ------------------------------------------
   *
   * 判断当前使用的存储方式，是使用本地存储还是阿里云还是七牛云
   *
   * @return boolean
   */
  public static function isLocalStorage()
  {
    try
    {
      $where = [
        'status' => 1,
        'title' => 'UPLOAD_CONFIG'
      ];

      $status = self::getCloudStorageTypeStatus();

      $response = Category::getRow($where, 'children');

      $data = $response->children ?? [];

      $result = [];

      foreach($data as $key => $item)
      {
        $config = $item->config[0] ?? [];

        if(!empty($config->title) && in_array($config->title, $status))
        {
          $result[$item->id] = $config->value;
        }
      }

      foreach($result as $id => $item)
      {
        if(1 == $item)
        {
          return $id;
        }
      }

      return 0;
    }
    catch(\Exception $e)
    {
      return false;
    }

  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-14
   * ------------------------------------------
   * 获取配置信息
   * ------------------------------------------
   *
   * 获取配置信息
   *
   * @param [type] $category_id 配置分类id
   * @return [type]
   */
  public static function getConfigData($category_id)
  {
    $model = new self();

    $where = [
      'status' => 1,
      'category_id' => $category_id
    ];

    return $model->getList($where);
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-14
   * ------------------------------------------
   * 获取配置值
   * ------------------------------------------
   *
   * 获取配置值
   *
   * @param [type] $category_id 配置标题
   * @return [type]
   */
  public static function getConfigValue($title)
  {
    $result = '';

    $model = new self();

    $where = [
      'status' => 1,
      'title' => $title
    ];

    $response = $model->getRow($where);

    if(!empty($response))
    {
      $result = $response->value;
    }

    return $result;
  }




  // 关联函数 ------------------------------------------------------

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-07-08
   * ------------------------------------------
   * 系统配置与系统配置分类关联函数
   * ------------------------------------------
   *
   * 系统配置与系统配置分类关联函数
   *
   * @return [关联对象]
   */
  public function category()
  {
    return $this->hasOne('App\Models\Common\System\Config\Category', 'id', 'category_id')->where(['status'=>1]);
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-03-07
   * ------------------------------------------
   * 注册关联删除
   * ------------------------------------------
   *
   * 注册关联删除
   *
   * @return [type]
   */
  public static function boot()
  {
    parent::boot();

    // 配置值小于15为系统核心配置不能删除不可以删除
    static::deleting(function($model) {
      if(31 > $model->id)
      {
        return false;
      }
    });
  }
}
