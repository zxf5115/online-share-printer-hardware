<?php
namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;

use zxf5115\Upload\File;
use App\Http\Controllers\Api\BaseController;
use App\Http\Constant\Common\System\Code;

/**
 * @author zhangxiaofei [<1326336909@qq.com>]
 * @dateTime 2021-04-20
 *
 * 文件上传接口控制器类
 */
class FileController extends BaseController
{
  /**
   * @api {post} /api/file/file 01. 上传文件
   * @apiDescription 通过base64的内容进行文件上传
   * @apiGroup 03. 上传模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} file 文件数据
   * @apiParam {string} [category] 文件分类 excel word pdf video audio ...
   *
   * @apiSuccess (字段说明) {string} data 文件地址
   *
   * @apiSampleRequest /api/file/file
   * @apiVersion 1.0.0
   */
  public function file(Request $request)
  {
    try
    {
      $category = $request->category ?? 'file';

      $response = File::file_base64($request->file, $category);

      // 如果返回错误代码
      if(false === strpos($response, 'http'))
      {
        return self::message($response);
      }

      return self::success($response);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return self::error(Code::FILE_UPLOAD_ERROR);
    }
  }


  /**
   * @api {post} /api/file/picture 02. 上传图片
   * @apiDescription 通过base64的内容进行图片上传
   * @apiGroup 03. 上传模块
   * @apiPermission jwt
   * @apiHeader {String} Authorization 身份令牌
   * @apiHeaderExample {json} Header-Example:
   * {
   *   "Authorization": "Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiO"
   * }
   *
   * @apiParam {string} file 图片数据
   * @apiParam {string} [category] 图片分类 picture avatar ...
   *
   * @apiSuccess (字段说明) {string} data 图片地址
   *
   * @apiSampleRequest /api/file/picture
   * @apiVersion 1.0.0
   */
  public function picture(Request $request)
  {
    try
    {
      $category = $request->category ?? 'picture';

      $response = File::picture_base64($request->file, $category);

      // 如果返回错误代码
      if(false === strpos($response, 'http'))
      {
        return self::message($response);
      }

      return self::success($response);
    }
    catch(\Exception $e)
    {
      // 记录异常信息
      record($e);

      return self::error(Code::FILE_UPLOAD_ERROR);
    }
  }
}
