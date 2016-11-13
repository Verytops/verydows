<?php
date_default_timezone_set('PRC');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('APP_DIR', realpath('../'));
define('INSTALL_DIR', APP_DIR.DS.'install');
error_reporting(-1);
set_time_limit(0);
require(INSTALL_DIR.DS.'resources'.DS.'version.php');
require(INSTALL_DIR.DS.'resources'.DS.'function.php');
header("Content-type:text/html;charset=utf-8");
$step = request('step');
if(file_exists(INSTALL_DIR.DS.'installed.lock'))
{
    header('Location: ../index.php');
    exit;
}

switch($step)
{
    case 1:
    
        $license = check_license();
        include(INSTALL_DIR.DS.'template'.DS.'step_1.html');
    
    break;
    
    case 2: 
        
        $check_envir = check_envir();
        $check_dirs = check_dirs();
        include(INSTALL_DIR.DS.'template'.DS.'step_2.html');
    
    break;
    
    case 3: include(INSTALL_DIR.DS.'template'.DS.'step_3.html'); break;
    
    case 4:
        
        $db_host = trim(request('db_host', 'localhost'));
        $db_port = trim(request('db_port', '3306'));
        $db_user = trim(request('db_user', 'root'));
        $db_pass = trim(request('db_pass', ''));
        $db_name = trim(request('db_name', ''));
        $db_table_pre = trim(request('db_table_pre', ''));
        $admin_username = trim(request('admin_username', ''));
        $admin_password = trim(request('admin_password', ''));
        $admin_repassword = trim(request('admin_repassword', ''));
        $admin_email = trim(request('admin_email', ''));
        
        if(empty($db_name)) prompt('数据库名称不能为空!');
        if(!preg_match('/^[A-Za-z0-9_-]*$/', $db_table_pre)) prompt('数据库表前缀不符合格式要求!');
        if(!preg_match('/^[a-zA-Z][_a-zA-Z0-9]{4,15}$/', $admin_username)) prompt('管理员用户名不符合格式要求!');
        if(!preg_match('/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{5,31}$/', $admin_password)) prompt('管理员密码不符合格式要求!');
        if($admin_password != $admin_repassword) prompt('管理员密码两次不一致!');
        if(!preg_match('/^[a-zA-Z0-9]+([._\-\+]*[a-zA-Z0-9]+)*@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]+$/', $admin_email)) prompt('管理员邮箱不符合格式要求!');
        
        $dsn = "mysql:host={$db_host};port={$db_port}";
        try{
            $dbh = new PDO($dsn, $db_user, $db_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}`;");
        }catch(PDOException $e){
            prompt($e->getMessage());
        }
        $dbh = null;
        
        $dsn .= ";dbname={$db_name}";
        $dbh = new PDO($dsn, $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\''));
        
        include(INSTALL_DIR.DS.'template'.DS.'step_4.html');
        
        $streams = str_replace('#tablepre#', $db_table_pre, file_get_contents(INSTALL_DIR.DS.'resources'.DS.'db.sql'));
        $streams = str_replace('#http_host#', baseurl(), $streams);
        $line_array = preg_split("/\n/", $streams);
        $sql = '';
        $install_errors = 0;
        
        ob_start();
        foreach($line_array as $line)
        {
            if(preg_match("/^#|^\-\-/", ltrim($line)) && trim($sql) == '') continue;
            $sql .= "{$line}\n";
            if(!preg_match("/;$/", trim($line))) continue;
            if(substr_count($sql, "/*") != substr_count($sql, "*/")) continue;
            
            $r = $dbh->exec(trim($sql)) !== FALSE ? 0 : 1;
            $install_errors += $r;
            if(stripos($sql, 'CREATE TABLE') !== FALSE)
            {
                $tbl_name = substr($sql, 12, strpos($sql, '(') - strpos($sql, '`'));
                $tbl_name = trim(str_replace('`', '', $tbl_name));
                call_script('showTableStatus', $r, $tbl_name);
            }
            $sql = '';
        }
        $encrypt_key = md5(str_shuffle(uniqid(rand(), TRUE)));
        $dbh->exec("INSERT INTO `{$db_table_pre}setting` VALUES ('encrypt_key', '{$encrypt_key}')");
        
        $admin_password = md5($admin_password.'Verydows');
        $admin_hash = sha1(str_shuffle($encrypt_key));
        $r = $dbh->exec("INSERT INTO `{$db_table_pre}admin` (`user_id`, `username`, `password`, `email`, `created_date`, `hash`) VALUES ('1', '{$admin_username}', '{$admin_password}', '{$admin_email}', '{$_SERVER['REQUEST_TIME']}', '{$admin_hash}')") !== FALSE ? 0 : 1;
        $install_errors += $r;
        call_script('showAdminStatus', $r, $admin_username);

        $config = array
        (
            '#DB_HOST#' => $db_host,
            '#DB_PORT#' => $db_port,
            '#DB_USER#' => $db_user,
            '#DB_NAME#' => $db_name,
            '#DB_PASS#' => $db_pass,
            '#DB_TABLE_PRE#' => $db_table_pre,
            '#VERSION#' => VDS_VERSION,
            '#RELEASE#' => VDS_RELEASE,
            '#COMMENCED#' => strtotime(date('Ymd', time())),
        );
        $r = write_config($config) !== FALSE ? 0 : 1;
        $install_errors += $r;
        call_script('showConfigStatus', $r, 'config.php');
        
        $r = init_setting($encrypt_key) !== FALSE ? 0 : 1;
        $install_errors += $r;
        call_script('showSettingStatus', $r, "setting.php");
        
        if(request('sample_data', 0) == 1)
        {
            //$install_errors += install_demo_data($dbh, $db_table_pre);
            //movie_demo_folder();
        }
        
        if($install_errors == 0)
        {
            copy(INSTALL_DIR.DS.'resources'.DS.'entrance.php', APP_DIR.DS.'index.php');
            call_script('finished', 'success', '安装成功');
        }
        else
        {
            call_script('finished', 'failure', "安装失败，错误数：{$install_errors}");
        }
        
        ob_end_flush();
        $dbh = null;
        
    break;
    
    case 'completed': 
        
        file_put_contents(INSTALL_DIR.DS.'installed.lock', 'locked');
        include(INSTALL_DIR.DS.'template'.DS.'completed.html');
    
    break;
    
    default: include(INSTALL_DIR.DS.'template'.DS.'index.html');
}

?>