<?php
class retrieve_controller extends general_controller
{
    public function action_password()
    {
        $this->step = (int)request('step', 1);
        switch($this->step)
        {
            case 1: $this->compiler('retrieve_password.html'); break;
            
            case 2:
                
                if(empty($_SESSION['CAPTCHA']) || $_SESSION['CAPTCHA'] != strtolower(request('captcha', '', 'post'))) $this->prompt('error', '您输入的验证码不正确，请重试');
                $user_model = new user_model();
                if($user = $user_model->find(array('username' => trim(request('username', '', 'post'))), null, 'email, password'))
                {
                    $_SESSION['RETRIEVE_PWD']['EMAIL'] = $user['email'];
                    $_SESSION['RETRIEVE_PWD']['PASSWORD'] = $user['password'];
                    
                    $pos = strpos($user['email'], '@');
                    $this->email = substr($user['email'], 0, 1) . '***' . substr($user['email'], $pos - 2, 2) . substr($user['email'], $pos);
                    $this->token = vencrypt($user['email'], TRUE, sha1($user['password'])); 
                    
                    $this->compiler('retrieve_password.html');
                }
                else
                {
                    $this->prompt('error', '您输入的用户名不存在，请重试');
                }
                
            break;
            
            case 3:
                
                if(empty($_SESSION['RETRIEVE_PWD']) || 
                   empty($_SESSION['RETRIEVE_PWD']['CAPTCHA']) ||
                   $_SESSION['RETRIEVE_PWD']['EMAIL'] != vdecrypt(request('token'), 300, sha1($_SESSION['RETRIEVE_PWD']['PASSWORD']))
                ) $this->prompt('error', '非法请求');
                
                if($_SESSION['RETRIEVE_PWD']['CAPTCHA'] != strtolower(request('email_captcha', '', 'post'))) $this->prompt('error', '验证码错误');
                if($_SESSION['RETRIEVE_PWD']['EXPIRES'] < $_SERVER['REQUEST_TIME']) $this->prompt('error', '验证码已过期');
                
                $user_model = new user_model();
                $user = $user_model->find(array('email' => $_SESSION['RETRIEVE_PWD']['EMAIL'], 'password' => $_SESSION['RETRIEVE_PWD']['PASSWORD']));
                $_SESSION['RETRIEVE_PWD']['UID'] = $user['user_id'];
                unset($user, $_SESSION['CAPTCHA']);
                $this->compiler('retrieve_password.html');
            
            break;
            
            case 4:
                
                if(empty($_SESSION['RETRIEVE_PWD']['UID'])) $this->prompt('error', '非法请求');
                
                $data['password'] = request('password', '', 'post');
                $data['repassword'] = request('repassword', '', 'post');
                
                $user_model = new user_model();
                $verifier = $user_model->verifier($data, array('username' => FALSE, 'email' => FALSE, 'mobile' => FALSE));
                if(TRUE === $verifier)
                {
                    $user_model->update(array('user_id' => $_SESSION['RETRIEVE_PWD']['UID']), array('password' => md5e($data['password'])));
                    unset($_SESSION['RETRIEVE_PWD']);
                    $this->compiler('retrieve_password.html');
                }
                else
                {
                    $this->prompt('error', $verifier);
                }
            
            break;
            
            default:  jump(url('main', '404'));
        }
    }

}