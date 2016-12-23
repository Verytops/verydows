<?php
define('VIEW_DIR', APP_DIR.DS.'protected'.DS.'view');
$GLOBALS = require(APP_DIR.DS.'protected'.DS.'config.php');
$GLOBALS['cfg'] = require(APP_DIR.DS.'protected'.DS.'cache'.DS.'setting.php');
if($GLOBALS['cfg']['debug'])
{
    error_reporting(-1);
    ini_set('display_errors', 'On');
}
else
{
    error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
}
set_error_handler('_err_handle');
require(INCL_DIR.DS.'functions.php');

if($GLOBALS['cfg']['rewrite_enable'] && strpos($_SERVER['REQUEST_URI'], 'index.php?') === FALSE)
{
    if(($pos = strpos( $_SERVER['REQUEST_URI'], '?')) !== FALSE) parse_str(substr($_SERVER['REQUEST_URI'], $pos + 1), $_GET);
    foreach($GLOBALS['cfg']['rewrite_rule'] as $rule => $mapper)
    {
        if('/' == $rule)$rule = '';
        if(0!==stripos($rule, 'http://')) $rule = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') .'/'.$rule;
        $rule = '/'.str_ireplace(array('\\\\', 'http://', '/', '<', '>',  '.'), array('', '', '\/', '(?P<', '>\w+)', '\.'), $rule).'/i';
        if(preg_match($rule, 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $matchs))
        {
            $route = explode('/', $mapper);
            if(isset($route[2]))
            {
                list($_GET['m'], $_GET['c'], $_GET['a']) = $route;
            }
            else
            {
                list($_GET['c'], $_GET['a']) = $route;
            }
            foreach($matchs as $matchkey => $matchval)
            {
                if(!is_int($matchkey))$_GET[$matchkey] = $matchval;
            }
            break;
        }
    }
}

$_REQUEST = array_merge($_POST, $_GET);
$__module     = request('m', '');
$__controller = request('c', 'main');
$__action     = request('a', 'index');

if(!empty($__module))
{
    if(!is_available_classname($__module)) err("Err: Module name '$__module' is not correct!");
    if(!is_dir(APP_DIR.DS.'protected'.DS.'controller'.DS.$__module))err("Err: Module '$__module' is not exists!");
}
if(!is_available_classname($__controller)) err("Err: Controller name '$__controller' is not correct!");

spl_autoload_register('inner_autoload');
function inner_autoload($class)
{
    GLOBAL $__module;
    foreach(array('model', 'lib', 'controller'.(empty($__module)?'':DS.$__module)) as $dir)
    {
        $file = APP_DIR.DS.'protected'.DS.$dir.DS.$class.'.php';
        if(is_file($file))
        {
            include $file;
            return;
        }
        $lowerfile = strtolower($file);
        foreach(glob(APP_DIR.DS.'protected'.DS.$dir.DS.'*.php') as $file)
        {
            if(strtolower($file) === $lowerfile)
            {
                include $file;
                return;
            }
        }
    }
}

session_name('VDSSKEY');
session_start();

$controller_name = $__controller.'_controller';
$action_name = 'action_'.$__action;
if(!class_exists($controller_name, true)) err("Err: Controller '$controller_name' is not exists!");
$controller_obj = new $controller_name();
if(!method_exists($controller_obj, $action_name)) err("Err: Method '$action_name' of '$controller_name' is not exists!");

$controller_obj->$action_name();

