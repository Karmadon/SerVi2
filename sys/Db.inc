<?php

/**
 * Created by PhpStorm.
 * User: karmadon
 * Date: 04.01.16
 * Time: 17:39
 */
class Db
{
    var $show_errors = false;
    public $last_error = '';
    public $num_queries = 0;
    public $num_rows = 0;
    var $rows_affected = 0;
    public $insert_id = 0;
    var $last_query;
    var $last_result;
    var $queries;
    public $prefix = '';
    public $base_prefix;
    var $ready = false;
    var $tables = array('agents', 'clients', 'orders', 'order_type', 'order_speed', 'order_options', 'order_state', 'order_history');
    var $global_tables = array('users');
    public $col_info;
    public $time_start;
    public $options;
    public $users;
    protected $result;
    protected $reconnect_retries = 5;
    protected $dbuser;
    protected $dbpassword;
    protected $dbname;
    protected $dbhost;
    protected $dbh;
    private $has_connected = false;
    public $field_types = array();
    var $collect_stat = true;

    public function __construct($dbuser = DB_USER, $dbpassword = DB_PASSWORD, $dbname = DB_NAME, $dbhost = DB_HOST)
    {

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;

        $this->db_connect();
    }

    public function db_connect($allow_bail = true)
    {

        $client_flags = defined('MYSQL_CLIENT_FLAGS') ? MYSQL_CLIENT_FLAGS : 0;


        $this->dbh = mysqli_init();
        $port = null;
        $socket = null;
        $host = $this->dbhost;
        $port_or_socket = strstr($host, ':');


        if (!empty($port_or_socket)) {
            $host = substr($host, 0, strpos($host, ':'));
            $port_or_socket = substr($port_or_socket, 1);
            if (0 !== strpos($port_or_socket, '/')) {
                $port = intval($port_or_socket);
                $maybe_socket = strstr($port_or_socket, ':');
                if (!empty($maybe_socket)) {
                    $socket = substr($maybe_socket, 1);
                }
            } else {
                $socket = $port_or_socket;
            }
        }

        if (DEBUG) {
            mysqli_real_connect($this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket, $client_flags);
        } else {
            @mysqli_real_connect($this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket, $client_flags);
        }

        /* изменение набора символов на utf8 */
        if (!mysqli_set_charset($this->dbh, DB_CHARSET)) {
            printf("Ошибка при загрузке набора символов utf8: %s\n", mysqli_error($this->dbh));
        } else {
            //printf("Текущий набор символов: %s\n", mysqli_character_set_name($this->dbh));
        }


        if (!$this->dbh && $allow_bail) {

            return false;
        } elseif ($this->dbh) {

            $this->has_connected = true;
            $this->ready = true;
            $this->select($this->dbname, $this->dbh);


            return true;
        }

        return false;

    }

    public function select($db, $dbh = null)
    {
        if (is_null($dbh))
            $dbh = $this->dbh;
        $success = @mysqli_select_db($dbh, $db);
        if (!$success) {
            $this->ready = false;
        }
    }

    public function query($query)
    {

        $this->func_call = "\$db->query(\"$query\")";
        $this->last_query = $query;

        $this->flush();


        $this->do_query($query);

        // MySQL server has gone away, try to reconnect
        $mysql_errno = 0;
        if (!empty($this->dbh)) {
            $mysql_errno = mysqli_errno($this->dbh);
        }
        if (empty($this->dbh) || 2006 == $mysql_errno) {
            if ($this->check_connection()) {
                $this->do_query($query);
            } else {
                $this->insert_id = 0;
                return false;
            }
        }
        $this->last_error = mysqli_error($this->dbh);
        if ($this->last_error) {
            // Clear insert_id on a subsequent failed insert.
            if ($this->insert_id && preg_match('/^\s*(insert|replace)\s/i', $query))
                $this->insert_id = 0;
            return false;
        }
        if (preg_match('/^\s*(create|alter|truncate|drop)\s/i', $query)) {
            $return_val = $this->result;
        } elseif (preg_match('/^\s*(insert|delete|update|replace)\s/i', $query)) {
            $this->rows_affected = mysqli_affected_rows($this->dbh);

            if (preg_match('/^\s*(insert|replace)\s/i', $query)) {
                $this->insert_id = mysqli_insert_id($this->dbh);
            }
            $return_val = $this->rows_affected;

        } else {
            $num_rows = 0;
            if ($this->result instanceof mysqli_result) {
                while ($row = @mysqli_fetch_object($this->result)) {
                    $this->last_result[$num_rows] = $row;
                    $num_rows++;
                }
            } elseif (is_resource($this->result)) {
                while ($row = @mysql_fetch_object($this->result)) {
                    $this->last_result[$num_rows] = $row;
                    $num_rows++;
                }
            }

            // Log number of rows the query returned
            // and return number of rows selected
            $this->num_rows = $num_rows;
            $return_val = $num_rows;
        }

        return $return_val;
    }

