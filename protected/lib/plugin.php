<?php
class plugin
{
    private static $_instance;
    
    public static function instance($dir, $class, $args = array(), $force_replace = FALSE)
    {
        if(self::$_instance === null || $force_replace)
        {
            $file =  APP_DIR.DS.'plugin'.DS.$dir.DS.$class.'.php';
            if(is_file($file))
            {
                include($file);
                if(!empty($args))
                {
                    $ref = new ReflectionClass($class);
                    self::$_instance = $ref->newInstanceArgs($args);
                    unset($ref);
                }
                else
                {
                    self::$_instance = new $class();
                }
                return self::$_instance;
            }
            err("Err: Plugin Class '{$class}' is not exists!");
            return FALSE;
        }
        
        return self::$_instance;
    }
}