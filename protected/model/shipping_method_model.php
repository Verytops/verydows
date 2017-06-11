<?php
class shipping_method_model extends Model
{
    public $table_name = 'shipping_method';
    
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '名称不能为空'),
            'max_length' => array(60, '名称不能超过60个字符'),
        ),
        'instruction' => array
        (
            'max_length' => array(240, '说明不能超过240个字符'),
        ),
        'seq' => array
        (
            'is_seq' => array(TRUE, '排序必须为0-99之间的整数'),
        ),
    );
    
    public $addrules = array
    (
        'params' => array
        (
            'addrule_params_required' => '配送范围不能为空',
        ),
    );
    
    //自定义验证器：检查配送范围参数是否为空
    public function addrule_params_required($val)
    {
        return count(json_decode($val, TRUE)) > 0;
    }
    
    /**
     * 启用的配送方式列表(以id作为数据列表索引)
     */
    public function indexed_list()
    {
        if($find_all = $this->find_all(array('enable' => 1), 'seq ASC'))
        {
            $res = array();
            foreach($find_all as $v)
            {
                if($v['params']) $v['params'] = json_decode($v['params'], TRUE);
                $res[$v['id']] = $v;
            }
            return $res;
        }
        return null;
    }
    
    /**
     * 计算运费
     */
    public function check_freight($user_id, $shipping_id, $area, $cart)
    {
        if(!$method = $this->find(array('id' => $shipping_id))) return FALSE;
        $shipping = null;
        $method['params'] = json_decode($method['params'], TRUE);
        foreach($method['params'] as $m)
        {
            if($m['area'] == 0 || in_array($area, $m['area']))
            {
                $shipping = $m;
                break;
            }
        }
        if(!$shipping) return FALSE;
        
        switch($shipping['type'])
        {
            case 'fixed':
            
                $amount = $shipping['charges'];
            
            break;
            
            case 'weight':
            
                if($cart['weight'] > $shipping['first_weight'])
                {
                    $amount = $shipping['first_charges'] + ceil(($cart['weight'] - $shipping['first_weight']) / $shipping['added_weight']) * $shipping['added_charges'];
                }
                else
                {
                    $amount = $shipping['first_charges'];
                }
                
            break;
            
            case 'piece':
            
                if($cart['qty'] > $shipping['first_piece'])
                {
                    $amount = $shipping['first_charges'] + ceil(($cart['qty'] - $shipping['first_piece']) / $shipping['added_piece']) * $shipping['added_charges'];
                }  
                else
                {
                    $amount = $shipping['first_charges'];
                }
            
            break;
            
            default: $amount = 0;
        }

        return $amount;
    }
}
