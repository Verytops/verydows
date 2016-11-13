<?php
class review_controller extends general_controller
{
    public function action_list()
    {
        $this->is_logined();
        $this->compiler('user_review_list.html');
    }
    
    public function action_order()
    {
        $user_id = $this->is_logined();
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
                $this->compiler('user_review_order.html');
            }
            else
            {
                jump(url('mobile/main', '400'));
            }
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
}