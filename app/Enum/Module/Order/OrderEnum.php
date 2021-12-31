<?php
namespace App\Enum\Module\Order;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-06-29
 *
 * 订单枚举类
 */
class OrderEnum
{
  // 支付状态
  const ALI     = 1; // 支付宝
  const WECHAT  = 2; // 微信
  const BALANCE = 3; // 余额
  const APPLE   = 4; // 苹果

  const WAIT   = 0; // 待支付
  const PAY    = 1; // 已支付
  const FINISH = 2; // 已完成
  const ERROR  = 3; // 订单异常
  const CANCEL = 4; // 已取消
  const BACK   = 5; // 已退款


  // 支付类型
  public static $pay_type = [
    self::ALI       => [
      'value' => self::ALI,
      'text' => '支付宝'
    ],

    self::WECHAT => [
      'value' => self::WECHAT,
      'text' => '微信'
    ],

    self::BALANCE => [
      'value' => self::BALANCE,
      'text' => '余额'
    ],

    self::APPLE => [
      'value' => self::APPLE,
      'text' => '苹果'
    ],
  ];


  // 支付类型
  public static $pay = [
    self::WAIT       => [
      'value' => self::WAIT,
      'text' => '待支付'
    ],

    self::FINISH => [
      'value' => self::FINISH,
      'text' => '已支付'
    ],
  ];

  // 支付类型
  public static $order = [
    self::WAIT       => [
      'value' => self::WAIT,
      'text' => '待开始'
    ],

    self::PAY => [
      'value' => self::PAY,
      'text' => '已支付'
    ],

    self::FINISH => [
      'value' => self::FINISH,
      'text' => '已完成'
    ],

    self::ERROR => [
      'value' => self::ERROR,
      'text' => '订单异常'
    ],

    self::CANCEL => [
      'value' => self::CANCEL,
      'text' => '已取消'
    ],

    self::BACK => [
      'value' => self::BACK,
      'text' => '已退款'
    ],
  ];

  // 打印类型
  public static $type = [
    self::ALI       => [
      'value' => self::ALI,
      'text' => '黑白'
    ],

    self::WECHAT => [
      'value' => self::WECHAT,
      'text' => '彩色'
    ],
  ];


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-01-16
   * ------------------------------------------
   * 打印类型封装
   * ------------------------------------------
   *
   * 打印类型封装
   *
   * @param int $code 信息代码
   * @return 信息内容
   */
  public static function getTypeStatus($code)
  {
    return self::$type[$code] ?: self::$type[self::ALI];
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-01-16
   * ------------------------------------------
   * 支付类型封装
   * ------------------------------------------
   *
   * 支付类型封装
   *
   * @param int $code 信息代码
   * @return 信息内容
   */
  public static function getPayTypeStatus($code)
  {
    return self::$pay_type[$code] ?: self::$pay_type[self::ALI];
  }

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-01-16
   * ------------------------------------------
   * 支付状态封装
   * ------------------------------------------
   *
   * 支付状态封装
   *
   * @param int $code 信息代码
   * @return 信息内容
   */
  public static function getPayStatus($code)
  {
    return self::$pay[$code] ?: self::$pay[self::WAIT];
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-01-16
   * ------------------------------------------
   * 订单状态封装
   * ------------------------------------------
   *
   * 订单状态封装
   *
   * @param int $code 信息代码
   * @return 信息内容
   */
  public static function getOrderStatus($code)
  {
    return self::$order[$code] ?: self::$order[self::WAIT];
  }

}
