<?php
/**
 * Alipay Payment
 * @author Cigery
 */
class alipay extends abstract_payment
{
    public function create_pay_url($args)
    {
        $params = array
        (
            'partner'        => $this->config['partner'],
            'payment_type'   => '1',
            'notify_url'     => $this->baseurl. '/api/pay/notify/alipay',
            'out_trade_no'   => $args['order_id'],
            'subject'        => "{$GLOBALS['cfg']['site_name']}订单-{$args['order_id']}",
            'total_fee'      => $args['order_amount'],
            '_input_charset' => 'utf-8',
            'transport'      => 'http',
        );
        
        if($this->device == 'mobile')
        {
            $params['service'] = 'alipay.wap.create.direct.pay.by.user';
            $params['seller_id'] = $this->config['seller'];
            $params['return_url'] = $this->baseurl . '/m/pay/return/alipay.html';
            $params['show_url'] = url('mobile/order', 'view', array('id' => $args['order_id']));
        }
        else
        {
            $params['service'] = 'create_direct_pay_by_user';
            $params['seller_email'] = $this->config['seller'];
            $params['return_url'] = $this->baseurl . '/pay/return/alipay.html';
            $params['show_url'] = url('order', 'view', array('id' => $args['order_id']));
        }
        
        return 'https://mapi.alipay.com/gateway.do?'. $this->_set_params($params);
    }
    
    public function response($args)
    {
        if($this->_verifier($args))
        {
            $order_model = new order_model();
            $this->order = $order_model->find(array('order_id' => $args['out_trade_no']));
            if($args['trade_status'] == 'TRADE_FINISHED' || $args['trade_status'] == 'TRADE_SUCCESS')
            {
                $this->message = '付款成功！您可以在订单详情里关注您的订单状态';
                $this->completed($args['out_trade_no'], $args['trade_no']);
                return TRUE;
            }
            else
            {
                $this->message = '支付失败';
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
        if(empty($args) || empty($args['sign'])) return FALSE;
        $sign = $args['sign'];
        ksort($args);
        
        $args_str = '';
        foreach($args as $k => $v)
        {
            if(in_array($k, array('m', 'c', 'a'))) continue;
            if($k == 'sign' || $k == 'sign_type' || $k == 'pcode' || $v == '') continue;
            $args_str .= $k.'='.$v.'&';
        }
        
        $args_str = substr($args_str, 0, strlen($args_str) - 1) . $this->config['key'];
        if(get_magic_quotes_gpc()) $args_str = stripslashes($args_str);
        
        if($sign == md5($args_str)) return TRUE;
        return FALSE;
    }
    
    private function _set_params($params)
    {
        ksort($params);
        $args = $sign = '';
        foreach($params as $k => $v)
        {
            $args .= $k.'='.urlencode($v).'&';
            $sign .= $k.'='.$v.'&';
        }
        $args = substr($args, 0, strlen($args) - 1);
        $sign = md5(substr($sign, 0, strlen($sign) - 1) . $this->config['key']);
        return $args . '&sign='. $sign . '&sign_type=MD5';
    }
}