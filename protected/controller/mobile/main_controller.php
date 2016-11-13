<?php
class main_controller extends general_controller
{
    public function action_index()
    {   
        $this->searches = array
        (
            'hot' => !empty($GLOBALS['cfg']['goods_hot_searches']) ? explode(',', $GLOBALS['cfg']['goods_hot_searches']) : null,
            'history' => !empty($_COOKIE['vds_search_history']) ? unserialize($_COOKIE['vds_search_history']) : null,
        );
        
        $vcache = vcache::instance();
        $this->newarrival = $vcache->goods_model('find_goods', array(array('newarrival' => 1), $GLOBALS['cfg']['home_newarrival_num']), $GLOBALS['cfg']['data_cache_lifetime']);
        $this->recommend = $vcache->goods_model('find_goods', array(array('recommend' => 1), $GLOBALS['cfg']['home_recommend_num']), $GLOBALS['cfg']['data_cache_lifetime']);
        $this->bargain = $vcache->goods_model('find_goods', array(array('bargain' => 1), $GLOBALS['cfg']['home_bargain_num']), $GLOBALS['cfg']['data_cache_lifetime']);
        $this->latest_article = $vcache->article_model('get_latest_article', array($GLOBALS['cfg']['home_article_num']), $GLOBALS['cfg']['data_cache_lifetime']);
        $this->compiler('index.html');
    }
    
    public function action_400()
    {
        $this->status = 400;
        $this->title = '错误请求';
        $this->content = '您的客户端发送了一个错误或非法的请求';
        $this->compiler('error.html');
        exit;
    }
    
    public function action_404()
    {
        $this->status = 404;
        $this->title = '页面未找到';
        $this->content = '很抱歉, 你要访问的页面或资源不存在';
        $this->compiler('error.html');
        exit;
    }
}