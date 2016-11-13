<?php
class article_controller extends general_controller
{
    public function action_list()
    {
        $conditions = array();
        $cate_id = (int)request('cate', 0);
        if(!empty($cate_id)) $conditions['cate_id'] = $cate_id;
        $article_model = new article_model();
        $list = $article_model->find_all($conditions, 'created_date DESC', 'id, title, picture, link, created_date', array(request('page', 1), request('pernum', 10)));
        if($list)
        {
            $res = array('status' => 'success', 'list' => $list, 'paging' => $article_model->page);
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
    
}