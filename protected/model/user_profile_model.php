<?php
class user_profile_model extends Model
{
    public $table_name = 'user_profile';
    
    public $rules = array
    (
        'nickname' => array
        (
            'max_length' => array(30, '昵称不能超过30个字符'),
        ),
        'signature' => array
        (
            'max_length' => array(120, '个性签名不能超过120个字符')
        )
    );
    
    public $addrules = array
    (
        'gender' => array('addrule_gender_scope' => '性别无效'),
        'qq' => array('addrule_qq_format' => 'QQ号码无效')
    );
    
    //自定义验证器：检查性别
    public function addrule_gender_scope($val)
    {
        return in_array($val, array(0, 1, 2));
    }
    
    //自定义验证器：检查QQ号码格式
    public function addrule_qq_format($val)
    {
        return preg_match('/^$|^[1-9][0-9]{4,12}$/', $val) != 0;
    }

}