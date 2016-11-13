<?php
class aftersales_controller extends general_controller
{
	public function action_index()
    {
        $aftersales_model = new aftersales_model();
        if(request('step') == 'search')
        {
            $where = ' WHERE 1';
            $binds = array();
            
            $type = request('type', '');
            if($type != '')
            {
                $where .= " AND a.type = :type";
                $binds[':type'] = (int)$type;
            }
            
            $status = request('status', '');
            if($status != '')
            {
                $where .= " AND a.status = :status";
                $binds[':status'] = (int)$status;
            }
            
            $order_id = request('order_id', '');
            if($order_id != '')
            {
                $where .= " AND a.order_id = :order_id";
                $binds[':order_id'] = $order_id;
            }
            
            $results = array('status' => 'nodata');
            $total = $aftersales_model->query("SELECT COUNT(*) as count FROM {$aftersales_model->table_name} AS a {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $sort_id = request('sort_id', 0);
                $sort_map = array('as_id DESC', 'created_date ASC', 'created_date DESC');
                $sort = isset($sort_map[$sort_id])? $sort_map[$sort_id] : $sort_map[0];
                
                $limit = $aftersales_model->set_limit(array(request('page', 1), request('pernum', 15)), $total[0]['count']);
                
                $sql = "SELECT a.as_id, a.order_id, a.type, a.goods_qty, a.created_date, a.status,
                               b.id AS opt_map_id, b.goods_id, b.goods_name,
                               c.user_id, c.username
                        FROM {$aftersales_model->table_name} AS a
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                        ON a.order_id = b.order_id AND a.goods_id = b.goods_id
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS c
                        ON a.user_id = c.user_id
                        {$where} ORDER BY {$sort} {$limit}
                       ";
                
                if($list = $aftersales_model->query($sql, $binds))
                {
                    $type_map = $aftersales_model->type_map;
                    $status_map = $aftersales_model->status_map;
                    $opts_model = new order_goods_optional_model();
                    foreach($list as &$v)
                    {
                        $v['type'] = $type_map[$v['type']];
                        $v['status'] = $status_map[$v['status']];
                        $v['goods_opts'] = $opts_model->find_all(array('map_id' => $v['opt_map_id']));
                        $v['created_date'] = date('Y-m-d', $v['created_date']).'<br />'.date('H:i:s', $v['created_date']);
                    }
                }
                 
                $results = array
                (
                    'status' => 'success',
                    'list' => $list,
                    'paging' => $aftersales_model->page,
                );
            }

            echo json_encode($results);
        }
        else
        {
            $this->type_map = $aftersales_model->type_map;
            $this->status_map = $aftersales_model->status_map;
            $this->compiler('operation/aftersales_list.html');
        }
	}
    
    public function action_view()
    {
        $id = (int)request('id', 0);
        $aftersales_model = new aftersales_model();
        $sql = "SELECT a.*, b.id AS opt_map_id, b.goods_name, b.goods_image, c.username, c.email
                FROM {$aftersales_model->table_name} AS a
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                ON a.order_id = b.order_id AND a.goods_id = b.goods_id
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS c
                ON a.user_id = c.user_id
                WHERE a.as_id = :id
                LIMIT 1
               ";
        if($row = $aftersales_model->query($sql, array(':id' => $id)))
        {
            $opts_model = new order_goods_optional_model();
            $row[0]['goods_opts'] = $opts_model->find_all(array('map_id' => $row[0]['opt_map_id']));
            $this->rs = $row[0];
            $this->type_map = $aftersales_model->type_map;
            $this->status_map = $aftersales_model->status_map;
            $message_model = new aftersales_message_model();
            $this->message_list = $message_model->find_all(array('as_id' => $id), 'dateline ASC');
            $this->admin_list = vcache::instance()->admin_model('indexed_list');
            
            $this->compiler('operation/aftersales.html');
        }
        else
        {
            $this->prompt('error', '未找到相应的数据记录');
        }
    }
    
    public function action_status()
    {
        $as_id = (int)request('id', 0);
        $status = (int)request('status', 0);
        $aftersales_model = new aftersales_model();
        if($aftersales_model->update(array('as_id' => $as_id), array('status' => $status)) > 0)
        {
           $this->prompt('success', '操作成功', url($this->MOD.'/aftersales', 'view', array('id' => $as_id)));
        }
        else
        {
            $this->prompt('success', '操作失败');
        }
    }
    
    public function action_reply()
    {
        $data = array
        (
            'as_id' => (int)request('as_id', 0),
            'admin_id' => $_SESSION['ADMIN']['USER_ID'],
            'content' => trim(request('content', '')),
            'dateline' => $_SERVER['REQUEST_TIME'],
        );
            
        $message_model = new aftersales_message_model();
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
    
    public function action_delete()
    {
        if(request('step') == 'message')
        {
            $id = request('id');
            if(!empty($id) && is_array($id))
            {
                $message_model = new aftersales_message_model();
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
            $aftersales_model = new aftersales_model();
            $message_model = new aftersales_message_model();
            if(is_array($id))
            {
                $affected = 0;
                foreach($id as $v) 
                {
                    $condition = array('as_id' => (int)$v);
                    if($aftersales_model->delete($condition) > 0)
                    {
                        $affected += 1;
                        $message_model->delete($condition);
                    }
                }
                $failure = count($id) - $affected;
                $this->prompt('default', "成功删除 {$affected} 个售后服务记录, 失败 {$failure} 个", url($this->MOD.'/aftersales', 'index'));
            }
            else
            {
                $condition = array('as_id' => (int)$id);
                if($aftersales_model->delete($condition) > 0)
                {
                    $message_model->delete($condition);
                    $this->prompt('success', '删除成功', url($this->MOD.'/aftersales', 'index'));
                }
                else
                {
                    $this->prompt('error', '删除失败');
                }
            }
        }
    }
}