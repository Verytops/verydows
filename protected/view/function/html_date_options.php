<?php
function html_date_options($params = array())
{
    if(!isset($params['type']) || empty($params['type'])) return '';
    switch($params['type'])
    {
        case 'year':
        
            if(!isset($params['start_year'])) $params['start_year'] = -100;
            $end = date('Y');
            $start = $end + $params['start_year'];
            $unit = '年';
            
        break;
        
        case 'month':
        
            $end = 12;
            $start = 1;
            $unit = '月';
            
        break;
        
        case 'day':
        
            $end = 31;
            $start = 1;
            $unit = '日';
            
        break;
    }
    
    $html = '';
    if(isset($params['default']))
    {
        for($i = $start; $i <= $end; $i++)
        {
            $text = str_pad($i, 2, '0', STR_PAD_LEFT);
            if($params['default'] == $i)
            {
                $html .= "<option selected=\"selected\" value=\"{$i}\">{$text} {$unit}</option>";
            }
            else
            {
                $html .= "<option value=\"{$i}\">{$text} {$unit}</option>";
            }
        }
    }
    else
    {
        for($i = $start; $i <= $end; $i++)
        {
            $text = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html .= "<option value=\"{$i}\">{$text} {$unit}</option>";
        }
    }
    return $html;
}
