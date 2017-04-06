<?php
/**
 * 系统访问资源映射表
 */
return array
(
    array
    (
        'name' => '权限管理',
        'uri' => array
        (
            'admin@index' => '查看管理员列表',
            'admin@add' => '添加管理员',
            'admin@edit' => '编辑管理员',
            'admin@delete' => '删除管理员',
            'role@index' => '查看角色列表',
            'role@add' => '添加角色',
            'role@edit' => '编辑角色',
            'role@delete' => '删除角色',
        ),
    ),
    
    array
    (
        'name' => '系统配置',
        'uri' => array
        (
            'setting@index' => '查看系统设置',
            'setting@update' => '更新系统设置',
            'nav@index' => '查看导航列表',
            'nav@add' => '添加导航',
            'nav@edit' => '编辑导航',
            'nav@delete' => '删除导航',
            'shipping_method@index' => '查看配送方式列表',
            'shipping_method@add' => '添加配送方式',
            'shipping_method@edit' => '编辑配送方式',
            'shipping_method@delete' => '删除配送方式',
            'payment_method@index' => '查看支付方式列表',
            'payment_method@edit' => '编辑支付方式',
            'shipping_carrier@index' => '查看物流承运商列表',
            'shipping_carrier@add' => '添加物流承运商',
            'shipping_carrier@edit' => '编辑物流承运商',
            'shipping_carrier@delete' => '删除物流承运商',
        ),
    ),
    
    array
    (
        'name' => '系统应用',
        'uri' => array
        (
            'oauth@index' => '查看授权登录列表',
            'oauth@edit' => '设置授权登录配置',
        ),
    ),
    
    array
    (
        'name' => '系统工具',
        'uri' => array
        (
            'file@index' => '查看文件列表',
            'file@upload' => '上传文件',
            'file@rename' => '重命名文件',
            'file@delete' => '删除文件',
            'database@backup' => '数据库备份',
            'database@restore' => '数据库恢复',
            'database@optimize' => '数据库优化',
            'cleaner@index' => '查看系统清理',
            'cleaner@wiping' => '清理系统缓存',
        ),
    ),
    
    array
    (
        'name' => '商品管理',
        'uri' => array
        (
            'goods@index' => '查看商品列表',
            'goods@add' => '添加商品',
            'goods@edit' => '编辑商品',
            'goods@delete' => '删除商品',
            'goods@image' => '上传商品图片',
            'goods_optional_type@index' => '查看商品选项类型列表',
            'goods_optional_type@add' => '添加商品选项类型',
            'goods_optional_type@edit' => '编辑商品选项类型',
            'goods_optional_type@delete' => '删除商品选项类型',
            'goods_cate@index' => '查看商品分类列表',
            'goods_cate@add' => '添加商品分类',
            'goods_cate@edit' => '编辑商品分类',
            'goods_cate@delete' => '删除商品分类',
            'goods_cate_attr@index' => '查看商品分类属性',
            'goods_cate_attr@add' => '添加商品分类属性',
            'goods_cate_attr@edit' => '编辑商品分类属性',
            'goods_cate_attr@delete' => '删除商品分类属性',
            'brand@index' => '查看品牌列表',
            'brand@add' => '添加品牌',
            'brand@edit' => '编辑品牌',
            'brand@delete' => '删除品牌',
            'goods_review@index' => '查看商品评价列表',
            'goods_review@view' => '查看商品评价详情',
            'goods_review@approval' => '审核商品评价',
            'goods_review@reply' => '回复商品评价',
            'goods_review@delete' => '删除商品评价',
        ),
    ),
    
    array
    (
        'name' => '订单管理',
        'uri' => array
        (
            'order@index' => '查看订单列表',
            'order@view' => '查看订单详情',
            'order@operate' => '操作订单',
            'order@delete' => '删除订单',
            'order_shipping@index' => '查看发货列表',
            'order_shipping@delete' => '删除发货信息',
            'order_log@index' => '查看订单日志列表',
            'order_log@delete' => '查看订单日志',
        ),
    ),
    
    array
    (
        'name' => '用户管理',
        'uri' => array
        (
            'user@index' => '查看用户列表',
            'user@view' => '查看用户详细',
            'user@edit' => '编辑用户',
            'user@revise_account' => '调整账户数据',
            'user@delete' => '删除用户',
            'user_group@index' => '查看用户组列表',
            'user_group@add' => '添加用户组',
            'user_group@edit' => '编辑用户组',
            'user_group@delete' => '删除用户组',
            'user_account_log@index' => '查看账户日志列表',
            'user_account_log@delete' => '删除账户日志',
        ),
    ),
    
    array
    (
        'name' => '运营管理',
        'uri' => array
        (
            'feedback@index' => '查看咨询反馈列表',
            'feedback@view' => '查看咨询反馈详情',
            'feedback@reply' => '回复咨询反馈',
            'feedback@status' => '设置咨询反馈状态',
            'feedback@delete' => '删除咨询反馈',
            'aftersales@index' => '查看售后服务列表',
            'aftersales@view' => '查看售后服务详情',
            'aftersales@reply' => '回复售后服务',
            'aftersales@status' => '设置售后服务状态',
            'aftersales@delete' => '删除售后服务',
            'stats@order' => '查看订单统计',
            'stats@revenue' => '查看营收统计',
            'stats@visitor' => '查看访问统计',
            'friendlink@index' => '查看友情链接列表',
            'friendlink@add' => '添加友情链接',
            'friendlink@edit' => '编辑友情链接',
            'friendlink@delete' => '删除友情链接',
        ),
    ),
    
    array
    (
        'name' => '广告管理',
        'uri' => array
        (
            'adv@index' => '查看广告列表',
            'adv@add' => '添加广告',
            'adv@edit' => '编辑广告',
            'adv@delete' => '删除广告',
            'adv_position@index' => '查看广告位列表',
            'adv_position@add' => '添加广告位',
            'adv_position@edit' => '编辑广告位',
            'adv_position@delete' => '删除广告位',
        ),
    ),
    
    array
    (
        'name' => '文章管理',
        'uri' => array
        (
            'article@index' => '查看资讯列表',
            'article@add' => '添加资讯',
            'article@edit' => '编辑资讯',
            'article@editor' => '资讯编辑器上传',
            'article@delete' => '删除资讯',
            'article_cate@index' => '查看资讯分类列表',
            'article_cate@add' => '添加资讯分类',
            'article_cate@edit' => '编辑资讯分类',
            'article_cate@delete' => '删除资讯分类',
            'help@index' => '查看帮助信息列表',
            'help@add' => '添加帮助信息',
            'help@edit' => '编辑帮助信息',
            'help@delete' => '删除帮助信息',
            'help_cate@index' => '查看帮助分类列表',
            'help_cate@add' => '添加帮助分类',
            'help_cate@edit' => '编辑帮助分类',
            'help_cate@delete' => '删除帮助分类',
        ),
    ),
    
    array
    (
        'name' => '邮件管理',
        'uri' => array
        (
            'email_subscription@index' => '查看邮件订阅列表',
            'email_subscription@status' => '邮件订阅确认/退订',
            'email_subscription@delete' => '删除邮件订阅',
            'email_tpl@index' => '查看邮件模板列表',
            'email_tpl@add' => '添加邮件模板',
            'email_tpl@edit' => '编辑邮件模板',
            'email_tpl@delete' => '删除邮件模板',
            'email_queue@index' => '查看邮件队列列表',
            'email_queue@delete' => '删除邮件队列',
        ),
    ),
);
