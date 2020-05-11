<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
error_reporting(E_ERROR | E_PARSE );
return [
    //公众号
    'weixin'=>['appID'=>'wx858ef6b7d842c007','appsecret'=>'a767560a950671d28c640ccc27f3d6f7'],

    //澳邮
    'aue'=>['MemberId'=>'2818','Password'=>'AGSags28182818'],

    //omi支付
    'omipay' => ['mchID'=>'0042192497','key'=>'4138fdd18af64406a3a434db77b58327'],

    //财务类型
    'moneyType' => array(         
        1 => array('name'=>'澳币充值'),
        2 => array('name'=>'澳币消费'),
        3 => array('name'=>'澳币退款'),
        4 => array('name'=>'人民币充值'),
        5 => array('name'=>'人民币消费'),
        6 => array('name'=>'人民币退款'),
    ),

    //用户类型
    'memberGroup' => array(         
        array('id'=>1,'name'=>'普通用户'),
        array('id'=>2,'name'=>'会员用户')
    ),

    //包裹类型
    /*
    max 一个包裹最多几个
    same 相同商品一个包裹最多几个
    can 能和哪些商品混寄
    */
    'BAOGUO_ZH' => array(         
        array('id'=>1, 'name'=>'罐装奶粉','max'=>3,'same'=>99,'can'=>[]),
        array('id'=>2, 'name'=>'袋装奶粉','max'=>3,'same'=>99,'can'=>[]),
        array('id'=>3, 'name'=>'小罐奶粉','max'=>3,'same'=>99,'can'=>[]),
        
        array('id'=>4, 'name'=>'A类保健品','max'=>10,'same'=>6,'can'=>[5,6,7,8,9,10,11,12,13]),
        array('id'=>5, 'name'=>'A1(1个可混)','max'=>1,'same'=>1,'can'=>[4,7,8,9,10,11,12,13]),
        array('id'=>6, 'name'=>'A2(3个可混)','max'=>3,'same'=>3,'can'=>[4,7,8,9,10,11,12,13]),
        
        array('id'=>7, 'name'=>'B1(<$10)','max'=>2,'same'=>2,'can'=>[4,5,6,8,9,10,11,12,13]),
        array('id'=>8, 'name'=>'B2($10-$30)','max'=>1,'same'=>1,'can'=>[4,5,6,7,9,10,11,12,13]),

        array('id'=>9, 'name'=>'C类(FF等)','max'=>1,'same'=>1,'can'=>[4,5,6,7,8,10,11,12,13]), 

        array('id'=>10,'name'=>'D1(日用品)','max'=>4,'same'=>4,'can'=>[4,5,6,7,8,9,11,12,13]),
        array('id'=>11,'name'=>'D2(日用品)','max'=>2,'same'=>2,'can'=>[4,5,6,7,8,9,10,12,13]),

        array('id'=>12,'name'=>'E(婴儿食品)','max'=>15,'same'=>4,'can'=>[4,5,6,7,8,9,10,11,13]),
        array('id'=>13,'name'=>'F类(成人食品)','max'=>10,'same'=>4,'can'=>[4,5,6,7,8,9,10,11,12]),

        array('id'=>14,'name'=>'1个不混','max'=>1,'same'=>1,'can'=>[]),

        array('id'=>15,'name'=>'红酒','max'=>999,'same'=>999,'can'=>[]),
        array('id'=>16,'name'=>'手动面单','max'=>999,'same'=>999,'can'=>[]),
        array('id'=>17,'name'=>'生鲜','max'=>999,'same'=>999,'can'=>[])
    ),

    'BAOGUO_ZY' => array(
        array('id'=>1, 'name'=>'罐装奶粉','max'=>3,'same'=>99,'can'=>[]),
        array('id'=>2, 'name'=>'袋装奶粉','max'=>3,'same'=>99,'can'=>[]),
        array('id'=>3, 'name'=>'小罐奶粉','max'=>3,'same'=>99,'can'=>[]),
        
        array('id'=>4, 'name'=>'A类保健品','max'=>15,'same'=>6,'can'=>[5,6,7,8,9,10,11,12,13]),
        array('id'=>5, 'name'=>'A1(1个可混)','max'=>1,'same'=>1,'can'=>[4,7,8,9,10,11,12,13]),
        array('id'=>6, 'name'=>'A2(3个可混)','max'=>3,'same'=>3,'can'=>[4,7,8,9,10,11,12,13]),
        
        array('id'=>7, 'name'=>'B1(<$10)','max'=>4,'same'=>4,'can'=>[4,5,6,8,9,10,11,12,13]),
        array('id'=>8, 'name'=>'B2($10-$30)','max'=>2,'same'=>2,'can'=>[4,5,6,7,9,10,11,12,13]),

        array('id'=>9, 'name'=>'C类(FF等)','max'=>1,'same'=>1,'can'=>[4,5,6,7,8,10,11,12,13]), 

        array('id'=>10,'name'=>'D1(日用品)','max'=>6,'same'=>6,'can'=>[4,5,6,7,8,9,11,12,13]),
        array('id'=>11,'name'=>'D2(日用品)','max'=>3,'same'=>3,'can'=>[4,5,6,7,8,9,10,12,13]),

        array('id'=>12,'name'=>'E(婴儿食品)','max'=>20,'same'=>6,'can'=>[4,5,6,7,8,9,10,11,13]),
        array('id'=>13,'name'=>'F类(成人食品)','max'=>15,'same'=>6,'can'=>[4,5,6,7,8,9,10,11,12]),

        array('id'=>14,'name'=>'1个不混','max'=>1,'same'=>1,'can'=>[]),

        array('id'=>15,'name'=>'红酒','max'=>999,'same'=>999,'can'=>[]),
        array('id'=>16,'name'=>'手动面单','max'=>999,'same'=>999,'can'=>[]),
        array('id'=>17,'name'=>'生鲜','max'=>999,'same'=>999,'can'=>[])
    ),

    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    'app_namespace'          => 'app',
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    //加密key
    'DATA_CRYPT_KEY' => 'alex',

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'www',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',    

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',
 

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],


    //上传
    'UPLOAD_PATH' => '/uploads/', //上传保存路径
    'THUMB_IMAGE' => true, //是否启用缩略图
    'THUMB_WIDTH' => 200, //缩略图宽度
    'THUMB_HEIGHT' => 150, //缩略图高度
    'IMAGE_MAX_WIDTH' => 600,  //图片最大宽度

    'image_exts' => 'jpg,png,bmp,jpeg,gif,xls',
    'image_size' => 5, //单位兆

    //编辑器
    'UE_CONFIG'=>[
        /*UE编辑器设置*/
        'uedir' => '/_ueditor', // 默认编辑器所在路径
     
        /* 上传图片配置项 */
        "imageActionName"=>"uploadimage", /* 执行上传图片的action名称 */
        "imageFieldName"=>"upfile", /* 提交的图片表单名称 */
        "imageAllowFiles"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
        "imageCompressEnable"=>true, /* 是否压缩图片,默认是true */
        "imageCompressBorder"=>1600, /* 图片压缩最长边限制 */
        "imageInsertAlign"=>"none", /* 插入的图片浮动方式 */
        "imageUrlPrefix"=>"/uploads/images/", /* 图片访问路径前缀 */


        /* 涂鸦图片上传配置项 */
        "scrawlActionName"=>"uploadscrawl", /* 执行上传涂鸦的action名称 */
        "scrawlFieldName"=>"upfile", /* 提交的图片表单名称 */
        "scrawlPathFormat"=>"/uploads/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "scrawlMaxSize"=>204800000, /* 上传大小限制，单位B */
        "scrawlUrlPrefix"=>"", /* 图片访问路径前缀 */
        "scrawlInsertAlign"=>"none",

        /* 截图工具上传 */
        "snapscreenActionName"=>"uploadimage", /* 执行上传截图的action名称 */
        "snapscreenPathFormat"=>"/uploads/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "snapscreenUrlPrefix"=>"", /* 图片访问路径前缀 */
        "snapscreenInsertAlign"=>"none", /* 插入的图片浮动方式 */

        /* 抓取远程图片配置 */
        "catcherLocalDomain"=>["127.0.0.1", "localhost", "img.baidu.com"],
        "catcherActionName"=>"catchimage", /* 执行抓取远程图片的action名称 */
        "catcherFieldName"=>"source", /* 提交的图片列表表单名称 */
        "catcherPathFormat"=>"/uploads/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "catcherUrlPrefix"=>"", /* 图片访问路径前缀 */
        "catcherMaxSize"=>2048000, /* 上传大小限制，单位B */
        "catcherAllowFiles"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */

        /* 上传视频配置 */
        "videoActionName"=>"uploadvideo", /* 执行上传视频的action名称 */
        "videoFieldName"=>"upfile", /* 提交的视频表单名称 */
        "videoPathFormat"=>"/uploads/video/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "videoUrlPrefix"=>"", /* 视频访问路径前缀 */
        "videoMaxSize"=>102400000, /* 上传大小限制，单位B，默认100MB */
        "videoAllowFiles"=>[
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */

        /* 上传文件配置 */
        "fileActionName"=>"uploadfile", /* controller里,执行上传视频的action名称 */
        "fileFieldName"=>"upfile", /* 提交的文件表单名称 */
        "filePathFormat"=>"/uploads/file/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "fileUrlPrefix"=>"/uploads/files/", /* 文件访问路径前缀 */
        "fileMaxSize"=>51200000, /* 上传大小限制，单位B，默认50MB */
        "fileAllowFiles"=>[
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ], /* 上传文件格式显示 */

        /* 列出指定目录下的图片 */
        "imageManagerActionName"=>"listimage", /* 执行图片管理的action名称 */
        "imageManagerListPath"=>"/uploads/image/", /* 指定要列出图片的目录 */
        "imageManagerListSize"=>20, /* 每次列出文件数量 */
        "imageManagerUrlPrefix"=>"", /* 图片访问路径前缀 */
        "imageManagerInsertAlign"=>"none", /* 插入的图片浮动方式 */
        "imageManagerAllowFiles"=>[".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */

        /* 列出指定目录下的文件 */
        "fileManagerActionName"=>"listfile", /* 执行文件管理的action名称 */
        "fileManagerListPath"=>"/uploads/file/", /* 指定要列出文件的目录 */
        "fileManagerUrlPrefix"=>"", /* 文件访问路径前缀 */
        "fileManagerListSize"=>20, /* 每次列出文件数量 */
        "fileManagerAllowFiles"=>[
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ] /* 列出的文件类型 */
     ]

];
