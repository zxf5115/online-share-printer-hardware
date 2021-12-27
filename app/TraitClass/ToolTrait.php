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
  public static function int2byte($data)
  {
    $data = intval($data);

    $byte[0] = ($val >> 24 & 0xFF);
    $byte[1] = ($val >> 16 & 0xFF);
    $byte[2] = ($val >> 8 & 0xFF);
    $byte[3] = ($val & 0xFF);

    return $byte;
  }


  public static function byte2int($data)
  {
    $data = intval($data);

    $byte[0] = ($val >> 24 & 0xFF);
    $byte[1] = ($val >> 16 & 0xFF);
    $byte[2] = ($val >> 8 & 0xFF);
    $byte[3] = ($val & 0xFF);

    return $byte;
  }



  public static function string2byte($data)
  {
    $bytes = array();

    for($i = 0; $i < strlen($data); $i++)
    {
      $bytes[] = dechex(ord($data[$i]));
    }

    return implode(' ', $bytes);
  }


  public function byte2string($data)
  {
    $result = '';

    foreach($data as $ch)
    {
      $result .= chr(hexdec($ch));
    }

    if(0 === json_last_error())
    {
      if(!is_null(json_decode($str)))
      {
        // 删除控制字符，ps: 换行、缩进、空格
        $result = preg_replace('/[[:cntrl:]]/', '', $result);

        $result = json_decode($result, true);
      }
    }

    return $result;
  }
}