function url($c = 'main', $a = 'index', $param = array())
{
    if(is_array($c))
    {
        $param = $c;
        if(isset($param['m'])) $m = $param['m']; unset($param['m']);
        $c = $param['c']; unset($param['c']);
        $a = $param['a']; unset($param['a']);
    }
    
    $param = array_filter($param);
    $params = empty($param) ? '' : '&'. urldecode(http_build_query($param));

    if(isset($m))
    {
        $route = "$m/$c/$a";
        $url = $_SERVER["SCRIPT_NAME"]."?m=$m&c=$c&a=$a$params";
    }
    elseif(strpos($c, '/') !== false)
    {
        list($m, $c) = explode('/', $c);
        $route = "$m/$c/$a";
        $url = $_SERVER["SCRIPT_NAME"]."?m=$m&c=$c&a=$a$params";
    }
    else
    {
        $m = '';
        $route = "$c/$a";
        $url = $_SERVER["SCRIPT_NAME"]."?c=$c&a=$a$params";
    }
    
    if($GLOBALS['cfg']['rewrite_enable'] && ($m == '' || $m == 'mobile' || $m == 'api'))
    {
        static $urlArray = array();
        if(!isset($urlArray[$url]))
        {
            foreach($GLOBALS['cfg']['rewrite_rule'] as $rule => $mapper)
            {
                $mapper = '/'.str_ireplace(array('/', '<a>', '<c>', '<m>'), array('\/', '(?P<a>\w+)', '(?P<c>\w+)', '(?P<m>\w+)'), $mapper).'/i';
                if(preg_match($mapper, $route, $matchs))
                {
                    $urlArray[$url] = str_ireplace(array('<a>', '<c>', '<m>'), array($a, $c, $m), $rule);
                    if(!empty($param))
                    {
                        $_args = array();
                        foreach($param as $argkey => $arg)
                        {
                            $count = 0;
                            $urlArray[$url] = str_ireplace('<'.$argkey.'>', $arg, $urlArray[$url], $count);
                            if(!$count)$_args[$argkey] = $arg;
                        }
                        $urlArray[$url] = preg_replace('/<\w+>/', '', $urlArray[$url]).(!empty($_args) ? '?'.urldecode(http_build_query($_args)) : '');
                    }
					
                    if(0!==stripos($urlArray[$url], 'http://')) $urlArray[$url] = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') .'/'.$urlArray[$url];
                    $rule = str_ireplace(array('<m>', '<c>', '<a>'), '', $rule);
                    if(count($param) == preg_match_all('/<\w+>/is', $rule, $_match)) return $urlArray[$url];
                    break;
                }
            }
            return isset($urlArray[$url]) ? $urlArray[$url] : $url;
        }
        return $urlArray[$url];
    }
    return $url;
}

function dump($var, $exit = FALSE)
{
    $output = print_r($var, true);
    if(!$GLOBALS['cfg']['debug'])return error_log(str_replace("\n", '', $output));
    echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body><div align=left><pre>" .htmlspecialchars($output). "</pre></div></body></html>";
    if($exit) exit();
}

function is_available_classname($name)
{
    return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name);
}

class Controller
{
    private $_v;
    private $_data = array();

	public function init(){}
	public function __construct(){$this->init();}
	public function __get($name){return $this->_data[$name];}
	public function __set($name, $value){$this->_data[$name] = $value;}
	
    public function display($tpl_name)
    {
        if(!$this->_v) $this->_v = new View(VIEW_DIR, APP_DIR.DS.'protected'.DS.'cache'.DS.'template');
        $this->_v->assign($this->_data);
        echo $this->_v->render($tpl_name);
    }
}

class Model
{
    public $page;
    public $table_name;
    protected $sql = array();
	
    public function __construct($table_name = null)
    {
        $this->table_name = $GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']. ($table_name ? $table_name : $this->table_name);
    }
    
    public function find_all($conditions = array(), $sort = null, $fields = '*', $limit = null)
    {
        $sort = !empty($sort) ? ' ORDER BY '.$sort : '';
        $conditions = $this->_where($conditions);

        $sql = ' FROM '.$this->table_name.$conditions["_where"];
        $total = $this->query('SELECT COUNT(*) as M_COUNTER '.$sql, $conditions["_bindParams"]);
        if($total[0]['M_COUNTER'] > 0)
        {
            $limit = $this->set_limit($limit, $total[0]['M_COUNTER']);
            return $this->query('SELECT '. $fields . $sql . $sort . $limit, $conditions["_bindParams"]);
        }
        return null;
	}
	
    public function find($conditions = array(), $sort = null, $fields = '*')
    {
        $conditions = $this->_where($conditions);
        $sql = ' FROM '.$this->table_name.$conditions["_where"];
        $sort = !empty($sort) ? ' ORDER BY '.$sort : '';
        $res = $this->query('SELECT '. $fields . $sql . $sort . ' LIMIT 1', $conditions["_bindParams"]);
        return !empty($res) ? array_pop($res) : false;
    }
	
    public function update($conditions, $row)
    {
        $values = array();
        foreach($row as $k => $v)
        {
            $values[":M_UPDATE_".$k] = $v;
            $setstr[] = "`{$k}` = ".":M_UPDATE_".$k;
        }
        $conditions = $this->_where( $conditions );
        return $this->execute("UPDATE ".$this->table_name." SET ".implode(', ', $setstr).$conditions["_where"], $conditions["_bindParams"] + $values);
    }

