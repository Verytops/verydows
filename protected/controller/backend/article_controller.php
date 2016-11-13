<?php
class article_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $cate_id = request('cate_id', '');
            $status = request('status', '');
            $kw = request('kw', '');
            
            $where = 'WHERE 1';
            $binds = array();
            
            if($cate_id != '')
            {
                $where .= ' AND cate_id = :cate_id';
                $binds[':cate_id'] = $cate_id;
            }
            if($status != '')
            {
                $where .= ' AND status = :status';
                $binds[':status'] = $status;
            }
            if(!empty($kw))
            {
                $where .= ' AND title LIKE :kw';
                $binds[':kw'] = '%'.$kw.'%';
            }
            
            $article_model = new article_model();
            $total = $article_model->query("SELECT COUNT(*) as count FROM {$article_model->table_name} {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $limit = $article_model->set_limit(array(request('page', 1), request('pernum', 10)), $total[0]['count']);
                
                $sql = "SELECT id, cate_id, title, created_date, status
                        FROM {$article_model->table_name} {$where}
                        ORDER BY id DESC {$limit}
                       ";

                $results = array
                (
                    'status' => 'success',
                    'article_list' => $article_model->query($sql, $binds),
                    'paging' => $article_model->page
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
            $cate_model = new article_cate_model();
            $this->cate_list = $cate_model->find_all(null, 'cate_id DESC');
            $this->compiler('article/article_list.html');
        }
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'title' => trim(request('title', '')),
                'cate_id' => (int)request('cate_id', 0),
                'brief' => trim(request('brief', '')),
                'link' => trim(request('link', '')),
                'content' => stripslashes(request('content', '')),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'status' => (int)request('status', 0),
                'created_date' => $_SERVER['REQUEST_TIME'],
            );
                
            $article_model = new article_model();
            $verifier = $article_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($_FILES['picture_file']['name']))
                {
                    $uploader = new uploader('upload/article/image');
                    $uploader->date_dir = date('ym');
                    $picture = $uploader->upload_file('picture_file');
                    if ($picture['error'] != 'success') $this->prompt('error', $picture['error']);
                    $data['picture'] = $picture['url'];
                }
                else
                {
                    $data['picture'] = trim(request('picture_src', ''));
                }
                    
                $article_model->create($data);
                $this->prompt('success', '添加资讯成功', url($this->MOD.'/article', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $cate_model = new article_cate_model();
            $this->cate_list = $cate_model->find_all(null, 'cate_id DESC');
            $this->compiler('article/article.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'title' => trim(request('title', '')),
                'cate_id' => (int)request('cate_id', 0),
                'brief' => trim(request('brief', '')),
                'link' => trim(request('link', '')),
                'content' => stripslashes(request('content', '')),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'status' => (int)request('status', 0),
            );
            
            $article_model = new article_model();
            $verifier = $article_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($_FILES['picture_file']['name']))
                {
                    $uploader = new uploader('upload/article/image');
                    $uploader->date_dir = date('ym');
                    $picture = $uploader->upload_file('picture_file');
                    if($picture['error'] != 'success') $this->prompt('error', $picture['error']);
                    $data['picture'] = $picture['url'];
                }
                else
                {
                    $data['picture'] = trim(request('picture_src', '', 'post'));
                }
                
                $article_model->update(array('id' => request('id')), $data);
                $this->prompt('success', '更新资讯成功', url($this->MOD.'/article', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $id = (int)request('id', 0);
            $article_model = new article_model();
            if($this->rs = $article_model->find(array('id' => $id)))
            {
                $cate_model = new article_cate_model();
                $this->cate_list = $cate_model->find_all(null, 'cate_id DESC');
                $this->compiler('article/article.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_editor()
    {
        $uploader = new uploader('upload/article/editor');
        $uploader->date_dir = date('ym');
        $file = $uploader->upload_file('upfile');
        if($file['error'] == 'success')
        {
            $callback = request('callback');
            $res = array('state' => 'SUCCESS', 'url' => $file['url']);
            if($callback) echo '<script>'.$callback.'('.json_encode($res).')</script>';
            echo json_encode($res);
        }
        else
        {
            echo "<script>alert('{$file['error']}')</script>";
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $article_model = new article_model();
            foreach($id as $v) $affected += $article_model->delete(array('id' => (int)$v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个资讯记录, 失败 {$failure} 个", url($this->MOD.'/article', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }
}