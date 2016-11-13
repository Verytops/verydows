<?php
class category_controller extends general_controller
{
    public function action_index()
    {
        $id = (int)request('id', 0);
        $cate_model = new goods_cate_model();
        if($this->cateinfo = $cate_model->find(array('cate_id' => $id)))
        {
            $this->breadcrumbs = $cate_model->breadcrumbs($id);
            $this->recommend = vcache::instance()->goods_model('find_goods', array(array('cate' => $id, 'recommend' => 1), 5), $GLOBALS['cfg']['data_cache_lifetime']);
            $this->bargain = vcache::instance()->goods_model('find_goods', array(array('cate' => $id, 'bargain' => 1), 5), $GLOBALS['cfg']['data_cache_lifetime']);
            
            $conditions = array
            (
                'cate' => $id,
                'brand' => request('brand', ''), 
                'att' => request('att', ''),
                'minpri' => (int)request('minpri', 0),
                'maxpri' => (int)request('maxpri', 0),
                'sort' => (int)request('sort', 0),
                'page' => (int)request('page', 1),
            );
            
            $this->filters = $cate_model->set_filters($id, $conditions['att']);
            
            $goods_model = new goods_model();
            $this->history = $goods_model->get_history();
            $this->goods_list = $goods_model->find_goods($conditions, array(request('page', 1), $GLOBALS['cfg']['cate_goods_per_num']));
            $this->goods_paging = $goods_model->page;
            $this->u = $conditions;
            $this->compiler('category.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }

}