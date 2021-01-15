<?php
namespace DB;
use \mysqli;
use \Exception;


abstract class DB{
    // Hold the class instance.
    private static $db_name = DB_NAME;
    private static $user_name = DB_USER;
    private static $user_pwd = DB_PASS;
    private static $srv_name = DB_SERVER;
    private static $srv_port = DB_PORT;

    protected static $table;

    //TODO: Use prepared statments instead of string queries, to prevent SQL Injection
    //https://stackoverflow.com/questions/14066580/should-i-also-sanitize-data-coming-from-get-if-i-use-those-without-changing-th#answer-14066600
    static function make_query(string $query) { //Simply returns the result of the query
        //Create the so-needed aditional tables for the CMS to use 
        $conn = new mysqli(self::$srv_name, self::$user_name, self::$user_pwd,self::$db_name);
        $query_result = [];
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        if ($result = $conn->query($query)) {
            if(!is_bool($result)){
                if($result->num_rows > 1){
                    while($a = $result->fetch_object()){
                        $query_result[] = $a;
                    }
                }else{
                    $query_result = $result->fetch_object();
                }
            }else{

            }
         
        } else {
            //var_dump($conn->error);
            throw new Exception($conn->error, 1);
            //die();
        }

     
        if ($query_result != null) {
            if(!is_bool($result)) $result->close();
        }
        $conn->close();
        return $query_result;
    }

    static function make_query_array(string $query) { //Simply returns the result of the query
        //Create the so-needed aditional tables for the CMS to use 
        $conn = new mysqli(self::$srv_name, self::$user_name, self::$user_pwd,self::$db_name);
        $query_result = [];
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        if ($result = $conn->query($query)) {
            if(!is_bool($result)){
                if($result->num_rows > 1){
                    while($a = $result->fetch_object()){
                        $query_result[] = $a;
                    }
                }else{
                    $query_result[] = $result->fetch_object();
                }
            }else{

            }
         
        } else {
            var_dump($conn->error);
            die();
        }

     
        if ($query_result != null) {
            if(!is_bool($result)) $result->close();
        }
        $conn->close();
        return $query_result;
    }

    static function get_all($order_most_recent){
        $table = static::$table;
        $query = "select  * FROM `{$table}`";
        if($order_most_recent == true){
            $query = "select  * FROM `{$table}` ORDER BY date_modified DESC";
        }
        return static::make_query_array($query) ? static::make_query_array($query) : false;
    }

    static function get_by_id(int $id){
        $table = static::$table;
        $id = intval($id);
        $query = "select  * FROM `{$table}` WHERE `id`={$id}";
        return static::make_query($query);
    }

    static function delete(int $id){
        $table = static::$table;
        $query = "DELETE FROM `{$table}`
        WHERE `id`={$id}";
        return static::make_query($query);
    }

}
