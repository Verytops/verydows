<?php
class goods_cate_brand_model extends Model
{
    public $table_name = 'goods_cate_brand';
    
    /**
     * 查询分类下品牌
     */
    public function find_cate_brands($cate_id)
    {
        $sql = "SELECT a.cate_id, b.brand_id, brand_name FROM {$this->table_name} AS a
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}brand AS b
                ON a.brand_id = b.brand_id WHERE cate_id = :cate_id ORDER BY seq ASC
               ";
        return $this->query($sql, array(':cate_id' => $cate_id));
    }
}