<?php
class request_error_model extends Model
{
    public $table_name = 'request_error';
    
    public $increase = TRUE;
    
    public function check($ip, $captcha_setting = 0)
    {
        if($captcha_setting == 1)
        {
            return TRUE;
        }
        elseif($captcha_setting == 2)
        {
            if($row = $this->find(array('ip' => $ip, 'dateline' => strtotime('today')))) 
            {
                if($row['count'] > 3)
                {
                    if($row['count'] >= 10) $this->increase = FALSE;
                    return TRUE;
                }
            }
        }
        
        return FALSE;
    }
    
    public function lockout($ip)
    {
        $condition = array('ip' => $ip, 'dateline' => strtotime('today'));
        if($row = $this->find($condition))
        {
            if($row['count'] >= 10 && $row['lockout'] == 0)
            {
                $this->update($condition, array('lockout' => $_SERVER['REQUEST_TIME'] + 1800));
                return 30;
            }
            
            $lockout = $row['lockout'] - $_SERVER['REQUEST_TIME'];
            if($lockout <= 0)
            {
                if($row['count'] >= 10) $this->update($condition, array('count' => 6, 'lockout' => 0));
            }
            else
            {
                return ceil($lockout / 60);
            }
        }
        return 0;
    }
    
    public function incr_err($ip)
    {
        if($this->increase)
        {
            $sql = "INSERT INTO {$this->table_name}(ip, dateline, count) VALUES (:ip, :dateline, 1) ON DUPLICATE KEY UPDATE count = count + 1";
            return $this->execute($sql, array(':ip' => $ip, ':dateline' => strtotime('today')));
        }
        return FALSE;
    }
}