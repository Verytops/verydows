<?php
class user_model extends Model
{
    public $table_name = 'user';
    
    public $rules = array
    (    
        'email' => array
        (
            'is_required' => array(TRUE, '邮箱不能为空'),
            'is_email' => array(TRUE, '无效的邮箱地址'),
            'max_length' => array(60, '邮箱不能超过60个字符'),
        ),
        'mobile' => array
        (
            'is_required' => array(TRUE, '手机号码不能为空'),
            'is_moblie_no' => array(TRUE, '无效的手机号码'),
        ),
        'repassword' => array
        (
            'equal_to' => array('password', '两次密码不一致'),
        ),
    );
    
    public $addrules = array
    (
        'username' => array
        (
            'addrule_username_format' => '用户名不符合格式要求',
            'addrule_username_exist' => '该用户名已被使用',
        ),
        'password' => array
        (
            'addrule_password_format' => '密码不符合格式要求',
        ),
        'email' => array
        (
            'addrule_email_exist' => '该邮箱已被使用',
        ),
        'captcha' => array
        (
            'addrule_check_captcha' => '验证码不正确',
        ),
    );
    
    //自定义验证器：检查用户名格式(可包含字母、数字或下划线，须以字母开头，长度为5-16个字符)
    public function addrule_username_format($val)
    {
        return preg_match('/^[a-zA-Z][_a-zA-Z0-9]{4,15}$/', $val) != 0;
    }
    
    //自定义验证器：检查用户名是否存在
    public function addrule_username_exist($val)
    {
        if($this->find(array('username' => $val))) return FALSE;
        return TRUE;
    }
    
    //自定义验证器：检查密码格式(可包含字母、数字或特殊符号，长度为6-32个字符)
    public function addrule_password_format($val)
    {
        return preg_match('/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{5,31}$/', $val) != 0;
    }
    
    //自定义验证器：检查邮箱是否存在
    public function addrule_email_exist($val)
    {
        if($this->find(array('email' => $val))) return FALSE;
        return TRUE;
    }
    
    //自定义验证器：检查注册时验证码
    public function addrule_check_captcha($val)
    {
        if($GLOBALS['cfg']['captcha_user_register'])
        {
            if(empty($_SESSION['CAPTCHA']) || $_SESSION['CAPTCHA'] != $val)
            {
                unset($_SESSION['CAPTCHA']);
                return FALSE;
            }
        }
        unset($_SESSION['CAPTCHA']);
        return TRUE;
    }
    
    /**
     * 保持登录
     */
    public function stay_login($user_id, $password, $ip)
    {
        $cookie = vencrypt(md5($ip.substr($password, 6, 24)).$user_id, TRUE);
        setcookie('USER_STAYED', $cookie, $_SERVER['REQUEST_TIME'] + 604800, '/');
    }
    
    /**
     * 验证保持登陆
     */
    public function check_stayed($cookie, $ip)
    {
        if(!empty($cookie))
        {
            if($cookie = vdecrypt($cookie, 604800))
            {
                if($user = $this->find(array('user_id' => (int)substr($cookie, 32))))
                {
                    if(md5($ip.substr($user['password'], 6, 24)) == substr($cookie, 0, 32))
                    {
                        $this->set_logined_info($ip, $user['user_id'], $user['username'], $user['avatar']);
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }
    
    /**
     * 设置登录后信息
     */
    public function set_logined_info($ip, $user_id, $username, $avatar = '')
    {
        $record_model = new user_record_model();
        $rec = $record_model->find(array('user_id' => $user_id));
        $record_model->update(array('user_id' => $user_id), array('last_date' => $_SERVER['REQUEST_TIME'], 'last_ip' => $ip));
        $_SESSION['USER']['USER_ID'] = $user_id;
        $_SESSION['USER']['LAST_DATE'] = $rec['last_date'];
        $_SESSION['USER']['LAST_IP'] = $rec['last_ip'];
        setcookie('LOGINED_USER', $username, null, '/');
        setcookie('USER_AVATAR', $avatar, null, '/');
        unset($_SESSION['LOGIN_TOKEN']);
    }
    
    /**
     * 用户注册
     */
    public function register($row)
    {
        unset($row['repassword'], $row['captcha']);
        $row['password'] = md5e($row['password']);
        if($user_id = $this->create($row))
        {
            $ip = get_ip();   
            $sql  = "INSERT INTO {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user_account (`user_id`) VALUES ('{$user_id}');";
            $sql .= "INSERT INTO {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user_profile (`user_id`) VALUES ('{$user_id}');";
            $sql .= "INSERT INTO {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user_record (`user_id`, `created_date`, `created_ip`, `last_date`, `last_ip`) 
                     VALUES ('{$user_id}', '{$_SERVER['REQUEST_TIME']}', '{$ip}', '{$_SERVER['REQUEST_TIME']}', '{$ip}');
                    ";
            $this->execute($sql);
            $_SESSION['USER']['USER_ID'] = $user_id;
            $_SESSION['USER']['LAST_DATE'] = $_SERVER['REQUEST_TIME'];
            $_SESSION['USER']['LAST_IP'] = $ip;
            setcookie('LOGINED_USER', $row['username'], null, '/');
            return $user_id;
        }
        return FALSE;
    }
    
    /**
     * 注销登录信息
     */
    public function logout()
    {
        unset($_SESSION['USER'], $_SESSION['OAUTH']);
        $overtime = $_SERVER['REQUEST_TIME'] - 3600;
        setcookie('LOGINED_USER', null, $overtime, '/');
        setcookie('USER_AVATAR', null, $overtime, '/');
        setcookie('USER_STAYED', null, $overtime, '/');
    }
}