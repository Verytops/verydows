<?php
class goods_optional_type_model extends Model
{
    public $table_name = 'goods_optional_type';
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '类型名称不能为空'),
            'max_length' => array(20, '类型名称不能超过20个字符'),
        ),
    );
    
    public $addrules = array
    (
        'name' => array
        (
            'addrule_name_exist' => '该类型名称已存在',
        ),
    );
    
    //自定义验证器：检查名称是否存在
    public function addrule_name_exist($val)
    {
        if($this->find(array('name' => $val))) return FALSE;
        return TRUE;
    }
    
    /**
     * 获取选项类型列表(以主键作为列表数组索引)
     */
    public function indexed_list()
    {
        if($find_all = $this->find_all()) $find_all = array_column($find_all, 'name', 'type_id');
        return $find_all;
    }
}
