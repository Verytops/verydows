<?php
class admin_active_model extends Model
{
    public $table_name = 'admin_active';
    
    public function add_active()
    {
        if(!$this->find(array('sess_id' => session_id())))
        {          
            $data = array
            (
                'sess_id' => session_id(),
                'user_id' => $_SESSION['ADMIN']['USER_ID'],
                'ip' => get_ip(),
                'dateline' => $_SERVER['REQUEST_TIME'],
                'expires' => $_SERVER['REQUEST_TIME'] + ini_get('session.gc_maxlifetime'),
            );
            $this->create($data);
        }
    }
    
    public function update_active()
    {
        $this->update(array('sess_id' => session_id()), array('expires' => $_SERVER['REQUEST_TIME'] + ini_get('session.gc_maxlifetime')));
        $this->execute("DELETE FROM {$this->table_name} WHERE expires < {$_SERVER['REQUEST_TIME']}");
    }
    
    public function get_active_list()
    {
        $admin_model = new admin_model();
        $sql = "SELECT a.user_id, a.ip, a.dateline, b.username, b.name
                FROM {$this->table_name} AS a
                LEFT JOIN {$admin_model->table_name} AS b
                ON a.user_id = b.user_id
                ORDER BY dateline DESC
               ";
        return $this->query($sql);
    }
}
