<?php
declare(strict_types=1);

namespace App;

use PDO;

class DbService extends PDO
{
    private static DbService $instance;

    private function __construct()
    {
        $ini = parse_ini_file('app.ini');
        $dsh = 'mysql:host=' . $ini['db_host'] . ';dbname=' . $ini['db_name'];
        $user = $ini['db_user'];
        $password = $ini['db_password'];
        parent::__construct($dsh, $user, $password);
    }

    public static function getInstance(): DbService
    {
        if (!isset(self::$instance)) {
            self::$instance = new DbService();
        }
        return self::$instance;
    }
}