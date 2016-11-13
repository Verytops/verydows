<?php
class goods_review_controller extends general_controller
{
    public function action_index()
    {
        $goods_id = request('goods_id', '');
        $user_id = request('user_id', '');
        if(request('step') == 'search')
        {
            $where_1 = $where_2 = ' WHERE 1';
            $binds = array();
            
            if($goods_id != '')
            {
                $where_1 .= ' AND goods_id = :goods_id';
                $where_2 .= ' AND a.goods_id = :goods_id';
                $binds[':goods_id'] = (int)$goods_id;
            }
            if($user_id != '')
            {
                $where_1 .= ' AND user_id = :user_id';
                $where_2 .= ' AND a.user_id = :user_id';
                $binds[':user_id'] = (int)$user_id;
            }
            
            $status = request('status', '');
            if($status != '')
            {
                $where_1 .= ' AND status = :status';
                $where_2 .= ' AND a.status = :status';
                $binds[':status'] = $status;
            }
            
            $replied = request('replied', '');
            if($replied == 1)
            {
                $where_1 .= ' AND replied = ""';
                $where_2 .= ' AND a.replied = ""';
            }
            elseif($replied == 2)
            {
                $where_1 .= ' AND replied <> ""';
                $where_2 .= ' AND a.replied <> ""';
            }

            $review_model = new goods_review_model();
            $total = $review_model->query("SELECT COUNT(*) as count FROM {$review_model->table_name} {$where_1}", $binds);
            if($total[0]['count'] > 0)
            {
                $sort_id = request('sort_id', 0);
                $sortmap = array('review_id DESC', 'created_date DESC', 'created_date ASC', 'rating DESC', 'rating ASC');
                $sort = isset($sortmap[$sort_id])? $sortmap[$sort_id] : $sortmap[0];
                
                $limit = $review_model->set_limit(array(request('page', 1), request('pernum', 10)), $total[0]['count']);
                
                $sql = "SELECT a.review_id, a.order_id, a.rating, a.content, a.created_date, a.status, a.replied,
                               b.goods_id, b.goods_name,
                               c.user_id, c.username
                        FROM {$review_model->table_name} AS a
                        LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                        ON a.order_id = b.order_id AND a.goods_id = b.goods_id
                        LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS c
                        ON c.user_id = a.user_id
                        {$where_2}
                        ORDER BY {$sort} {$limit}
                       ";
                       
                $results = array
                (
                    'status' => 'success',
                    'list' => $review_model->query($sql, $binds),
                    'paging' => $review_model->page,
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
            if(!empty($goods_id))
            {
                $goods_model = new goods_model();
                $sql = "SELECT a.goods_id, a.goods_name, a.goods_image,
                               COUNT(b.review_id) AS count, 
                               AVG(b.rating) AS rating
                        FROM {$goods_model->table_name} AS a
                        LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}goods_review AS b
                        ON a.goods_id = b.goods_id
                        WHERE a.goods_id = :goods_id
                        GROUP BY b.goods_id
                       ";
                if($goods = $goods_model->query($sql, array(':goods_id' => $goods_id))) $this->goods = $goods[0];
            }
            if(!empty($user_id))
            {
                $user_model = new user_model();
                $this->user = $user_model->find(array('user_id' => $user_id), null, 'user_id, username, email');
            }
            
            $this->compiler('goods/review_list.html');
        }
    }
    
    public function action_view()
    {
        $id = (int)request('id', 0);
        $review_model = new goods_review_model();
        $sql = "SELECT a.review_id, a.order_id, a.rating, a.content, a.created_date, a.status, a.replied,
                       b.goods_id, b.goods_name, b.goods_image,
                       c.user_id, c.username, c.email
                FROM {$review_model->table_name} AS a
                LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods AS b
                ON a.order_id = b.order_id AND a.goods_id = b.goods_id
                LEFT JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user AS c
                ON a.user_id = c.user_id
                WHERE a.review_id = :id
                LIMIT 1
               ";
        
        if($row = $review_model->query($sql, array(':id' => $id)))
        {
            if(!empty($row[0]['replied'])) $row[0]['replied'] = json_decode($row[0]['replied'], TRUE);
            $this->rs = $row[0];
            $this->rating_map = $review_model->rating_map;
            $this->compiler('goods/review.html');
        }
        else
        {
            $this->prompt('error', '未找到相应的评价记录');
        }
    }
    
    public function action_approval()
    {
        $id = request('id');
        $status = (int)request('status', 0);
        $review_model = new goods_review_model();
        if(is_array($id))
        {
            $affected = 0;
            foreach($id as $v) $affected += $review_model->update(array('review_id' => $v), array('status' => $status));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功审核 {$affected} 条评价, 失败 {$failure} 条");
        }
        else
        {
            $review_model->update(array('review_id' => $id), array('status' => $status));
            jump(url($this->MOD.'/goods_review', 'view', array('id' => $id)));
        }
    }
    
    public function action_reply()
    {
        $id = (int)request('id', 0);
        $data = array
        (
            'admin' => $_SESSION['ADMIN']['USERNAME'],
            'content' => trim(request('content', '')),
            'dateline' => $_SERVER['REQUEST_TIME'],
        );
        
        $review_model = new goods_review_model();
        if($data['content'] != '')
        {
            $review_model->update(array('review_id' => $id), array('replied' => json_encode($data)));
            $this->prompt('success', '回复评价成功', url($this->MOD.'/goods_review', 'view', array('id' => $id)));
        }
        else
        {
            $review_model->update(array('review_id' => $id), array('replied' => ''));
            $this->prompt('success', '回复被清除', url($this->MOD.'/goods_review', 'view', array('id' => $id)));
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(!empty($id))
        {
            $review_model = new goods_review_model();
            if(is_array($id))
            {
                $affected = 0;
                foreach($id as $v)
                {
                    $condition = array('review_id' => (int)$v);
                    $affected += $review_model->delete($condition);
                }
                $failure = count($id) - $affected;
                $this->prompt('default', "成功删除 {$affected} 条评价, 失败 {$failure} 条");
            }
            else
            {
                $review_model->delete(array('review_id' => (int)$id));
                $this->prompt('success', '删除评价成功', url($this->MOD.'/goods_review', 'index'));
            }
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    } 
}