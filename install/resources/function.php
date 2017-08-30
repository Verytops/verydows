<?php
function check_license()
{
    if($license = @file_get_contents(APP_DIR.DS.'LICENSE.txt')) return str_replace(chr(10), '<br />', $license);
    die('LICENSE文件不存在，无法继续安装');
}

function request($name, $default = FALSE, $method = 'request')
{
    switch($method)
    {
        case 'get': $value = isset($_GET[$name]) ? $_GET[$name]: FALSE; break;
        case 'post': $value = isset($_POST[$name]) ? $_POST[$name] : FALSE; break;
        case 'cookie': $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : FALSE; break;
        case 'request': 
        default:
            $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : FALSE;
        break;
   }
   if(FALSE === $value) return $default;
   return $value;
}

function prompt($msg, $url = 'history.go(-1)')
{
    $msg = addslashes($msg);
    echo "<script type=\"text/javascript\">alert(\"{$msg}\");{$url};</script>";
    exit;
}

function check_envir()
{
    $envir['disable'] = 0;
    $ok_html = "<font class=\"green\">支持 √</font>";
    $no_html = "<font class=\"red\">不支持 ×</font>";

    if(version_compare(PHP_VERSION, '5.2', '>='))
    {
        $envir['php'] = "<font class=\"green\">".PHP_VERSION." √</font>";
    }
    else
    {
        $envir['php'] = "<font class=\"red\">× PHP版本太低，Verydows系统要求PHP >= 5.2，您当前的版本号为".PHP_VERSION."</font>";
        $envir['disable'] += 1;
    }
    if(extension_loaded('pdo') && extension_loaded('pdo_mysql'))
    {
        $envir['pdo'] = $ok_html;
    }
    else
    {
        $envir['pdo'] = $no_html;
        $envir['disable'] += 1;
    }
    if(ini_get('file_uploads'))
    {
        $envir['upload'] = $ok_html;
    }
    else
    {
        $envir['upload'] = $no_html;
        $envir['disable'] += 1;
    }
    if(function_exists('file_get_contents'))
    {
        $envir['file_get_contents'] = $ok_html;
    }
    else
    {
        $envir['file_get_contents'] = $no_html;
        $envir['disable'] += 1;
    }
    if(function_exists('fsockopen'))
    {
        $envir['fsockopen'] = $ok_html;
    }
    else
    {
        $envir['fsockopen'] = $no_html;
        $envir['disable'] += 1;
    }
    if(function_exists('curl_init'))
    {
        $envir['cURL'] = $ok_html;
    }
    else
    {
        $envir['cURL'] = $no_html;
        $envir['disable'] += 1;
    }
    if(extension_loaded('gd') && function_exists('imagecreate'))
    {
        $envir['gd'] = $ok_html;
    }
    else
    {
        $envir['gd'] = $no_html;
        $envir['disable'] += 1;
    }
    return $envir;
}

function check_dirs()
{
    $dirs = array
    (
        '/',
        '/protected',
        '/protected/controller',
        '/protected/controller/api',
        '/protected/controller/backend',
        '/protected/controller/mobile',
        '/protected/model',
        '/protected/include',
        '/protected/lib',
        '/protected/cache',
        '/protected/cache/data',
        '/protected/cache/template',
        '/protected/cache/static',
        '/protected/view',
        '/protected/view/function',
        '/protected/view/adv',
        '/protected/view/backend',
        '/protected/view/frontend',
        '/protected/view/mobile',
        '/protected/view/mail',
        '/public',
        '/plugin',
        '/install',
        '/install/resources',
    );
    
    $results = array();
    
    foreach($dirs as $k => $v)
    {
        $results[$k]['dir'] = $v;
        
        $dir = realpath(APP_DIR.$v);
        
        if(is_readable($dir))
        {
            $results[$k]['read'] = "<font class=\"green\">√</font>";
        }
        else
        {
            $results[$k]['read'] = "<font class=\"red\">×</font>";
        }
        
        if(is_writable($dir))
        {
            $results[$k]['write'] = "<font class=\"green\">√</font>";
        }
        else
        {
            $results[$k]['write'] = "<font class=\"red\">×</font>";
        }
    }
    return $results;
}

function install_demo_data($dbh, $table_pre)
{
    $streams = str_replace('#tablepre#', $table_pre, file_get_contents(realpath(INSTALL_DIR.'/resources/demo/data.sql')));
    $line_array = preg_split("/\n/", $streams);
    $sql = '';
    $errors = 0;
    foreach($line_array as $line)
    {
        if(preg_match("/^#|^\-\-/", ltrim($line)) && trim($sql) == '') continue;
        $sql .= "{$line}\n";
        if(!preg_match("/;$/", trim($line))) continue;
        if(substr_count($sql, "/*") != substr_count($sql, "*/")) continue;
        $r = $dbh->exec(trim($sql)) !== FALSE ? 0 : 1;
        $errors += $r;
        $tbl_name = trim(str_replace(array('`', 'INSERT INTO'), '', substr($sql, 0, strpos($sql, 'VALUES'))));
        call_script('showDemoStatus', $r, $tbl_name);
        $sql = '';
    }
    return $errors;
}

function call_script($script, $error, $data)
{
    echo "<script type=\"text/javascript\">{$script}('{$error}', '{$data}');</script>";
    flush();
    ob_flush();
    usleep(200000);
}

function write_config($params)
{
    $streams = file_get_contents(INSTALL_DIR.DS.'resources'.DS.'config.php');
    foreach($params as $k => $v) $streams = str_replace($k, $v, $streams);
    return file_put_contents(APP_DIR.DS.'protected'.DS.'config.php', $streams);
}

function init_setting($encrypt_key)
{
    $setting = include(INSTALL_DIR.DS.'resources'.DS.'setting.php');
    $setting['http_host'] = baseurl();
    $setting['encrypt_key'] = $encrypt_key;
    $codes = "<?php \nreturn ".var_export($setting, TRUE).";";
    file_put_contents(APP_DIR.DS.'protected'.DS.'cache'.DS.'setting.php', $codes);
}

function baseurl()
{
    $protocol = 'http://';
    if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 1 || $_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] == 443)) $protocol = 'https://';
    $host = dirname($_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
    $host = str_replace('/install', '', $host);
    return $protocol.$host;
}

function movie_demo_folder()
{
    $image_dir = APP_DIR.DS.'upload'.DS.'goods'.DS.'image';
    $album_dir = APP_DIR.DS.'upload'.DS.'goods'.DS.'album';
    if(is_dir(APP_DIR.DS.'upload'.DS.'goods'))
    {
        if(is_dir($image_dir)) rmdir($image_dir);
        if(is_dir($album_dir)) rmdir($album_dir);
    }
    else
    {
        mkdir(APP_DIR.DS.'upload'.DS.'goods', 0777, TRUE);
    }
    rename(realpath(INSTALL_DIR.'/resources/demo/goods/image'), $image_dir);
    rename(realpath(INSTALL_DIR.'/resources/demo/goods/album'), $album_dir);
}

?>
