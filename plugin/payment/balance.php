<?php
/**
 * Balance Payment
 * @author Cigery
 */
class balance extends abstract_payment
{
    public function create_pay_url($args)
    {
        if($this->device == 'mobile')
        {
            $url = url('mobile/pay', 'return', array('pcode' => 'balance', 'order_id' => $args['order_id']));
        }
        else
        {
            $url = url('pay', 'return', array('pcode' => 'balance', 'order_id' => $args['order_id']));
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
            if($this->order = $order_model->find(array('user_id' => $user_id, 'order_id' => $order_id)))
            {
                $account_model = new user_account_model();
                $account = $account_model->find(array('user_id' => $user_id));
                if($account['balance'] >= $this->order['order_amount'])
                {
                    if($account_model->decr(array('user_id' => $user_id), 'balance', $this->order['order_amount']))
                    {
                        $log_model = new user_account_log_model();
                        $log_model->create(array(
                            'user_id' => $user_id,
                            'balance' => 0 - $this->order['order_amount'],
                            'cause' => "使用账户余额支付订单[{$order_id}]",
                            'dateline' => $_SERVER['REQUEST_TIME'],
                        ));
                        $this->completed($order_id);
                        $this->message = '付款成功！您可以在订单详情里关注您的订单状态';
                        return TRUE;
                    }
                    else
                    {
                        $this->message = '付款失败！请稍后重试';
                    }
                }
                else
                {
                    $this->message = '抱歉！您的余额不足';
                }
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