    public function flush()
    {
        $this->last_result = array();
        $this->col_info = null;
        $this->last_query = null;
        $this->rows_affected = $this->num_rows = 0;
        $this->last_error = '';

        if ($this->result instanceof mysqli_result) {
            mysqli_free_result($this->result);
            $this->result = null;

            // Sanity check before using the handle
            if (empty($this->dbh) || !($this->dbh instanceof mysqli)) {
                return;
            }

            // Clear out any results from a multi-query
            while (mysqli_more_results($this->dbh)) {
                mysqli_next_result($this->dbh);
            }
        } elseif (is_resource($this->result)) {
            mysql_free_result($this->result);
        }
    }

    private function do_query($query)
    {

        if($this->collect_stat)
            $this->timer_start();


        $this->result = @mysqli_query($this->dbh, $query);

        //print_r($this->result);


        $this->num_queries++;

        if($this->collect_stat)
            $this->queries[] = array($query, $this->timer_stop());

    }

    public function timer_start()
    {
        $this->time_start = microtime(true);
        return true;
    }

    public function timer_stop()
    {
        return (microtime(true) - $this->time_start);
    }

    protected function load_col_info() {
        if ( $this->col_info )
            return;

            $num_fields = @mysqli_num_fields( $this->result );
            for ( $i = 0; $i < $num_fields; $i++ ) {
                $this->col_info[$i] = @mysqli_fetch_field($this->result);
            }
    }

    public function get_col_info( $info_type = 'name', $col_offset = -1 ) {
        $this->load_col_info();

        if ( $this->col_info ) {
            if ( $col_offset == -1 ) {
                $i = 0;
                $new_array = array();
                foreach( (array) $this->col_info as $col ) {
                    $new_array[$i] = $col->{$info_type};
                    $i++;
                }
                return $new_array;
            } else {
                return $this->col_info[$col_offset]->{$info_type};
            }
        }
    }

    public function check_connection($allow_bail = true)
    {

        if (@mysqli_ping($this->dbh)) {
            return true;
        }

        // Disable warnings, as we don't want to see a multitude of "unable to connect" messages
        if (DEBUG) {
            $error_reporting = error_reporting();
            error_reporting($error_reporting & ~E_WARNING);
        }

        if (!$allow_bail) {
            return false;
        }

    }

    public function prepare( $query, $args ) {
        if ( is_null( $query ) )
            return 1;



        $args = func_get_args();
        array_shift( $args );
        // If args were passed as an array (as in vsprintf), move them up
        if ( isset( $args[0] ) && is_array($args[0]) )
            $args = $args[0];
        $query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
        $query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
        $query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
        $query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
        array_walk( $args, array( $this, 'escape_by_ref' ) );
        return @vsprintf( $query, $args );
    }

    public function insert( $table, $data, $format = null, $type = 'INSERT' ) {
        $this->insert_id = 0;

        if ( ! in_array( strtoupper( $type ), array( 'REPLACE', 'INSERT' ) ) ) {
            return false;
        }

        $data = $this->process_fields( $table, $data, $format );
        if ( false === $data ) {
            return false;
        }

        $formats = $values = array();
        foreach ( $data as $value ) {
            $formats[] = $value['format'];
            $values[]  = $value['value'];
        }

        $fields  = '`' . implode( '`, `', array_keys( $data ) ) . '`';
        $formats = implode( ', ', $formats );

        $sql = "$type INTO `$table` ($fields) VALUES ($formats)";

        $this->check_current_query = false;
        return $this->query( $this->prepare( $sql, $values ) );
    }

    public function update( $table, $data, $where, $format = null, $where_format = null ) {
        if ( ! is_array( $data ) || ! is_array( $where ) ) {
            return false;
        }

        $data = $this->process_fields( $table, $data, $format );
        if ( false === $data ) {
            return false;
        }
        $where = $this->process_fields( $table, $where, $where_format );
        if ( false === $where ) {
            return false;
        }

        $fields = $conditions = $values = array();
        foreach ( $data as $field => $value ) {
            $fields[] = "`$field` = " . $value['format'];
            $values[] = $value['value'];
        }
        foreach ( $where as $field => $value ) {
            $conditions[] = "`$field` = " . $value['format'];
            $values[] = $value['value'];
        }

        $fields = implode( ', ', $fields );
        $conditions = implode( ' AND ', $conditions );

        $sql = "UPDATE `$table` SET $fields WHERE $conditions";

        $this->check_current_query = false;
        return $this->query( $this->prepare( $sql, $values ) );
    }

