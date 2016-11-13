<?php
class user_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $where = 'WHERE 1';
            $binds = array();
            $kw = trim(request('kw', ''));
            if($kw != '')
            {
                $where_field = request('type', 0) == 0 ? 'username' : 'email';
                $where .= " AND {$where_field} LIKE :kw";
                $binds[':kw'] = '%'.$kw.'%';
            }
            
            $user_model = new user_model();
            $total = $user_model->query("SELECT COUNT(*) as count FROM {$user_model->table_name} {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $limit = $user_model->set_limit(array(request('page', 1), request('pernum', 15)), $total[0]['count']);
                
                $sql = "SELECT a.user_id, a.username, a.email, a.status, a.email_status,
                               b.created_date, b.last_date
                        FROM {$user_model->table_name} AS a
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user_record AS b
                        ON a.user_id = b.user_id
                        {$where}
                        ORDER BY a.user_id DESC {$limit}
                       ";
                
                $results = array
                (
                    'status' => 'success',
                    'list' => $user_model->query($sql, $binds),
                    'paging' => $user_model->page,
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
            $this->compiler('user/user_list.html');
        }
    }

    public function action_view()
    {
        $user_id = (int)request('id', 0);
        $condition = array('user_id' => $user_id);
        $user_model = new user_model();
        if($user = $user_model->find($condition))
        {
            switch(request('step'))
            {
                case 'order':
                
                    $order_model = new order_model();
                    $this->user = $user;
                    $this->status_map = $order_model->status_map;
                    $this->compiler('user/order_list.html');
                
                break;
                
                case 'consignee':
                
                    $consignee_model = new user_consignee_model();
                    $this->consignee_list = $consignee_model->get_user_consignee_list($user_id);
                    $this->user = $user;
                    $this->compiler('user/user_consignee_list.html');
                
                break;
                
                case 'account':
   
                    $account_model = new user_account_model();
                    $this->account = $account_model->find($condition);
                    $this->user = $user;
                    $this->compiler('user/user_account_log_list.html');
                    
                break;
                
                default:
                
                    include(VIEW_DIR.DS.'function'.DS.'html_date_options.php');
                    //使用记录
                    $record_model = new user_record_model();
                    $this->record = $record_model->find($condition);
                    //资料信息
                    $profile_model = new user_profile_model();
                    $this->profile = $profile_model->find($condition);
                    //账户信息
                    $account_model = new user_account_model();
                    $this->account = $account_model->get_user_account($user_id);
                    $this->user = $user;
                    $this->compiler('user/user.html');
            }
        }
        else
        {
            $this->prompt('error', '无法找到相应的用户记录');
        }
    }
    
    public function action_revise_account()
    {
        $user_id = (int)request('id', 0);
        $data = array
        (
            'balance' => (float)request('balance', 0),
            'points' => (int)request('points', 0),
            'exp' => (int)request('exp', 0),
            'cause' => trim(request('cause', '')),
        );
        if(empty($data['balance']) && empty($data['points']) && empty($data['exp'])) $this->prompt('error', '经验、余额或积分至少输入一项');
        if($data['cause'] == '') $this->prompt('error', '原因备注不能为空');
          
        $account_model = new user_account_model();
        $verifier = $account_model->verifier($data);
        if(TRUE === $verifier)
        {
            $sym_balance = request('sym_balance', 1);
            $sym_points = request('sym_points', 1);
            $sym_exp = request('sym_exp', 1);
            if($sym_balance == -1) $data['balance'] = 0 - $data['balance'];
            if($sym_points == -1) $data['points'] = 0 - $data['points'];
            if($sym_exp == -1) $data['exp'] = 0 - $data['exp'];
             
            $sql = "UPDATE {$account_model->table_name}
                    SET balance = balance + :balance, points = points + :points, exp = exp + :exp
                    WHERE user_id = :user_id
                   ";
            $binds = array(':balance' => $data['balance'], ':points' => $data['points'], ':exp' => $data['exp'], ':user_id' => $user_id);
            if($account_model->execute($sql, $binds) > 0)
            {
                $data['user_id'] = $user_id;
                $data['admin_id'] = $_SESSION['ADMIN']['USER_ID'];
                $data['dateline'] = $_SERVER['REQUEST_TIME'];
                $log_model = new user_account_log_model();
                $log_model->create($data);
                
                $this->prompt('success', '调整用户账户数据成功', url($this->MOD.'/user', 'view', array('id' => $user_id)));
            }    
            else
            {
                $this->prompt('error', '调整用户账户数据失败');
            }       
        }
        else
        {
            $this->prompt('error', $verifier);
        }
    }
    
    public function action_reset_password()
    {
        $data = array
        (
            'password' => trim(request('password', '')),
            'repassword' => trim(request('repassword', '')),
        );
        
        $user_model = new user_model();
        $verifier = $user_model->verifier($data, array('username' => FALSE, 'email' => FALSE));
        if(TRUE === $verifier)
        {
            $condition = array('user_id' => request('id'));
            $data['password'] = md5($data['password']);
            unset($data['repassword']);
            $user_model->update($condition, $data);
            $this->prompt('success', '修改用户密码成功');
        }
        else
        {
            $this->prompt('error', $verifier);
        }
    }
    
    public function action_edit_profile()
    {
        $data = array
        (
            'nickname' => trim(request('nickname', '')),
            'gender' => (int)request('gender', 0),
            'birth_year' => (int)request('birth_year', 0),
            'birth_month' => (int)request('birth_month', 0),
            'birth_day' => (int)request('birth_day', 0),
            'qq' => trim(request('qq', '')),
            'signature' => trim(request('signature', '')),
        );
        
        $profile_model = new user_profile_model();
        $verifier = $profile_model->verifier($data);
        if(TRUE === $verifier)
        {
            $user_id = (int)request('id', 0);
            if(!empty($_FILES['avatar']['name']))
            {
                $save_path = 'upload/user/avatar';
                $save_name = uniqid($user_id);
                $uploader = new uploader($save_path);
                $avatar = $uploader->upload_file('avatar', $save_name);
                if($avatar['error'] != 'success') $this->prompt('error', $avatar['error']);
                $data['avatar'] = $avatar['name'];
            }
            $profile_model->update(array('user_id' => $user_id), $data);
            $this->prompt('success', '更新用户资料成功');
        }
        else
        {
            $this->prompt('error', $verifier);
        }
    }
    
    public function action_delete()
    {
        $condition = array('user_id' => request('id'));
        $user_model = new user_model();
        if($user_model->delete($condition) > 0)
        {
            //删除用户资料
            $profile_model = new user_profile_model();
            $profile_model->delete($condition);
            //删除用户账户
            $account_model = new user_account_model();
            $account_model->delete($condition);
            //删除用户收件人
            $consignee_model = new user_consignee_model();
            $consignee_model->delete($condition);
            //删除用户收藏
            $favorite_model = new user_favorite_model();
            $favorite_model->delete($condition);
            //删除商品评价
            $review_model = new goods_review_model();
            $review_model->delete($condition);
            //删除售后记录
            $aftersales_model = new aftersales_model();
            $aftersales_model->delete($condition);
            
            $this->prompt('success', '删除用户成功', url($this->MOD.'/user', 'index'));
        }  
        else
        {
            $this->prompt('error', '删除用户失败');
        }   
    }

}