    public function incr($conditions, $field, $optval = 1)
    {
        $conditions = $this->_where( $conditions );
        return $this->execute("UPDATE ".$this->table_name." SET `{$field}` = `{$field}` + :M_INCR_VAL ".$conditions["_where"], $conditions["_bindParams"] + array(":M_INCR_VAL" => $optval));
    }
    
    public function decr($conditions, $field, $optval = 1){return $this->incr($conditions, $field, - $optval);}
	
    public function delete($conditions)
    {
        $conditions = $this->_where( $conditions );
        return $this->execute("DELETE FROM ".$this->table_name.$conditions["_where"], $conditions["_bindParams"]);
    }
	
    public function create($row, $return_field = null)
    {
        $values = array();
        foreach($row as $k => $v)
        {
            $keys[] = "`{$k}`";
            $values[":".$k] = $v;
            $marks[] = ":".$k;
        }
        $this->execute("INSERT INTO ".$this->table_name." (".implode(', ', $keys).") VALUES (".implode(', ', $marks).")", $values);
        return $this->db_instance($GLOBALS['mysql'], 'master')->lastInsertId($return_field);
    }
	
    public function find_count($conditions = array())
    {
        $conditions = $this->_where( $conditions );
        $count = $this->query("SELECT COUNT(*) AS M_COUNTER FROM ".$this->table_name.$conditions["_where"], $conditions["_bindParams"]);
        return $count[0]['M_COUNTER'];
    }
	
	public function dump_sql(){return $this->sql;}
	
    public function pager($page, $pernum = 10, $scope = 10, $total)
    {
        $this->page = null;
        if($total > $pernum)
        {
            $total_page = ceil($total / $pernum);
            $page = min(intval(max($page, 1)), $total);
            $this->page = array
            (
                'total_count' => $total, 
                'page_size'   => $pernum,
                'total_page'  => $total_page,
                'first_page'  => 1,
                'prev_page'   => ( ( 1 == $page ) ? 1 : ($page - 1) ),
                'next_page'   => ( ( $page == $total_page ) ? $total_page : ($page + 1)),
                'last_page'   => $total_page,
                'current_page'=> $page,
                'all_pages'   => array(),
                'scope'       => $scope,
                'offset'      => ($page - 1) * $pernum,
                'limit'       => $pernum,
            );
            $scope = (int)$scope;
            if($total_page <= $scope)
            {
                $this->page['all_pages'] = range(1, $total_page);
            }
            elseif($page <= $scope/2)
            {
                $this->page['all_pages'] = range(1, $scope);
            }
            elseif($page <= $total_page - $scope/2)
            {
                $right = $page + (int)($scope/2);
                $this->page['all_pages'] = range($right-$scope+1, $right);
            }
            else
            {
                $this->page['all_pages'] = range($total_page-$scope+1, $total_page);
            }
        }
        return $this->page;
    }
    
    public function set_limit($limit = null, $total)
    {
        if(is_array($limit))
        {
            $limit = $limit + array(1, 10, 10);
            foreach($limit as &$v) $v = (int)$v;
            $this->pager($limit[0], $limit[1], $limit[2], $total);
            return empty($this->page) ? '' : " LIMIT {$this->page['offset']},{$this->page['limit']}";
        }
        return $limit ? ' LIMIT '.$limit : '';
    }
	
    public function query($sql, $params = array()){return $this->execute($sql, $params, true);}
    
    public function execute($sql, $params = array(), $readonly = FALSE)
    {
        $this->sql[] = $sql;
        if($readonly && !empty($GLOBALS['mysql']['MYSQL_SLAVE']))
        {
            $slave_key = array_rand($GLOBALS['mysql']['MYSQL_SLAVE']);
            $sth = $this->db_instance($GLOBALS['mysql']['MYSQL_SLAVE'][$slave_key], 'slave_'.$slave_key)->prepare($sql);
        }
        else
        {
            $sth = $this->db_instance($GLOBALS['mysql'], 'master')->prepare($sql);
        }
		
        if(is_array($params) && !empty($params))
        {
            foreach($params as $k=>&$v) $sth->bindParam($k, $v);
        }
        if($sth->execute()) return $readonly ? $sth->fetchAll(PDO::FETCH_ASSOC) : $sth->rowCount();
        $err = $sth->errorInfo();
        err('Database SQL: "' . $sql. '", ErrorInfo: '. $err[2], 1);
    }
    
