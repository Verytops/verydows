<?php
class order_consignee_model extends Model
{
    public $table_name = 'order_consignee';
    
    public $rules = array
    (
        'receiver' => array
        (
            'is_required' => array(TRUE, '收件人姓名不能为空'),
            'max_length' => array(30, '收件人姓名不能超过30个字符'),
        ),
        'province' => array
        (
            'is_required' => array(TRUE, '省份不能为空'),
            'max_length' => array(20, '省份不能超过20个字符'),
        ),
        'city' => array
        (
            'is_required' => array(TRUE, '城市不能为空'),
            'max_length' => array(20, '城市不能超过20个字符'),
        ),
        'borough' => array
        (
            'is_required' => array(TRUE, '区县不能为空'),
            'max_length' => array(20, '区县不能超过20个字符'),
        ),
        'address' => array
        (
            'is_required' => array(TRUE, '详细地址不能为空'),
            'max_length' => array(240, '详细地址不能超过240个字符'),
        ),  
        'zip' => array
        (
            'is_zip' => array(TRUE, '邮编格式不正确'),
        ),
        'mobile' => array
        (
            'is_moblie_no' => array(TRUE, '手机号码格式不正确'),
        ),
    );
    
    /**
     * 添加订单收件人记录
     */
    public function add_records($order_id, $consignee)
    {
        $area = new area();
        $maps = $area->get_area_name($consignee['province'], $consignee['city'], $consignee['borough']);
        return $this->create
        (
            array
            (
                'order_id' => $order_id,
                'receiver' => $consignee['receiver'],
                'province' => $maps['province'],
                'city' => $maps['city'],
                'borough' => $maps['borough'],
                'address' => $consignee['address'],
                'zip' => $consignee['zip'],
                'mobile' => $consignee['mobile'],
            )
        );
    }

}