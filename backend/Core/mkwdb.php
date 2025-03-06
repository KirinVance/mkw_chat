<?php

class mkwdb
{
    private $pdo;

    function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        try {
            $this->pdo = new \PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Connection failed.");
        }
    }

    public function query(string $query): void
    {
        $this->pdo->query($query);
    }

    public function select(string $query): array
    {
        $stmt = $this->pdo->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $results;
    }

    public function selectOne(string $query): array
    {
        $stmt = $this->pdo->query($query . " LIMIT 1");
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($results)) {
            return [];
        }

        return $results[0];
    }

    public function insert(string $table, array $params): int
    {
        $names = array_keys($params);
        $values = array_values($params);
        $namesString = implode(', ', $names);
        $valuesString = implode(', :', $names);
        $sqm = "INSERT INTO {$table} ({$namesString}) VALUES (:{$valuesString})";

        $stmt = $this->pdo->prepare($sqm);
        for($i = 0; $i < count($names); $i++) {
            $stmt->bindParam(":{$names[$i]}", $values[$i]);
        }
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $params, string $where): int
    {
        $names = array_keys($params);
        $values = array_values($params);

        $paramsString = "";
        for($i = 0; $i < count($names); $i++) {
            $paramsString .= "{$names[$i]} = :{$names[$i]}, ";
        }
        $paramsString = substr($paramsString, 0, -2);

        $sqm = "UPDATE {$table} SET {$paramsString} WHERE {$where}";

        $stmt = $this->pdo->prepare($sqm);
        for($i = 0; $i < count($names); $i++) {
            $stmt->bindParam(":{$names[$i]}", $values[$i]);
        }
        $stmt->execute();

        return $stmt->rowCount();
    }
}