    public function db_instance($db_config, $db_config_key, $force_replace = FALSE)
    {
        if($force_replace || empty($GLOBALS['instance']['mysql'][$db_config_key]))
        {
            try{
                $GLOBALS['instance']['mysql'][$db_config_key] = new PDO('mysql:dbname='.$db_config['MYSQL_DB'].';host='.$db_config['MYSQL_HOST'].';port='.$db_config['MYSQL_PORT'], $db_config['MYSQL_USER'], $db_config['MYSQL_PASS'], array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \''.$db_config['MYSQL_CHARSET'].'\''));
            }catch(PDOException $e){err('Database Err: '.$e->getMessage());}
        }
        return $GLOBALS['instance']['mysql'][$db_config_key];
    }
	
    private function _where($conditions)
    {
        $result = array( "_where" => " ","_bindParams" => array());
        if(is_array($conditions) && !empty($conditions))
        {
            $fieldss = array(); $sql = null; $join = array();
            if(isset($conditions[0]) && $sql = $conditions[0]) unset($conditions[0]);
            foreach($conditions as $key => $condition)
            {
                if(substr($key, 0, 1) != ":")
                {
                    unset($conditions[$key]);
                    $conditions[":".$key] = $condition;
                }
                $join[] = "`{$key}` = :{$key}";
            }
            if(!$sql) $sql = join(" AND ",$join);

            $result["_where"] = " WHERE ". $sql;
            $result["_bindParams"] = $conditions;
        }
        return $result;
    }
    
    public function verifier($data, $slices = array())
    {
        if(!isset($this->rules)) $this->rules = array();
        if(!empty($this->addrules))
        {
            foreach($this->addrules as $k => $v)
            {
                foreach($v as $kk => $vv) 
                {
                    $add = array($kk => array($this->$kk(isset($data[$k])? $data[$k] : null), $vv));
                    if(isset($this->rules[$k])) $this->rules[$k] = $this->rules[$k] + $add; else $this->rules[$k] = $add;
                }
            }
        }

        if(!empty($this->rules))
        {
            $verifier = new verifier($data, $this->rules);
            if(!empty($slices)) $verifier->rules_slices($slices);
            return $verifier->checking();
        }
        return array('Undefined validation rules');
    }
}

class View
{
    private $left_delimiter, $right_delimiter, $template_dir, $compile_dir;
    private $template_vals = array();
	
    public function __construct($template_dir, $compile_dir, $left_delimiter = '<{', $right_delimiter = '}>')
    {
        $this->left_delimiter = $left_delimiter; 
        $this->right_delimiter = $right_delimiter;
        $this->template_dir = $template_dir;     
        $this->compile_dir  = $compile_dir;
    }
	
    public function render($tempalte_name)
    {
        $complied_file = $this->compile($tempalte_name);
		
        @ob_start();
        extract($this->template_vals, EXTR_SKIP);
        $_view_obj = & $this;
        include $complied_file;
		
        return ob_get_clean();
    } 
	
    public function assign($mixed, $val = '')
    {
        if(is_array($mixed))
        {
            foreach($mixed as $k => $v)
            {
                if($k != '')$this->template_vals[$k] = $v;
            }
        }
        else
        {
            if($mixed != '')$this->template_vals[$mixed] = $val;
        }
    }

    public function compile($tempalte_name)
    {
        $file = $this->template_dir.DS.$tempalte_name;
        if(!$file = realpath($file)) err('Err: "'.$this->template_dir.DS.$tempalte_name.'" is not exists!');
        if(!is_writable($this->compile_dir) || !is_readable($this->compile_dir)) err('Err: Directory "'.$this->compile_dir.'" is not writable or readable');
        $complied_file = $this->compile_dir.DS.md5($file).'.'.filemtime($file).'.'.basename($tempalte_name).'.php';
        if(is_file($complied_file)) return $complied_file;

        $template_data = file_get_contents($file); 
        $template_data = $this->_compile_struct($template_data);
        $template_data = $this->_compile_function($template_data);
        $template_data = '<?php if(!class_exists("View", false)) exit("no direct access allowed");?>'.$template_data;
		
        $this->_clear_compliedfile($tempalte_name);
        file_put_contents($complied_file, $template_data);
		
        return $complied_file;
    }

