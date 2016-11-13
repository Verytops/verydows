<?php
function html_paging($params){
    if(!isset($params['paging']) || empty($params['paging'])) return '';
    $args = array();
    foreach($params as $k => $v)
    {
        if(!in_array($k, array('m','c','a','paging','class','mypage'))) $args[$k] = $v;
    }
    $pagingindex = isset($params['paging']['mypage']) ? $params['paging']['mypage'] : 'page';
    $current = $params['paging']['current_page'];
    $scope = $params['paging']['scope'];
    $html = "<div class=\"{$params['class']}\">";
    $html .= "<span class=\"tot\">共计<b>".$params['paging']['total_count']."</b>项</span>";
    if($current >= $scope)
    {
        $first = url($params['m'].'/'.$params['c'], $params['a'], $args + array($pagingindex => $params['paging']['first_page']));
        $html .= "<a href=\"{$first}\">首 页</a>";
    }
    if($current != $params['paging']['first_page'])
    {
        $url = url($params['m'].'/'.$params['c'], $params['a'], $args + array($pagingindex => $params['paging']['prev_page']));
        $html .= "<a href=\"{$url}\">上一页</a>";
    }
    else
    {
        $html .= "<span class=\"disabled\">上一页</span>";
    }
    foreach($params['paging']['all_pages'] as $p)
    {
        if($p == $current)
        {
            $html .= "<span class=\"cur\">{$p}</span>";
        }
        else
        {
            if( ($current <  $scope && $p <  $scope) ||
                ($current > $params['paging']['last_page'] -  $scope && $p > $params['paging']['last_page'] -  $scope ) ||
                ($p < $current +  $scope && $p > $current -  $scope)
              )
              { $url = url($params['m'].'/'.$params['c'], $params['a'], $args + array($pagingindex => $p));
                $html .= "<a href=\"$url\">{$p}</a>";
              }
        }
    }
    if($current != $params['paging']['last_page'])
    {
        $url = url($params['m'].'/'.$params['c'], $params['a'], $args + array($pagingindex => $params['paging']['next_page']));
        $html .= "<a href=\"$url\">下一页</a>";
    }
    else
    {
        $html .= "<span class=\"disabled\">下一页</span>";
    }
    if($params['paging']['total_page'] - $current >=  $scope)
    {
        $first = url($params['m'].'/'.$params['c'], $params['a'], $args + array($pagingindex => $params['paging']['last_page']));
        $html .= "<a href=\"{$first}\">末 页</a>";
    }
    $html .= "<span class=\"pct\">页码 <b>".$current."</b> / ".$params['paging']['total_page']."</span>";
    $html .= '</div>';
    return $html;
}