<?php
class shipping_carrier_model extends Model
{
    public $table_name = 'shipping_carrier';
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '名称不能为空'),
            'max_length' => array(30, '名称不能超过30个字符'),
        ),
    );
    
    /**
     * 物流承运商列表(以主键作为数据列表索引)
     */
    public function indexed_list()
    {
        if($find_all = $this->find_all()) return array_column($find_all, null, 'id');
        return $find_all;
    }
}
