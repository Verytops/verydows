<?php
class feedback_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $where = ' WHERE 1';
            $binds = array();
            
            $type = request('type', '');
            if($type != '')
            {
                $where .= ' AND a.type = :type';
                $binds[':type'] = (int)$type;
            }
            $status = request('status', '');
            if($status != '')
            {
                $where .= ' AND a.status = :status';
                $binds[':status'] = (int)$status;
            }
            
            $results = array('status' => 'nodata');
            
            $feedback_model = new feedback_model();
            $total = $feedback_model->query("SELECT COUNT(*) as count FROM {$feedback_model->table_name} AS a {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $limit = $feedback_model->set_limit(array(request('page', 1), request('pernum', 15)), $total[0]['count']);
                $sql = "SELECT a.fb_id, a.type, a.subject, a.created_date, a.status,
                               b.user_id, b.username
                        FROM {$feedback_model->table_name} AS a
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS b
                        ON a.user_id = b.user_id
                        {$where} ORDER BY fb_id DESC {$limit}
                       ";
                if($list = $feedback_model->query($sql, $binds))
                {
                    $type_map = $feedback_model->type_map;
                    $status_map = $feedback_model->status_map;
                    foreach($list as &$v)
                    {
                        $v['type'] = $type_map[$v['type']];
                        $v['status'] = $status_map[$v['status']];
                        $v['created_date'] = date('Y-m-d', $v['created_date']).'<br />'.date('H:i:s', $v['created_date']);
                    }
                    $results = array
                    (
                        'status' => 'success',
                        'list' => $list,
                        'paging' => $feedback_model->page,
                    );
                }
            }
            echo json_encode($results);
        }
        else
        {
            $feedback_model = new feedback_model();
            $this->type_map = $feedback_model->type_map;
            $this->status_map = $feedback_model->status_map;
            $this->compiler('operation/feedback_list.html');
        }
    }
    
    public function action_view()
    {
        $id = (int)request('id', 0);
        $feedback_model = new feedback_model();
        $sql = "SELECT a.*, b.username
                FROM {$feedback_model->table_name} AS a
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS b
                ON a.user_id = b.user_id
                WHERE a.fb_id = :id
                LIMIT 1
               ";
        
        if($row = $feedback_model->query($sql, array(':id' => $id)))
        {
            $this->rs = $row[0];
            $this->type_map = $feedback_model->type_map;
            $this->status_map = $feedback_model->status_map;
            $message_model = new feedback_message_model();
            $this->message_list = $message_model->find_all(array('fb_id' => $id), 'dateline ASC');
            $this->admin_list = vcache::instance()->admin_model('indexed_list');
            $this->compiler('operation/feedback.html');
        }
        else
        {
            $this->prompt('error', '未找到相应的数据记录');
        }
    }
    
    public function action_reply()
    {
        $data = array
        (
            'fb_id' => (int)request('fb_id', 0),
            'admin_id' => $_SESSION['ADMIN']['USER_ID'],
            'content' => trim(request('content', '')),
            'dateline' => $_SERVER['REQUEST_TIME'],
        );
        
        $message_model = new feedback_message_model();
        $verifier = $message_model->verifier($data);
        if(TRUE === $verifier)
        {
            $message_model->create($data);
            $this->prompt('success', '回复消息成功');
        }
        else
        {
            $this->prompt('error', $verifier);
        }
    }
    
    public function action_status()
    {
        $fb_id = (int)request('id', 0);
        $status = (int)request('status', 0);
        $feedback_model = new feedback_model();
        if($feedback_model->update(array('fb_id' => $fb_id), array('status' => $status)) > 0)
        {
            $this->prompt('success', '操作成功', url($this->MOD.'/feedback', 'view', array('id' => $fb_id)));
        }
        else
        {
            $this->prompt('success', '操作失败');
        }
    }
    
    public function action_delete()
    {
        if(request('step') == 'message')
        {
            $id = request('id');
            if(!empty($id) && is_array($id))
            {
                $message_model = new feedback_message_model();
                $affected = 0;
                foreach($id as $v) if($message_model->delete(array('id' => (int)$v)) > 0) $affected += 1; 
                $failure = count($id) - $affected;
                $this->prompt('default', "成功删除 {$affected} 个消息记录, 失败 {$failure} 个");
            }
            else
            {
                $this->prompt('error', '参数错误');
            }
        }
        else
        {
            $id = request('id');
            $feedback_model = new feedback_model();
            $message_model = new feedback_message_model();
            if(is_array($id))
            {
                $affected = 0;
                foreach($id as $v) 
                {
                    $condition = array('fb_id' => (int)$v);
                    if($feedback_model->delete($condition) > 0)
                    {
                        $affected += 1;
                        $message_model->delete($condition);
                    }
                }
                $failure = count($id) - $affected;
                $this->prompt('default', "成功删除 {$affected} 个咨询反馈记录, 失败 {$failure} 个", url($this->MOD.'/feedback', 'index'));
            }
            else
            {
                $condition = array('fb_id' => $id);
                if($feedback_model->delete($condition) > 0)
                {
                    $message_model->delete($condition);
                    $this->prompt('success', '删除成功', url($this->MOD.'/feedback', 'index'));
                }
                else
                {
                    $this->prompt('error', '删除失败');
                }
            }
        }
    }
}