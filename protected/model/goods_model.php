<?php
class goods_model extends Model
{
    public $table_name = 'goods';
    
    public $rules = array
    (
        'goods_name' => array
        (
            'is_required' => array(TRUE, '商品名称不能为空'),
            'max_length' => array(180, '商品名称不能超过180个字符'),
        ),
        'goods_sn' => array
        (
            'max_length' => array(20, '商品货号不能超过20个字符'),
        ),
        'now_price' => array
        (
            'is_required' => array(TRUE, '当前售价不能为空'),
            'is_decimal' => array(TRUE, '当前售价格式不正确'),
        ),
        'original_price' => array
        (
            'is_decimal' => array(TRUE, '原售价格式不正确'),
        ),
        'stock_qty' => array
        (
            'is_nonegint' => array(TRUE, '库存数量必须是非负整数'),
        ),
        'goods_weight' => array
        (
            'is_decimal' => array(TRUE, '重量格式不正确'),
        ),
    );
    
    /**
     * 按条件查找商品
     */
    public function find_goods($conditions = array(), $limit = null)
    {
        $where = 'WHERE status = 1';
        $binds = array();
        if(!empty($conditions['cate']))
        {
            $where .= " AND (cate_id = :cate OR cate_id IN (SELECT cate_id FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_cate WHERE parent_id = :cate))";
            $binds[':cate'] = $conditions['cate'];
        }
        if(!empty($conditions['brand']))
        {
            $where .= ' AND brand_id = :brand';
            $binds[':brand'] = $conditions['brand'];
        }
        if(!empty($conditions['newarrival']))
        {
            $where .= ' AND newarrival = 1';
        }
        if(!empty($conditions['recommend']))
        {
            $where .= ' AND recommend = 1';
        }
        if(!empty($conditions['bargain']))
        {
            $where .= ' AND bargain = 1';
        }
        if(!empty($conditions['minpri']))
        {
            $where .= ' AND now_price >= :minpri';
            $binds[':minpri'] = $conditions['minpri'];
        }
        if(!empty($conditions['maxpri']))
        {
            $where .= ' AND now_price <= :maxpri';
            $binds[':maxpri'] = $conditions['maxpri'];
        }
        if(isset($conditions['kw']) && $conditions['kw'] != '')
        {
            $conditions['kw'] = sql_escape($conditions['kw']);
            if($GLOBALS['cfg']['goods_fulltext_query'] == 1)
            {
                $where .= ' AND MATCH (goods_name,meta_keywords) AGAINST (:kw IN BOOLEAN MODE)';
                $binds[':kw'] = $conditions['kw'];
            }
            else
            {
                $where .= ' AND (goods_name LIKE :inskw OR LOCATE(:kw, meta_keywords))';
                $binds[':inskw'] = '%'.$conditions['kw'].'%';
                $binds[':kw'] = $conditions['kw'];
            }
            
        }
        if(!empty($conditions['att']))
        {
            $att = explode('@', urldecode($conditions['att']));
            $newatt = array();
            foreach($att as $v) if(!empty($v)) $newatt[substr($v, 0, strpos($v, '_'))] = substr($v, strpos($v, '_') + 1);
            $goods_attr_model = new goods_attr_model();
            $relax_atids = array();
            foreach($newatt as $k => $v)
            {
                if($gatids = $goods_attr_model->find_all(array('attr_id' => $k, 'value' => $v), null, 'goods_id')) 
                {
                    foreach($gatids as $v) $relax_atids[$k][] = $v['goods_id'];
                }
                else
                {
                    $relax_atids[$k] = array();
                }
            }
            sort($relax_atids);
            $strict_atids = mult_array_intersect($relax_atids);
            $strict_atids = $strict_atids === FALSE ? $relax_atids[0] : $strict_atids;
            $attr_ids = !empty($strict_atids) ? implode(',', $strict_atids) : 0;
            $where .= " AND goods_id IN ({$attr_ids})";
        }
        
        $total = $this->query("SELECT COUNT(*) as count FROM {$this->table_name} {$where}", $binds);
        if($total[0]['count'] > 0)
        {
            $limit = $this->set_limit($limit, $total[0]['count']);
            if(isset($conditions['sort']))
            {
                $sort_map = array('goods_id DESC', 'now_price ASC', 'now_price DESC', 'created_date DESC', 'created_date ASC');
                $sort = isset($sort_map[$conditions['sort']]) ? $sort_map[$conditions['sort']] : $sort_map[0];
            }
            else
            {
                $sort = 'goods_id DESC';
            }
            $fields = 'goods_id, cate_id, brand_id, goods_name, original_price, goods_brief, now_price, goods_image';
            $sql = "SELECT {$fields} FROM {$this->table_name} {$where} ORDER BY {$sort} {$limit}";
            return $this->query($sql, $binds);
        }
        
        return null;
    }
    
