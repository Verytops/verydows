<?php
class user_account_log_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $where = ' WHERE 1';
            $binds = array();
            $username = trim(request('username', ''));
            $user_id = (int)request('user_id', 0);
            if($username != '')
            {
                $where .=  " AND a.user_id = :user_id";
                $user_model = new user_model();
                if($user = $user_model->find(array('username' => $username), null, 'user_id'))
                {
                    $binds[':user_id'] = $user['user_id'];
                }
                else
                {
                    $binds[':user_id'] = 0;
                }
            }elseif(!empty($user_id))
            {
                $where .=  " AND a.user_id = :user_id";
                $binds[':user_id'] = $user_id;
            }
            
            $log_model = new user_account_log_model();
            $total = $log_model->query("SELECT COUNT(*) as count FROM {$log_model->table_name} AS a {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $limit = $log_model->set_limit(array(request('page', 1), request('pernum', 15)), $total[0]['count']);
                $sql = "SELECT a.*, b.username AS user, c.username AS admin
                        FROM {$log_model->table_name} AS a
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS b
                        ON a.user_id = b.user_id
                        LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}admin AS c
                        ON a.admin_id = c.user_id
                        {$where} ORDER BY a.id DESC {$limit}
                       ";
                
                $results = array
                (
                    'status' => 'success',
                    'list' => $log_model->query($sql, $binds),
                    'paging' => $log_model->page,
                );
            }
            else
            {
                $results = array('status' => 'nodata');
            }
            echo json_encode($results);
        }
        else
        {
            $this->compiler('user/account_log_list.html');
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $log_model = new user_account_log_model();
            foreach($id as $v)
            {
                $affected += $log_model->delete(array('id' => (int)$v));
            }
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 条日志记录, 失败 {$failure} 条", url($this->MOD.'/user_account_log', 'index'));
        }
        else
        {
            $this->prompt('error', '无法获取参数');
        }
    }
    
}