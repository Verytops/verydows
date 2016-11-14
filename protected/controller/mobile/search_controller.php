<?php
class search_controller extends general_controller
{
    public function action_index()
    {
        $conditions = array
        (
            'cate' => (int)request('cate', 0),
            'brand' => (int)request('brand', 0),
            'att' => request('att', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'kw' => strip_tags(trim(request('kw', ''))),
            'sort' => (int)request('sort', 0),
            'page' => (int)request('page', 1),
        );
        
        $goods_model = new goods_model();
        $this->goods_list = $goods_model->find_goods($conditions, array($conditions['page'], 10));
        $this->filters = $goods_model->set_search_filters($conditions);
        $this->u = $conditions;
        $this->hot_searches = !empty($GLOBALS['cfg']['goods_hot_searches']) ? explode(',', $GLOBALS['cfg']['goods_hot_searches']) : null;
        $this->compiler('search.html');
    }

}