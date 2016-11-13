<?php
date_default_timezone_set('PRC');

return array
(
    'mysql' => array
    (
        'MYSQL_HOST' => '#DB_HOST#',
        'MYSQL_PORT' => '#DB_PORT#',
        'MYSQL_USER' => '#DB_USER#',
        'MYSQL_DB'   => '#DB_NAME#',
        'MYSQL_PASS' => '#DB_PASS#',
        'MYSQL_DB_TABLE_PRE' => '#DB_TABLE_PRE#',
        'MYSQL_CHARSET' => 'UTF8',
    ),
    
    'verydows' => array
    (
        'VERSION' => '#VERSION#',
        'RELEASE' => '#RELEASE#',
        'COMMENCED' => '#COMMENCED#',
    ),
);