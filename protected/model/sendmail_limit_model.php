<?php
class sendmail_limit_model extends Model
{
    public $table_name = 'sendmail_limit';
    
    /**
     * 当日发送次数加1
     */
    public function increase($type, $user_id = 0, $ip = '')
    {
        $row = array('type' => $type, 'user_id' => $user_id, 'ip' => $ip, 'dateline' => strtotime(date('Ymd')));
        if($this->incr($row, 'count') == 0) $this->create($row);
    }
    
    /**
     * 检查当日发送次数
     */
    public function check_counts($type, $user_id = 0, $ip = '')
    {
        $limit_model = new sendmail_limit_model();
        if($count = $limit_model->find(array('type' => $type, 'user_id' => $user_id, 'ip' => $ip, 'dateline' => strtotime(date('Ymd'))), null, 'count'))
        {
            if($count['count'] >= 5) return FALSE;
        }
        return TRUE;
    }
}