<?php

class Database {

    // Initiate the database.
    private $config_db_display_debug = false;
    private $config_db_host          = "h2774525.stratoserver.net";
    private $config_db_user          = "dataintegration";
    private $config_db_pass          = "gis7&B85";
    private $config_db_name          = "dataintegration";

    // Class variables.
    private $display_debug = true;
    private $link = null;
    public $filter;
    static $inst = null;
    public static $counter = 0;

    /**
     * Allow the class to send admins a message alerting them to errors
     * on production sites
     *
     * @access public
     * @param string $error
     * @param string $query
     */
    public function log_db_errors($error, $query)
    {
        $message = '<p>Error at '. date('Y-m-d H:i:s').':</p>';
        $message .= '<p>Query: '. htmlentities( $query ).'<br />';
        $message .= 'Error: ' . $error;
        $message .= '</p>';

        trigger_error( $message );

        if (!defined($this->display_debug) || (defined($this->display_debug) && $this->display_debug))
            echo $message;
    }

    /**
     * Database constructor.
     *
     * @param null|string $host
     * @param null|string $user
     * @param null|string $pass
     * @param null|string $db
     */
    public function __construct($host = null, $user = null, $pass = null, $db = null)
    {
        if (!is_null($host)) $this->config_db_host = $host;
        if (!is_null($user)) $this->config_db_user = $user;
        if (!is_null($pass)) $this->config_db_pass = $pass;
        if (!is_null($db))   $this->config_db_name = $db;

        $this->display_debug = $this->config_db_display_debug;

        mb_internal_encoding( 'UTF-8' );
        mb_regex_encoding( 'UTF-8' );
        mysqli_report( MYSQLI_REPORT_STRICT );
        try {
            $this->link = new mysqli( $this->config_db_host, $this->config_db_user, $this->config_db_pass, $this->config_db_name );
            $this->link->set_charset( "utf8mb4" );
        } catch ( Exception $e ) {
            die( 'Unable to connect to database' );
        }
    }

    /**
     * Database destructor.
     */
    public function __destruct()
    {
        if($this->link)
        {
            $this->disconnect();
        }
    }

    /**
     * Sanitize user data
     *
     * Example usage:
     * $user_name = $database->filter( $_POST['user_name'] );
     *
     * Or to filter an entire array:
     * $data = array( 'name' => $_POST['name'], 'email' => 'email@address.com' );
     * $data = $database->filter( $data );
     *
     * @access public
     * @param mixed $data
     * @return mixed $data
     */
    public function filter($data)
    {
        if (!is_array($data))
        {
            $data = $this->link->real_escape_string($data);
            $data = trim(htmlentities($data, ENT_QUOTES, 'UTF-8', false));
            return $data;
        }

        $data = array_map([$this, 'filter'], $data);
        return $data;
    }

    /**
     * Extra function to filter when only mysqli_real_escape_string is needed
     * @access public
     * @param mixed $data
     * @return mixed $data
     */
    public function escape($data)
    {
        if (!is_array($data))
        {
            $data = $this->link->real_escape_string( $data );
            return $data;
        }

        $data = array_map( array( $this, 'escape' ), $data );
        return $data;
    }

    /**
     * Normalize sanitized data for display (reverse $database->filter cleaning)
     *
     * Example usage:
     * echo $database->clean( $data_from_database );
     *
     * @access public
     * @param string $data
     * @return string $data
     */
    public function clean($data)
    {
        $data = stripslashes($data);
        $data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
        $data = nl2br($data);
        $data = urldecode($data);
        return $data;
    }

    /**
     * Determine if common non-encapsulated fields are being used
     *
     * Example usage:
     * if( $database->db_common( $query ) )
     * {
     *      //Do something
     * }
     * Used by function exists
     *
     * @access public
     * @param string
     * @param array
     * @return bool
     *
     */
    public function db_common( $value = '' )
    {
        if (is_array($value))
        {
            foreach( $value as $v )
            {
                if( preg_match('/AES_DECRYPT/i', $v) || preg_match('/AES_ENCRYPT/i', $v) || preg_match('/now()/i', $v))
                    return true;
                else
                    return false;
            }
        }
        else
        {
            if( preg_match('/AES_DECRYPT/i', $value) || preg_match('/AES_ENCRYPT/i', $value) || preg_match('/now()/i', $value))
                return true;
        }

        return false;
    }

