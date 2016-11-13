<?php
class pay_controller extends general_controller
{
    public function action_url()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('order_id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('user_id' => $user_id, 'order_id' => $order_id, 'order_status' => 1)))
        {
            $payment_id = (int)request('payment_id');
            $payment_map = vcache::instance()->payment_method_model('indexed_list');
            if(isset($payment_map[$payment_id]))
            {
                $order_model->update(array('order_id' => $order_id), array('payment_method' => $payment_id));
                $order['payment_method'] = $payment_id;
                $plugin = plugin::instance('payment', $payment_map[$payment_id]['pcode'], array($payment_map[$payment_id]['params']));
                $plugin->device = request('device', 'pc');
                $res = array('status' => 'success', 'url' => $plugin->create_pay_url($order));
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '支付方式不存在');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '订单不存在');
        }
        echo json_encode($res);
    }
    
    public function action_notify()
    {
        $pcode = request('pcode', '', 'get');
        $res = 'fail';
        $payment_model = new payment_method_model();
        if($payment = $payment_model->find(array('pcode' => $pcode, 'enable' => 1), null, 'params'))
        {
            $plugin = plugin::instance('payment', $pcode, array($payment['params']));
            if($plugin->response($_POST)) $res = 'success';
        }
        echo $res;
    }
}