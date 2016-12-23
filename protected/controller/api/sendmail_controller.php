<?php
class sendmail_controller extends general_controller
{
    public function action_auth()
    {
        $user_id = $this->is_logined();
        $email = request('email', '');
        if(verifier::is_required($email, TRUE) && verifier::is_email($email, TRUE))
        {
            $tpl_id = 'email_captcha';
            $limit_model = new sendmail_limit_model();
            if($limit_model->check_counts($tpl_id, $user_id))
            {
                $captcha = str_shuffle(random_chars(4, TRUE).random_chars(2));
                $_SESSION['EMAIL_AUTH']['EMAIL'] = $email;
                $_SESSION['EMAIL_AUTH']['CAPTCHA'] = strtolower($captcha);
                $_SESSION['EMAIL_AUTH']['EXPIRES'] = $_SERVER['REQUEST_TIME'] + 3600;
                
                $tpl_model = new email_tpl_model();
                if($tpl_model->send_captcha($email, $captcha))
                {
                    $limit_model->increase($tpl_id, $user_id);
                    $res = array('status' => 'success');
                }
                else
                {
                    $res = array('status' => 'error', 'msg' => '发送邮件失败，请与网站管理员联系!');
                }
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '抱歉！您今日发送该邮件次数已超上限!');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '邮箱地址无效');
        }
        echo json_encode($res);
    }
    
    public function action_retrieve_password()
    {
        if(empty($_SESSION['RETRIEVE_PWD'])) die('Bad Request!');
        
        $tpl_id = 'email_captcha';
        $ip = get_ip();
        $limit_model = new sendmail_limit_model();
        if($limit_model->check_counts($tpl_id, 0, $ip))
        {
            $captcha = random_chars(4, TRUE).random_chars(2);
            $tpl_model = new email_tpl_model();
            if($tpl_model->send_captcha($_SESSION['RETRIEVE_PWD']['EMAIL'], $captcha))
            {
                $_SESSION['RETRIEVE_PWD']['CAPTCHA'] = strtolower($captcha);
                $_SESSION['RETRIEVE_PWD']['EXPIRES'] = $_SERVER['REQUEST_TIME'] + 3600;
                
                $limit_model->increase($tpl_id, 0, $ip);
                $res = array('status' => 'success');
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '发送邮件失败，请与网站管理员联系!');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '抱歉！您今日发送该邮件次数已超上限!');
        }
        echo json_encode($res);
    }

}
