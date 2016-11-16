<?php
class adv_model extends Model
{
    public $table_name = 'adv';
    
    public $type_map = array('image' => '图片', 'flash' => 'Flash', 'text' => '文字', 'code' => '代码');
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '广告名称不能为空'),
            'max_length' => array(100, '广告名不能超过100个字符'),
        ),
        'start_date' => array
        (
            'is_time' => array(TRUE, '起始日期不是一个有效时间格式'),
        ),
        'end_date' => array
        (
            'is_time' => array(TRUE, '结束日期不是一个有效时间格式'),
        ),
        'seq' => array
        (
            'is_seq' => array(TRUE, '显示顺序必须为0-99之间的整数'),
        ),
        'width' => array
        (
            'is_nonegint' => array(TRUE, '宽度必须为非负整数'),
            'max_length' => array(4, '宽度不能超过4位数'),
        ),
        'height' => array
        (
            'is_nonegint' => array(TRUE, '高度必须为非负整数'),
            'max_length' => array(4, '高度不能超过4位数'),
        ),
        'link' => array
        (
            'is_required' => array(TRUE, '链接地址不能为空'),
        ),
        'content' => array
        (
            'is_required' => array(TRUE, '广告内容不能为空'),
        ),
    );
    
    public $addrules = array
    (
        'type' => array('addrule_valid_type' => '必须选择一个有效的广告类型'),
    );
   
    //自定义验证器：广告类型是否有效
    public function addrule_valid_type($val)
    {
        $type_map = $this->type_map;
        return isset($type_map[$val]);
    }
    
    /**
     * 获取当前有效广告代码列表(以广告位id作为列表索引)
     */
    public function get_adv_codes_list()
    {
        $today = strtotime(date('Ymd'));
        $sql = "SELECT position_id, codes FROM {$this->table_name}
                WHERE (start_date <= {$today} OR start_date = 0) AND 
                      (end_date >= {$today} OR end_date = 0) AND
                      status = 1     
                ORDER BY seq ASC 
               ";
        $results = array();
        if($arr = $this->query($sql))
        {
            foreach($arr as $v) $results[$v['position_id']][] = $v['codes'];
        }
        return $results;
    }
    
}
