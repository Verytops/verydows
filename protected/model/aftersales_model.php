<?php
class aftersales_model extends Model
{
    public $table_name = 'aftersales';

    public $type_map = array('报修', '换货', '退货');
    
    public $status_map = array('待审核', '进行中', '已完成');
    
    public $rules = array
    (
        'cause' => array
        (
            'min_length' => array(15, '原因描述不能少于15个字符'),
            'max_length' => array(500, '原因描述不能超过500个字符'),
        ),
        'mobile' => array
        (
            'is_required' => array(TRUE, '手机号码不能为空'),
            'is_moblie_no' => array(TRUE, '手机号码无效'),
        )
    );
    
    public $addrules = array
    (
        'type' => array
        (
            'addrule_valid_type' => '请选择一个有效的处理类型',
        ),
    );
    
    //自定义验证器：检查处理类型是否有效
    public function addrule_valid_type($val)
    {
        return isset($this->type_map[$val]);
    }
    
    /**
     * 获取用户售后服务列表
     */
    public function get_user_aftersales($user_id, $limit = null)
    {
        $total = $this->find_count(array('user_id' => $user_id));
        if($total > 0)
        {
            $limit = $this->set_limit($limit, $total);
            $sql = "SELECT a.as_id, a.order_id, a.type, a.goods_qty, a.status, a.created_date,
                           b.id AS opt_map_id, b.goods_id, b.goods_name, b.goods_image
                    FROM {$this->table_name} AS a
                    INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                    ON a.order_id = b.order_id AND a.goods_id = b.goods_id
                    WHERE a.user_id = :user_id
                    ORDER BY a.as_id DESC
                   ";
            if($res = $this->query($sql, array(':user_id' => $user_id)))
            {
                $opts_model = new order_goods_optional_model();
                foreach($res as &$v)
                {
                    $v['status'] = $this->status_map[$v['status']];
                    $v['type'] = $this->type_map[$v['type']];
                    $v['created_date'] = date('Y年m月d日', $v['created_date']);
                    $v['goods_opts'] = $opts_model->find_all(array('map_id' => $v['opt_map_id']));
                }
                return $res;
            }
        }
        return null;
    }
    
    /**
     * 检查是否允许申请售后
     */
    public function check_apply_allowed($user_id, $order_id, $goods_id, $qty = 1)
    {
        $sql = "SELECT a.order_id, b.goods_id
                FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order AS a
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                ON a.order_id = b.order_id
                WHERE a.order_status = 4 AND a.user_id = :user_id AND a.order_id = :order_id AND b.goods_id = :goods_id AND b.goods_qty >= :qty
               ";
        if($this->query($sql, array(':user_id' => $user_id, ':order_id' => $order_id, ':goods_id' => $goods_id, ':qty' => $qty))) return TRUE;     
        return FALSE;
    }

}
