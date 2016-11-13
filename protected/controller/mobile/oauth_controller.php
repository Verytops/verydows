<?php
class oauth_controller extends general_controller
{
    public function action_bind()
    {
        $party = sql_escape(request('party'));
        $oauth_model = new oauth_model();
        if($oauth = $oauth_model->find(array('party' => $party)))
        {
            $oauth_obj = plugin::instance('oauth', $party, array($oauth['params']), TRUE);
            if($access_token = $oauth_obj->check_callback($_GET))
            {
                if($oauth_key = $oauth_obj->get_oauth_key($access_token))
                {
                    $user_oauth_model = new user_oauth_model();
                    if($user_oauth_model->is_authorized($party, $oauth_key)) jump(url('mobile/user', 'index'));
                
                    $_SESSION['OAUTH']['KEY'] = $oauth_key;
                    $this->oauth = array('name' => $oauth['name'], 'party' => $party);
                    $error_model = new request_error_model();
                    $this->login_captcha = $error_model->check(get_ip(), $GLOBALS['cfg']['captcha_user_login']);
                    $this->compiler('oauth_bind.html');
                }
                else
                {
                    $this->prompt('error', '获取第三方授权登录身份标识失败!', url('mobile/user', 'login'), 5);
                }
            }
            else
            {
                $this->prompt('error', '第三方授权验证未通过!', url('mobile/user', 'login'), 5);
            }
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
}