    public function delete( $table, $where, $where_format = null ) {
        if ( ! is_array( $where ) ) {
            return false;
        }

        $where = $this->process_fields( $table, $where, $where_format );
        if ( false === $where ) {
            return false;
        }

        $conditions = $values = array();
        foreach ( $where as $field => $value ) {
            $conditions[] = "`$field` = " . $value['format'];
            $values[] = $value['value'];
        }

        $conditions = implode( ' AND ', $conditions );

        $sql = "DELETE FROM `$table` WHERE $conditions";

        $this->check_current_query = false;
        return $this->query( $this->prepare( $sql, $values ) );
    }

    protected function process_fields( $table, $data, $format ) {
        $data = $this->process_field_formats( $data, $format );
        if ( false === $data ) {
            return false;
        }

        $converted_data = $this->strip_invalid_text( $data );

        if ( $data !== $converted_data ) {
            return false;
        }

        return $data;
    }

    protected function process_field_formats( $data, $format ) {
        $formats = $original_formats = (array) $format;

        foreach ( $data as $field => $value ) {
            $value = array(
                'value'  => $value,
                'format' => '%s',
            );

            if ( ! empty( $format ) ) {
                $value['format'] = array_shift( $formats );
                if ( ! $value['format'] ) {
                    $value['format'] = reset( $original_formats );
                }
            } elseif ( isset( $this->field_types[ $field ] ) ) {
                $value['format'] = $this->field_types[ $field ];
            }

            $data[ $field ] = $value;
        }

        return $data;
    }

    protected function strip_invalid_text( $data ) {
        $db_check_string = false;

        foreach ( $data as &$value ) {
            $charset = $value['charset'];

            if ( is_array( $value['length'] ) ) {
                $length = $value['length']['length'];
                $truncate_by_byte_length = 'byte' === $value['length']['type'];
            } else {
                $length = false;
                // Since we have no length, we'll never truncate.
                // Initialize the variable to false. true would take us
                // through an unnecessary (for this case) codepath below.
                $truncate_by_byte_length = false;
            }

            // There's no charset to temp with.
            if ( false === $charset ) {
                continue;
            }

            // Column isn't a string.
            if ( ! is_string( $value['value'] ) ) {
                continue;
            }

            $needs_validation = true;
            if (
                // latin1 can store any byte sequence
                'latin1' === $charset
                ||
                // ASCII is always OK.
                ( ! isset( $value['ascii'] ) )
            ) {
                $truncate_by_byte_length = true;
                $needs_validation = false;
            }

            if ( $truncate_by_byte_length ) {
                if ( false !== $length && strlen( $value['value'] ) > $length ) {
                    $value['value'] = substr( $value['value'], 0, $length );
                }


                if ( ! $needs_validation ) {
                    continue;
                }
            }

            // utf8 can be handled by regex, which is a bunch faster than a DB lookup.
            if ( ( 'utf8' === $charset || 'utf8mb3' === $charset || 'utf8mb4' === $charset ) && function_exists( 'mb_strlen' ) ) {
                $regex = '/
					(
						(?: [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
						|   [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
						|   \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
						|   [\xE1-\xEC][\x80-\xBF]{2}
						|   \xED[\x80-\x9F][\x80-\xBF]
						|   [\xEE-\xEF][\x80-\xBF]{2}';

                if ( 'utf8mb4' === $charset ) {
                    $regex .= '
						|    \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
						|    [\xF1-\xF3][\x80-\xBF]{3}
						|    \xF4[\x80-\x8F][\x80-\xBF]{2}
					';
                }

                $regex .= '){1,40}                          # ...one or more times
					)
					| .                                  # anything else
					/x';
                $value['value'] = preg_replace( $regex, '$1', $value['value'] );


                if ( false !== $length && mb_strlen( $value['value'], 'UTF-8' ) > $length ) {
                    $value['value'] = mb_substr( $value['value'], 0, $length, 'UTF-8' );
                }
                continue;
            }

            // We couldn't use any local conversions, send it to the DB.
            $value['db'] = $db_check_string = true;
        }
        unset( $value ); // Remove by reference.

        if ( $db_check_string ) {
            $queries = array();

            $sql = array();
            foreach ( $queries as $column => $query ) {
                if ( ! $query ) {
                    continue;
                }

                $sql[] = $query . " AS x_$column";
            }

            $this->check_current_query = false;

            foreach ( array_keys( $data ) as $column ) {
                if ( isset( $row["x_$column"] ) ) {
                    $data[ $column ]['value'] = $row["x_$column"];
                }
            }
        }

        return $data;
    }

    public function escape_by_ref( &$string ) {
        if ( ! is_float( $string ) )
        {
            if ( $this->dbh ) {
                    return mysqli_real_escape_string( $this->dbh, $string );
                } else {
                    return false;
                }
            }

            return addslashes( $string );
        }


}
