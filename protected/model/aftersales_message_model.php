<?php
class aftersales_message_model extends Model
{
    public $table_name = 'aftersales_message';

    public $rules = array
    (
        'content' => array
        (
            'is_required' => array(TRUE, '消息内容不能为空'),
            'min_length' => array(15, '消息内容不能少于15个字符'),
            'max_length' => array(500, '消息内容不能超过500个字符'),
        ),
    );
}
