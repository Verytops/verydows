<?php
class order_controller extends general_controller
{
    public function action_confirm()
    {
        $user_id = $this->is_logined();
        $cart = json_decode(stripslashes(request('CARTS', null, 'cookie')), TRUE);
        if($cart)
        {
            $goods_model = new goods_model();
            $this->cart = $goods_model->get_cart_items($cart);
            $consignee_model = new user_consignee_model();
            $this->consignee_list = $consignee_model->get_user_consignee_list($user_id);
            $this->shipping_list = vcache::instance()->shipping_method_model('indexed_list');
            $this->compiler('order_confirm.html');
        }
        else
        {
            jump(url('mobile/cart', 'index'));
        }
    }
    
    public function action_list()
    {
        $this->is_logined();
        $pending = request('pending');
        switch($pending)
        {
            case 'pay': $title = '待付款'; break;
            case 'ship': $title = '待发货'; break;
            case 'sign': $title = '待签收'; break;
            case 'review': $title = '待评价'; break;
            default: $title = '全部订单';
        }
        $this->title = $title;
        $this->pending = $pending;
        $this->compiler('user_order_list.html');
    }
    
    public function action_view()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            $vcache = vcache::instance();
            $payment_map = $vcache->payment_method_model('indexed_list');
            $shipping_map = $vcache->shipping_method_model('indexed_list');
            $order['payment_method_name'] = $payment_map[$order['payment_method']]['name'];
            $order['shipping_method_name'] = $shipping_map[$order['shipping_method']]['name'];
  
            $condition = array('order_id' => $order_id);
            $consignee_model = new order_consignee_model();
            $this->consignee = $consignee_model->find($condition);
            
            $order_goods_model = new order_goods_model();
            $this->goods_list = $order_goods_model->get_goods_list($order_id);
            
            $this->progress = $order_model->get_user_order_progress($order['order_status'], $order['payment_method']);
            $this->status_map = $order_model->status_map;
            
            if($order['order_status'] == 1 && $order['payment_method'] != 2)
            {
                if(!$this->countdown = $order_model->is_overdue($order_id, $order['created_date'])) $order['order_status'] = 0;
            }
            elseif($order['order_status'] == 3)
            {
                $shipping_model = new order_shipping_model();
                if($shipping = $shipping_model->find($condition, 'dateline DESC'))
                {
                    $this->countdown = intval($shipping['dateline'] + $GLOBALS['cfg']['order_delivery_expires'] * 86400 - $_SERVER['REQUEST_TIME']);
                    if(!$this->countdown) $order_model->update($condition, array('order_status' => 4));
                    $this->shipping = $shipping;
                    $carrier_map = $vcache->shipping_carrier_model('indexed_list');
                    $this->carrier = $carrier_map[$shipping['carrier_id']];
                }
            }
            
            $this->order = $order;
            $this->compiler('user_order_details.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
    
    public function action_cancel()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            if($order['order_status'] == 1)
            {
                $order_model->update(array('order_id' => $order_id), array('order_status' => 0));
                $order_goods_model = new order_goods_model();
                $order_goods_model->restocking($order_id);
                $this->prompt('success', '取消订单成功', url('mobile/order', 'view', array('id' => $order_id)));
            }
            
            jump(url('mobile/main', '400'));
        }
        
        jump(url('mobile/main', '404'));
    }
    
    public function action_delivered()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id, 'order_status' => 3)))
        {
            $order_model->update(array('order_id' => $order_id), array('order_status' => 4));
            $this->prompt('success', '签收成功，感谢您的购买！如有任何售后问题请及时与客服联系', url('mobile/order', 'view', array('id' => $order_id)), 3);
        }
        jump(url('mobile/main', '400'));
    }
    
    public function action_rebuy()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            if($cart = request('CARTS', array(), 'cookie')) $cart = json_decode($cart, TRUE);
            
            $order_goods_model = new order_goods_model();
            $opts_model = new order_goods_optional_model();
            $goods_list = $order_goods_model->find_all(array('order_id' => $order_id), null, 'id, goods_id, goods_qty');
            foreach($goods_list as $v)
            {
                $key = $v['goods_id'];
                $opt_ids = null;
                if($opts = $opts_model->find_all(array('map_id' => $v['id'])))
                {
                    $opts = array_column($opts, 'opt_id');
                    $key .= implode('_', $opts);
                }
                $cart[$key] = array('id' => $v['goods_id'], 'qty' => $v['goods_qty'], 'opts' => $opts);
            }
            setcookie('CARTS', json_encode($cart), $_SERVER['REQUEST_TIME'] + 604800, '/');
            jump(url('mobile/cart', 'index'));
        }
        jump(url('mobile/main', '400'));
    }
}