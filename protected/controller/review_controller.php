<?php
class review_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $review_model = new goods_review_model();
        $this->reviews = array
        (
            'list' => $review_model->get_user_reviews($user_id, request('page')),
            'paging' => $review_model->page,
        );
        $this->compiler('user_review_list.html');
    }
    
    public function action_order()
    {
        $user_id = $this->is_logined();
        if(request('step') == 'submit')
        {
            $order_id = bigintstr(request('order_id'));
            $order_model = new order_model();
            if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
            {
                if($order['order_status'] == 4)
                {
                    $goods_id = (int)request('goods_id', 0);
                    $order_goods_model = new order_goods_model();
                    if($goods = $order_goods_model->find(array('order_id' => $order_id, 'goods_id' => $goods_id)))
                    {
                        if($goods['is_reviewed'] == 1) $this->prompt('error', '您已对该商品作出过评价');
                        
                        $review_model = new goods_review_model();
                        $data = array
                        (
                            'order_id' => $order_id,
                            'goods_id' => $goods_id,
                            'user_id' => $user_id,
                            'rating' => (int)request('rating', 0),
                            'content' => trim(strip_tags(request('content', ''))),
                            'created_date' => $_SERVER['REQUEST_TIME'],
                            'replied' => '',
                        );           
                                
                        $verifier = $review_model->verifier($data);
                        if(TRUE === $verifier)
                        {
                            if($review_model->create($data))
                            {
                                $order_goods_model->update(array('id' => $goods['id']), array('is_reviewed' => 1));
                                $this->prompt('success', '发表评价成功');
                            }
                            else
                            {
                                $this->prompt('error', '提交评价失败，请稍后重试');
                            }
                        }
                        else
                        {
                            $this->prompt('error', $verifier);
                        }
                    }
                    else
                    {
                        $this->prompt('error', '订单商品不存在');
                    }
                }
                else
                {
                    $this->prompt('error', '交易尚未完成，请完成后再评价');
                }
            }
            else
            {
                jump(url('main', '404'));
            }
        }
        else
        {
            $order_id = bigintstr(request('order_id'));
            $order_model = new order_model();
            if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
            {
                if($order['order_status'] == 4)
                {
                    $order_goods_model = new order_goods_model();
                    $opts_model = new order_goods_optional_model();
                    $goods_list = $order_goods_model->find_all(array('order_id' => $order_id));
                    foreach($goods_list as &$v)
                    {
                        $v['goods_opts'] = $opts_model->find_all(array('map_id' => $v['id']));
                    }
                    $this->goods_list = $goods_list;
                    $this->compiler('user_order_review.html');
                }
                else
                {
                    $this->prompt('error', '交易尚未完成，您还无法进行此操作');
                }
            }
            else
            {
                jump(url('main', '404'));
            }
        }
    }
}