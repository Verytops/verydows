<?php
class goods_attr_model extends Model
{
    public $table_name = 'goods_attr';
    
    /**
     * 获取商品属性及属性可选值
     */
    public function get_goods_attrs($cate_id, $goods_id)
    {
        $sql = "SELECT a.attr_id, a.name, a.opts, a.uom, b.value
                FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_cate_attr AS a
                LEFT JOIN (SELECT * FROM {$this->table_name} WHERE goods_id = :goods_id) AS b
                ON a.attr_id = b.attr_id
                WHERE a.cate_id = :cate_id
                ORDER BY a.seq ASC
               ";   
        if($results = $this->query($sql, array(':cate_id' => $cate_id, ':goods_id' => $goods_id)))
        {
            foreach($results as &$v)
            {
                if(!empty($v['opts'])) $v['opts'] = json_decode($v['opts'], TRUE);
                if($v['value'] === null) $v['value'] = '';
            }
        }
            
        return $results;
    }
    
    /**
     * 获取商品属性规格参数
     */
    public function get_goods_specs($goods_id)
    {
        $cate_attr_model = new goods_cate_attr_model();
        $sql = "SELECT a.value, b.name, b.uom
                FROM {$this->table_name} AS a
                LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_cate_attr AS b
                ON a.attr_id = b.attr_id
                WHERE a.goods_id = :goods_id
                ORDER BY b.seq ASC
               ";
        return $this->query($sql, array(':goods_id' => $goods_id));
    }
}