    private function _compile_struct($template_data)
    {
        $foreach_inner_before = '<?php $_foreach_$3_counter = 0; $_foreach_$3_total = count($1);?>';
        $foreach_inner_after  = '<?php $_foreach_$3_index = $_foreach_$3_counter;$_foreach_$3_iteration = $_foreach_$3_counter + 1;$_foreach_$3_first = ($_foreach_$3_counter == 0);$_foreach_$3_last = ($_foreach_$3_counter == $_foreach_$3_total - 1);$_foreach_$3_counter++;?>';
        $ld = $this->left_delimiter;
        $rd = $this->right_delimiter;
        $pattern_map = array(
            '<{\*([\s\S]+?)\*}>' => '<?php /* $1*/?>',
            '(<{((?!}>).)*?)(\$[\w\_\"\'\[\]]+?)\.(\w+)(.*?}>)' => '$1$3[\'$4\']$5',
            '(<{.*?)(\$(\w+)@(index|iteration|first|last|total))+(.*?}>)' => '$1$_foreach_$3_$4$5',
            '<{(\$[\S]+?)\snofilter\s*}>'          => '<?php echo $1; ?>',
            '<{(\$[\w\_\"\'\[\]]+?)\s*=(.*?)\s*}>'           => '<?php $1 =$2; ?>',
            '<{(\$[\S]+?)}>'          => '<?php echo htmlspecialchars($1, ENT_QUOTES, "UTF-8"); ?>',
            '<{if\s*(.+?)}>'          => '<?php if ($1) : ?>',
            '<{elseif\s*(.+?)}>'      => '<?php elseif ($1) : ?>',
            '<{else}>'                => '<?php else : ?>',
            '<{break}>'               => '<?php break; ?>',
            '<{continue}>'            => '<?php continue; ?>',
            '<{\/if}>'                => '<?php endif; ?>',
            '<{foreach\s*(\$[\w\.\_\"\'\[\]]+?)\s*as(\s*)\$([\w\_\"\'\[\]]+?)}>' => $foreach_inner_before.'<?php foreach( $1 as $$3 ) : ?>'.$foreach_inner_after,
            '<{foreach\s*(\$[\w\.\_\"\'\[\]]+?)\s*as\s*(\$[\w\_\"\'\[\]]+?)\s*=>\s*\$([\w\_\"\'\[\]]+?)}>'  => $foreach_inner_before.'<?php foreach( $1 as $2 => $$3 ) : ?>'.$foreach_inner_after,
            '<{\/foreach}>'           => '<?php endforeach; ?>',
            '<{include\s*file=(.+?)}>'=> '<?php include $_view_obj->compile($1); ?>',
        );
        $pattern = $replacement = array();
        foreach($pattern_map as $p => $r)
        {
            $pattern = '/'.str_replace(array("<{", "}>"), array($this->left_delimiter.'\s*','\s*'.$this->right_delimiter), $p).'/i';
            $count = 1;
            while($count != 0){
                $template_data = preg_replace($pattern, $r, $template_data, -1, $count);
            }
        }
        return $template_data;
    }
	
    private function _compile_function($template_data)
    {
        $pattern = '/'.$this->left_delimiter.'([\w_]+)\s*(.*?)'.$this->right_delimiter.'/';
        return preg_replace_callback($pattern, array($this, '_compile_function_callback'), $template_data);
    }
	
    private function _compile_function_callback($matches)
    {
        if(empty($matches[2]))return '<?php echo '.$matches[1].'();?>';
        $sysfunc = preg_replace('/\((.*)\)\s*$/', '<?php echo '.$matches[1].'($1);?>', $matches[2], -1, $count);
        if($count)return $sysfunc;
        $pattern_inner = '/\b([\w_]+?)\s*=\s*(\$[\w"\'\]\[\-_>\$]+|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\')\s*?/'; 
        
        $params = "";
        if(preg_match_all($pattern_inner, $matches[2], $matches_inner, PREG_SET_ORDER))
        {
            $params = "array(";
            foreach($matches_inner as $m) $params .= '\''. $m[1]."'=>".$m[2].", ";
            $params .= ")";
        }
        else
        {
            err('Err: Parameters of \''.$matches[1].'\' is incorrect!');
        }
        return '<?php echo '.$matches[1].'('.$params.');?>';
    }

