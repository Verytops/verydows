<?php
class review_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $review_model = new goods_review_model();
        if($list = $review_model->get_user_reviews($user_id, array(request('page', 1), request('pernum', 10))))
        {
            $res = array('status' => 'success', 'list' => $list, 'paging' => $review_model->page);
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
    
    public function action_submit()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('order_id'));
        $goods_id = (int)request('goods_id', 0);
        $order_goods_model = new order_goods_model();
        if($order_goods_model->allowed_review($user_id, $order_id, $goods_id))
        {
            $review_model = new goods_review_model();
            $data = array
            (
                'order_id' => $order_id,
                'goods_id' => $goods_id,
                'user_id' => $user_id,
                'rating' => (int)request('rating', 5),
                'content' => strip_tags(trim(request('content', ''))),
                'created_date' => $_SERVER['REQUEST_TIME'],
                'replied' => '',
            );
            $verifier = $review_model->verifier($data);
            if(TRUE === $verifier)
            {
                if($review_model->create($data))
                {
                    $order_goods_model->update(array('order_id' => $order_id, 'goods_id' => $goods_id), array('is_reviewed' => 1));
                    $res = array('status' => 'success');
                }
                else
                {
                    $res = array('status' => 'error', 'msg' => '评价失败, 请稍后重试');
                }
            }
            else
            {
                $res = array('status' => 'error', 'msg' => $verifier[0]);
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '非法请求');
        }
        echo json_encode($res);
    }
}