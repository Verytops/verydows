<?php
class adv_position_model extends Model
{
    public $table_name = 'adv_position';
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '广告位名称不能为空'),
            'max_length' => array(100, '广告位名称不能超过100个字符'),
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
        'codes' => array
        (
            'is_required' => array(TRUE, '模板代码不能为空'),
        ),
    );
    
    /**
     * 获取广告位列表(并将id作为列表数组索引)
     */
    public function indexed_list()
    {
        if($find_all = $this->find_all(null, 'id ASC', 'id, name, width, height')) $find_all = array_column($find_all, null, 'id');
        return $find_all; 
    }
    
    /**
     * 保存广告位模板
     */
    public function save_tpl_file($id, $codes)
    {
        $path = VIEW_DIR.DS.'adv'.DS.$id.'.html';
        file_put_contents($path, $codes);
    }
    
    /**
     * 编译后取出模板
     */
    public function fetch_tpl($id)
    {
        $tpl_name = $id.'.html';
        if(is_file(VIEW_DIR.DS.'adv'.DS.$tpl_name))
        {
            $adv_list = vcache::instance()->adv_model('get_adv_codes_list');
            if(isset($adv_list[$id]))
            {
                $view = new View(VIEW_DIR.DS.'adv', APP_DIR.DS.'protected'.DS.'cache'.DS.'template');
                $view->assign(array('vars' => $adv_list[$id]));
                return $view->render($tpl_name);
            }
        }
        return FALSE;
    }
}
