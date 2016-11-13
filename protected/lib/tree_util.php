<?php
/**
 * 无限分级
 * @author  Luokery
 */
class tree_util
{
    private $pk_field;
    private $parent_field;
    public $tree = array();
    
    public function __construct($data, $pk_field = 'cate_id', $parent_field = 'parent_id')
    {	
        $this->pk_field = $pk_field;
        $this->parent_field = $parent_field;
        $this->set_tree($data);
    }

    public function set_tree($data)
    {
        while(!empty($data))
        {
            $item = current($data);
            $tr = $this->set_node($item[$this->parent_field], $item[$this->pk_field], $item);
            if($tr == TRUE) unset($data[key($data)]);
            else current($data) === FALSE ? reset($data) : next($data);
            if(current($data) === FALSE) reset($data);
        } 
    }

    private function set_node($pid, $id, $data)
    {
        $parent = $this->get_parent($pid); //查询是否有父节点
        
        if($pid == 0)
        {
            if(count($this->tree) == 0) //是父节点, 合并数组
                $this->tree[] = $data + array('lv' => 0);  
            else
                $this->tree = array_merge($this->tree, array($data + array('lv' => 0)));
                
            return TRUE;
        }
        elseif($parent['key'] >= 0)
        {
            array_splice($this->tree, $parent['key'] + 1, 0, array($data + array('lv' => $parent['plv'] + 1)));
            return TRUE;
        }

        return FALSE;
    }

    private function get_parent($id)
    {
        $num = -1;
        $plv = 0;
        
        foreach(($this->tree) as $k => $v)
        {
            if($v[$this->pk_field] == $id)
            {
                $num = $k;
                $plv = $v['lv'];
                break;
            }
            next($this->tree);
        }

        return array('key'=>$num, 'plv' =>$plv);
    }
}
?>