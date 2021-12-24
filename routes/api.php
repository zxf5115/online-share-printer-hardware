<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
  'namespace'  =>  'Api',
  'middleware'  =>  'serializer:array'
], function ($api)
{
  $api->group([
    'middleware'  =>  'api.throttle', // 启用节流限制
    'limit'  =>  1000, // 允许次数
    'expires'  =>  1, // 分钟
  ], function ($api)
  {
    $api->group(['namespace' => 'System'], function ($api) {
      $api->post('certification', 'LoginController@certification'); // 认证
    });


    $api->group(['namespace' => 'Module'], function ($api) {



      // 常见问题路由
      $api->group(['prefix'  => 'problem'], function ($api) {
        $api->get('list', 'ProblemController@list');
        $api->get('select', 'ProblemController@select');
        $api->get('view/{id}', 'ProblemController@view');

        // 常见问题分类路由
        $api->group(['namespace' => 'Problem', 'prefix'  => 'category'], function ($api) {
          $api->get('select', 'CategoryController@select');
        });
      });


      // 打印价格路由
      $api->group(['prefix'  => 'price'], function ($api) {
        $api->get('select', 'PriceController@select');
        $api->get('view/{id}', 'PriceController@view');
      });


      // 会员路由
      $api->group(['prefix'  => 'member', 'middleware' => ['auth:api', 'refresh.token.api', 'failure']], function ($api) {
        $api->get('archive', 'MemberController@archive');
        $api->get('status', 'MemberController@status');
        $api->post('handle', 'MemberController@handle');
        $api->post('change_code', 'MemberController@change_code');
        $api->post('change_mobile', 'MemberController@change_mobile');
        $api->get('data', 'MemberController@data');


        // 会员关联内容路由
        $api->group(['namespace' => 'Member'], function ($api) {

          // 会员消息路由
          $api->group(['prefix'  => 'notice'], function ($api) {
            $api->get('list', 'NoticeController@list');
            $api->post('finish', 'NoticeController@finish');
          });


          // 会员投诉路由
          $api->group(['prefix'  => 'complain'], function ($api) {
            $api->get('list', 'ComplainController@list');
            $api->get('view/{id}', 'ComplainController@view');
            $api->post('handle', 'ComplainController@handle');
          });

          // 会员订单路由
          $api->group(['prefix'  => 'order'], function ($api) {
            $api->get('list', 'OrderController@list');
            $api->get('view/{id}', 'OrderController@view');
            $api->post('first_step', 'OrderController@first_step');
            $api->post('second_step', 'OrderController@second_step');
            $api->post('handle', 'OrderController@handle');
            $api->post('pay', 'OrderController@pay');
            $api->post('delete', 'OrderController@delete');
          });
        });
      });
    });
  });
});
