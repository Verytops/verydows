<?php
class goods_controller extends general_controller
{
    public function action_search()
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
        );
        
        $goods_model = new goods_model();
        if($list = $goods_model->find_goods($conditions, array(request('page', 1), request('pernum', 10))))
        {
            echo json_encode(array('status' => 'success', 'list' => $list, 'paging' => $goods_model->page));
        }
        else
        {
            echo json_encode(array('status' => 'nodata'));
        }
    }
    
    public function action_rating()
    {
        $goods_id = (int)request('goods_id', 0);
        $review_model = new goods_review_model();
        $res = $review_model->get_rating_stats($goods_id);
        echo json_encode($res);
    }
    
    public function action_reviews()
    {
        $goods_id = (int)request('goods_id', 0);
        $rating_type = (int)request('rating_type', 0);
        $review_model = new goods_review_model();
        if($list = $review_model->get_goods_reviews($goods_id, $rating_type, array(request('page', 1), request('pernum', 10))))
        {
            echo json_encode(array('status' => 'success', 'list' => $list, 'paging' => $review_model->page));
        }
        else
        {
            echo json_encode(array('status' => 'nodata'));
        }
    }
}