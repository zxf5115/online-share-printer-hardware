<?php
namespace App\Enum;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-09-25
 *
 * 基础枚举类
 */
class BaseEnum
{
  // 状态封装
  const ENABLE  = 1;
  const DISABLE = 2;
  const DELETE  = -1;
  const VANISH  = -2;


  // 状态封装
  public static $status = [
    self::ENABLE       => [
      'value' => self::ENABLE,
      'text' => '正常'
    ],

    self::DISABLE       => [
      'value' => self::DISABLE,
      'text' => '禁用'
    ],

    self::DELETE       => [
      'value' => self::DELETE,
      'text' => '删除'
    ],

    self::VANISH => [
      'value' => self::VANISH,
      'text' => '消失'
    ]
  ];


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2020-09-25
   * ------------------------------------------
   * 状态类型封装
   * ------------------------------------------
   *
   * 状态类型封装
   *
   * @param int $code 状态代码
   * @return 状态信息
   */
  public static function getStatus($code)
  {
    return self::$status[$code] ?: self::$status[self::ENABLE];
  }
}
