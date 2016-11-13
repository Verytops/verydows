<?php
class aftersales_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $this->compiler('user_aftersales_list.html');
    }
    
    public function action_order()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('order_id'));
        $order_model = new order_model();
        if($order_model->find(array('user_id' => $user_id, 'order_id' => $order_id, 'order_status' => 4)))
        {
            $order_goods_model = new order_goods_model();
            $this->goods_list = $order_goods_model->get_goods_list($order_id);
            $this->compiler('user_aftersales_order.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
    
    public function action_apply()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('order_id'));
        $goods_id = (int)request('goods_id', 0);
        $aftersales_model = new aftersales_model();
        if($row = $aftersales_model->find(array('user_id' => $user_id, 'order_id' => $order_id, 'goods_id' => $goods_id)))
        {
            jump(url('mobile/aftersales', 'view', array('id' => $row['as_id'])));
        }
        
        if($aftersales_model->check_apply_allowed($user_id, $order_id, $goods_id))
        {
            $order_goods_model = new order_goods_model();
            $goods = $order_goods_model->find(array('order_id' => $order_id, 'goods_id' => $goods_id));
            $opts_model = new order_goods_optional_model();
            $goods['goods_opts'] = $opts_model->find_all(array('map_id' => $goods['id']));
            $this->goods = $goods;
            $this->compiler('user_aftersales_apply.html');
        }
        else
        {
            jump(url('mobile/main', '400'));
        }
    }
    
    public function action_view()
    {
        $user_id = $this->is_logined();
        $as_id = (int)request('id', 0);
        $aftersales_model = new aftersales_model();
        if($aftersales = $aftersales_model->find(array('as_id' => $as_id, 'user_id' => $user_id)))
        {
            $aftersales['type_text'] = $aftersales_model->type_map[$aftersales['type']];
            $aftersales['status_text'] = $aftersales_model->status_map[$aftersales['status']];
            $this->aftersales = $aftersales;
            $message_model = new aftersales_message_model();
            $this->message_list = $message_model->find_all(array('as_id' => $as_id), 'dateline ASC');
            $this->compiler('user_aftersales_details.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
}