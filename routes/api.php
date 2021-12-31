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


      // 订单路由
      $api->group(['prefix'  => 'order'], function ($api) {
        $api->get('task', 'OrderController@task');
        $api->post('result', 'OrderController@result');
      });
    });
  });
});
