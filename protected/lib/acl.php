<?php
class acl
{
    private $module;
    /**
     * 匿名用户访问资源列表
     */
    private $anonymous = array
    (
        'main@index',
        'main@login',
    );
    
    /**
     * 系统一般用户访问资源列表
     */
    private $sysusers = array
    (
        'main@panel',
        'main@dashboard',
        'main@logout',
    );
    
    public function __construct($module)
    {
        $this->module = $module;
    }
    
    /**
     * 权限检查
     */
    public function check()
    {
       if(FALSE === $this->verifier()) $this->prompt();
    }
    
    /**
     * 设置身份标识
     */
    public static function set_id($identity)
    {
        $_SESSION['ADMIN']['USER_ID'] = $identity;
    }
    
    /**
     * 获取身份标识
     */
    public static function get_id()
    {
        if(isset($_SESSION['ADMIN']['USER_ID'])) return $_SESSION['ADMIN']['USER_ID'];
        return FALSE;
    }
    
    /**
     * 验证身份及拥有权限
     */
    private function verifier()
    {
        $identity = self::get_id();
        if($identity == 1) return TRUE; //初始化超级管理员跳过验证
        
        GLOBAL $__controller, $__action;
        $uri = $__controller.'@'.$__action;
        
        if(!in_array($uri, $this->anonymous))
        {
            if(!empty($identity)) 
            {
                if(!in_array($uri, $this->sysusers))
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
    
    /**
     * 无访问权限跳转提示
     */
    private function prompt()
    {
        header('Content-Type: text/html; charset=utf-8');
        $identity = self::get_id();
        if(empty($identity))
        {
            $url = url($this->module.'/main', 'index');
            echo "<script type='text/javascript'>alert('您还没有登陆或登录超时, 请重新登录!');parent.window.location.href='{$url}';</script>";
        }
        else
        {
           echo "<script type='text/javascript'>alert('您无权访问此资源!');window.history.back();</script>"; 
        }
        exit;
    }
}
