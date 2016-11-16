<?php
include(VIEW_DIR.DS.'function'.DS.'layout.php');
include(VIEW_DIR.DS.'function'.DS.'reviser.php');

class general_controller extends Controller
{
    public function init()
    {
        $this->common = array
        (
            'baseurl' => $GLOBALS['cfg']['http_host'],
            'theme' => $GLOBALS['cfg']['http_host'].'/public/theme/frontend/'.$GLOBALS['cfg']['enabled_theme'],
        );
        utilities::crontab();
    }

    protected function compiler($tpl_name)
    {
        $this->display('frontend'.DS.$GLOBALS['cfg']['enabled_theme'].DS.$tpl_name);
    }
    
    protected function prompt($type = null, $text = '', $redirect = null, $time = 3)
    {
        if(empty($type)) $type = 'default';
        if(empty($redirect)) $redirect = 'javascript:history.back()';
        $this->rs = array('type' => $type, 'text' => $text, 'redirect' => $redirect, 'time' => $time);
        $this->compiler('prompt.html');
        exit;
    }
    
    protected function is_logined($jump = TRUE)
    {
        if(empty($_SESSION['USER']))
        {
            $redirect = url('user', 'login');
            if($cookie = request('USER_STAYED', null, 'cookie'))
            {
                $user_model = new user_model();
                if($user_model->check_stayed($cookie, get_ip()))
                {
                    $_SESSION['REDIRECT'] = $_SERVER['REQUEST_URI'];
                    $redirect = $_SERVER['REQUEST_URI'];
                }
            }
            if($jump) jump($redirect);
            return FALSE;
        }
        return $_SESSION['USER']['USER_ID'];
    }
} 