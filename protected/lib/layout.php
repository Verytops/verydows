<?php
class layout
{
    private static $path = array();
    
    private static $_instance = null;
    
    public static function instance()
    {
        if(self::$_instance === null)
        {
            self::$path = array
            (
                'tpl' => VIEW_DIR.DS.'frontend'.DS.$GLOBALS['cfg']['enabled_theme'].DS.'layout',
                'cache' => APP_DIR.DS.'protected'.DS.'cache'.DS.'template',
                'static' => APP_DIR.DS.'protected'.DS.'cache'.DS.'static',
            );
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function tpl_render($tplname, $assigns = array(), $staticize = FALSE)
    {
        $view = new View(self::$path['tpl'], self::$path['cache']);
        if(!empty($assigns)) $view->assign($assigns);
        $contents = $view->render($tplname);
        if($staticize) file_put_contents(self::$path['static'].DS.$tplname, $contents);
        return $contents;
    }

    public function check_static_file($filename)
    {
        if($contents = @file_get_contents(self::$path['static'].DS.$filename)) return $contents;
        return FALSE;
    }

}
