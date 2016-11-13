<?php
class user_favorite_model extends Model
{
    public $table_name = 'user_favorite';
    
    public function add($user_id, $goods_id)
    {
        $sql = "INSERT INTO {$this->table_name}(user_id, goods_id, created_date) VALUES (:user_id, :goods_id, :created_date) 
                ON DUPLICATE KEY UPDATE created_date = :created_date";
        return $this->execute($sql, array(':user_id' => $user_id, ':goods_id' => $goods_id, 'created_date' => $_SERVER['REQUEST_TIME']));
    }
    
    /**
     * 获取用户商品收藏列表
     */
    public function get_user_favorites($user_id, $limit = null)
    {   
        $total = $this->find_count(array('user_id' => $user_id));
        if($total > 0)
        {
            $limit = $this->set_limit($limit, $total);
            $goods_model = new goods_model();
            $sql = "SELECT a.goods_id, a.created_date, b.goods_name, b.now_price, b.goods_image
                    FROM {$this->table_name} AS a
                    INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods AS b
                    ON a.goods_id = b.goods_id
                    WHERE a.user_id = :user_id
                    ORDER BY a.created_date DESC
                    {$limit}
                   ";
            if($res = $this->query($sql, array(':user_id' => $user_id))) return $res;
        }
        return null;
    }
}