    private function _clear_compliedfile($tempalte_name)
    {
        $dir = scandir($this->compile_dir);
        if($dir)
        {
            $part = md5(realpath($this->template_dir.DS.$tempalte_name));
            foreach($dir as $d)
            {
                if(substr($d, 0, strlen($part)) == $part) @unlink($this->compile_dir.DS.$d);
            }
        }
    }
}

function _err_handle($errno, $errstr, $errfile, $errline)
{
    if(0 === error_reporting()) return;
    $msg = "ERROR";
    switch($errno)
    {
        case E_WARNING: $msg = "WARNING"; break;
        case E_NOTICE: $msg = "NOTICE"; break;
        case E_STRICT: $msg = "STRICT"; break;
        case 8192: $msg = "DEPRECATED"; break;
        default : $msg = "Unknown Error Type";
    }
    err("$msg: $errstr in $errfile on line $errline");
}

function err($msg)
{
    $traces = debug_backtrace();
    if(!$GLOBALS['cfg']['debug'])
    {
        if(!empty($GLOBALS['err_handler']))
        {
            call_user_func($GLOBALS['err_handler'], $msg, $traces);
        }
        else
        {
            error_log($msg);
        }
    }
    else
    {
        if(ob_get_contents()) ob_end_clean();
        function _err_highlight_code($code){if(preg_match('/\<\?(php)?[^[:graph:]]/i', $code)){return highlight_string($code, TRUE);}else{return preg_replace('/(&lt;\?php&nbsp;)+/i', "", highlight_string("<?php ".$code, TRUE));}}
        function _err_getsource($file, $line){if(!(file_exists($file) && is_file($file))) {return '';}$data = file($file);$count = count($data) - 1;$start = $line - 5;if ($start < 1) {$start = 1;}$end = $line + 5;if ($end > $count) {$end = $count + 1;}$returns = array();for($i = $start; $i <= $end; $i++) {if($i == $line){$returns[] = "<div id='current'>".$i.".&nbsp;"._err_highlight_code($data[$i - 1], TRUE)."</div>";}else{$returns[] = $i.".&nbsp;"._err_highlight_code($data[$i - 1], TRUE);}}return $returns;
}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta name="robots" content="noindex, nofollow, noarchive" /><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title><?php echo $msg;?></title><style>body{padding:0;margin:0;word-wrap:break-word;word-break:break-all;font-family:Courier,Arial,sans-serif;background:#EBF8FF;color:#5E5E5E;}div,h2,p,span{margin:0; padding:0;}ul{margin:0; padding:0; list-style-type:none;font-size:0;line-height:0;}#body{width:918px;margin:0 auto;}#main{width:918px;margin:13px auto 0 auto;padding:0 0 35px 0;}#contents{width:918px;float:left;margin:13px auto 0 auto;background:#FFF;padding:8px 0 0 9px;}#contents h2{display:block;background:#CFF0F3;font:bold 20px;padding:12px 0 12px 30px;margin:0 10px 22px 1px;}#contents ul{padding:0 0 0 18px;font-size:0;line-height:0;}#contents ul li{display:block;padding:0;color:#8F8F8F;background-color:inherit;font:normal 14px Arial, Helvetica, sans-serif;margin:0;}#contents ul li span{display:block;color:#408BAA;background-color:inherit;font:bold 14px Arial, Helvetica, sans-serif;padding:0 0 10px 0;margin:0;}#oneborder{width:800px;font:normal 14px Arial, Helvetica, sans-serif;border:#EBF3F5 solid 4px;margin:0 30px 20px 30px;padding:10px 20px;line-height:23px;}#oneborder span{padding:0;margin:0;}#oneborder #current{background:#CFF0F3;}</style></head><body><div id="main"><div id="contents"><h2><?php echo $msg?></h2><?php foreach($traces as $trace){if(is_array($trace)&&!empty($trace["file"])){$souceline = _err_getsource($trace["file"], $trace["line"]);if($souceline){?><ul><li><span><?php echo $trace["file"];?> on line <?php echo $trace["line"];?> </span></li></ul><div id="oneborder"><?php foreach($souceline as $singleline)echo $singleline;?></div><?php }}}?></div></div><div style="clear:both;padding-bottom:50px;" /></body></html><?php }
    exit;
}