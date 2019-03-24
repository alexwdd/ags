<?php

/**
 *
 *  Configuration Class
 *  配置类
 *
 */
class OmiPayConfig
{

    // API版本
    const API_VERSION = "v2";

    const DOMAIN = "https://www.omipay.com.au/omipay/api/v2";

    const DOMAINCN = "https://www.omipay.com.cn/omipay/api/v2";

    // =======【curl代理设置】===================================
    /**
     * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     * @var unknown_type
     */
    const CURL_PROXY_HOST = "0.0.0.0";//"192.168.0.1";  
    const CURL_PROXY_PORT = 0;//8080;
}

