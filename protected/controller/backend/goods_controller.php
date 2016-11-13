<?php
class goods_controller extends general_controller
{
    public function action_index()
    {   
        if(request('step') == 'search')
        {
            $cate_id = (int)request('cate_id', 0);
            $brand_id = (int)request('brand_id', 0);
            $sign_id = request('sign_id', '');
            $status = request('status', '');
            $kw = request('kw', '');
    
            $where = 'WHERE 1';
            $binds = array();
            
            if(!empty($cate_id))
            {
                $where .= ' AND cate_id = :cate_id';
                $binds[':cate_id'] = $cate_id;
            }
            
            if(!empty($brand_id))
            {
                $where .= ' AND brand_id = :brand_id';
                $binds[':brand_id'] = $brand_id;
            }
            
            $sign_map = array('newarrival', 'recommend', 'bargain');
            if(isset($sign_map[$sign_id])) $where .= " AND {$sign_map[$sign_id]} = 1";
            
            if($status != '')
            {
                $where .= ' AND status = :status';
                $binds[':status'] = $status;
            }
    
            if($kw != '')
            {
                $where .= ' AND goods_name LIKE :kw';
                $binds[':kw'] = '%'.$kw.'%';
            }
            
            $goods_model = new goods_model();
            $total = $goods_model->query("SELECT COUNT(*) as count FROM {$goods_model->table_name} {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $fields = 'goods_id, goods_name, goods_sn, now_price, stock_qty, created_date, newarrival, recommend, bargain, status';
                $limit = $goods_model->set_limit(array(request('page', 1), request('pernum', 10)), $total[0]['count']);
                
                $sort_id = request('sort_id', 0, 'post');
                $sort_map = array('goods_id DESC', 'created_date DESC', 'created_date ASC', 'now_price DESC', 'now_price ASC');
                $sort = isset($sort_map[$sort_id])? $sort_map[$sort_id] : $sort_map[0];
                
                $sql = "SELECT {$fields} FROM {$goods_model->table_name} {$where} ORDER BY {$sort} {$limit}";
                       
                $results = array
                (
                    'status' => 'success',
                    'list' => $goods_model->query($sql, $binds),
                    'paging' => $goods_model->page,
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
            $cate_model = new goods_cate_model();
            $this->cate_list = $cate_model->indexed_cate_tree();
            $brand_model = new brand_model();
            $this->brand_list = $brand_model->indexed_list();
            $this->compiler('goods/goods_list.html');
        }
    }

    public function action_add()
    {
        if(request('step') == 'submit')
        {   
            $data = array
            (
                'goods_name' => trim(request('goods_name', '')),
                'cate_id' => (int)request('cate_id', 0),
                'brand_id' => (int)request('brand_id', 0),
                'goods_sn' => trim(request('goods_sn', '')),
                'now_price' => request('now_price', ''),
                'original_price' => (float)request('original_price', ''),
                'goods_image' => request('goods_image', ''),
                'goods_brief' => stripslashes(request('goods_brief', '')),
                'goods_content' => stripslashes(request('goods_content', '')),
                'stock_qty' => (int)request('stock_qty', 0),
                'goods_weight' => (float)request('goods_weight', 0),
                'newarrival' => (int)request('newarrival', 0),
                'recommend' => (int)request('recommend', 0),
                'bargain' => (int)request('bargain', 0),
                'status' => (int)request('status', 1),
                'meta_keywords' => trim(str_replace('，', ',', request('meta_keywords', ''))),
                'meta_description' => trim(request('meta_description', '')),
                'created_date' => $_SERVER['REQUEST_TIME'],
            );
            
            $goods_model = new goods_model();
            $verifier = $goods_model->verifier($data);
            if(TRUE === $verifier)
            {
                $max_id = $goods_model->query("SELECT MAX(goods_id) AS id FROM {$goods_model->table_name}");
                $max_id = !empty($max_id[0]['id']) ? $max_id[0]['id'] : 1;
                //商品货号
                if(empty($data['goods_sn'])) $data['goods_sn'] = $this->create_sn($data['cate_id'], $data['brand_id'], $max_id);
                $goods_id = $goods_model->create($data);
                //商品相册
                $album = request('album');
                if(!empty($album) && is_array($album))
                {
                    $album_model = new goods_album_model();
                    foreach($album as $v) $album_model->create(array('goods_id' => $goods_id, 'image' => $v));
                }
                //购买选项
                if($opts = request('goods_opts', null))
                {
                    $optl_model = new goods_optional_model();
                    $optl_model->add_goods_optional($goods_id, $opts);
                }
                $this->prompt('success', '添加商品成功', url($this->MOD.'/goods', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $cate_model = new goods_cate_model();
            $this->cate_list = $cate_model->indexed_cate_tree();
            $brand_model = new brand_model();
            $this->brand_list = $brand_model->indexed_list();
            $optional_type_model = new goods_optional_type_model();
            $this->opt_type_list = $optional_type_model->indexed_list();
            $this->compiler('goods/goods.html');
        }
    }
    
    public function action_edit()
    {
        switch(request('step'))
        {
            case 'update':
            
                $goods_id = (int)request('id', 0);
                $data = array
                (
                    'goods_name' => trim(request('goods_name', '')),
                    'cate_id' => (int)request('cate_id', 0),
                    'brand_id' => (int)request('brand_id', 0),
                    'goods_sn' => trim(request('goods_sn', '')),
                    'now_price' => request('now_price', ''),
                    'original_price' => (float)request('original_price', ''),
                    'goods_image' => request('goods_image', ''),
                    'goods_brief' => stripslashes(request('goods_brief', '')),
                    'goods_content' => stripslashes(request('goods_content', '')),
                    'stock_qty' => (int)request('stock_qty', 0),
                    'goods_weight' => (float)request('goods_weight', 0),
                    'newarrival' => (int)request('newarrival', 0),
                    'recommend' => (int)request('recommend', 0),
                    'bargain' => (int)request('bargain', 0),
                    'status' => (int)request('status', 1),
                    'meta_keywords' => trim(str_replace('，', ',', request('meta_keywords', ''))),
                    'meta_description' => trim(request('meta_description', '')),
                    'created_date' => $_SERVER['REQUEST_TIME'],
                );
                if(empty($data['goods_sn'])) $data['goods_sn'] = $this->create_sn($data['cate_id'], $data['brand_id'], $goods_id);
                
                $goods_model = new goods_model();
                $verifier = $goods_model->verifier($data);
                if(TRUE === $verifier)
                {
                    $condition = array('goods_id' => $goods_id);
                    if($goods_model->update($condition, $data) > 0)
                    {
                        //商品相册
                        $album_model = new goods_album_model();
                        $album_model->delete($condition);
                        $album = request('album');
                        if(!empty($album) && is_array($album))
                        {
                            foreach($album as $v) $album_model->create(array('goods_id' => $goods_id, 'image' => $v));
                        }
                        //商品可选项
                        $opt_model = new goods_optional_model();
                        $opt_model->delete($condition);
                        if($opts = request('goods_opts', null)) $opt_model->add_goods_optional($goods_id, $opts);
                        
                        $this->prompt('success', '更新商品成功', url($this->MOD.'/goods', 'index'));
                    }
                    else
                    {
                        $this->prompt('error', '更新商品失败');
                    }
                }
                else
                {
                    $this->prompt('error', $verifier);
                }
                
            break;
            
            case 'attr':
                
                $op = request('op');
                if($op == 'list')
                {
                    $cate_id = (int)request('cate_id', 0);
                    $goods_id = (int)request('goods_id', 0);
                    $goods_attr_model = new goods_attr_model();
                    $res['list'] = $goods_attr_model->get_goods_attrs($cate_id, $goods_id);
                    echo json_encode($res);
                }
                elseif($op == 'update')
                {
                    $goods_id = (int)request('goods_id', 0);
                    $goods_attr_model = new goods_attr_model();
                    $goods_attr_model->delete(array('goods_id' => $goods_id));
                    $attrs = request('attrs', array(), 'post');
                    if(isset($attrs['id']) && isset($attrs['value']) && $goods_attrs = array_combine($attrs['id'], $attrs['value']))
                    {
                        foreach($goods_attrs as $k => $v)
                        {
                            $v = trim($v);
                            if($v != '') $goods_attr_model->create(array('goods_id' => $goods_id, 'attr_id' => $k, 'value' => $v));
                        }
                        $this->prompt('success', '更新商品属性规格成功');
                    }
                    else
                    {
                        $this->prompt('error', '更新商品属性规格失败');
                    }
                }
                else
                {
                    $goods_id = (int)request('id', 0);
                    $goods_model = new goods_model();
                    if($this->goods = $goods_model->find(array('goods_id' => $goods_id)))
                    {
                        $cate_model = new goods_cate_model();
                        $this->cate_list = $cate_model->indexed_cate_tree();
                        $this->compiler('goods/goods_attr.html');
                    }
                    else
                    {
                        $this->prompt('error', '未找到相应的数据记录');
                    }
                }

            break;
            
            case 'related':
                
                $op = request('op');
                if($op == 'list')
                {
                    $cate_id = (int)request('cate_id', 0);
                    $brand_id = (int)request('brand_id', 0);
                    $kw = trim(request('kw', ''));
                    $where = 'WHERE 1';
                    $binds = array();
                    if(!empty($cate_id))
                    {
                        $where .= ' AND cate_id = :cate_id';
                        $binds[':cate_id'] = $cate_id;
                    }
                    if(!empty($brand_id))
                    {
                        $where .= ' AND brand_id = :brand_id';
                        $binds[':brand_id'] = $brand_id;
                    }
                    if($kw != '')
                    {
                        $where .= ' AND (goods_name LIKE :kw OR goods_sn = :sn)';
                        $binds[':kw'] = '%'.$kw.'%';
                        $binds[':sn'] = $kw;
                    }
                
                    $goods_model = new goods_model();
                    $sql = "SELECT goods_id, goods_name
                            FROM {$goods_model->table_name} {$where}
                            ORDER BY goods_id DESC";
                    
                    $res['list'] = $goods_model->query($sql, $binds);
                    echo json_encode($res);
                }
                elseif($op == 'update')
                {
                    $goods_id = (int)request('id', 0);
                    $related_model = new goods_related_model();
                    $related_model->delete(array('goods_id' => $goods_id));
                    $related_model->delete(array('related_id' => $goods_id, 'direction' => 2));
                    if($related = request('related', null)) $related_model->add_related($goods_id, $related);
                    $this->prompt('success', '更新关联商品成功');
                }
                else
                {
                    $goods_id = (int)request('id', 0);
                    $goods_model = new goods_model();
                    if($this->goods = $goods_model->find(array('goods_id' => $goods_id)))
                    {
                        $cate_model = new goods_cate_model();
                        $this->cate_list = $cate_model->indexed_cate_tree();
                        $brand_model = new brand_model();
                        $this->brand_list = $brand_model->indexed_list();
                        $related_model = new goods_related_model();
                        $this->related = $related_model->get_related_goods($goods_id);
                        $this->compiler('goods/goods_related.html');
                    }
                    else
                    {
                        $this->prompt('error', '未找到相应的数据记录');
                    }
                }
            
            break;
            
            default:
                
                $goods_id = (int)request('id', 0);
                $condition = array('goods_id' => $goods_id);
                $goods_model = new goods_model();
                
                if($this->rs = $goods_model->find($condition))
                {
                    $cate_model = new goods_cate_model();
                    $this->cate_list = $cate_model->indexed_cate_tree();
                    $brand_model = new brand_model();
                    $this->brand_list = $brand_model->indexed_list();                  
                    //获取商品相册
                    $album_model = new goods_album_model();
                    $this->album_list = $album_model->find_all($condition);
                    //获取购买选项
                    $optional_type_model = new goods_optional_type_model();
                    $this->opt_type_list = $optional_type_model->indexed_list();
                    $opt_model = new goods_optional_model();
                    $this->opt_list = $opt_model->get_goods_optional($goods_id);
                    
                    $this->compiler('goods/goods.html');
                }
                else
                {
                    $this->prompt('error', '未找到相应的数据记录');
                }
        }
    }
    
    public function action_image()
    {
        switch(request('step'))
        {
            case 'list':
                
                $dir = request('dir', 'prime');
                $path = "upload/goods/{$dir}/0/";
                if($list = glob(str_replace('/', DS, $path) .'*'))
                {
                    $dlen = strlen($path);
                    $images = array();
                    $list = array_paging($list, request('page', 1), request('pernum', 18));
                    foreach($list['slice'] as $k => $v)
                    {
                        $v = str_replace('\\', '/', $v);
                        $images[$k]['name'] = substr($v, $dlen);
                        $images[$k]['url'] = $this->baseurl. '/' . $v;
                    }
                    
                    $res = array('status' => 'success', 'list' => $images, 'paging' => $list['pagination']);
                }
                else
                {
                    $res = array('status' => 'nodata');
                }
                
                echo json_encode($res);
            
            break;
            
            case 'upload':
            
                if(!empty($_FILES))
                {
                    if(!$_FILES['file']['error'] && is_uploaded_file($_FILES['file']['tmp_name']))
                    {
                        $save_dir = 'upload/goods/';
                        if(request('dirtype') == 'prime')
                        {
                            $save_dir .= 'prime/0';
                            $thumb_dir = 'upload/goods/prime';
                            $thumb_size = $GLOBALS['cfg']['goods_img_thumb'];
                        }
                        else
                        {
                            $save_dir .= 'album/0';
                            $thumb_dir = 'upload/goods/album';
                            $thumb_size = $GLOBALS['cfg']['goods_album_thumb'];
                        }
                        
                        $uploader = new uploader($save_dir, $GLOBALS['cfg']['upload_goods_filetype'], $GLOBALS['cfg']['upload_goods_filesize']);
                        $uploader->thumb_dir = $thumb_dir;
                        $uploader->thumb_size = $thumb_size;
                        $res = $uploader->upload_file('file', uniqid(rand(10, 99)));
                        echo json_encode($res);
                    }
                }
            
            break;
            
            case 'editor':
            
                $save_path = 'upload/goods/editor/'.date('ym');
                $uploader = new uploader($save_path, $GLOBALS['cfg']['upload_goods_filetype']);
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
            
            break;
        }
        
    }

    public function action_delete()
    {
        $id = (int)request('id', 0);
        $condition = array('goods_id' => $id);
        $goods_model = new goods_model();

        if(($goods_model->delete($condition)) > 0)
        {
            //删除相册数据
            $album_model = new goods_album_model();
            $album_model->delete($condition);
            //删除商品选项
            $optl_model = new goods_optional_model();
            $optl_model->delete($condition);
            //删除关联商品
            $related_model = new goods_related_model();
            $related_model->delete($condition);
            $related_model->delete(array('related_id' => $id));
            //删除商品属性
            $attr_model = new goods_attr_model();
            $attr_model->delete($condition);
            //删除商品评价
            $review_model = new goods_review_model();
            $review_model->delete($condition);
            
            $this->prompt('success', '删除商品成功', url($this->MOD.'/goods', 'index'));
        }
        else
        {
            $this->prompt('error', '删除商品失败');
        }
    }
    
    private function create_sn($cate_id = 0, $brand_id = 0, $goods_id)
    {
        $sn = str_pad($cate_id, 3, 0, STR_PAD_LEFT). str_pad($brand_id, 3, 0, STR_PAD_LEFT) . $goods_id;
        $sn .= str_pad(mt_rand(0, 999), 3, 0, STR_PAD_LEFT);
        return $sn;
    }
}