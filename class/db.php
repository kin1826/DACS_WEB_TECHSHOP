<?php

$config['DB_HOST'] = 'localhost';
$config['DB_USER'] = 'root';
$config['DB_PASS'] = '1234';
$config['DB_NAME'] = 'techstore_db';

class DB {
  private $link;
  private $error;

  function __construct() {
      global $config;
      try {
        $this->link = mysqli_connect(
          $config['DB_HOST'],
          $config['DB_USER'],
          $config['DB_PASS'],
          $config['DB_NAME']
        );

        if (!$this->link) {
          throw new Exception("Can't connect to database: " . mysqli_connect_error());
        }

        mysqli_query($this->link, "set names 'utf8'");
        mysqli_set_charset($this->link, 'utf8');

      } catch (Exception $e) {
        $this->error = $e->getMessage();
        die($this->error);
      }
  }


  /**
   * Execute a query
   * @param string $query
   * @return mysqli_result|bool
   */
  public function db_query($query) {
    $result = mysqli_query($this->link, $query);
    if (!$result) {
      $this->error = "Query failed: " . mysqli_error($this->link) . " | Query: " . $query;
      // You can log this error instead of dying in production
      // error_log($this->error);
      return false;
    }
    return $result;
  }

  /**
   * Fetch row
   * @param mysqli_result $result
   * @return array|bool
   */
  public function db_fetch($result) {
    if (!$result) return false;
    return mysqli_fetch_assoc($result);
  }

  /**
   * Fetch all rows
   * @param mysqli_result $result
   * @return array
   */
  public function db_fetch_all($result) {
    if (!$result) return [];
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
    }
    return $rows;
  }

  /**
   * Get number of rows
   * @param mysqli_result $result
   * @return int
   */
  public function db_num_rows($result) {
    if (!$result) return 0;
    return mysqli_num_rows($result);
  }

  /**
   * Get last insert ID
   * @return int
   */
  public function db_insert_id() {
    return mysqli_insert_id($this->link);
  }

  /**
   * Escape string to prevent SQL injection
   * @param string $str
   * @return string
   */
  public function db_escape($str) {
    return mysqli_real_escape_string($this->link, $str);
  }

  /**
   * Get affected rows
   * @return int
   */
  public function db_affected_rows() {
    return mysqli_affected_rows($this->link);
  }

  /**
   * Get last error
   * @return string
   */
  public function get_error() {
    return $this->error;
  }

  /**
   * Close connection
   */
  public function __destruct() {
    if ($this->link) {
      mysqli_close($this->link);
    }
  }

  /**
   * Simple SELECT query helper
   * @param string $table
   * @param string $where
   * @param string $fields
   * @return array
   */
  public function db_select($table, $where = "", $fields = "*") {
    $query = "SELECT $fields FROM $table";
    if (!empty($where)) {
      $query .= " WHERE $where";
    }

    $result = $this->db_query($query);
    if (!$result) return [];

    return $this->db_fetch_all($result);
  }

  /**
   * Simple INSERT query helper
   * @param string $table
   * @param array $data
   * @return bool|int
   */
  public function db_insert($table, $data) {
    if (!is_array($data) || empty($data)) return false;

    $fields = [];
    $values = [];

    foreach ($data as $field => $value) {
      $fields[] = "`" . $this->db_escape($field) . "`";
      $values[] = "'" . $this->db_escape($value) . "'";
    }

    $fields_str = implode(", ", $fields);
    $values_str = implode(", ", $values);

    $query = "INSERT INTO $table ($fields_str) VALUES ($values_str)";
    $result = $this->db_query($query);

    if ($result) {
      return $this->db_insert_id();
    }

    return false;
  }

  /**
   * Simple UPDATE query helper
   * @param string $table
   * @param array $data
   * @param string $where
   * @return bool
   */
  public function db_update($table, $data, $where) {
    if (!is_array($data) || empty($data)) return false;

    $set_parts = [];
    foreach ($data as $field => $value) {
      $set_parts[] = "`" . $this->db_escape($field) . "` = '" . $this->db_escape($value) . "'";
    }

    $set_str = implode(", ", $set_parts);
    $query = "UPDATE $table SET $set_str WHERE $where";

    return $this->db_query($query) !== false;
  }

  /**
   * Simple DELETE query helper
   * @param string $table
   * @param string $where
   * @return bool
   */
  public function db_delete($table, $where) {
    $query = "DELETE FROM $table WHERE $where";
    return $this->db_query($query) !== false;
  }
}

?>
