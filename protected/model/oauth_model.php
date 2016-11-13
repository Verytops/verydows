<?php
class oauth_model extends Model
{
    public $table_name = 'oauth';
    
    /**
     * 获取启用的授权登录连接列表
     */
    public function get_enable_list($mod = '')
    {
        if($list = $this->find_all(array('enable' => 1))) 
        {
            $state = md5(uniqid(rand(), TRUE));
            foreach($list as &$v)
            {
                $oauth_obj = plugin::instance('oauth', $v['party'], array($v['params'], $mod), TRUE);
                $v['url'] = $oauth_obj->create_login_url($state);
            }
        }
        return $list;
    }
}