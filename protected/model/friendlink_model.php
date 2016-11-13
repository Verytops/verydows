<?php
class friendlink_model extends Model
{
    public $table_name = 'friendlink';

    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '名称不能为空'),
            'max_length' => array(60, '名称不能超过60个字符'),
        ),
        
        'url' => array
        (
            'is_required' => array(TRUE, '链接地址不能为空'),
        ),
        
        'seq' => array
        (
            'is_seq' => array(TRUE, '排序必须为0-99之间的整数'),
        ),
    );

}
