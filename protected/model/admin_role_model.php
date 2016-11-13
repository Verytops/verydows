<?php
class admin_role_model extends Model
{
    public $table_name = 'admin_role';
    
    public function get_acls($user_id)
    {
        $acls = array();
        if(!empty($user_id))
        {
            $sql = "SELECT role_acl FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}role
                    WHERE role_id IN (SELECT role_id FROM {$this->table_name} WHERE user_id = {$user_id})
                   ";
            if($role_acls = $this->query($sql))
            {
                 foreach($role_acls as $v) if(!empty($v['role_acl'])) $acls = array_merge($acls, json_decode($v['role_acl'], TRUE));
            }
        }
        return $acls;
    }
}