<?php
namespace App\Enum\Module\Order;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-06-29
 *
 * 订单日志枚举类
 */
class LogEnum
{
  const ONE  = 1; // 卡纸


  // 打印类型
  public static $type = [
    self::ONE       => [
      'value' => self::ONE,
      'text' => '卡纸'
    ],
  ];


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-01-16
   * ------------------------------------------
   * 日志类型封装
   * ------------------------------------------
   *
   * 日志类型封装
   *
   * @param int $code 信息代码
   * @return 信息内容
   */
  public static function getTypeStatus($code)
  {
    return self::$type[$code] ?: self::$type[self::ONE];
  }
}