    public function set_search_filters($conditions)
    {
        $filters = $binds = array();
        $where = 'WHERE status = 1';

        if($conditions['kw'])
        {
            $conditions['kw'] = sql_escape($conditions['kw']);
            if($GLOBALS['cfg']['goods_fulltext_query'] == 1)
            {
                $where .= ' AND MATCH (goods_name,meta_keywords) AGAINST (:kw IN BOOLEAN MODE)';
                $binds[':kw'] = $conditions['kw'];
            }
            else
            {
                $where .= ' AND (goods_name LIKE :inskw OR LOCATE(:kw, meta_keywords))';
                $binds[':inskw'] = '%'.$conditions['kw'].'%';
                $binds[':kw'] = $conditions['kw'];
            }
        }
        
        $sql = "SELECT cate_id, cate_name FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_cate
                WHERE cate_id in (SELECT cate_id FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods {$where} GROUP BY cate_id)
                ORDER BY seq ASC
               ";
        $filters['cate'] = $this->query($sql, $binds);
        
        $sql = "SELECT brand_id, brand_name FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}brand
                WHERE brand_id in (SELECT brand_id FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods {$where} GROUP BY brand_id)
                ORDER BY seq ASC
               ";
        $filters['brand'] = $this->query($sql, $binds);
        
        if($conditions['cate'])
        {
            $filters['attr'] = vcache::instance()->goods_cate_attr_model('find_all', array(array('cate_id' => $conditions['cate'], 'filtrate' => 1), 'seq ASC'));
            if($filters['attr'])
            {
                $attarr = !empty($conditions['att']) ? explode('@', urldecode($conditions['att'])) : array();
                $newatt = array();
                foreach($attarr as $u)
                {
                    if(!empty($u)) $newatt[substr($u, 0, strpos($u, '_'))] = $u;          
                }
                $newattstr = !empty($newatt) ? implode('@', $newatt) : '';

                foreach($filters['attr'] as &$v)
                {
                    if(!empty($v['opts']))
                    {
                        $opts = json_decode($v['opts'], TRUE);
                        $v['opts'] = array();
                        foreach($opts as $k => $o)
                        {
                            $v['opts'][$k]['name'] = $o . $v['uom'];
                            $v['opts'][$k]['att'] = urlencode($newattstr.'@'.$v['attr_id'].'_'.$o);
                            $v['opts'][$k]['checked'] = 0;
                            if(in_array($v['attr_id'].'_'.$o, $newatt)) $v['opts'][$k]['checked'] = 1;
                        }
                        $v['unlimit']['att'] = urlencode(implode('@', array_diff_key($newatt, array($v['attr_id'] => ''))));
                        $v['unlimit']['checked'] = isset($newatt[$v['attr_id']]) ? 0 : 1;
                    }
                    else
                    {
                        $v['opts'] = array();
                    }
                }
            }
        }

        //价格筛选
        $filters['price'] = array();
        $sql = "SELECT count(goods_id) AS count, min(now_price) AS min, max(now_price) AS max
                FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods {$where}
               ";
        if($pri_query = $this->query($sql, $binds))
        {
            $pri_max_num = round($pri_query[0]['count'] / 10);
            if($pri_max_num >= 2)
            {
                if($pri_max_num >= 6) $pri_max_num = 6;
                $pri_incr = ceil(($pri_query[0]['max'] - $pri_query[0]['min']) / $pri_max_num);
                for ($i = 1; $i <= $pri_max_num; $i++)
                {
                    $l = $pri_incr * ($i - 1) + 1;
                    $r = $pri_incr * $i;
                    
                    if($i == 1) $min = 0; else $min = intval(str_pad(substr($l, 0, 2), strlen($l), 9, STR_PAD_RIGHT));
                    if($i == $pri_max_num)
                    {
                        $max = 0;
                        $str = $min.'以上';
                    }
                    else
                    {
                        $max = intval(str_pad(substr($r, 0, 2), strlen($r), 9, STR_PAD_RIGHT));
                        $str = $min.'-'.$max;
                    }
                    $filters['price'][] = array('min' => $min, 'max' => $max, 'str' => $str);
                }
            }
        }

        return $filters;
    }
    
    /**
     * 获取猜你喜欢的商品
     */
    public function get_guess_like($cookie = null)
    {
        if($cookie)
        {
            $ids = array();
            $history = array_slice(explode(',', $cookie), 0, 5);
            foreach($history as $k => $v) $ids[$k + 1] = (int)$v;
            $questionmarks = str_repeat('?,', count($ids) - 1) . '?';
            $related_model = new goods_related_model();
            $sql = "SELECT goods_id, goods_name, original_price, now_price, goods_image
                    FROM {$this->table_name}
                    WHERE status = 1 AND goods_id in (SELECT goods_id FROM {$related_model->table_name} WHERE related_id in ({$questionmarks}))
                    ORDER BY goods_id DESC
                   ";
            return $this->query($sql, $ids);
        }
        
        return null;
    }
    
