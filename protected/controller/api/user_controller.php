<?php
class user_controller extends general_controller
{
    public function action_login()
    {
        $client_ip = get_ip();
        $error_model = new request_error_model();
        if($error_model->check($client_ip, $GLOBALS['cfg']['captcha_user_login']))
        {
            $captcha = strtolower(trim(request('captcha', '', 'post')));
            if(empty($_SESSION['CAPTCHA']) || $_SESSION['CAPTCHA'] != $captcha)
            {
                unset($_SESSION['CAPTCHA']);
                die(json_encode(array('status' => 'error', 'captcha' => 'enabled', 'msg' => '验证码不正确')));
            }
            unset($_SESSION['CAPTCHA']);
        }
        
        $username = trim(request('username', '', 'post'));
        $password = request('password', '', 'post');
        
        $user_model = new user_model();
        if($user = $user_model->find(array('username' => $username, 'password' => $password)))
        {
            if(request('stay')) $user_model->stay_login($user['user_id'], $user['password'], $client_ip);
            $user_model->set_logined_info($client_ip, $user['user_id'], $user['username'], $user['avatar']);
            $res = array('status' => 'success', 'redirect' => isset($_SESSION['REDIRECT']) ? $_SESSION['REDIRECT'] : null);
            unset($_SESSION['REDIRECT']);
        }
        else
        {
            $error_model->incr_err($client_ip);
            $res = array('status' => 'error', 'msg' => '用户名或密码错误');
        }
        
        echo json_encode($res);
    }

