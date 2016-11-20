<?php
class security_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $user_model = new user_model();
        $user = $user_model->find(array('user_id' => $user_id));
        $oauth_model = new user_oauth_model();
        $user['oauth_list'] = $oauth_model->find_all(array('user_id' => $user_id), null, 'party');
        $this->user = $user;
        $this->compiler('user_security.html');
    }
    
    public function action_setting()
    {
        $user_id = $this->is_logined();
        $condition = array('user_id' => $user_id);
        $field = request('field');
        switch($field)
        {
            case 'email':
                
                $user_model = new user_model();
                $user = $user_model->find($condition);
                $this->email = $user['email'];
                $this->field = $field;
            
            break;
            
            case 'mobile':
            
                $user_model = new user_model();
                $user = $user_model->find($condition);
                $this->mobile = $user['mobile'];
                $this->field = $field;
            
            break;
            
            case 'password':
                
                $this->field = $field;
            
            break;
            
            default: jump(url('main', '404'));
        }
        
        $this->compiler('user_security_setting.html');
    }
    
    public function action_update()
    {
        $user_id = $this->is_logined();
        $condition = array('user_id' => $user_id);
        switch(request('field'))
        {
            case 'email':
                
                if(empty($_SESSION['EMAIL_AUTH'])) $this->prompt('error', '非法请求');
                if($_SESSION['EMAIL_AUTH']['CAPTCHA'] != strtolower(request('email_captcha', ''))) $this->prompt('error', '验证码不正确');
                if($_SESSION['EMAIL_AUTH']['EXPIRES'] < $_SERVER['REQUEST_TIME']) $this->prompt('error', '验证码过期');
                if($_SESSION['EMAIL_AUTH']['EMAIL'] != request('email')) $this->prompt('error', '您所输入的邮箱与接收验证码的邮箱不一致');
                
                $user_model = new user_model();
                $user = $user_model->find($condition);
                if($user['email'] == $_SESSION['EMAIL_AUTH']['EMAIL'])
                {
                    if($user['email_status'] == 0)
                    {
                        $user_model->update($condition, array('email_status' => 1));
                    }
                    else
                    {
                        $this->prompt('error', '您的邮箱未作更换，请勿重复提交');
                    }
                }
                else
                {
                    if(!$user_model->find(array('email' => $_SESSION['EMAIL_AUTH']['EMAIL'])))
                    {
                        $user_model->update($condition, array('email' => $_SESSION['EMAIL_AUTH']['EMAIL'], 'email_status' => 1));
                    }
                    else
                    {
                        $this->prompt('error', '该邮箱已被其账号使用');
                    }
                }
                
                $this->prompt('success', '设置成功', url('security', 'index'));
            
            break;
            
            case 'mobile':
            
                $mobile = request('mobile', '');
                if(!verifier::is_required($mobile, TRUE)) $this->prompt('error', '手机号码不能为空');
                if(!verifier::is_moblie_no($mobile, TRUE)) $this->prompt('error', '手机号码无效');
                $user_model = new user_model();
                $user_model->update($condition, array('mobile' => $mobile));
                $this->prompt('success', '设置成功', url('security', 'index'));
            
            break;
            
            case 'password':
            
                $user_model = new user_model();
                $old_password = trim(request('old_password', '', 'post'));
                if($user_model->find(array('user_id' => $user_id, 'password' => md5e($old_password))))
                {
                    $data['password'] = trim(request('password', '', 'post'));
                    $data['repassword'] = trim(request('repassword', '', 'post'));
                    $verifier = $user_model->verifier($data, array('username' => FALSE, 'email' => FALSE, 'mobile' => FALSE, 'captcha' => FALSE));
                    if(TRUE === $verifier)
                    {
                        $user_model->update(array('user_id' => $user_id), array('password' => md5e($data['password'])));
                        $user_model->logout();
                        $this->prompt('success', '密码更改成功，请用新密码重新登录', url('user', 'login'));
                    }
                    else
                    {
                        $this->prompt('error', $verifier);
                    }
                }
                else
                {
                    $this->prompt('error', '原密码不正确，请重新输入');
                }
            
            break;
            
            default: jump(url('main', '404'));
        }
    }
}