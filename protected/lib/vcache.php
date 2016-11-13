<?php
class vcache
{
    private static $_instance = null;
    
    private static $_mem = null;
    
    public static function instance($type = 'memcache', $force_replace = FALSE)
    {
        if(self::$_instance === null || $force_replace)
        {
            self::$_instance = new self();
            if(class_exists('Memcache') && $type == 'memcache')
            {
                $mem = new Memcache();
                if($mem->addServer($_SERVER['SERVER_NAME'], 11211)) self::$_mem = $mem;
                $mem->setCompressThreshold(20000, 0.2);
            }
        }
        return self::$_instance;
    }
    
    public function __call($outer, array $args)
    {
        if(empty($args[1]))
        {
            $args[1] = array();
            $key = $outer . $args[0];
        }
        else
        {
            $key = $outer . $args[0] . serialize($args[1]);
        }
        if(!isset($args[2])) $args[2] = 0;
        if($args[2] == -1) return $this->delete($key);
        $cache = $this->get($key);
        if(FALSE === $cache)
        {
            $obj = new $outer();
            $data = call_user_func_array(array($obj, $args[0]), $args[1]);
            $this->set($key, $data, $args[2]);
            return $data;
        }
        return $cache;
    }
    
    public function set($key = '', $val = null, $expires = 0)
    {
        if(!empty($expires)) $expires = $_SERVER['REQUEST_TIME'] + $expires;
        if(self::$_mem) return @self::$_mem->set($key, $val, FALSE, $expires);
        
        return file_put_contents($this->_filename($key), '<?php die();?>,'.$expires.','.base64_encode(serialize($val)));
    }
    
    public function get($key = '')
    {
        if(self::$_mem) return @self::$_mem->get($key, FALSE);
        
        $cache = $this->_filename($key);
        if(is_readable($cache))
        {
            $data = explode(',', file_get_contents($cache));
            $expires = (int)$data[1];
            if($expires === 0 || $expires - $_SERVER['REQUEST_TIME'] > 0) return unserialize(base64_decode($data[2]));
            $this->delete($key);
        }
        return FALSE;
    }
    
    public function delete($key = '')
    {
        if(self::$_mem) return self::$_mem->delete($key, 0);
        return @unlink($this->_filename($key));
    }
    
    public function flush()
    {
        if(self::$_mem) return @self::$_mem->flush();
        
        $res = TRUE;
        $dir = $this->_path();
        foreach(glob($dir.DS.'*') as $v) $res = @unlink($v);
        return $res;
    }
    
    private function _path()
    {
        $path = APP_DIR.DS.'protected'.DS.'cache'.DS.'data';
        if(!is_dir($path)) mkdir($path, 0755);
        return $path;
    }
    
    private function _filename($key)
    {
        return $this->_path() . DS . md5($key) . '.php';
    }
}