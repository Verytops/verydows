<?php
class goods_cate_model extends Model
{
    public $table_name = 'goods_cate';
    
    public $rules = array
    (
        'cate_name' => array
        (
            'is_required' => array(TRUE, '分类名称不能为空'),
            'max_length' => array(60, '分类名称不能超过60个字符'),    
        ),
        'seq' => array
        (
            'is_required' => array(TRUE, '排序不能为空'),
            'is_seq' => array(TRUE, '排序必须为0-99之间的整数'),
        ),
    );
    
    /**
     * 获取分类树(以主键作为分类树数组索引)
     */
    public function indexed_cate_tree()
    {
        if($find_all = $this->find_all(null, 'seq ASC', 'cate_id, parent_id, cate_name, seq'))
        {
            $tree_until = new tree_util($find_all);
            return array_column($tree_until->tree, null, 'cate_id');
        }
        return $find_all;
    }
    
    /**
     * 获取商品分类栏中的1、2级分类(只找顶级和其子分类)
     */
    public function goods_cate_bar()
    {
        $field = 'cate_id, cate_name';
        if($cates = $this->find_all(array('parent_id' => 0), 'seq ASC', $field))
        {
            foreach($cates as $k => $v) $cates[$k]['children'] = $this->find_all(array('parent_id' => $v['cate_id']), 'seq ASC', $field);
        }
        return $cates;
    }
    
    /**
     * 分类面包屑
     */
    public function breadcrumbs($cate_id)
    {
        $results = array();
        $cate = $this->find(array('cate_id' => $cate_id), null, 'cate_id, parent_id, cate_name');
        while(TRUE)
        {
            if(!empty($cate))
            {
                array_unshift($results, $cate);
                $cate = $this->find(array('cate_id' => $cate['parent_id']), null, 'cate_id, parent_id, cate_name');
            }
            else
            {
                break;
            }
        }
        return $results;
    }
    
    /**
     * 设置分类的筛选项
     */
    public function set_filters($cate_id, $att = '')
    {
        $filters = array();
        
        //品牌筛选
        $filters['brand'] = vcache::instance()->goods_cate_brand_model('find_cate_brands', array($cate_id));
        
        //属性筛选
        if($filters['attr'] = vcache::instance()->goods_cate_attr_model('find_all', array(array('cate_id' => $cate_id, 'filtrate' => 1), 'seq ASC')))
        {
            $attarr = !empty($att) ? explode('@', urldecode($att)) : array();
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
                    //array_unshift($v['opts'], $unlimit);
                }
                else
                {
                    $v['opts'] = array();
                }
            }
        }
        //价格筛选
        $filters['price'] = $this->auto_price_zone($cate_id);
        
        return $filters;
    }
    
    /**
     * 自动智能价格分区
     */
    private function auto_price_zone($cate_id, $zone_qty = 3)
    {
        $res = array();
        $sql = "SELECT now_price FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods
                WHERE cate_id = :cate OR cate_id in (SELECT cate_id FROM {$this->table_name} WHERE parent_id = :cate)
               ";
        if($goods = $this->query($sql, array(':cate' => $cate_id)))
        {
            foreach($goods as $v) $prices[] = ceil($v['now_price']);
            $prices = array_unique($prices);
            sort($prices);
            $count = count($prices);
            $per = floor($count / $zone_qty);
            
            if($per > 1)
            {
                for ($i = 1; $i <= $zone_qty; $i++)
                {
                    $lk = $per * ($i - 1);
                    $hk = $per * $i;
                    
                    if($i == 1)
                    {
                        $min = 0;
                        $max = str_pad(substr($prices[$hk], 0, 2), strlen($prices[$hk]), 9, STR_PAD_RIGHT);
                        $str = '0-'.$max;
                    }
                    elseif($i == $zone_qty)
                    {
                        $min = intval(str_pad(substr($prices[$lk], 0, 2), strlen($prices[$lk]), 9, STR_PAD_RIGHT)) + 1;
                        $max = 0;
                        $str = $min.'以上';
                    }
                    else
                    {
                        $min = intval(str_pad(substr($prices[$lk], 0, 2), strlen($prices[$lk]), 9, STR_PAD_RIGHT)) + 1;
                        $max = str_pad(substr($prices[$hk], 0, 2), strlen($prices[$hk]), 9, STR_PAD_RIGHT);
                        $str = $min.'-'.$max;
                    }
                    $res[] = array('min' => $min, 'max' => $max, 'str' => $str);
                }
            }
        }

        return $res;
    }
}
