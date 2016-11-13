<?php
abstract class abstract_payment
{
    protected $config = array();
    
    public $device;
    
    public $baseurl;
    
    public $message = '';
    
    public $order = array();
    
    public function __construct($params = null, $mod = '')
    {
        if(!empty($params)) $this->config = json_decode($params, TRUE);
        $this->device = $mod;
        $this->baseurl = baseurl();
    }
    
    abstract protected function create_pay_url($args);
    
    abstract protected function response($args);

    protected function completed($order_id, $trade_id = '')
    {
        $data = array
        (
            'order_status' => 2,
            'thirdparty_trade_id' => $trade_id,
            'payment_date' => $_SERVER['REQUEST_TIME'],
        );
        $order_model = new order_model();
        return $order_model->update(array('order_id' => $order_id), $data);
    }
}