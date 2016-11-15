<?php
class order_model extends Model
{
    public $table_name = 'order';
    
    public $status_map = array
    (
        0 => '交易取消',
        1 => '等待付款',
        2 => '等待发货',
        3 => '等待签收',
        4 => '交易完成',
    );

    /**
     * 生成订单号
     */
    public function create_order_id()
    {
        $l = str_replace('.', '', sprintf("%.2f", ($_SERVER['REQUEST_TIME'] - $GLOBALS['verydows']['COMMENCED']) / 3600));
        $l = str_pad($l, 8, 0, STR_PAD_RIGHT) . date('s');
        $r = substr(microtime(), 2, 3). rand(0, 9) . rand(0, 9);
        $order_id = $l.$r;
        if($this->find(array('order_id' => $order_id))) return $this->create_order_id();
        return $order_id;
    }
    
    /**
     * 获取订单当前给用户的显示进度
     */
    public function get_user_order_progress($order_status, $payment_method)
    {
        switch($order_status)
        {
            case 0: $progress = array(1 => '提交订单', 0 => '交易取消'); break;
            
            case 1: 
                
                if($payment_method == 2)
                {
                    $progress = array(1 => '提交订单', 2 => '货到付款', 3 => '等待发货');
                }
                else
                {
                    $progress = array(1 => '提交订单', 2 => '等待付款');
                }
 
            break;
            
            case 2: 
            
                $progress = array(1 => '提交订单', 2 => '完成付款', 3 => '等待发货');
                if($payment_method == 2) $progress[2] = '货到付款';
            
            break;

            case 3:
            
                $progress = array(1 => '提交订单', 2 => '完成付款', 3=> '正在配送', 4 => '等待签收');
                if($payment_method == 2) $progress[2] = '货到付款';
                
            break;
            
            case 4: $progress = array(1 => '提交订单', 2 => '完成付款', 3 => '完成配送', 4 => '签收完成'); break;
            
            default: $progress = FALSE;
        }
        
        return $progress;
    }
    
    /**
     * 获取付款按钮操作
     */
    public function get_pay_btn_handle($order)
    {
        $payment_model = new payment_method_model();
        if($payment = $payment_model->find(array('id' => $order['payment_method'], 'enable' => 1)))
        {
            $gateway_obj = plugin::instance('payment', $payment['pcode'], array($payment['params']));
            return $gateway_obj->create_pay_url($order);
        }
        return FALSE;
    }
    
    /**
     * 订单是否超期未付款
     * @return 超期自动取消返回FALSE 未超期返回剩余时间
     */
    public function is_overdue($order_id, $created_date)
    {
        $expire = $created_date + ($GLOBALS['cfg']['order_cancel_expires'] * 3600);
        if($expire <= $_SERVER['REQUEST_TIME'])
        {
            $this->update(array('order_id' => $order_id), array('order_status' => 0));
            return FALSE;
        }
        return $expire - $_SERVER['REQUEST_TIME'];
    }
    
    
    /**
     * 过期订单批量处理
     */
    public function expired()
    {
        $expire = $_SERVER['REQUEST_TIME'] - ($GLOBALS['cfg']['order_cancel_expires'] * 3600);
        $sql = "SELECT order_id FROM {$this->table_name}
                WHERE (order_status = 1 AND payment_method <> 2) AND created_date <= {$expire}";  
        if($outdated = $this->query($sql))
        {
            $order_goods_model = new order_goods_model();
            foreach($outdated as $v)
            {
                if($this->update(array('order_id' => $v['order_id']), array('order_status' => 0))) $order_goods_model->restocking($v['order_id']);
            }
        }
    }
}