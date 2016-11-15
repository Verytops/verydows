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
            $this->shipping_method_list = vcache::instance()->shipping_method_model('indexed_list');
            $this->payment_method_list = vcache::instance()->payment_method_model('indexed_list');
            $this->compiler('order_confirm.html');
        }
        else
        {
            jump(url('cart', 'index'));
        }
    }
    
    public function action_submit()
    {
        $user_id = $this->is_logined();
        //检查购物车信息
        $cart = json_decode(stripslashes(request('CARTS', null, 'cookie')), TRUE);
        if(!$cart) $this->prompt('error', '无法获取购物车数据');
        $goods_model = new goods_model();
        if(!$cart = $goods_model->get_cart_items($cart)) $this->prompt('error', '购物车商品数据不正确');
        //检查收件人信息
        $csn_id = (int)request('csn_id', 0);
        $consignee_model = new user_consignee_model();
        if(!$consignee = $consignee_model->find(array('id' => $csn_id, 'user_id' => $user_id))) $this->prompt('error', '无法获取收件人地址信息');
        //检查配送方式
        $shipping_id = (int)request('shipping_id', 0);
        $shipping_map = vcache::instance()->shipping_method_model('indexed_list');
        if(!isset($shipping_map[$shipping_id])) $this->prompt('error', '配送方式不存在');
        //检查运费
        $shipping_model = new shipping_method_model();
        $shipping_amount = $shipping_model->check_freight($user_id, $shipping_id, $consignee['province'], $cart);
        if(FALSE === $shipping_amount) $this->prompt('error', '计算运费失败');
        //检查付款方式
        $payment_id = (int)request('payment_id', 0);
        $payment_map = vcache::instance()->payment_method_model('indexed_list');
        if(!isset($payment_map[$payment_id]))
        {
            $payment_id = current($payment_map);
            $payment_id = $payment_id['id'];
        }
        //创建订单
        $order_model = new order_model();
        $data = array
        (
            'order_id' => $order_model->create_order_id(),
            'user_id' => $user_id,
            'shipping_method' => $shipping_id,
            'payment_method' => $payment_id,
            'goods_amount' => $cart['amount'],
            'shipping_amount' => $shipping_amount,
            'order_amount' => $cart['amount'] + $shipping_amount,
            'memos' => trim(strip_tags(request('memos', ''))),
            'created_date' => $_SERVER['REQUEST_TIME'],
            'order_status' => 1,
        );
        
        if($order_model->create($data))
        {
            $order_goods_model = new order_goods_model();
            $order_goods_model->add_records($data['order_id'], $cart['items']);
            $order_consignee_model = new order_consignee_model();
            $order_consignee_model->add_records($data['order_id'], $consignee);
            setcookie('CARTS', null, $_SERVER['REQUEST_TIME'] - 3600, '/');
            jump(url('pay', 'index', array('order_id' => $data['order_id'])));
        }
        else
        {
            $this->prompt('error', '创建订单失败，请稍后重试');
        }
    }
    
    public function action_list()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $page_id = request('page', 1);
        if($order_list = $order_model->find_all(array('user_id' => $user_id), 'created_date DESC', '*', array($page_id, 10)))
        {
            $order_goods_model = new order_goods_model();
            foreach($order_list as &$v) $v['goods_list'] = $order_goods_model->get_goods_list($v['order_id']);
        }
                
        $this->order_list = array('rows' => $order_list, 'paging' => $order_model->page);
        $this->payment_map = vcache::instance()->payment_method_model('indexed_list');
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
            jump(url('main', '404'));
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
                $this->prompt('success', '取消订单成功', url('order', 'view', array('id' => $order_id)));
            }
            else
            {
                $this->prompt('error', '参数非法');
            }
        }
        else
        {
            jump(url('main', '404'));
        }
    }
    
    public function action_delivered()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            if($order['order_status'] == 3)
            {
                $order_model->update(array('order_id' => $order_id), array('order_status' => 4));
                $this->prompt('success', '签收成功，感谢您的购买！如有任何售后问题请及时与客服联系', url('order', 'view', array('id' => $order_id)), 5);
            }
            else
            {
                $this->prompt('error', '参数非法');
            }
        }
        
        jump(url('main', '404'));
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
            jump(url('cart', 'index'));
        }
        
        jump(url('main', '404'));
    }
}