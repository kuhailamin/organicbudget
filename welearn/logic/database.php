<?php

class database {

    private $db_server;

    function __construct($db_hostname, $db_database, $db_username, $db_password) {
        $this->db_server = $connection = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);

        if (!$this->db_server)
            die("Unable to connect to MySQL: " . mysqli_connect_errno());
    }

    function get_connection() {
        return $this->db_server;
    }

    function send_SQL($SQL) {
        $result = mysqli_query($this->db_server, $SQL);
        return $result;
    }

    function update($table, $values, $where) {
        $sql = "UPDATE $table SET ";

        if (count($values)) {
            $index = 0;
            foreach ($values as $key => $value) {
                if ($index > 0)
                    $sql.=" , ";
                if ($value == null) {
                    $sql.="$key=NULL ";
                } else {
                    $sql.="$key='$value' ";
                }
                $index++;
            }
        }
        if ($where != "")
            $sql.="WHERE $where";


        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function insert($table, $values) {
        $sql = "INSERT INTO " . $table . " ";

        if (count($values) > 0) {
            $sql.="(";
            $i = 0;
            foreach ($values as $key => $value) {
                if ($value != "" || $value != null) {
                    if ($i > 0)
                        $sql.=" , ";
                    $sql.=$key;
                    $i++;
                }
            }
            $sql.=") VALUES (";
            $i = 0;
            foreach ($values as $key => $value) {
                if ($value != "" || $value != null) {
                    if ($i > 0)
                        $sql.=" , ";
                    $sql.="'$value'";
                    $i++;
                }
            }
            $sql.=")";
        }
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function last_insert_id() {
        return mysqli_insert_id($this->db_server);
    }

    function select_like($table, $fields, $values) {
        $sql = "SELECT " . $fields . " FROM " . $table . " WHERE ";
        $i = 0;
        foreach ($values as $key => $value) {
            if ($i > 0)
                $sql.=" OR ";
            $sql.="$key LIKE '%$value%'";
            $i++;
        }

        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function select_like_SQL($table, $fields, $values) {
        $sql = "SELECT " . $fields . " FROM " . $table . " WHERE ";
        $i = 0;
        foreach ($values as $key => $value) {
            if ($i > 0)
                $sql.=" OR ";
            $sql.="$key LIKE '%$value%'";
            $i++;
        }

        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function count_inner_join($table1, $table2, $fields, $on, $where = "TRUE") {
        $sql = "SELECT COUNT(*) As 'Count' FROM " . $table1;
        $sql.=" INNER JOIN $table2 ON ";
        $sql.=$on;
        $sql.=" WHERE $where";
        $result = mysqli_query($this->db_server, $sql);
        $count = 0;
        if (!$result)
            return 0;
        while ($row = mysqli_fetch_array($result)) {
            $count = intval($row["Count"]);
        }
        return $count;
    }

    function distinct_inner_join_SQL($table1, $table2, $fields, $on, $where = "TRUE", $order_by = "") {
        $sql = "SELECT DISTINCT " . $fields . " FROM " . $table1;
        $sql.=" INNER JOIN $table2 ON ";
        $sql.=$on;
        $sql.=" WHERE $where";
        $sql.=" $order_by";
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function inner_join_SQL($table1, $table2, $fields, $on, $where = "TRUE", $order_by = "") {
        $sql = "SELECT " . $fields . " FROM " . $table1;
        $sql.=" INNER JOIN $table2 ON ";
        $sql.=$on;
        $sql.=" WHERE $where";
        $sql.=" $order_by";
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function left_join_SQL($table1, $table2, $fields, $on, $where = "TRUE", $order_by = "", $limit = "") {
        $sql = "SELECT " . $fields . " FROM " . $table1;
        $sql.=" LEFT JOIN $table2 ON ";
        $sql.=$on;
        $sql.=" WHERE $where";
        $sql.=" $order_by";
        $sql.=" $limit";
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function union_SQL($sqls) {
        $final = "";
        $result = "";
        $i = 0;
        foreach ($sqls as $sql) {
            if ($i > 0)
                $final.=" UNION ";
            $final.=$sql;
            $i++;
        }

        return $final;
    }

    function union($sqls) {
        $final = "";
        $result = "";
        $i = 0;
        foreach ($sqls as $sql) {
            if ($i > 0)
                $final.=" UNION ";
            $final.=$sql;
            $i++;
        }
        if ($final != "") {
            $result = mysqli_query($this->db_server, $sql);
        }
        return $result;
    }

    function delete_where($table, $where) {
        $sql = "DELETE  FROM " . $table . " WHERE " . $where;
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function select_fields_where($table, $fields = "*", $where = "TRUE", $order_by = "") {
        $where=trim($where)==""?"TRUE":$where;
        $sql = "SELECT " . $fields . " FROM " . $table . " WHERE " . $where . "  $order_by";
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function select_fields_TOP($table, $fields = "*", $top = "1") {
        $sql = "SELECT " . $fields . " FROM " . $table . " LIMIT " . $top;
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function select_fields($table, $fields = "*", $order_by = "") {
        $sql = "SELECT " . $fields . " FROM " . $table . " $order_by";
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function select_where_top($table, $top = 10, $where = "true", $order_by = "") {
        //$result="called";
        $sql = "SELECT * FROM " . $table . " WHERE " . $where . " $order_by" . " LIMIT $top";
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function count($table, $where = "TRUE") {
        $sql = "SELECT COUNT(*) As 'Count' FROM " . $table . " WHERE $where";
        $result = mysqli_query($this->db_server, $sql);
        $count = 0;
        if (!$result)
            return 0;
        while ($row = mysqli_fetch_array($result)) {
            $count = intval($row["Count"]);
        }
        return $count;
    }

    function select_where($table, $where) {
        //$result="called";
        $sql = "SELECT * FROM " . $table . " WHERE " . $where;
        $result = mysqli_query($this->db_server, $sql);
        return $result;
    }

    function general_query($SQL) {
        $result = $this->connection->query($SQL);
        $result = mysqli_query($this->db_server, $SQL);
        return $result;
    }

    function LIKE_ALL_WORDS($field, &$values) {
        $LIKE = "";
        $index = 0;
        foreach ($values as $value) {
            if ($index != 0)
                $LIKE.=" OR ";
            $LIKE.="$field LIKE '%$value%' ";
            $index++;
        }
        return $LIKE;
    }

    function close() {
        mysqli_close($this->db_server);
    }

    function __destruct() {
        mysqli_close($this->db_server);
    }

}

?>
