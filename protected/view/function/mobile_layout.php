<?php
function layout_adv($params = array())
{
    if(isset($params['id']))
    {
        $html_path = APP_DIR.DS.'protected'.DS.'cache'.DS.'static'.DS.'adv_'.$params['id'].'.html';
        if($html = @file_get_contents($html_path)) return $html;
        
        $postion_model = new adv_position_model();
        if($tpl = $postion_model->fetch_tpl($params['id']))
        {
            file_put_contents($html_path, $tpl);
            return $tpl;
        }
    }
    return '';
}