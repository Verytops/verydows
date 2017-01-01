<?php
class acl
{
    private $_module;

    private $_anonymous = array
    (
        'main@index',
        'main@login',
    );
    
    private $_sysusers = array
    (
        'main@panel',
        'main@dashboard',
        'main@logout',
    );
    
    public function __construct($module)
    {
        $this->_module = $module;
    }
    
    public function check()
    {
       if(FALSE === $this->_verifier()) $this->_prompt();
    }
    
    public static function set_id($identity)
    {
        $_SESSION['ADMIN']['USER_ID'] = $identity;
    }
    
    public static function get_id()
    {
        if(isset($_SESSION['ADMIN']['USER_ID'])) return $_SESSION['ADMIN']['USER_ID'];
        return FALSE;
    }
    
    private function _verifier()
    {
        $identity = self::get_id();
        if($identity == 1) return TRUE; //初始化超级管理员跳过验证
        
        GLOBAL $__controller, $__action;
        $uri = $__controller.'@'.$__action;
        
        if(!in_array($uri, $this->_anonymous))
        {
            if(!empty($identity)) 
            {
                if(!in_array($uri, $this->_sysusers))
                {
                    $role_model = new admin_role_model();
                    $acls = $role_model->get_acls($identity);
                    if(in_array($uri, $acls)) return TRUE; //有访问权限
                }
                else
                {
                     return TRUE; //有访问权限
                }
            }
        }
        else
        {
            return TRUE; //有访问权限
        }
        
        return FALSE; //无访问权限
    }
    
    private function _prompt()
    {
        header('Content-Type: text/html; charset=utf-8');
        $identity = self::get_id();
        if(empty($identity))
        {
            $url = url($this->_module.'/main', 'index');
            echo "<script type='text/javascript'>alert('您还没有登陆或登录超时, 请重新登录!');top.location.href='{$url}';</script>";
        }
        else
        {
           echo "<script type='text/javascript'>alert('您无权访问此资源!');window.history.back();</script>"; 
        }
        exit;
    }
}
