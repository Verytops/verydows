<?php
class role_model extends Model
{
    public $table_name = 'role';
    
    public $rules = array
    (
        'role_name' => array
        (
            'is_required' => array(TRUE, '角色名不能为空'),
            'max_length' => array(50, '角色名不能超过50个字符'),
        ),
        'role_desc' => array
        (
            'max_length' => array(240, '角色简介不能超过240个字符'),
        ),
    );
}