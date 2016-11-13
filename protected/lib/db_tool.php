<?php
class db_tool
{
    public function export($tables = array(), $save_dir = null, $save_name = null)
    {
        if(empty($save_dir)) $save_dir = APP_DIR.DS.'protected'.DS.'resources'.DS.'backup';
        if(!is_writable($save_dir))
        {
            err("Err: Backup directory is not writable or does not exists");
            return FALSE;
        }
        
        $model = new Model();
        $sth = $model->db_instance($GLOBALS['mysql'], 'master');
        $db_version = $sth->query('SELECT VERSION()')->fetchColumn();
        $content  = "# -------------------------------------------------------------\n";
        $content .= "# <?php die();?>\n";
        $content .= "# Verydows Database Backup\n";
        $content .= "# Program: Verydows ". $GLOBALS['verydows']['VERSION'] . " Release " . $GLOBALS['verydows']['RELEASE'] . "\n";
        $content .= "# MySql: {$db_version} \n";
        $content .= "# Database: {$GLOBALS['mysql']['MYSQL_DB']} \n";
        $content .= "# Creation: " . date('Y-m-d H:i:s') . "\n";
        $content .= "# Official: http://www.verydows.com\n";
        $content .= "# -------------------------------------------------------------\n\n";
        
        if(empty($tables))
        {
            $tables = $sth->query("SHOW TABLES LIKE '{$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}%'")->fetchAll(PDO::FETCH_NUM);
            $tables = array_column($tables, 0);
        }
        
        foreach($tables as $table)
        {
            $create = $sth->query("SHOW CREATE TABLE {$table}")->fetch(PDO::FETCH_NUM);
            $content .= "DROP TABLE IF EXISTS `{$table}`;\n{$create[1]};\n\n";
            $values = '';
            $rows_query = $sth->query("SELECT * FROM {$table}");
            while($row = $rows_query->fetch(PDO::FETCH_NUM)) $values .= "\n('" . implode("','", array_map('addslashes', $row)) . "'),";
            if($values != '') $content .= "INSERT INTO `{$table}` VALUES" . rtrim($values, ",") . ";\n\n\n";
        }
        
        if(empty($save_name)) $save_name = date('Ymd').'_'.random_chars(10).'.php';
        
        if(file_put_contents($save_dir.DS.$save_name, $content) === FALSE)
        {
            err("Err: Backup Failed");
            return FALSE;
        }

        return TRUE;
    }
    
    public function import($file)
    {
        if(file_exists($file))
        {
            $model = new Model();
            $streams = str_replace("\r", "\n", file_get_contents($file));
            $line_array = preg_split("/\n/", $streams);
            $sql = '';
            
            foreach($line_array as $line)
            {
                if(preg_match("/^#|^\-\-/", ltrim($line)) && trim($sql) == '') continue;
                
                $sql .= "{$line}\n";
                
                if(!preg_match("/;$/", trim($line))) continue;
                if(substr_count($sql, "/*") != substr_count($sql, "*/")) continue;
                
                $model->execute(trim($sql));
                $sql = '';
            }
            return TRUE;
        }
        
        err("Err: Backup file does not exists");
        return FALSE;
    }
}