    /**
     * Perform queries
     * All following functions run through this function
     *
     * @access public
     * @param string
     * @return string
     * @return array
     * @return bool
     */
    public function query($query)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $full_query = $this->link->query($query);

        if ($this->link->error)
        {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        }

        return true;
    }

    /**
     * Determine if database table exists
     * Example usage:
     * if( !$database->table_exists( 'checkingfortable' ) )
     * {
     *      //Install your table or throw error
     * }
     *
     * @access public
     * @param string
     * @return bool
     */
    public function table_exists($name)
    {
        self::$counter++;

        $check = $this->link->query("SELECT 1 FROM $name");

        if ($check !== false)
        {
            if ($check->num_rows > 0)
                return true;
            else
                return false;
        }

        return false;
    }

    /**
     * Count number of rows found matching a specific query
     *
     * Example usage:
     * $rows = $database->num_rows( "SELECT id FROM users WHERE user_id = 44" );
     *
     * @access public
     * @param string
     * @return int
     */
    public function num_rows($query)
    {
        self::$counter++;
        $num_rows = $this->link->query($query);

        if( $this->link->error )
        {
            $this->log_db_errors($this->link->error, $query);
            return $this->link->error;
        }

        return $num_rows->num_rows;
    }

    /**
     * Run check to see if value exists, returns true or false
     *
     * Example Usage:
     * $check_user = array(
     *    'user_email' => 'someuser@gmail.com',
     *    'user_id' => 48
     * );
     * $exists = $database->exists( 'your_table', 'user_id', $check_user );
     *
     * @access public
     * @param string database table name
     * @param string field to check (i.e. 'user_id' or COUNT(user_id))
     * @param array column name => column value to match
     * @return bool
     */
    public function exists($table = '', $check_val = '', $params = [])
    {
        self::$counter++;

        if (empty($table) || empty($check_val) || empty($params))
            return false;

        $check = [];
        foreach ($params as $field => $value)
        {
            if (!empty($field) && !empty($value))
            {
                if ($this->db_common($value))
                    $check[] = "$field = $value";
                else
                    $check[] = "$field = '$value'";
            }
        }

        $check = implode(' AND ', $check);
        $rs_check = "SELECT $check_val FROM ".$table." WHERE $check";
        $number = $this->num_rows( $rs_check );

        return $number !== 0;
    }

    /**
     * Return specific row based on db query
     *
     * Example usage:
     * list( $name, $email ) = $database->get_row( "SELECT name, email FROM users WHERE user_id = 44" );
     *
     * @access public
     * @param string
     * @param bool $object (true returns results as objects)
     * @return bool|array|stdClass
     */
    public function get_row( $query, $object = false)
    {
        self::$counter++;
        $row = $this->link->query( $query );

        if ($this->link->error)
        {
            $this->log_db_errors($this->link->error, $query);
            return false;
        }

        $r = !$object ? $row->fetch_row() : $row->fetch_object();
        return $r;
    }

    /**
     * Perform query to retrieve array of associated results
     *
     * Example usage:
     * $users = $database->get_results( "SELECT name, email FROM users ORDER BY name ASC" );
     * foreach( $users as $user )
     * {
     *      echo $user['name'] . ': '. $user['email'] .'<br />';
     * }
     *
     * @access public
     * @param string
     * @param bool $object (true returns object)
     * @return bool|array
     */
    public function get_results($query, $object = false)
    {
        self::$counter++;

        $row = null;

        $results = $this->link->query($query);
        if ($this->link->error)
        {
            $this->log_db_errors($this->link->error, $query);
            return false;
        }

        $row = [];
        while($r = (!$object) ? $results->fetch_assoc() : $results->fetch_object())
        {
            $row[] = $r;
        }

        return $row;
    }

    /**
     * Insert data into database table
     *
     * Example usage:
     * $user_data = array(
     *      'name' => 'Bennett',
     *      'email' => 'email@address.com',
     *      'active' => 1
     * );
     * $database->insert( 'users_table', $user_data );
     *
     * @access public
     * @param string table name
     * @param array table column => column value
     * @return bool
     */
    public function insert($table, $variables = [])
    {
        self::$counter++;

        if (empty($variables))
            return false;

        $sql = "INSERT INTO ". $table;
        $fields = [];
        $values = [];
        foreach ($variables as $field => $value)
        {
            $fields[] = $field;
            $values[] = "'".$value."'";
        }

        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';

        $sql .= $fields .' VALUES '. $values;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $query = $this->link->query($sql);

        if ($this->link->error)
        {
            $this->log_db_errors($this->link->error, $sql);
            return false;
        }

        return true;
    }

    /**
     * Insert data KNOWN TO BE SECURE into database table
     * Ensure that this function is only used with safe data
     * No class-side sanitizing is performed on values found to contain common sql commands
     * As dictated by the db_common function
     * All fields are assumed to be properly encapsulated before initiating this function
     *
     * @access public
     * @param string table name
     * @param array table column => column value
     * @return bool
     */
    public function insert_safe($table, $variables = [])
    {
        self::$counter++;

        if (empty($variables))
            return false;

        $sql = "INSERT INTO ". $table;
        $fields = [];
        $values = [];
        foreach ($variables as $field => $value)
        {
            $fields[] = $this->filter($field);
            $values[] = $value;
        }

        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';

        $sql .= $fields .' VALUES '. $values;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $query = $this->link->query($sql);

        if( $this->link->error )
        {
            $this->log_db_errors($this->link->error, $sql);
            return false;
        }

        return true;
    }

    /**
     * Insert multiple records in a single query into a database table
     *
     * Example usage:
     * $fields = array(
     *      'name',
     *      'email',
     *      'active'
     *  );
     *  $records = array(
     *     array(
     *          'Bennett', 'bennett@email.com', 1
     *      ),
     *      array(
     *          'Lori', 'lori@email.com', 0
     *      ),
     *      array(
     *          'Nick', 'nick@nick.com', 1, 'This will not be added'
     *      ),
     *      array(
     *          'Meghan', 'meghan@email.com', 1
     *      )
     * );
     *  $database->insert_multi( 'users_table', $fields, $records );
     *
     * @access public
     * @param string table name
     * @param array table columns
     * @param array records
     * @return bool
     * @return int number of records inserted
     *
     */
    public function insert_multi($table, $columns = [], $records = [])
    {
        self::$counter++;

        if (empty($columns) || empty($records))
            return false;

        $number_columns = count($columns);
        $added = 0;
        $sql = "INSERT INTO ". $table;
        $fields = [];

        foreach ($columns as $field)
        {
            $fields[] = '`'.$field.'`';
        }

        $fields = ' (' . implode(', ', $fields) . ')';
        $values = [];

        foreach ($records as $record)
        {
            if (count($record) == $number_columns)
            {
                $values[] = '(\''. implode( '\', \'', array_values( $record ) ) .'\')';
                $added++;
            }
        }

        $values = implode( ', ', $values );
        $sql .= $fields .' VALUES '. $values;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $query = $this->link->query( $sql );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $sql );
            return false;
        }

        return $added;
    }

    /**
     * Update data in database table
     *
     * Example usage:
     * $update = array( 'name' => 'Not bennett', 'email' => 'someotheremail@email.com' );
     * $update_where = array( 'user_id' => 44, 'name' => 'Bennett' );
     * $database->update( 'users_table', $update, $update_where, 1 );
     *
     * @access public
     * @param string table name
     * @param array values to update table column => column value
     * @param array where parameters table column => column value
     * @param int|string limit
     * @return bool
     *
     */
    public function update($table, $variables = [], $where = [], $limit = '')
    {
        self::$counter++;

        $clause = [];
        $updates = [];

        if (empty($variables))
            return false;

        $sql = "UPDATE ". $table ." SET ";
        foreach( $variables as $field => $value )
        {
            $updates[] = "`$field` = '$value'";
        }
        $sql .= implode(', ', $updates);

        if (!empty($where))
        {
            foreach ($where as $field => $value)
            {
                $clause[] = "$field = '$value'";
            }

            $sql .= ' WHERE '. implode(' AND ', $clause);
        }

        if (!empty( $limit ))
            $sql .= ' LIMIT '. $limit;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $query = $this->link->query($sql);
        if ($this->link->error)
        {
            $this->log_db_errors($this->link->error, $sql);
            return false;
        }

        return true;
    }

    /**
     * Delete data from table
     *
     * Example usage:
     * $where = array( 'user_id' => 44, 'email' => 'someotheremail@email.com' );
     * $database->delete( 'users_table', $where, 1 );
     *
     * @access public
     * @param string table name
     * @param array where parameters table column => column value
     * @param int|string max number of rows to remove.
     * @return bool
     */
    public function delete($table, $where = [], $limit = '')
    {
        self::$counter++;

        $clause = [];

        if (empty($where))
            return false;

        /** @noinspection SqlWithoutWhere */
        $sql = "DELETE FROM ". $table;
        foreach ($where as $field => $value)
        {
            $clause[] = "$field = '$value'";
        }
        $sql .= " WHERE ". implode(' AND ', $clause);

        if (!empty($limit))
        {
            $sql .= " LIMIT ". $limit;
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $query = $this->link->query($sql);
        if ($this->link->error)
        {
            $this->log_db_errors($this->link->error, $sql);
            return false;
        }

        return true;
    }

    /**
     * Get last auto-incrementing ID associated with an insertion
     *
     * Example usage:
     * $database->insert( 'users_table', $user );
     * $last = $database->lastid();
     *
     * @access public
     * @return int
     */
    public function lastId()
    {
        self::$counter++;
        return $this->link->insert_id;
    }

    /**
     * Return the number of rows affected by a given query
     *
     * Example usage:
     * $database->insert( 'users_table', $user );
     * $database->affected();
     *
     * @access public
     * @return int
     */
    public function affected()
    {
        return $this->link->affected_rows;
    }

    /**
     * Get number of fields
     *
     * Example usage:
     * echo $database->num_fields( "SELECT * FROM users_table" );
     *
     * @access public
     * @param string query
     * @return int
     */
    public function num_fields($query)
    {
        self::$counter++;
        $query = $this->link->query($query);
        $fields = $query->field_count;
        return $fields;
    }

    /**
     * Get field names associated with a table
     *
     * Example usage:
     * $fields = $database->list_fields( "SELECT * FROM users_table" );
     * echo '<pre>';
     * print_r( $fields );
     * echo '</pre>';
     *
     * @access public
     * @param string query
     * @return array
     */
    public function list_fields($query)
    {
        self::$counter++;
        $query = $this->link->query($query);
        $listed_fields = $query->fetch_fields();
        return $listed_fields;
    }

    /**
     * Truncate entire tables
     *
     * Example usage:
     * $remove_tables = array( 'users_table', 'user_data' );
     * echo $database->truncate( $remove_tables );
     *
     * @access public
     * @param array database table names
     * @return int number of tables truncated
     *
     */
    public function truncate($tables = [])
    {
        if (empty($tables))
            return 0;

        $truncated = 0;
        foreach( $tables as $table )
        {
            $truncate = "TRUNCATE TABLE `".trim($table)."`";
            $this->link->query($truncate);
            if (!$this->link->error)
            {
                $truncated++;
                self::$counter++;
            }
        }

        return $truncated;
    }

    /**
     * Output results of queries
     *
     * @access public
     * @param string variable
     * @param bool echo [true,false] defaults to true
     * @return string
     *
     */
    public function display($variable, $echo = true)
    {
        $out = '';

        if (!is_array($variable))
        {
            $out .= $variable;
        }
        else
        {
            $out .= '<pre>';
            $out .= print_r($variable, TRUE);
            $out .= '</pre>';
        }

        if ($echo === true)
            echo $out;

        return $out;
    }

    /**
     * Output the total number of queries
     * Generally designed to be used at the bottom of a page after
     * scripts have been run and initialized as needed
     *
     * Example usage:
     * echo 'There were '. $database->total_queries() . ' performed';
     *
     * @access public
     * @return int
     */
    public function total_queries()
    {
        return self::$counter;
    }

    /**
     * Singleton function
     *
     * Example usage:
     * $database = DB::getInstance();
     *
     * @access private
     * @return self
     */
    static function getInstance()
    {
        if (self::$inst == null)
            self::$inst = new Database();

        return self::$inst;
    }

    /**
     * Disconnect from db server
     * Called automatically from __destruct function
     */
    public function disconnect()
    {
        $this->link->close();
    }

    /**
     * Imports an predefined sql file.
     * Returns whether the operation was successful.
     *
     * @param string $path Path the the SQL file.
     * @return bool Whether the operation was successful.
     */
    public function import($path)
    {
        if (!file_exists($path)) return false;

        $lines = file($path);
        $temp = '';

        foreach ($lines as $line)
        {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            $temp .= $line;

            if (substr(trim($line), -1, 1) == ';')
            {
                $this->query($temp) or print ('Error performing query: ' . $temp . '<br><br>');
                $temp = '';
            }
        }

        return true;
    }
}