<?php
class brand_model extends Model
{
    public $table_name = 'brand';
    
    public $rules = array
    (
        'brand_name' => array
        (
            'is_required' => array(TRUE, '品牌名称不能为空'),
            'max_length' => array(60, '品牌名称不能超过60个字符'),     
        ),
        'seq' => array
        (
            'is_seq' => array(TRUE, '排序必须为0-99之间的整数'),
        ),
    );
    
    /**
     * 获取品牌列表(并将brand_id作为列表数组索引)
     */
    public function indexed_list()
    {
        if($find_all = $this->find_all(null, 'seq ASC')) $find_all = array_column($find_all, null, 'brand_id');
        return $find_all; 
    }
}
