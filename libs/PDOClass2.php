<?php

/*****

  Connection class to bbdd with PDO drivers.
  Driver List: https://www.php.net/manual/es/pdo.drivers.php

  The way to use it in a PHP program is to add the PDOClass.php file to the beginning of the file and call the methods directly. As it is a static class it is not necessary to declare the variables a direct call can be made.


*****/

class PDOClass2
{

  protected static $pathLogs = null;

  function __construct() {

    $pathLogs = dirname( dirname( __FILE__)) . '/logs/';

  }

  public static function Connection( $config = [])
  {

    if ( empty( $config))
    {
      error_log( date("Y-m-d H:i:s") . " - Config file empty \n", 3, static::$pathLogs."db_error.log");
      $return = [
        'success' => false,
        'data' => 'Config file empty',
      ];
      return ( [ 'success' => false, 'data' => $return]);
    }

    try
    {
      $connection = new PDO( $config['dsn'], $config['username'], $config['password']);
      $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      return ( [ 'success' => true, 'data' => $connection]);

    }
    catch (PDOException $e)
    {

      $_error = print_r( $e->getTrace(), true) . "\n" . $e->getMessage();

      $_error = [
        'trace' => $e->getTrace(),
        'errormsg' => $e->getMessage(),
      ];

      error_log( date("Y-m-d H:i:s") . " - " . print_r( $_error, true) . "\n", 3, static::$pathLogs."db_error.log");
      $return = [
        'success' => false,
        'data' => $_error,
      ];

      return ( $return);
    }

  }


  public static function ExecuteQuery( $params = [], $connection)
  {

   try
    {
      $stmt = $connection->prepare( $params['query']);
      $stmt->execute( $params['params'] );
      $data = $stmt->fetchAll( PDO::FETCH_ASSOC);
      $count = $stmt->rowCount();
      $stmt->closeCursor();

      $return = [ 'success' => true, 'data' => $data, 'count' => $count];
    }
    catch (PDOException $e)
    {

      $_error = [
        'trace' => $e->getTrace(),
        'errormsg' => $e->getMessage(),
      ];

      error_log( date("Y-m-d H:i:s") . " - " . print_r( $_error, true) . "\n", 3, static::$pathLogs."db_error.log");
      $return = [
        'success' => false,
        'data' => $_error,
      ];

    }

    unset ( $stmt);

    return ( $return);
  }

  public static function Execute( $params = [], $connection)
  {

    try
    {
      $stmt = $connection->prepare( $params['query']);
      $stmt->execute( $params['params'] );
      $count = $stmt->rowCount();

      $return = [ 'success' => true, 'count' => $count];
    }
    catch (PDOException $e)
    {

      $_error = [
        'trace' => $e->getTrace(),
        'errormsg' => $e->getMessage(),
      ];

      error_log( date("Y-m-d H:i:s") . " - " . print_r( $_error, true) . "\n", 3, static::$pathLogs."db_error.log");
      $return = [
        'success' => false,
        'data' => $_error,
      ];
    }

    unset ( $stmt);

    return ( $return);

  }

  public static function Insert( $params = [], $connection)
  {

    if ( empty( $connection))
    {
      $config_db = ConfigClass::get("database.twitter");
      $connection = self::Connection( $config_db);
    }

    $data = [];
    $fields = $fields_values = $a_values = "";

    foreach ( $params['fields'] as $key => $value)
    {
      $fields .= $key . ",";
      $fields_values .= " ?,";
      $a_values .= $value . ".:.";
    }

    $fields  = substr( $fields, 0, strlen( $fields) - 1);
    $fields_values  = substr( $fields_values, 0, strlen( $fields_values) - 1);
    $a_values  = substr( $a_values, 0, strlen( $a_values) - 3);


    try
    {

      $sql = "insert into " . $params['table'] . "( {$fields} ) values( ".$fields_values." )";

      $stmt = $connection->prepare( $sql);
      $r = $stmt->execute( explode( ".:.", $a_values));
      $count = $stmt->rowCount();
      $id = $connection->lastInsertId();

      $return = [ 'success' => true, 'data' => $data, 'last_id' =>  $id, 'count' => $count];

    }
    catch (PDOException $e)
    {

      $_error = [
        'trace' => $e->getTrace(),
        'errormsg' => $e->getMessage(),
      ];

      error_log( date("Y-m-d H:i:s") . " - " . print_r( $_error, true) . "\n", 3, static::$pathLogs."db_error.log");
      $return = [
        'success' => false,
        'data' => $_error,
      ];

    }

    unset( $stmt);

    return ( $return );

  }


}