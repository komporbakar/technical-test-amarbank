<?php

namespace App\Application\Models;

use \PDO;

class DB
{

    private $pdo;
    private $host = 'localhost';
    private $user = 'root';
    private $pass = ''; //password db
    private $dbname = 'amarbank_loans';

    public function __construct()
    {
        $this->pdo = $this->connect();
    }


    public function connect()
    {
        $conn_str = "mysql:host=$this->host;dbname=$this->dbname";
        $conn = new PDO($conn_str, $this->user, $this->pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

    public function executeQuery($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }
}
