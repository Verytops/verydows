<?php
class help_model extends Model
{
    public $table_name = 'help';
    
    public $rules = array
    (
        'title' => array
        (
            'is_required' => array(TRUE, '标题不能为空'),
            'max_length' => array(100, '标题不能超过100个字符'),
        ),
        'meta_keywords' => array
        (
            'max_length' => array(240, 'Meta 关键词不能超过240个字符'),
        ),
        'meta_description' => array
        (
            'max_length' => array(240, 'Meta 描述不能超过240个字符'),
        ),
    );
    
    /**
     * 按分类获取全部帮助信息列表
     */
    public function cated_help_list($limit = null)
    {
        $cate_model = new help_cate_model();
        if($list = $cate_model->indexed_list())
        {
            foreach($list as &$v)
            {
                $v['children'] = $this->find_all(array('cate_id' => $v['cate_id']), 'seq ASC', 'id, title, link', $limit);
            }
        }
        return $list;
    }
}