<?php
class adv_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $where = 'WHERE 1';
            $binds = array();
            if($start_date = strtotime(request('start_date', ''))) $where .= " AND start_date >= {$start_date}";
            if($end_date = strtotime(request('end_date', ''))) $where .= " AND end_date <= {$end_date}";
            
            $type = request('type', '');
            if($type != '')
            {
                $where .= ' AND type = :type';
                $binds[':type'] = $type;
            }
            
            $status = request('status', '');
            if($status != '')
            {
                $where .= ' AND status = :status';
                $binds[':status'] = (int)$status;
            }
            
            $kw = trim(request('kw', ''));
            if($kw != '')
            {
                $where .= ' AND name LIKE :kw';
                $binds[':kw'] = '%'.$kw.'%';
            }
            
            $adv_model = new adv_model();
            $total = $adv_model->query("SELECT COUNT(*) as count FROM {$adv_model->table_name} {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $limit = $adv_model->set_limit(array(request('page', 1), request('pernum', 15)), $total[0]['count']);
                $sql = "SELECT adv_id, position_id, name, type, start_date, end_date, seq, status
                        FROM {$adv_model->table_name} {$where}
                        ORDER BY adv_id DESC {$limit}
                       ";
                
                $list = $adv_model->query($sql ,$binds);
                $position_map = vcache::instance()->adv_position_model('indexed_list');
                foreach($list as &$v)
                {
                    $v['position_name'] = isset($position_map[$v['position_id']]) ? $position_map[$v['position_id']]['name'] : '';
                    $v['type'] = $adv_model->type_map[$v['type']];
                    $v['start_date'] = !empty($v['start_date']) ? date('Y-m-d', $v['start_date']) : '';
                    $v['end_date'] = !empty($v['end_date']) ? date('Y-m-d', $v['end_date']) : '';
                }
                
                $results = array
                (
                    'status' => 'success',
                    'list' => $list,
                    'paging' => $adv_model->page,
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
            $adv_model = new adv_model();
            $this->type_list = $adv_model->type_map;
            $this->position_list = vcache::instance()->adv_position_model('indexed_list');
            $this->compiler('adv/adv_list.html');
        }
    }

    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $type = request('type', '');
            $data = array
            (
                'name' => trim(request('name', '')),
                'position_id' => request('position_id', 0),
                'type' => $type,
                'start_date' => trim(request('start_date', '')),
                'end_date' => trim(request('end_date', '')),
                'seq' => (int)request('seq', 99),
                'status' => (int)request('status', 0),
            );
            
            $adv_model = new adv_model();
            $rule_slices = array('width' => FALSE, 'height' => FALSE, 'link' => FALSE, 'content' => FALSE);
            $verifier = $adv_model->verifier($data, $rule_slices);
            if(TRUE === $verifier)
            {
                $data['start_date'] = !empty($data['start_date']) ? strtotime($data['start_date']) : 0;
                $data['end_date'] = !empty($data['end_date']) ? strtotime($data['end_date']) : 0;
                $params = (array)request($type.'_params');
                $processed = $this->process_params($type, $params);
                $data = $data + $processed;
                $adv_model->create($data);
                $this->prompt('success', '添加广告成功', url($this->MOD.'/adv', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->position_list = vcache::instance()->adv_position_model('indexed_list');
            $this->position_id = (int)request('position', 0);
            $this->compiler('adv/adv.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $type = request('type', '');
            $data = array
            (
                'name' => trim(request('name', '', 'post')),
                'position_id' => request('position_id', 0, 'post'),
                'type' => $type,
                'start_date' => trim(request('start_date', 0, 'post')),
                'end_date' => trim(request('end_date', 0, 'post')),
                'seq' => trim(request('seq', 99, 'post')),
                'status' => intval(request('status', 0, 'post')),
            );
            
            $adv_model = new adv_model();
            $rule_slices = array('width' => FALSE, 'height' => FALSE, 'link' => FALSE, 'content' => FALSE);
            $verifier = $adv_model->verifier($data, $rule_slices);
            if(TRUE === $verifier)
            {
                $data['start_date'] = !empty($data['start_date']) ? strtotime($data['start_date']) : 0;
                $data['end_date'] = !empty($data['end_date']) ? strtotime($data['end_date']) : 0;
                $params = (array)request($type.'_params');
                $processed = $this->process_params($type, $params);
                $data = $data + $processed;
                
                $adv_model->update(array('adv_id' => request('id')), $data);
                $this->prompt('success', '更新广告成功', url($this->MOD.'/adv', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $adv_model = new adv_model();
            if($rs = $adv_model->find(array('adv_id' => (int)request('id', 0))))
            {
                $rs['params'] = json_decode($rs['params'], TRUE);
                $this->rs = $rs;
                $this->position_list = vcache::instance()->adv_position_model('indexed_list');
                $this->compiler('adv/adv.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_delete()
    {
        $id = (array)request('id');
        if(!empty($id))
        {
            $affected = 0;
            $adv_model = new adv_model();
            foreach($id as $v) $affected += $adv_model->delete(array('adv_id' => (int)$v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个记录, 失败 {$failure} 个", url($this->MOD.'/adv', 'index'));
        }
        else
        {
            $this->prompt('error', '无法获取参数');
        }
    }
    
    //处理不同广告参数
    private function process_params($type, $params)
    {
        $adv_model = new adv_model();
        $rule_slices = array('name' => FALSE, 'start_date' => FALSE, 'end_date' => FALSE, 'seq' => FALSE, 'type' => FALSE);
        switch($type)
        {
            case 'image':
            
                $rule_slices['content'] = FALSE;
                $verifier = $adv_model->verifier($params, $rule_slices);
                if(TRUE === $verifier)
                {
                    if(!empty($_FILES['image_file']['name']))
                    {
                        $save_path = 'upload/adv/image';
                        $uploader = new uploader($save_path);
                        $image = $uploader->upload_file('image_file');
                        if($image['error'] != 'success') $this->prompt('error', $image['error']);
                        $params['src'] = $image['url'];
                    }
                    if(!empty($params['src']))
                    {
                        $img_attr = "src=\"{$params['src']}\"";
                        if(!empty($params['width'])) $img_attr .= " width=\"{$params['width']}\"";
                        if(!empty($params['height'])) $img_attr .= " height=\"{$params['height']}\"";
                        if(!empty($params['title'])) $img_attr .= " alt=\"{$params['title']}\"";
                        $data['codes'] = "<a href=\"{$params['link']}\"><img {$img_attr} border=\"0\" /></a>";
                    }
                    else
                    {
                        $this->prompt('error', '请上传图片文件或输入文件URL');
                    }
                }
                else
                {
                    $this->prompt('error', $verifier);
                }
                        
            break;
                    
            case 'flash':
                    
                $rule_slices['content'] = $rule_slices['link'] = FALSE;
                $verifier = $adv_model->verifier($params, $rule_slices);
                if(TRUE === $verifier)
                {
                    if(!empty($_FILES['flash_file']['name']))
                    {
                        $save_path = 'upload/adv/flash';
                        $uploader = new uploader($save_path, array('.swf', '.flv'));
                        $flash = $uploader->upload_file('flash_file');
                        if($flash['error'] != 'success') $this->prompt('error', $flash['error']);
                        $params['src'] = $flash['url'];
                    }
                    if(!empty($params['src']))
                    {
                        $flash_attr = "src=\"{$params['src']}\"";
                        if(!empty($params['width'])) $flash_attr .= " width=\"{$params['width']}\"";
                        if(!empty($params['height'])) $flash_attr .= " height=\"{$params['height']}\"";
                        $data['codes'] = "<embed {$flash_attr} type=\"application/x-shockwave-flash\" wmode=\"transparent\"></embed>";
                    }
                    else
                    {
                        $this->prompt('error', '请上传Flash文件或输入文件URL');
                    }
                }
                else
                {
                    $this->prompt('error', $verifier);
                }
                        
            break;
                    
            case 'text':
                
                $rule_slices['width'] = $rule_slices['height'] = FALSE;
                $verifier = $adv_model->verifier($params, $rule_slices);
                if(TRUE === $verifier)
                {
                    $stylestr = '';
                    foreach(explode(',', $params['style']) as $item)
                    {
                        if(!empty($item))
                        {
                            $k = strtok($item, ':'); $v = strtok(':');
                            if(!empty($v))
                            {
                                switch($k)
                                {
                                    case 'c': $stylestr .= "color:{$v};"; break;
                                    case 's': $stylestr .= "font-size:{$v};"; break;
                                    case 'b': $stylestr .= "font-weight:bold;"; break;
                                    case 'u': $stylestr .= "text-decoration:underline;"; break;
                                    case 'i': $stylestr .= "font-style:italic;"; break;
                                }
                            }
                            $stylearr[$k] = $v;
                        }
                    }
                    $params['style'] = $stylearr;
                    if($stylestr != '') $stylestr = " style=\"{$stylestr}\"";
                    $data['codes'] = "<a href=\"{$params['link']}\"{$stylestr}>{$params['content']}</a>";
                }
                else
                {
                    $this->prompt('error', $verifier);
                }  
                        
            break;
                    
            case 'code':
                    
                $rule_slices['width'] = $rule_slices['height'] = $rule_slices['link'] = FALSE;
                $verifier = $adv_model->verifier($params, $rule_slices);
                if(TRUE === $verifier)
                {
                    $data['codes'] = stripslashes($params['content']);
                    $params = array();
                }
                else
                {
                    $this->prompt('error', $verifier);
                }
                        
            break;
        }
        
        $data['params'] = json_encode($params);
        return $data;
    }
    
}