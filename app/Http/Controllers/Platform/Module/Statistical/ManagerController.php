<?php
namespace App\Http\Controllers\Platform\Module\Statistical;

use Illuminate\Http\Request;

use App\Http\Constant\Code;
use App\Http\Constant\Parameter;
use App\Http\Controllers\Platform\BaseController;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-09-17
 *
 * 店长控制器类
 */
class ManagerController extends BaseController
{
  // 模型名称
  protected $_model = 'App\Models\Platform\Module\Manager';

  // 默认查询条件
  protected $_where = [
    'role_id' => 2
  ];

  // 客户端搜索字段
  protected $_params = [
    'parent_id',
    'username',
    'nickname'
  ];

  // 关联对象
  protected $_relevance = [
    'list' => [
      'parent',
      'archive',
      'asset'
    ],
    'view' => [
      'parent',
      'archive',
      'asset',
      'manager'
    ]
  ];


  /**
   * @author zhangxiaofei [<1326336909@qq.com>]
   * @dateTime 2021-04-16
   * ------------------------------------------
   * 收益总计
   * ------------------------------------------
   *
   * 收益总计
   *
   * @param Request $request [请求参数]
   * @return [type]
   */
  public function total(Request $request)
  {
    try
    {
      $condition = self::getBaseWhereData();

      $page = $request->limit ?? 10;

      // 对用户请求进行过滤
      $filter = $this->filter($request->all());

      $condition = array_merge($condition, $this->_where, $filter);

      $relevance = self::getRelevanceData($this->_relevance, 'list');

      $response = $this->_model::getList($condition, $relevance, $this->_order, false, $page);

      $total = 0.00;

      foreach($response as $item)
      {
        if(!empty($item->asset))
        {
          $total = bcadd($total, $item->asset->withdrawal_money, 2);
        }
      }

      return self::success($total);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      self::record($e);

      return self::error(Code::ERROR);
    }
  }
}