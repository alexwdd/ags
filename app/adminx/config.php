<?php
return [
    //权限认证
    "rbac" => [
        'USER_AUTH_ON'=>true,
        'USER_AUTH_TYPE' => 1,        // 默认认证类型 1 登录认证 2 实时认证
        'USER_AUTH_KEY'  =>'authId', // 用户认证SESSION标记
        'ADMIN_AUTH_KEY' =>'administrator',
        'NOT_AUTH_ACTION' => [ // 默认无需认证操作
            'adminx/index/index',
            'adminx/index/main',
            'adminx/index/menu',
            'adminx/upload/index',
            'adminx/editor/index'            
        ]
    ],

    'DB_DIR' => './databak/',

    //模板布局
    'template' => [
        'layout_on' => true,
        'layout_name' => 'layout',
    ],    

    "TABLE_MODEL"=>array(
        0=>array("id"=>1,"name"=>"文章","show"=>1),
        1=>array("id"=>2,"name"=>"商品","show"=>1),
        2=>array("id"=>3,"name"=>"视频","show"=>0),
        3=>array("id"=>4,"name"=>"图片","show"=>0),
        4=>array("id"=>5,"name"=>"下载","show"=>0),
        5=>array("id"=>6,"name"=>"广告","show"=>1),
        6=>array("id"=>7,"name"=>"友情链接","show"=>1),
        7=>array("id"=>8,"name"=>"留言","show"=>1),
    ),

    'leftMenu' => [
        [
            'menuId' => "1",
            'menuName'=>'后台应用',
            'menuIcon'=>'fa-cubes',
            'menuHref'=>'',
            'parentMenuId'=>"0",
        ], 
        [
            'menuId' => "1001",
            'menuName'=>'内容管理',
            'menuIcon'=>'fa-file',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ], 
        [
            'menuId' => "1002",
            'menuName'=>'会员管理',
            'menuIcon'=>'fa-user',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ],  
        [
            'menuId' => "1003",
            'menuName'=>'商城管理',
            'menuIcon'=>'fa-shopping-cart',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ],
        [
            'menuId' => "1004",
            'menuName'=>'订单管理',
            'menuIcon'=>'fa-list',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ],
        [
            'menuId' => "1005",
            'menuName'=>'收银台',
            'menuIcon'=>'fa-user-o',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ], 
        [
            'menuId' => "1006",
            'menuName'=>'财务管理',
            'menuIcon'=>'fa-rmb',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ],  
        [
            'menuId' => "1007",
            'menuName'=>'管理员设置',
            'menuIcon'=>'fa-user',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ], 
        [
            'menuId' => "1008",
            'menuName'=>'数据备份还原',
            'menuIcon'=>'fa-database',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ], 
        [
            'menuId' => "1009",
            'menuName'=>'系统设置',
            'menuIcon'=>'fa-cogs',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ]
    ]
];