    /**
     * 保存商品浏览历史
     */
    public function set_history($goods_id, $num = 20)
    {
        if($history = request('FOOTPRINT', null, 'cookie'))
        {
            $history = explode(',', $history);
            if(!in_array($goods_id, $history))
            {
                array_unshift($history, $goods_id);
                setcookie('FOOTPRINT', implode(',', array_slice($history, 0, $num)), $_SERVER['REQUEST_TIME'] + 604800, '/');
            }
        }
        else
        {
            setcookie('FOOTPRINT', $goods_id, $_SERVER['REQUEST_TIME'] + 604800, '/');
        }
    }
    
    /**
     * 获取商品浏览历史
     */
    public function get_history($limit = 10)
    {
        if($cookie = request('FOOTPRINT', null, 'cookie'))
        {
            $ids = array();
            $history = array_slice(explode(',', $cookie), 0, $GLOBALS['cfg']['goods_history_num']);
            foreach($history as $k => $v) $ids[$k + 1] = (int)$v;
            $marks = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "SELECT goods_id, goods_name, original_price, now_price, goods_image
                    FROM {$this->table_name}
                    WHERE goods_id in ({$marks})
                   ";
            if(!empty($limit)) $sql .= " LIMIT {$limit}";
            return $this->query($sql, $ids);
        }
        return null;
    }

    /**
     * 获取购物车中商品数据
     */
    public function get_cart_items(array $items)
    {
        $ids = array();
        $i = 0;
        foreach($items as $v)
        {
            if(!in_array($v['id'], $ids)){$i += 1; $ids[$i] = (int)$v['id'];}
        }
        unset($i);
        
        if(empty($ids)) return FALSE;
        
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT goods_id, goods_name, now_price, goods_image, goods_weight, stock_qty
                FROM {$this->table_name} WHERE status = 1 AND goods_id in ({$marks})";
        
        if($goods_map = $this->query($sql, $ids))
        {
            unset($ids, $marks);
            $goods_map = array_column($goods_map, null, 'goods_id');
            $res['items'] = array();
            $res['amount'] = $res['qty'] = $res['weight'] = 0;
            foreach($items as $k => $v)
            {
                if(!isset($goods_map[$v['id']])) continue;
                $res['items'][$k] = $goods_map[$v['id']];
                $res['items'][$k]['opts'] = null;
                $res['items'][$k]['now_price'] = $goods_map[$v['id']]['now_price'];
                if(!empty($v['opts']))
                {
                    $ids = array();
                    foreach($v['opts'] as $i => $opt_id) $ids[$i + 1] = (int)$opt_id;
                    $marks = str_repeat('?,', count($ids) - 1) . '?';
                    $res['items'][$k]['opts'] = $this->query(
                        "SELECT a.id, a.opt_text, a.opt_price, b.name as type
                         FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_optional AS a
                         INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_optional_type AS b
                         ON a.type_id = b.type_id
                         WHERE a.goods_id = ".(int)$v['id']." AND a.id in ({$marks})", $ids
                    );
                    foreach($res['items'][$k]['opts'] as $prices) $res['items'][$k]['now_price'] += $prices['opt_price'];
                }
                $res['items'][$k]['now_price'] = sprintf('%1.2f', $res['items'][$k]['now_price']);
                $res['items'][$k]['qty'] = (int)$v['qty'] ? $v['qty'] : 1;
                $res['items'][$k]['subtotal'] = sprintf('%1.2f', $res['items'][$k]['now_price'] * $res['items'][$k]['qty']);
                $res['items'][$k]['json'] = json_encode($v);
                $res['amount'] += $res['items'][$k]['subtotal'];
                $res['qty'] += $res['items'][$k]['qty'];
                $res['weight'] += floatval($res['items'][$k]['goods_weight'] * $res['items'][$k]['qty']);
            }
            $res['amount'] = sprintf('%1.2f', $res['amount']);
            $res['kinds'] = count($res['items']);
            return $res;
        }
        return FALSE;
    }
    
    /**
     * 获取相关联商品
     */
    public function get_related($goods_id, $limit = 5)
    {
        $sql = "SELECT goods_id, goods_name, original_price, now_price, goods_image
                FROM {$this->table_name}
                WHERE goods_id in (SELECT goods_id FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_related WHERE related_id = :goods_id)
                ORDER BY goods_id DESC LIMIT {$limit}
               "; 
        return $this->query($sql, array(':goods_id' => $goods_id));
    }
    
    /**
     * 商品销售排行
     */
    public function get_bestseller($cate_id = null, $limit = 10)
    {
        $where = "WHERE 1";
        if(!empty($cate_id)) $where .= " AND b.cate_id = {$cate_id}";
        $sql = "SELECT a.goods_id, COUNT(1) AS count, b.goods_name, b.now_price, b.goods_image
                FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS a 
                LEFT JOIN {$this->table_name} AS b
                ON a.goods_id = b.goods_id
                {$where}
                GROUP BY a.goods_id
                ORDER BY COUNT(1) DESC
                LIMIT {$limit}
               ";
        return $this->query($sql);
        
    }
}
