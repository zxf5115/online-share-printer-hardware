<?php
namespace App\TraitClass;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2020-10-22
 *
 * 工具特征
 */
trait ToolTrait
{
  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-28
   * ------------------------------------------
   * 解析数据
   * ------------------------------------------
   *
   * 将下游发来的字节流字符串数据进行解析
   *
   * @return [type]
   */
  public static function parseData($data)
  {
    // 将下游发过来的字节流字符串转换为ascii值
    $data = self::stringToAscii($data);

    // 根据0xff(255) 0xfe(254) 分割ascii值
    $data = self::partitionPackage($data);

    return json_decode($data, true);
  }

  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-28
   * ------------------------------------------
   * 分割ascii值数据包
   * ------------------------------------------
   *
   * 将ascii值数据包按照边界值进行分割
   *
   * @param [type] $data [description]
   * @return [type]
   */
  public static function partitionPackage($data)
  {
    $response = '';

    // 根据ascii值 255 254 进行分割数据
    $data = explode('255 254 ', $data);

    foreach($data as $item)
    {
      $str = '';

      // 过滤两边多余空格字符和预定义字符
      $item = trim($item);

      // 如果内容为空, 跳过本次操作
      if(empty($item))
      {
        continue;
      }

      // 将本次数据根据 空格 分割成数组
      $result = explode(' ', $item);


      // TODO: 暂时没发现不检测包长有什么问题
      // 过滤掉前两位下游数据包长度
      $result = array_slice($result, 2);

      // 将ascii值转义ascii图形
      foreach($result as $vo)
      {
        $response .= chr($vo);
      }
    }

    return $response;
  }




  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-28
   * ------------------------------------------
   * 将字节流字符串转换为ascii值字符串
   * ------------------------------------------
   *
   * 将下游传过来的字节流字符串转换为ascii值字符串
   *
   * @param [type] $data 字节流字符串
   * @return [type]
   */
  public static function stringToAscii($data)
  {
    if(empty($data))
    {
      return '';
    }

    for($i = 0; $i < strlen($data); $i++)
    {
      $bytes[] = ord($data[$i]);
    }

    return trim(implode(' ', $bytes));
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-28
   * ------------------------------------------
   * 将字符串转换为字节流数据
   * ------------------------------------------
   *
   * 将字符串转换为字节流数据
   *
   * @param [type] $data 字符串
   * @return [type]
   */
  public static function stringAddPrefix($data)
  {
    $total = strlen($data);

    $prefix = chr(0xff) . chr(0xfe) . chr(0) . chr($total);

    return $prefix . $data;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-12-30
   * ------------------------------------------
   * 获取当前毫秒时间戳
   * ------------------------------------------
   *
   * 获取当前毫秒时间戳
   *
   * @return [type]
   */
  public static function msectime()
  {
    list($msec, $sec) = explode(' ', microtime());

    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

    return $msectime;
  }


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2022-01-07
   * ------------------------------------------
   * 将大PDF文件分割成多个小PDF文件，返回文件地址
   * ------------------------------------------
   *
   * 将大PDF文件分割成多个小PDF文件，返回文件地址
   *
   * @param [type] $page_total 文件页数
   * @return [type]
   */
  public static function getSeparateFileUrl($page_total, $threshold = 10)
  {
    $response = [];

    for($i = 1; $i < 20; $i++)
    {
      $total = bcdiv($page_total, $i);

      if($threshold > $total)
      {
        break;
      }
    }

    for($x = 0; $x < $i; $x++)
    {
      $page = bcmul($x, 10);

      $start = bcadd($page, 1);

      $end = bcadd($page, 10);

      $end = $end > $page_total ? $page_total : $end;

      $response[] = "$start-$end";
    }

    return $response;
  }
}
