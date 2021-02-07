<?php

class MySqlToPostgreSQL extends CommonClass
{

	private $connection_mysql = null;
	private $connection_pgsql = null;


	private function SaveSequence( $data)
	{

		$tabla = $data['tabla'];
		$sequence_name = $data['tabla']."_id_seq";

		$sequence = "
			CREATE SEQUENCE public.".$sequence_name."
		    INCREMENT 1
		    START ".$data['last_id']."
		    MINVALUE 1
		    MAXVALUE 2147483647
		    CACHE 1;

			ALTER SEQUENCE public.".$sequence_name."
	   		OWNER TO postgres;

	   	ALTER TABLE ".$tabla." ALTER COLUMN id SET DEFAULT nextval('".$sequence_name."');
			ALTER TABLE ".$tabla." ALTER COLUMN id SET NOT NULL;
			ALTER SEQUENCE ".$sequence_name." OWNED BY ".$tabla.".id;
	   	";

		file_put_contents(  ConfigClass::get("config.ruta_logs")['sequences'] . $sequence_name . ".sql" , $sequence);

	  echo "Create sequence: " . $sequence_name . EOF;

	}

	private function GenericConnection( $config)
	{

		$conn_arr = PDOClass2::Connection( $config);

  	if ( $conn_arr['success'] == true)
  	{
  		return( $conn_arr['data']);
  	}
  	else
  	{
  		throw new Exception("Error Processing connection: " . $config['dsn'], 1);
  		die;
  	}

	}


