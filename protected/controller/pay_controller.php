<?php
class pay_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => bigintstr(request('order_id')), 'user_id' => $user_id)))
        {
            $payment_map = vcache::instance()->payment_method_model('indexed_list');
            if($change_payment = (int)request('change_payment', 0))
            {
                if($change_payment == 2)
                {
                    $order_shipping_model = new order_shipping_model();
                    if($order_shipping_model->find(array('order_id' => $order['order_id']))) $this->prompt('error', '您的订单已发货，无法更改为其他付款方式');
                }
                
                $payment_model = new payment_method_model();
                if($change_payment != $order['payment_method'] && 
                isset($payment_map[$change_payment]) && $change_payment != $order['payment_method'])
                {
                    $order_model->update(array('order_id' => $order['order_id']), array('payment_method' => $change_payment));
                    $order['payment_method'] = $change_payment;
                }
            }
            
            if($order['order_status'] == 1)
            {
                if(!isset($payment_map[$order['payment_method']])) $this->prompt('error', '付款方式不存在');
                
                $payment = $payment_map[$order['payment_method']];
                $pay_plugin = plugin::instance('payment', $payment['pcode'], array($payment['params']));
                $this->payment = array('name' => $payment['name'], 'url' => $pay_plugin->create_pay_url($order));
                $this->payment_list = $payment_map;
                $this->order = $order;
                $this->compiler('pay.html');
            }
            else
            {
                $this->prompt('error', '您无法进行此操作');
            }
        }
        else
        {
            jump(url('main', '404'));
        }
    }
    
    public function action_return()
    {
        $pcode = sql_escape(request('pcode', ''));
        $payment_model = new payment_method_model();
        if($payment = $payment_model->find(array('pcode' => $pcode, 'enable' => 1), null, 'params'))
        {
            $plugin = plugin::instance('payment', $pcode, array($payment['params']));
            if($plugin->response($_GET))
            {
                $this->status = 'success';
            }
            else
            {
                $this->status = 'error';
            }
            $this->message = $plugin->message;
            $this->order = $plugin->order;
            $this->compiler('pay_return.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }
}