<?php
class nav_model extends Model
{
    public $table_name = 'navigation';

    public $pos_map = array
    (
        0 => '主导航栏',
        1 => '顶部导航栏',
        2 => '底部导航栏',
    );
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '名称不能为空'),
            'max_length' => array(60, '名称不能超过60个字符'),
        ),
        'link' => array
        (
            'is_required' => array(TRUE, '链接地址不能为空'),
        ),
        'seq' => array
        (
            'is_seq' => array(TRUE, '排序必须为0-99之间的整数数字'),
        ),
    );
    
    /**
     * 获取网站导航栏
     */
    public function get_site_nav()
    {
        $results = array('main' => array(), 'top' => array(), 'bottom' => array());
        if($find_all = $this->find_all(array('visible' => 1), 'seq ASC', 'name, link, position, target'))
        {
            foreach($find_all as $v)
            {
                if($v['target'] == 1) $v['target'] = ' target="_blank"'; else $v['target'] = '';
                switch($v['position'])
                {
                    case 0: $results['main'][] = $v; break;
                    case 1: $results['top'][] = $v; break;
                    case 2: $results['bottom'][] = $v; break;
                }
            }
        }
        return $results;
    }
}