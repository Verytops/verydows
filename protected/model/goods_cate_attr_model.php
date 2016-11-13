<?php
class goods_cate_attr_model extends Model
{
    public $table_name = 'goods_cate_attr';
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '属性名称不能为空'),
            'max_length' => array(60, '属性名称不能超过60个字符'),
        ),
        'seq' => array
        (
            'is_seq' => array(TRUE, '排序必须为0-99之间的整数'),
        ),
    );
}