<?php
require_once 'configs/database.php';

abstract class BaseModel {
    protected static $_connection;

    public function __construct() {
        if (!isset(self::$_connection)) {
            self::$_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            if (self::$_connection->connect_errno) {
                die("Database connection failed: " . self::$_connection->connect_error);
            }
            // optional: set charset
            mysqli_set_charset(self::$_connection, 'utf8mb4');
        }
    }

    protected function query($sql) {
        $result = self::$_connection->query($sql);
        if ($result === false) {
            error_log("MySQL error: (" . self::$_connection->errno . ") " . self::$_connection->error . " — SQL: $sql");
        }
        return $result;
    }

    protected function select($sql) {
        $result = $this->query($sql);
        $rows = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        return $rows;
    }

    // Prepared select using get_result() (mysqlnd)
    protected function selectPrepared($sql, $types = '', $params = []) {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . self::$_connection->error);
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result(); // requires mysqlnd
        $rows = [];
        if ($res instanceof mysqli_result) {
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $res->free();
        }
        $stmt->close();
        return $rows;
    }

    protected function executePrepared($sql, $types = '', $params = []) {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . self::$_connection->error);
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $ok = $stmt->execute();
        if ($stmt->affected_rows === 0 && $ok === false) {
            error_log("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        return $ok;
    }

    // For direct insert/update/delete if needed
    protected function delete($sql) { return $this->query($sql); }
    protected function update($sql) { return $this->query($sql); }
    protected function insert($sql) { return $this->query($sql); }
}