    public function action_register()
    {
        $data = array
        (
            'username' => trim(request('username', '', 'post')),
            'email' => trim(request('email', '', 'post')),
            'password' => trim(request('password', '', 'post')),
            'repassword' => trim(request('repassword', '', 'post')),
            'captcha' => strtolower(trim(request('captcha', ''))),
        );
        
        $user_model = new user_model();
        $verifier = $user_model->verifier($data, array('mobile' => FALSE));
        if(TRUE === $verifier)
        {
            if($user_model->register($data))
            {
                $res = array('status' => 'success');
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '注册失败!请稍后重试');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => $verifier[0]);
        }
        echo json_encode($res);
    }
    
    public function action_edit()
    {
        $user_id = $this->is_logined();
        $condition = array('user_id' => $user_id);
        switch(request('field'))
        {
            case 'avatar':
               
                $streams = request('streams', '');
                $mime = request('mime', 'image/jpeg');
                
                if($im = imager::create($streams, 'base64'))
                {
                    $region = array
                    (
                        'x' => (int)request('x', 0),
                        'y' => (int)request('y', 0),
                        'w' => (int)request('w', 0),
                        'h' => (int)request('h', 0),
                        'tw' => 60,
                        'th' => 60,
                    );
    
                    $date = date('ym');
                    $save_path = "upload/user/avatar/{$date}/".md5(uniqid(rand(), TRUE));
                    if($avatar = imager::crop($im, $region, $save_path, $mime))
                    {
                        $res['status'] = 'success';
                        $res['avatar'] = substr($avatar, strrpos($avatar, $date));
                        $user_model = new user_model();
                        $user_model->update(array('user_id' => $user_id), array('avatar' => $res['avatar']));
                    }
                    else
                    {
                        $res = array('status' => 'error', 'msg' => '保存头像失败');
                    }
                }
                else
                {
                    $res = array('status' => 'error', 'msg' => '创建头像失败');
                }
                echo json_encode($res);
                
            break;
            
            case 'email':
                
                if(empty($_SESSION['EMAIL_AUTH'])) die(json_encode(array('status' => 'error', 'msg' => '非法请求')));
                if($_SESSION['EMAIL_AUTH']['CAPTCHA'] != request('email_captcha')) die(json_encode(array('status' => 'error', 'msg' => '验证码不正确')));
                if($_SESSION['EMAIL_AUTH']['EXPIRES'] < $_SERVER['REQUEST_TIME']) die(json_encode(array('status' => 'error', 'msg' => '验证码过期')));
                if($_SESSION['EMAIL_AUTH']['EMAIL'] != request('email')) die(json_encode(array('status' => 'error', 'msg' => '您所输入的邮箱与接收验证码的邮箱不一致')));
                
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
                        die(json_encode(array('status' => 'error', 'msg' => '您的邮箱未作更换，请勿重复提交')));
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
                        die(json_encode(array('status' => 'error', 'msg' => '该邮箱已被其他账号使用')));
                    }
                }
                
                echo json_encode(array('status' => 'success'));
            
            break;
            
            case 'mobile':
                
                $mobile = request('mobile', '');
                if(!verifier::is_required($mobile, TRUE)) die(json_encode(array('status' => 'error', 'msg' => '手机号码不能为空')));
                if(!verifier::is_moblie_no($mobile, TRUE)) die(json_encode(array('status' => 'error', 'msg' => '手机号码无效')));
                $user_model = new user_model();
                $user_model->update($condition, array('mobile' => $mobile));
                echo json_encode(array('status' => 'success'));
            
            break;
            
            case 'nickname':
                
                $nickname = strip_tags(request('nickname', ''));
                if(!verifier::is_required($nickname, TRUE)) die(json_encode(array('status' => 'error', 'msg' => '昵称不能为空')));
                if(!verifier::max_length($nickname, 30)) die(json_encode(array('status' => 'error', 'msg' => '昵称不能超过30个字符')));
                $profile_model = new user_profile_model();
                $profile_model->update($condition, array('nickname' => $nickname));
                echo json_encode(array('status' => 'success'));
                
            break;
            
            case 'gender':
                
                $gender = (int)request('gender', 0);
                $profile_model = new user_profile_model();
                if(!$profile_model->addrule_gender_scope($gender)) die(json_encode(array('status' => 'error', 'msg' => '性别无效')));
                $profile_model->update($condition, array('gender' => $gender));
                echo json_encode(array('status' => 'success'));
                
            break;
            
            case 'qq':
                
                $qq = request('qq', '');
                if(!verifier::is_required($qq, TRUE)) die(json_encode(array('status' => 'error', 'msg' => 'QQ号码不能为空')));
                $profile_model = new user_profile_model();
                if(!$profile_model->addrule_qq_format($qq)) die(json_encode(array('status' => 'error', 'msg' => 'QQ号码无效')));
                $profile_model->update($condition, array('qq' => $qq));
                echo json_encode(array('status' => 'success'));
                
            break;
            
            case 'birthdate':
                
                $data['birth_year'] = (int)request('birth_year', 0);
                $data['birth_month'] = (int)request('birth_month', 0);
                $data['birth_day'] = (int)request('birth_day', 0);
                $profile_model = new user_profile_model();
                $profile_model->update($condition, $data);
                echo json_encode(array('status' => 'success'));
                
            break;
            
            case 'signature':
                
                $signature = strip_tags(request('signature', ''));
                if(!verifier::is_required($signature, TRUE)) die(json_encode(array('status' => 'error', 'msg' => '个性签名不能为空')));
                if(!verifier::max_length($signature, 120)) die(json_encode(array('status' => 'error', 'msg' => '个性签名不能超过120个字')));
                $profile_model = new user_profile_model();
                $profile_model->update($condition, array('signature' => $signature));
                echo json_encode(array('status' => 'success'));
                
            break;
        }
    }
    
    public function action_avatar()
    {
        $user_id = $this->is_logined();
        if(request('step') == 'crop') //剪裁
        {
            $streams = request('streams', '', 'post');
            $mime = request('mime', 'image/jpeg', 'post');
            if($im = imager::create($streams, 'base64'))
            {
                $region = array
                (
                    'x' => (int)request('x', 0, 'post'),
                    'y' => (int)request('y', 0, 'post'),
                    'w' => (int)request('w', 0, 'post'),
                    'h' => (int)request('h', 0, 'post'),
                    'tw' => 60,
                    'th' => 60,
                );
                    
                $date = date('ym');
                $save_path = "upload/user/avatar/{$date}/".md5(uniqid(rand(), TRUE));
                if($avatar = imager::crop($im, $region, $save_path, $mime))
                {
                    $res['status'] = 'success';
                    $res['avatar'] = substr($avatar, strrpos($avatar, $date));
                    $user_model = new user_model();
                    $user_model->update(array('user_id' => $user_id), array('avatar' => $res['avatar']));
                }
            }
            echo json_encode($res);
        }
        else //上传
        {
            $res = array('status' => 'error', 'callback' => request('callback', 'showCrop', 'post'), 'data' => '');
            if($user_id)
            {
                if(!empty($_FILES['avatar_file']['name']))
                {
                    $save_path = 'upload/tmp/'.$user_id;
                    if($tmp = imager::resize($_FILES['avatar_file']['tmp_name'], 300, 300, $save_path))
                    {
                        $res['status'] = 'success';
                        $res['data'] = imager::imtobase64($tmp, $_FILES['avatar_file']['type']);
                        @unlink($tmp);
                    }
                }
            }
            echo "<script type=\"text/javascript\">window.parent.{$res['callback']}('{$res['status']}', '{$res['data']}');</script>";
        }
    }
    
    public function action_footprint()
    {
        $goods_model = new goods_model();
        if($list = $goods_model->get_history())
        {
            $res = array('status' => 'success', 'list' => $list);
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
}
