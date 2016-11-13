<?php
function layout_topper($params = array())
{
    if($html = layout::instance()->check_static_file('topper.html')) return $html;
    $nav = vcache::instance()->nav_model('get_site_nav');
    $assigns['nav'] = $nav['top'];
    $assigns = $assigns + $params;
    return  layout::instance()->tpl_render('topper.html', $assigns, TRUE);
}

function layout_header($params = array())
{
    if($html = layout::instance()->check_static_file('header.html')) return $html;
    $nav = vcache::instance()->nav_model('get_site_nav');
    $assigns['nav'] = $nav['main'];
    $assigns['hot_searches'] = explode(',', $GLOBALS['cfg']['goods_hot_searches']);
    $assigns = $assigns + $params;
    return layout::instance()->tpl_render('header.html', $assigns, TRUE);
}

function layout_helper()
{
    if($html = layout::instance()->check_static_file('helper.html')) return $html;
    $assigns['helper_list'] = vcache::instance()->help_model('cated_help_list');
    return layout::instance()->tpl_render('helper.html', $assigns, TRUE);
}

function layout_footer()
{
    if($html = layout::instance()->check_static_file('footer.html')) return $html;
    $nav = vcache::instance()->nav_model('get_site_nav');
    $assigns['nav'] = $nav['bottom'];
    $assigns['footer'] = $GLOBALS['cfg']['footer_info'];
    $assigns['footer'] .= base64_decode('PHAgc3R5bGU9ImZvbnQtc2l6ZToxMnB4O2NvbG9yOiM5OTk7dGV4dC1hbGlnbjpjZW50ZXI7bWFyZ2luOjE1cHggMDsiPlBvd2VyZWQgYnkgPGEgdGFyZ2V0PSJfYmxhbmsiIGhyZWY9Imh0dHA6Ly93d3cudmVyeWRvd3MuY29tIj5WZXJ5ZG93czwvYT48L3A+');
    if(!empty($GLOBALS['cfg']['visitor_stats'])) $assigns['footer'] .= "<script type=\"text/javascript\" src=\"{$GLOBALS['cfg']['http_host']}/public/script/stats.js\"></script>";
    return layout::instance()->tpl_render('footer.html', $assigns, TRUE);
}

function layout_catebar()
{
    if($html = layout::instance()->check_static_file('catebar.html')) return $html;
    return layout::instance()->tpl_render('catebar.html', array('catebar' => vcache::instance()->goods_cate_model('goods_cate_bar')), TRUE);    
}

function layout_login($params = array())
{
    $_SESSION['LOGIN_TOKEN'] = array('KEY' => random_chars(5), 'VAL' => random_chars(9, TRUE));
    return layout::instance()->tpl_render('loginbar.html', $params, FALSE);
}

function layout_usermenu()
{
    $assigns['menu_list'] = array
    (
        array('name' => '用户中心', 'c' => 'user', 'a' => 'index'),
        array('name' => '个人资料', 'c' => 'user', 'a' => 'profile'),
        array('name' => '我的订单', 'c' => 'order', 'a' => 'list'),
        array('name' => '我的评价', 'c' => 'review', 'a' => 'list'),
        array('name' => '收件地址', 'c' => 'consignee', 'a' => 'list'),
        array('name' => '我的收藏', 'c' => 'favorite', 'a' => 'list'),
        array('name' => '售后服务', 'c' => 'aftersales', 'a' => 'list'),
        array('name' => '咨询反馈', 'c' => 'feedback', 'a' => 'list'),
        array('name' => '安全设置', 'c' => 'security', 'a' => 'index'),
    );
    return layout::instance()->tpl_render('usermenu.html', $assigns, FALSE);
}

function layout_adv($params = array())
{
    if(isset($params['id']))
    {
        $html_path = APP_DIR.DS.'protected'.DS.'cache'.DS.'static'.DS.'adv_'.$params['id'].'.html';
        if($html = @file_get_contents($html_path)) return $html;
        
        $postion_model = new adv_position_model();
        if($tpl = $postion_model->fetch_tpl($params['id']))
        {
            file_put_contents($html_path, $tpl);
            return $tpl;
        }
    }
    return '';
}

function layout_paging($params = array())
{
    if(empty($params['paging'])) return '';
    
    $args = array();
    
    foreach($params as $k => $v)
    {
        if(!in_array($k, array('c','a','paging','class','index'))) $args[$k] = $v;
    }
    
    $index = isset($params['paging']['index']) ? $params['paging']['index'] : 'page';
    
    $html = "<div class=\"{$params['class']}\">";
    $html .= "<span class=\"tot\">共计<b>".$params['paging']['total_count']."</b>项</span>";
    
    if($params['paging']['current_page'] >= $params['paging']['scope'])
    {
        $html .= "<a href=\"".url($params['c'], $params['a'], $args + array($index => $params['paging']['first_page']))."\">首 页</a>";
    }

    if($params['paging']['current_page'] != $params['paging']['first_page'])
    {
		$html .= "<a href=\"".url($params['c'], $params['a'], $args + array($index => $params['paging']['prev_page']))."\">上一页</a>";
	}
    else
    {
        $html .= "<span class=\"disabled\">上一页</span>";
    }
    
    foreach($params['paging']['all_pages'] as $p)
    {
        if($p == $params['paging']['current_page'])
        {
            $html .= "<span class=\"cur\">{$p}</span>";
        }
        else
        {
            if(
                ($params['paging']['current_page'] <  $params['paging']['scope'] && $p <  $params['paging']['scope']) ||
                ($params['paging']['current_page'] > $params['paging']['last_page'] -  $params['paging']['scope'] && $p > $params['paging']['last_page'] -  $params['paging']['scope'] ) ||
                ($p < $params['paging']['current_page'] +  $params['paging']['scope'] && $p > $params['paging']['current_page'] -  $params['paging']['scope'])
            )
            $html .= "<a href=\"".url($params['c'], $params['a'], $args + array($index => $p))."\">{$p}</a>";
        }
    }
    
    if($params['paging']['current_page'] != $params['paging']['last_page'])
    {
        $html .= "<a href=\"".url($params['c'], $params['a'], $args + array($index => $params['paging']['next_page']))."\">下一页</a>";
    }
    else
    {
        $html .= "<span class=\"disabled\">下一页</span>";
    }
    
    if($params['paging']['total_page'] - $params['paging']['current_page'] >= $params['paging']['scope'])
    {
        $html .= "<a href=\"".url($params['c'], $params['a'], $args + array($index => $params['paging']['last_page']))."\">末 页</a>";
    }

    $html .= "<span class=\"pct\">页码 <b>{$params['paging']['current_page']}</b> / {$params['paging']['total_page']}</span>";
    $html .= '</div>';
    return $html;
}