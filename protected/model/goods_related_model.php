<?php
class goods_related_model extends Model
{
    public $table_name = 'goods_related';
    
    public function add_related($goods_id, $related)
    {
        foreach($related as $v)
        {
            $v = explode('-', $v);
            $this->create(array('goods_id' => $goods_id, 'related_id' => $v[0], 'direction' => $v[1]));
            //如果是双向关联再对换id添加一次  
            if($v[1] == 2) $this->create(array('goods_id' => $v[0], 'related_id' => $goods_id, 'direction' => 2));
        }
    }
    
    public function get_related_goods($goods_id)
    {
        $goods_model = new goods_model();
        $sql = "SELECT a.direction, b.goods_id, b.goods_name
                FROM (SELECT * FROM {$this->table_name} WHERE goods_id = :goods_id) AS a
                LEFT JOIN {$goods_model->table_name} AS b
                ON a.related_id = b.goods_id
               ";
               
        return $this->query($sql, array(':goods_id' => $goods_id));
    }

}

