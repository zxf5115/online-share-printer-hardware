<?php
namespace App\Enum\Module\Printer;

use App\Enum\BaseEnum;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-09-04
 *
 * 打印机枚举类
 */
class PrinterEnum extends BaseEnum
{
  // 机器状态
  const ONLINE  = 1;
  const OFFLINE = 2;
  const FAULT   = 3;

  // 阅读状态
  public static $status = [
    self::ONLINE => [
      'value' => self::ONLINE,
      'text' => '在线'
    ],

    self::OFFLINE => [
      'value' => self::OFFLINE,
      'text' => '离线'
    ],

    self::FAULT => [
      'value' => self::FAULT,
      'text' => '故障'
    ]
  ];

  // 打印机类型封装
  public static $switch = [
    self::ONLINE => [
      'value' => self::ONLINE,
      'text' => '已绑定'
    ],

    self::OFFLINE => [
      'value' => self::OFFLINE,
      'text' => '未绑定'
    ]
  ];

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-09-04
   * ------------------------------------------
   * 机器状态状态值
   * ------------------------------------------
   *
   * 机器状态状态值
   *
   * @param int $code 信息代码
   * @return 信息内容
   */
  public static function getActivateStatus($code)
  {
    return self::$status[$code] ?: self::$status[self::ONLINE];
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-09-25
   * ------------------------------------------
   * 使用状态封装
   * ------------------------------------------
   *
   * 使用状态封装
   *
   * @param int $code 状态代码
   * @return 状态信息
   */
  public static function getBindStatus($code)
  {
    return self::$switch[$code] ?: self::$switch[self::OFFLINE];
  }
}
