<?php
class order_shipping_model extends Model
{
    public $table_name = 'order_shipping';
    
    public $rules = array
    (
        'tracking_no' => array('is_required' => array(TRUE, '运单号不能为空'), 'max_length' => array(20, '运单号不能超过20个字符')),
        'memos' => array('max_length' => array(240, '备注不能超过240个字符')),
    );
    
    public $addrules = array
    (
        'carrier_id' => array
        (
            'addrule_valid_carrier' => '必须选择一个有效的物流承运商',
        ),
    );
    
    //自定义验证器：物流承运商是否有效
    public function addrule_valid_carrier($val)
    {
        $carriers = $vcache->shipping_carrier_model('indexed_list', null, FALSE);
        return isset($types[$val]);
    }
}