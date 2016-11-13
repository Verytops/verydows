<?php
class file_controller extends general_controller
{
    public function action_index()
    {
        $path = urldecode(request('path', ''));
        $root = 'upload/';
        $opendir = $root . $path;
        if(substr($opendir, -1, 1) != '/') $opendir = $opendir . '/';
        
        $results = array();
        $list = array_paging(glob(str_replace('/', DS, $opendir) .'*'), request('page', 1), request('pernum', 15));
        if(!empty($list['slice']))
        {
            $rlen = strlen($root);
            $dlen = strlen($opendir);
            foreach($list['slice'] as $k => $v)
            {
                $v = str_replace('\\', '/', $v);
                $results[$k]['name'] = substr($v, $dlen);
                $results[$k]['path'] = substr($v, $rlen);
                if(is_dir($v))
                {
                    $results[$k]['size'] = '/';
                    $results[$k]['type'] = 'folder';
                }
                else
                {
                    switch(strtolower(strrchr($v, '.')))
                    {
                        case '.jpg':
                        case '.jpeg':
                        case '.gif':
                        case '.png':
                        case '.bmp':
                            $results[$k]['type'] = 'picture';
                        break;
                        
                        case '.swf':
                        case '.flv':
                            $results[$k]['type'] = 'flash';
                        break;
                        
                        default: $results[$k]['type'] = 'file';
                    }
                    $results[$k]['size'] = bytes_to_size(filesize($v));
                    $results[$k]['path'] = substr($v, $rlen);
                }
            }
        }
        
        $this->results = $results;
        $this->paging = $list['pagination'];
        if(!empty($path) && $path != '/')
        {
            $this->parentdir = substr($path, 0, strrpos($path, '/'));
        }
        else
        {
            $this->parentdir = '/';
        }
        $this->path = $path;
        $this->compiler('tools/file_list.html');
    }
    
    public function action_upload()
    {
        $path = request('path', '', 'post');
        if(!empty($_FILES['file']))
        {
            $root = 'upload';
            if($path == '') $save_path = $root.DS;
            else $save_path = $root.DS.$path.DS;
            
            if(is_dir($save_path))
            {
                $format_limit = empty($GLOBALS['cfg']['upload_filetype']) ? null : explode('|', $GLOBALS['cfg']['upload_filetype']);
                $size_limit = empty($GLOBALS['cfg']['upload_filesize']) ? null : size_to_bytes($GLOBALS['cfg']['upload_filesize']);
                $uploader = new uploader($save_path, $format_limit, $size_limit);
                $results = $uploader->upload_file('file', false);
                $error = array();
                foreach($results as $k => $v)
                {
                    if($v['error'] != 'success') $error[] = "文件[{$_FILES['file']['name'][$k]}]".$v['error'];
                }
                if(empty($error)) $this->prompt('success', '上传文件成功');
                else $this->prompt('error', $error);
            }
            else
            {
                $this->prompt('error', '文件保存路径错误');
            }
        }
        else
        {
            $this->prompt('error', '获取上传文件失败');
        }
    }
    
    public function action_rename()
    {
        $newname = trim(request('newname', ''));
        $oldname = request('oldname', '');
        if($newname == '') $this->prompt('error', '文件名不能为空');
        if(preg_match('/\/|\\\|\:|\*|\?|\"|\<|\>|\|/', $newname) != 0) $this->prompt('error', "文件名不能包含 \/:*?\"| 符号");
        if($oldname != '')
        {
            $root = 'upload/';
            $oldname = $root.$oldname;
            
            if(file_exists($oldname))
            {
                if($path = substr($oldname, 0, strrpos($oldname, '/'))) $newname = $path.DS.$newname;
                else $newname = $root.$newname;
            }
            else
            {
                $this->prompt('error', '文件或目录不存在');
            }
            
            $oldname = str_replace('/', DS, $oldname);
            $newname = str_replace('/', DS, $newname);
            if(rename($oldname, $newname)) $this->prompt('success', '文件重命名成功');
            $this->prompt('error', '文件重命名失败');
        }
        else
        {
            $this->prompt('error', '获取文件路径错误');
        }
    }
    
    public function action_delete()
    {
        $path = request('path', '');
        if(is_array($path) && !empty($path))
        {
            $root = 'upload/';
            $error = array();
            foreach($path as $v)
            {
                $file = str_replace('/', DS, $root.$v);
                
                if(is_dir($file))
                {
                    if(!@rmdir($file)) $error[] = "无法删除非空文件夹({$file})";
                }
                elseif(is_file($file))
                {
                    if(!@unlink($file)) $error[] = "删除文件({$file})失败";
                }
                else
                {
                    $error[] = "文件({$file})不存在";
                }
            }
            
            if(empty($error)) $this->prompt('success', '删除文件成功');
            $this->prompt('error', $error);
        }
        else
        {
            $this->prompt('error', '获取文件路径错误');
        }
    }
}