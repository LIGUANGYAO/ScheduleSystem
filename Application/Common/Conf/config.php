<?php
return array(
    'URL_MODEL' => 2, //URL模式 REWRITE模式
    'TMPL_TEMPLATE_SUFFIX' => '.html',
    'LOAD_EXT_FILE' => 'functions',
    'URL_CASE_INSENSITIVE' => true, //不区分URL大小写

    'db_type' => 'mysql', // 数据库类型
    'db_host' => 'localhost',
    'db_name' => 'ss_db', // 数据库名称
    'db_user' => 'root', // 主机名
    'db_pwd' => '', // 密码
    'DB_PORT' =>  '3306',      // 端口，留空则取默认端口
    'DB_PREFIX' =>  '',          // 数据库表前缀
    'DB_CHARSET' =>  'utf8',      // 数据库编码

    //缓存配置
    'DATA_CACHE_TYPE'=>'file',
    'DATA_CACHE_TIME'=>'3600',
);
