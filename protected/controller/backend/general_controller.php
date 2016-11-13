<?php
include(VIEW_DIR.DS.'function'.DS.'backend_paging.php');

class general_controller extends Controller
{
    public function init()
    {
        $this->MOD = substr(strrchr(dirname(__FILE__), DS), 1);
        $acl = new acl($this->MOD);
        $acl->check();
        $this->baseurl = $GLOBALS['cfg']['http_host'];
    }
    
    protected function compiler($tpl_name)
    {
        $this->display($this->MOD.DS.$tpl_name);
    }
    
    protected function prompt($type = 'default', $text = '', $redirect = '', $time = 3)
    {
        if(empty($redirect)) $redirect = 'javascript:history.back()';
        $this->rs = array('type' => $type, 'text' => $text, 'redirect' => $redirect, 'time' => $time);
        $this->compiler('prompt.html');
        exit;
    }
    
} 