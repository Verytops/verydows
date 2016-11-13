<?php
class cleaner_controller extends general_controller
{
    public function action_index()
    {
        $this->compiler('tools/cleaner.html');
    }
    
    public function action_wiping()
    {
        $clean = request('clean', null, 'post');
        if(!is_array($clean) || empty($clean)) $clean = array('data', 'static', 'template');
        $error = FALSE;
        foreach($clean as $v)
        {
            if($error = $this->proceed($v)) break;
        }
        if($error)
        {
            $res = array('status' => 'error', 'msg' => $error);
        }
        else
        {
            $res = array('status' => 'success');
        }
        echo json_encode($res);
    }
    
    private function proceed($clean)
    {
        $error = FALSE;
        switch($clean)
        {
            case 'data': 
                
               if(!vcache::instance()->flush()) $error = '清理数据缓存失败';
            
            break;
            
            case 'template': 
                
                $path = APP_DIR.DS.'protected'.DS.'cache'.DS.'template'.DS;
                foreach(glob($path . '*') as $v)
                {
                    if(!@unlink($v))
                    {
                        $error = '清理模板缓存失败';
                        break;
                    }
                }
            
            break;
            
            case 'static':
            
                $path = APP_DIR.DS.'protected'.DS.'cache'.DS.'static'.DS;
                foreach(glob($path . '*') as $v)
                {
                    if(!@unlink($v))
                    {
                        $error = '清理静态缓存失败';
                        break;
                    }
                }
                
            break;
        }
        
        return $error;
    }

}