<?php
class search_controller extends general_controller
{
    public function action_index()
    {
        $conditions = array
        (
            'cate' => (int)request('cate', 0, 'get'),
            'brand' => (int)request('brand', 0, 'get'),
            'att' => request('att', ''),
            'minpri' => (int)request('minpri', 0, 'get'),
            'maxpri' => (int)request('maxpri', 0, 'get'),
            'kw' => strip_tags(trim(request('kw', '', 'get'))),
            'sort' => (int)request('sort', 0, 'get'),
            'page' => (int)request('page', 1, 'get'),
        );

        $goods_model = new goods_model();
        $this->goods_list = $goods_model->find_goods($conditions, array($conditions['page'], 10));
        $this->filters = $goods_model->set_search_filters($conditions);
        $this->u = $conditions;
        $this->compiler('search.html');
    }

}