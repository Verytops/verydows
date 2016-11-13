<?php
class payment_method_model extends Model
{
    public $table_name = 'payment_method';
    
    public $type_map = array('线上支付', '线下支付');
    
    public $rules = array
    (
        'instruction' => array('max_length' => array(240, '说明不能超过240个字符')),
        'seq' => array('is_seq' => array(TRUE, '排序必须为0-99之间的整数')),
    );
    
    /**
     * 支付方式列表(以主键作为数据列表索引)
     */
    public function indexed_list()
    {
        if($find_all = $this->find_all(array('enable' => 1), 'seq ASC')) return array_column($find_all, null, 'id');
        return $find_all;
    }
}