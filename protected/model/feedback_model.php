<?php
class feedback_model extends Model
{
    public $table_name = 'feedback';

    public $type_map = array('其他', '商品', '活动', '售后', '投诉');
    
    public $status_map = array('待审核', '进行中', '已完成');
    
    public $rules = array
    (
        'subject' => array
        (
            'is_required' => array(TRUE, '主题不能为空'),
            'max_length' => array(120, '主题不能超过120个字符'),
        ),
        'content' => array
        (
            'is_required' => array(TRUE, '内容不能为空'),
            'min_length' => array(15, '内容不能少于15个字符'),
            'max_length' => array(500, '内容不能超过500个字符'),
        ),
        'mobile' => array
        (
            'is_required' => array(TRUE, '手机号码不能为空'),
            'is_moblie_no' => array(TRUE, '请填写一个有效的手机号码'),
        ),
    );
    
    public $addrules = array
    (
        'type' => array
        (
            'addrule_valid_type' => '请选择一个有效的类型',
        )
    );
    
    //自定义验证器：检查处理类型是否有效
    public function addrule_valid_type($val)
    {
        return isset($this->type_map[$val]);
    }
    
    public function get_user_feedback($user_id, $limit = null)
    {
        $res = $this->find_all(array('user_id' => $user_id), 'created_date DESC', 'fb_id, type, subject, created_date, status', $limit);
        if($res)
        {
            foreach($res as &$v)
            {
                $v['type'] = $this->type_map[$v['type']];
                $v['created_date'] = date('Y年m月d日', $v['created_date']);
            }
            return $res;
        }
        return null;
    }
}
