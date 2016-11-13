<?php
/**
 * COD Payment
 * @author Cigery
 */
class cod extends abstract_payment
{
    public function create_pay_url($args)
    {
        if($this->device == 'mobile')
        {
            $url = url('mobile/pay', 'return', array('pcode' => 'cod', 'order_id' => $args['order_id']));
        }
        else
        {
            $url = url('pay', 'return', array('pcode' => 'cod', 'order_id' => $args['order_id']));
        }
        return $url;
    }
    
    public function response($args)
    {
        if($this->_verifier($args))
        {
            $user_id = $_SESSION['USER']['USER_ID'];
            $order_id = bigintstr($args['order_id']);
            $order_model = new order_model();
            if($this->order = $order_model->find(array('user_id' => $user_id, 'order_id' => $order_id, 'order_status' => 1)))
            {
                $this->message = '提交成功，我们将会尽快与您联系确认订单后为您发货';
                return TRUE;
            }
            else
            {
                $this->message = '订单不存在';
            }
        }
        else
        {
            $this->message = '付款验证失败';
        }
        return FALSE;
    }
    
    private function _verifier($args)
    {
        if(empty($_SESSION['USER']['USER_ID']) || empty($args['order_id'])) return FALSE;
        return TRUE;
    }
}