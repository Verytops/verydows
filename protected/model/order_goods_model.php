<?php
class order_goods_model extends Model
{
    public $table_name = 'order_goods';
     
    /**
     * 添加订单商品记录
     */
    public function add_records($order_id, $goods_list)
    {
        $goods_model = new goods_model();
        $opts_model = new order_goods_optional_model();
        foreach($goods_list as $v)
        {
            $data = array
            (
                'order_id' => $order_id,
                'goods_id' => $v['goods_id'],
                'goods_name' => $v['goods_name'],
                'goods_image' => $v['goods_image'],
                'goods_price' => $v['now_price'],
                'goods_qty' => $v['qty'],
            );
            if($id = $this->create($data))
            {
                if(!empty($v['opts']))
                {
                    foreach($v['opts'] as $o) $opts_model->create(array('map_id' => $id, 'opt_id' => $o['id'], 'opt_type' => $o['type'], 'opt_text' => $o['opt_text'])); 
                }
                //同时减除商品库存
                $goods_model->decr(array('goods_id' => $v['goods_id']), 'stock_qty', $v['qty']);
            }
        }
    }
    
    /**
     * 重置订单中商品库存
     */
    public function restocking($order_id, $method = 'incr')
    {
        if($arr = $this->find_all(array('order_id' => $order_id), null, 'goods_id, goods_qty'))
        {
            $goods_model = new goods_model();
            foreach($arr as $v) $goods_model->$method(array('goods_id' => $v['goods_id']), 'stock_qty', $v['goods_qty']);
        }
    }
    
    /**
     * 获取订单商品列表
     */
    public function get_goods_list($order_id)
    {
        if($list = $this->find_all(array('order_id' => $order_id)))
        {
            $opts_model = new order_goods_optional_model();
            foreach($list as &$v)
            {
                $v['goods_opts'] = $opts_model->find_all(array('map_id' => $v['id']));
            }
        }
        return $list;
    }
    
    /**
     * 检查该购买商品是否允许评价
     */
    public function allowed_review($user_id, $order_id, $goods_id)
    {
        $sql = "SELECT a.*, b.user_id FROM {$this->table_name} AS a
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order AS b
                ON a.order_id = b.order_id
                WHERE a.order_id = :order_id AND a.goods_id = :goods_id AND a.is_reviewed = 0 AND b.user_id = :user_id
                LIMIT 1
               ";
        return $this->query($sql, array(':user_id' => $user_id, ':order_id' => $order_id, ':goods_id' => $goods_id)) ? TRUE : FALSE;
    }
}