<?php
class pay_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        if($this->order = $order_model->find(array('order_id' => request('order_id'), 'user_id' => $user_id)))
        {
            $this->payment_list = vcache::instance()->payment_method_model('indexed_list');
            $this->compiler('pay.html');
        }
        else
        {
            jump(url('mobile/main', '400'));
        }
    }
    
    public function action_return()
    {
        $pcode = request('pcode', '', 'get');
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
            jump(url('mobile/main', '400'));
        }
    }
}