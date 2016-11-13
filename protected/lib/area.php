<?php
class area
{
    private $area_map;
    
    public function __construct()
    {
        $this->area_map = include(INCL_DIR.DS.'area_map.php');
    }
    
    public function get_all(){return $this->area_map;}
    
    public function get_children($province = 0, $city = 0)
    {
        $map = $this->area_map;
        $children = array();
        if(!empty($province))
        {
            foreach($map[$province]['children'] as $k => $v) $children[$k] = $v['name'];
            unset($k, $v);
            if(!empty($city))
            {
                $children = array();
                foreach($map[$province]['children'][$city]['children'] as $k => $v) $children[$k] = $v;
            }
        }
        else
        {
            foreach($map as $k => $v) $children[$k] = $v['name'];
        }
        return $children;
    }
    
    public function get_area_name($province = 0, $city = 0, $borough = 0)
    {
        $map = $this->area_map;
        return array
        (
            'province' => isset($map[$province]) ? $map[$province]['name'] : null,
            'city' => isset($map[$province]['children'][$city]) ? $map[$province]['children'][$city]['name'] : null,
            'borough' => isset($map[$province]['children'][$city]['children'][$borough]) ? $map[$province]['children'][$city]['children'][$borough] : null,
        );
    }
    
    public function __destruct()
    {
        $this->area_map = null;
    }
}
