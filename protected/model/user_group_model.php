<?php
class user_group_model extends Model
{
    public $table_name = 'user_group';
    
    public $rules = array
    (
        'group_name' => array
        (
            'is_required' => array(TRUE, '用户组名称不能为空'),
            'max_length' => array(60, '用户组名称不能超过60个字符'),
        ),
        'min_exp' => array
        (
            'is_required' => array(TRUE, '经验值下限不能为空'),
            'is_nonegint' => array(TRUE, '经验值下限必须为非负整数'),
        ),
    );
    
    public $addrules = array
    (
        'min_exp' => array
        (
            'addrule_minexp_exist' => '已存在该经验值下限的用户组',
        ),
        'discount_rate' => array
        (
            'addrule_discount_format' => '折扣率必须为0-100之间的整数',
        ),
    );
    
    //自定义验证器：检查经验值下限是否已存在
    public function addrule_minexp_exist($val)
    {
        if($this->find(array('min_exp' => $val))) return FALSE;
        return TRUE;
    }
    
    //自定义验证器：检查折扣率格式
    public function addrule_discount_format($val)
    {
        return preg_match('/^(?:0|[1-9][0-9]?|100)$/', $val) != 0;
    }
}