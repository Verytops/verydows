<?php
class order_shipping_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $condition = array();
            $kw = request('kw', '');
            if($kw != '')
            {
                if(request('type', 0) == 1)
                {
                    $condition = array('order_id' => $kw);
                }
                else
                {
                    $condition = array('tracking_no' => $kw);
                }
            }
            
            $shipping_model = new order_shipping_model();
            $list = $shipping_model->find_all($condition, 'id DESC', '*', array(request('page', 1), request('pernum', 15)));
            
            if(!empty($list))
            {
                $carrier_map = vcache::instance()->shipping_carrier_model('indexed_list');
                foreach($list as &$v)
                {
                    if(isset($carrier_map[$v['carrier_id']]))
                    {
                        $v['carrier_name'] = $carrier_map[$v['carrier_id']]['name'];
                        $v['tracking_url'] = $carrier_map[$v['carrier_id']]['tracking_url'] . $v['tracking_no'];
                    }
                    else
                    {
                        $v['carrier_name'] = 'Unknown';
                        $v['tracking_url'] = '';
                    }
                    
                    $v['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
                }
                
                $results = array
                (
                    'status' => 'success',
                    'list' => $list,
                    'paging' => $shipping_model->page,
                );
            }
            else
            {
                $results = array('status' => 'error');
            }
            
            echo json_encode($results);
        }
        else
        {
            
            $this->compiler('order/shipping_list.html');
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $shipping_model = new order_shipping_model();
            foreach($id as $v) $affected += $shipping_model->delete(array('id' => $v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个发货记录, 失败 {$failure} 个", url($this->MOD.'/order_shipping', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }

}