<?php
class DB_Query
{
  private $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  // Execute a query with parameters and return statement
  public function query($sql, $params = [])
  {
    $stmt = $this->pdo->prepare($sql);
    if (!$stmt) {
      throw new Exception("Failed to prepare statement");
    }
    $stmt->execute($params);
    return $stmt;
  }

  // Fetch all rows as associative array
  public function fetchAll($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Fetch a single row
  public function fetchOne($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Fetch a single column (like COUNT)
  public function fetchColumn($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetchColumn();
  }

  // Insert data into table; $data is associative array of column => value
  public function insert($table, $data)
  {
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute(array_values($data));
  }

  // Update data in table where condition; $data and $condition are associative arrays
  public function update($table, $data, $condition)
  {
    $setClauses = [];
    $params = [];
    foreach ($data as $col => $val) {
      $setClauses[] = "$col = ?";
      $params[] = $val;
    }
    $whereClauses = [];
    foreach ($condition as $col => $val) {
      $whereClauses[] = "$col = ?";
      $params[] = $val;
    }
    $sql = "UPDATE $table SET " . implode(',', $setClauses) . " WHERE " . implode(' AND ', $whereClauses);
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
  }

  // Delete from table where condition
  public function delete($table, $condition)
  {
    $whereClauses = [];
    $params = [];
    foreach ($condition as $col => $val) {
      $whereClauses[] = "$col = ?";
      $params[] = $val;
    }
    $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClauses);
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
  }
}
