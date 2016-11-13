<?php
class goods_review_model extends Model
{
    public $table_name = 'goods_review';
    
    public $rules = array
    (
        'content' => array
        (
            'is_required' => array(TRUE, '评价内容不能为空'),
            'min_length' => array(15, '评价内容不能少于15个字符'),
            'max_length' => array(500, '评价内容不能超过500个字符'),
        ),
    );
    
    public $addrules = array
    (
        'rating' => array
        (
            'addrule_rating_format' => '请选择1-5分之间的评分',
        ),
    );
    
    //自定义验证器：检查评分等级(只能是1-5的数字)
    public function addrule_rating_format($val)
    {
        return preg_match('/[1-5]/', $val) != 0;
    }
    
    public $rating_map = array
    (
        1 => '很不满意',
        2 => '不满意',
        3 => '一般',
        4 => '满意',
        5 => '非常满意',
    );
    
    /**
     * 按条件获取商品评价
     */
    public function get_goods_reviews($goods_id, $rating_type = 0, $limit = null)
    {
        $where = 'WHERE a.goods_id = :goods_id';
        $binds = array(':goods_id' => $goods_id);

        $rating_type_map = array(1 => '>= 4', 2 => '= 3', 3 => '<= 2');
        if(isset($rating_type_map[$rating_type]))
        {
            $where .= " AND a.rating {$rating_type_map[$rating_type]}";
        }
        
        if($GLOBALS['cfg']['user_review_approve'] == 1) $where .= ' AND a.status = 1';

        $total = $this->query("SELECT COUNT(*) AS count FROM {$this->table_name} AS a {$where}", $binds);
        if($total[0]['count'] > 0)
        {
            $limit = $this->set_limit($limit, $total[0]['count']);

            $sql = "SELECT a.review_id, a.rating, a.content, a.created_date, a.replied, b.user_id, b.username, b.avatar
                    FROM {$this->table_name} AS a
                    INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS b
                    ON a.user_id = b.user_id
                    {$where}
                    ORDER BY a.review_id DESC
                    {$limit}
                   ";
                   
            $res = $this->query($sql, $binds);
            foreach($res as &$v)
            {
                $v['username'] = substr($v['username'], 0, 2) . '***' . substr($v['username'], -2);
                $v['satis'] = $this->rating_map[$v['rating']];
                $v['created_date'] = date('Y-m-d H:i:s', $v['created_date']);
                if(!empty($v['replied']))
                {
                    $replied = json_decode($v['replied'], TRUE);
                    $replied['dateline'] = date('Y-m-d H:i:s', $replied['dateline']);
                    $v['replied'] = $replied;
                }
            }
            return $res;
        }
        
        return null;
    }
    
    /**
     * 获取商品评分统计
     */
    public function get_rating_stats($goods_id)
    {
        $sql = "SELECT COUNT(*) AS count, rating
                FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_review
                WHERE goods_id = :goods_id
                GROUP BY rating
               ";
        
        $good = $medi = $poor = array('qty' => 0, 'ratio' => 0);
        $res = array('total' => 0, 'avg' => 0, 'satis' => 0, 'good' => $good, 'medi' => $medi, 'poor' => $poor);
        unset($good);
        
        if($arr = $this->query($sql, array(':goods_id' => $goods_id)))
        {
            $numerator = 0;
            foreach($arr as $v)
            {
                $res['total'] += $v['count'];
                $numerator += $v['count'] * $v['rating'];
                if($v['rating'] >= 4) $res['good']['qty'] += $v['count'];
                elseif($v['rating'] == 3) $res['medi']['qty'] += $v['count'];
                else $res['poor']['qty'] += $v['count'];
            }
            $satis = ($res['good']['qty'] + $res['medi']['qty']) / $res['total'];
            $res['satis'] = round($satis * 100, 1);
            $res['avg'] = round($numerator / $res['total'], 1);
            $res['good']['ratio'] = round(($res['good']['qty'] / $res['total']) * 100, 1);
            $res['medi']['ratio'] = round(($res['medi']['qty'] / $res['total']) * 100, 1);
            $res['poor']['ratio'] = round(($res['poor']['qty'] / $res['total']) * 100, 1);
        }
        
        return $res;
    }
    
    /**
     * 获取用户评价
     */
    public function get_user_reviews($user_id, $limit = null)
    {
        $total = $this->find_count(array('user_id' => $user_id));
        if($total > 0)
        {
            $limit = $this->set_limit($limit, $total);
            $sql = "SELECT a.review_id, a.order_id, a.rating, a.content, a.created_date, b.goods_id, b.goods_name, b.goods_image
                    FROM {$this->table_name} AS a
                    INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                    ON a.order_id = b.order_id AND a.goods_id = b.goods_id
                    WHERE a.user_id = :user_id
                    ORDER BY a.created_date DESC
                    {$limit}
                   ";
            if($list = $this->query($sql, array(':user_id' => $user_id)))
            {
                foreach($list as &$v)
                {
                    $v['satis'] = $this->rating_map[$v['rating']];
                    $v['created_date'] = date('Y-m-d H:i:s', $v['created_date']);
                    if(!empty($v['replied']))
                    {
                        $replied = json_decode($v['replied'], TRUE);
                        $replied['dateline'] = date('Y-m-d H:i:s', $replied['dateline']);
                        $v['replied'] = $replied;
                    }
                }
                return $list;
            }
        }
        return null;
    }
    
}