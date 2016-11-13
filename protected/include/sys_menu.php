<?php
return array
(
    '常用菜单' => array
    (
        '面板首页' => 'c=main&a=dashboard',

        '商品管理' => array
        (
            '商品列表' => 'c=goods&a=index',
            '商品分类' => 'c=goods_cate&a=index',
            '品牌列表' => 'c=brand&a=index',
            '选项类型' => 'c=goods_optional_type&a=index',
            '商品评价' => 'c=goods_review&a=index'
        ),

        '订单管理' => array
        (
            '订单列表' => 'c=order&a=index',
            '发货列表' => 'c=order_shipping&a=index',
            '订单日志' => 'c=order_log&a=index',
        ),

        '用户管理' => array
        (
            '用户列表' => 'c=user&a=index',
            '用户组' => 'c=user_group&a=index',
            '账户日志' => 'c=user_account_log&a=index',
        ),
        
        '运营管理' => array
        (
            '咨询反馈' => 'c=feedback&a=index',
            '售后服务' => 'c=aftersales&a=index',
            '订单统计' => 'c=stats&a=order',
            '营收统计' => 'c=stats&a=revenue',
            '访问统计' => 'c=stats&a=visitor',
            '友情链接' => 'c=friendlink&a=index',
        ),
        
        '广告管理' => array
        (
            '广告位列表' => 'c=adv_position&a=index',
            '广告列表' => 'c=adv&a=index',
        ),
        
        '文章管理' => array
        (
            '资讯列表' => 'c=article&a=index',
            '资讯分类' => 'c=article_cate&a=index',
            '帮助列表' => 'c=help&a=index',
            '帮助分类' => 'c=help_cate&a=index',
        ),
        
        '邮件管理' => array
        (
            '订阅列表' => 'c=email_subscription&a=index',
            '邮件模板' => 'c=email_tpl&a=index',
            '邮件队列' => 'c=email_queue&a=index',
        ),
    ),
    
    '系统核心' => array
    (
        '权限管理' => array
        (
            '管理员列表' => 'c=admin&a=index',
            '角色列表' => 'c=role&a=index',
        ),

        '系统配置' => array
        (
            '系统设置' => 'c=setting&a=index',
            '导航设置' => 'c=nav&a=index',
            '配送方式' => 'c=shipping_method&a=index',
            '支付方式' => 'c=payment_method&a=index',
            '物流承运商' => 'c=shipping_carrier&a=index',
        ),
        
        '系统应用' => array
        (
            '授权登录' => 'c=oauth&a=index',
        ),
        
        '系统工具' => array
        (
            '文件管理' => 'c=file&a=index',
            '数据库' => 'c=database&a=backup',
            '系统清理' => 'c=cleaner&a=index',
        ),
    )
);