	public function Init( $data = array())
	{

		$params = explode( " ", $data[0]);

		$param1 = "";
		$import_sql = False;
		if ( isset( $params[0]))
		{

			$param1 = explode( "=", $params[0]);

		}

		if ( $param1[0] == "import_sql" && $param1[1] == "true")
		{

			$import_sql = True;

		}


		$_config = ConfigClass::get("config.database")['mysql'];
		$this->connection_mysql = $this->GenericConnection( $_config);


  	$mysql_types = ConfigClass::get("equivalences.mysql_to_postgres");


  	$params['query'] = "SHOW Tables";
    $params['params'] = array();

    $rows_tables = PDOClass2::ExecuteQuery( $params, $this->connection_mysql);



    $schema_table = "";
    foreach ( $rows_tables['data'] as $table)
    {

    	$data['tabla'] = $table['Tables_in_RMARKS'];


    	$params_fields['query'] = "DESCRIBE " . $data['tabla'];
	    $params_fields['params'] = array();



	    $rows_fields = PDOClass2::ExecuteQuery( $params_fields, $this->connection_mysql);


	    $schema_table = "CREATE TABLE public.".$table['Tables_in_RMARKS']." (";
	    foreach ($rows_fields['data'] as $field)
	    {

	    	$data['field']['name'] = $field['Field'];

	    	if ( $field['Extra'] == "auto_increment")
	    	{

	    		# Get las auto_increment value
	    		$params_auto['query'] = "SELECT AUTO_INCREMENT FROM information_schema.tables
	    													WHERE table_name = ? AND table_schema = ?";
			    $params_auto['params'] = array( $data['tabla'], $_config['dbname']);

			    $rows_auto = PDOClass2::ExecuteQuery( $params_auto, $this->connection_mysql);

			    $data['last_id'] = $rows_auto['data'][0]['AUTO_INCREMENT'];

					$this->SaveSequence( $data);

	    	}

	    	if ( substr( $field['Type'], 0, 7) == 'varchar')
	    	{
	    		$field['Type'] = 'varchar';
	    	}

	    	if ( $field['Null'] == 'NO' )
	    	{
	    		$null = " NOT NULL ";
	    	}
	    	else
	    	{
	    		$null = " NULL ";
	    	}

	    	$type = $mysql_types[$field['Type']];

	    	if ( $type == '')
	    	{
	    		$type = ' text ';
	    	}

	    	$schema_table .= $field['Field'] . " " . $type . $null . "," . EOF;

	    	# MySql Dump
	    	#

	    }

	    $schema_table = substr( $schema_table, 0, strlen( $schema_table) - 2);
	    $schema_table .= ");";


	 		file_put_contents(  ConfigClass::get("config.ruta_logs")['tables'] . $table['Tables_in_RMARKS'] . ".sql", $schema_table);

	    echo "Create table: " . $table['Tables_in_RMARKS'] . EOF;

	    # Execute sql scripts
	    #
	    $file_inserts = ConfigClass::get("config.ruta_logs")['data'] .  $table['Tables_in_RMARKS'] . ".sql";


			$file_dump = "mysqldump -h " . $_config['hostname'] . " -u " . $_config['username'] . " -p" . $_config['password'] . " --no-create-db --no-create-info --compact --skip-quote-names --default-character-set=utf8mb4 " . $_config['dbname'] . " " . $table['Tables_in_RMARKS'] . " > " . $file_inserts;


			exec( $file_dump, $output);


		  echo "Export data from table: " . $table['Tables_in_RMARKS'] . EOF;

    }

    echo EOF . "End creating tables, sequences and data" . EOF . EOF;
    readline( "Press any key to continue ...");


    ## Postgres Section
    #
    # If we want to import the files created in the same process,
    # we verify the existence of the database in theconfiguration file and if it does not exist,
    # we create it and then execute the scripts
    #

	  if ( $import_sql)
	  {

	  	$_config_pg = ConfigClass::get("config.database")['postgres'];
			$this->connection_pgsql = $this->GenericConnection( $_config_pg);


			# looking for an existing database name
	  	$params_dbname['query'] = "SELECT datname FROM pg_database WHERE datname = ? ";
	  	$params_dbname['params'] = array( $_config_pg['dbname']);

	  	$rows_dbname = PDOClass2::ExecuteQuery( $params_dbname, $this->connection_pgsql);


			if ( empty( $rows_dbname['data']))
			{
				$params_create_db['query'] = "CREATE DATABASE " . $_config_pg['dbname'] . ";";
		    $params_create_db['params'] = array();

		    $return = PDOClass2::Execute( $params_create_db, $this->connection_pgsql);
			}

			# Create tables
			#
			$files_tables = scandir('./output/tables/');

	    foreach($files_tables as  $value)
	    {

	    	if ( $value != '.' And $value != '..')
	    	{


		    	$file = file_get_contents( "./output/tables/" . $value);

		    	$params_t['query'] = $file;
			    $params_t['params'] = array();

			    $return = PDOClass2::Execute( $params_t, $this->connection_pgsql);


			    if ( $return['success'] == "1")
			    {
			    	echo "Create  table: " . $value . EOF;
			    }
			    else
			    {
			    	die( "Error creating table " . $value . " - " . $return['data']['errormsg']);
			    }

	    	}

	    }

	    # Insert data
	    #
	    $files_data = scandir('./output/data/');

	    foreach($files_data as  $table_data)
	    {

	    	if ( $table_data != '.' And $table_data != '..')
	    	{

	    		$file_data = file_get_contents( "./output/data/" . $table_data);

	    		$file_data = str_replace( "\'", "''", $file_data);

	    		$params_data['query'] = $file_data;
			    $params_data['params'] = array();


			    $return = PDOClass2::Execute( $params_data, $this->connection_pgsql);

	    		echo "Insert data into table: " . $table_data . EOF;

	    	}

	    }


	    ## Import sequences
	    $files_sequences = scandir('./output/sequences/');

	    foreach($files_sequences as  $value_sequence)
	    {

	    	if ( $value_sequence != '.' And $value_sequence != '..')
	    	{

		    	$file_sequences = file_get_contents( "./output/sequences/" . $value_sequence);

		    	$file_array = explode( ";", $file_sequences);

					foreach ( $file_array as $seq)
					{

						if ( trim( $seq) != "")
						{

							$params_sq['query'] = trim( $seq);
			    		$params_sq['params'] = array();

			    		$return = PDOClass2::Execute( $params_sq, $this->connection_pgsql);

			    		echo "Create  sequence: " . $seq . EOF;
						}
					}

			    //print_r( json_encode( $return) . EOF);

	    	}

	    }


	  }
	  else
	  {

	  	echo "

	  		The files are in the directories:

	  				output/data/
	  				output/sequences/
	  				output/tables/

	  	";
	  	die;

	  }

	}



	public function AAA()
	{

		$_config = ConfigClass::get("config.database")['postgres'];
		$conn_arr = PDOClass2::Connection( $_config);

  	if ( $conn_arr['success'] == true)
  	{
  		$this->connection_pgsql =  $conn_arr['data'];
  	}
  	else
  	{
  		throw new Exception("Error Processing connection_pgsql", 1);
  	}

		$files_sequences = scandir('./output/sequences/');

    foreach($files_sequences as  $value)
    {

    	if ( $value != '.' And $value != '..')
    	{

	    	$file = file_get_contents( "./output/sequences/" . $value);

	    	$file_array = explode( ";", $file);

				foreach ( $file_array as $seq)
				{
					print_r( $seq);
					if ( trim( $seq) != "")
					{

						$params_sq['query'] = trim( $seq);
		    		$params_sq['params'] = array();

		    		$return = PDOClass2::Execute( $params_sq, $this->connection_pgsql);

					}
				}

		    print_r( json_encode( $return) . EOF);

    	}

    }

	}


}