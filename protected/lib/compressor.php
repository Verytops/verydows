<?php
class compressor
{
    private $_cache_dir;
    
    public function __construct()
    {
        $this->_cache_dir = APP_DIR.DS.'public'.DS.'cache';
    }
    
    public function condense($src, $type)
    {
        if($file = @file_get_contents($src))
        {
            switch($ext)
            {
                case 'css': $res = $this->_css($file); break;
                case 'js': $res = $this->_js($js); break;
                default: $res = FALSE;
            }
            return $res
        }
        return FALSE;
    }
    
    private function _css($file)
    {
        if($path = realpath($file))
        {
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', file_get_contents($path));
            $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
            return file_put_contents($this->_cache_dir.DS.md5($file).'.css', $css) !== FALSE;
        }
        return FALSE;
    }
    
    private function _js($file)
    {
        if($path = realpath($file))
        {
            $blocks = array
            (
              'http://' => '#?http?#',
              'https://' => '#?https?#',
            );
            
            $js = file_get_contents($path);
            $js = str_replace(array_keys($blocks), array_values($blocks), $js);
            $js = preg_replace('#\/\*.*\*\/#isU','',$js);
            $js = preg_replace('#\s?(=|>=|\?|:|==|\+|\|\||\+=|>|<|\/|\-|,|\()\s?#', '$1', $js);
            $js = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    "), '', $js);
            $js = str_replace(array_values($blocks), array_keys($blocks), $js);
            
            return file_put_contents($this->cache_dir.DS.md5($file).'.js', $js) !== FALSE;
        }
        return FALSE;
    }
    
}