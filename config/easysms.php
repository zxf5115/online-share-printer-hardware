<?php
return [
  // HTTP 请求的超时时间（秒）
  'timeout' => 5.0,

  // 默认发送配置
  'default' => [
    // 网关调用策略，默认：顺序调用
    'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

    // 默认可用的发送网关
    'gateways' => [
      'huawei',
    ],
  ],

  // 可用的网关配置
  'gateways' => [
    'errorlog' => [
      'file' => storage_path().'/sms/easy-sms.log',
    ],

    'huawei' => [
      'endpoint' => '', // APP接入地址
      'app_key' => '', // APP KEY
      'app_secret' => '', // APP SECRET
      'from' => [
          'default' => '', // 通知类型
          'code' => '', // 验证码类型
      ],
      'callback' => '' // 短信状态回调地址
    ],
  ],
];
