<?php
class order_log_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $condition = null;
            $kw = request('kw', '');
            if($kw != '') $condition = array('order_id' => $kw);
            
            $log_model = new order_log_model();
            $list = $log_model->find_all($condition, 'id DESC', '*', array(request('page', 1), request('pernum', 15)));
            
            if(!empty($list))
            {   
                $admin_list = vcache::instance()->admin_model('indexed_list');
                $operate_map = $log_model->operate_map;
                foreach($list as &$v)
                {
                    $v['username'] = $admin_list[$v['admin_id']]['username'];
                    $v['dateline'] = date('Y-m-d H:i:s', $v['dateline']);
                    $v['operate'] = $operate_map[$v['operate']];
                }
                
                $results = array
                (
                    'status' => 'success',
                    'list' => $list,
                    'paging' => $log_model->page,
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
            $this->compiler('order/log_list.html');
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $log_model = new order_log_model();
            foreach($id as $v) $affected += $log_model->delete(array('id' => (int)$v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个日志记录, 失败 {$failure} 个", url($this->MOD.'/order_log', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }
}