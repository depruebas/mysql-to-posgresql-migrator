<?php

class MySqlToPostgreSQL extends CommonClass
{


	private function SaveSequence( $data_sequence)
	{

		$tabla = $data_sequence['tabla'];
		$sequence_name = $data_sequence['tabla']."_id_seq";

		$sequence = "
			CREATE SEQUENCE public.".$sequence_name."
		    INCREMENT 1
		    START ".$data_sequence['last_id']."
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

	public function Init( $data = [])
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
		$connection_mysql = $this->GenericConnection( $_config);


  	$mysql_types = ConfigClass::get("equivalences.mysql_to_postgres");


  	$params['query'] = "SHOW Tables";
    $params['params'] = [];

    $rows_tables = PDOClass2::ExecuteQuery( $params, $connection_mysql);

    $schema_table = "";
    foreach ( $rows_tables['data'] as $key => $table)
    {

			$table_one = implode( "", $table);

    	$tables_in = $table_one;

    	//$data['tabla'] = $tables_in;

    	$params_fields['query'] = "DESCRIBE " . $tables_in;
	    $params_fields['params'] = [];


	    $rows_fields = PDOClass2::ExecuteQuery( $params_fields, $connection_mysql);


	    $schema_table = "CREATE TABLE public.".$tables_in." (";
	    foreach ($rows_fields['data'] as $field)
	    {

	    	$data['field']['name'] = $field['Field'];

	    	if ( $field['Extra'] == "auto_increment")
	    	{

	    		# Get las auto_increment value
	    		$params_auto['query'] = "SELECT AUTO_INCREMENT FROM information_schema.tables
	    													WHERE table_name = ? AND table_schema = ?";
			    $params_auto['params'] = [ $tables_in, $_config['dbname']];

			    $rows_auto = PDOClass2::ExecuteQuery( $params_auto, $connection_mysql);

			    $data_sequence['last_id'] = $rows_auto['data'][0]['AUTO_INCREMENT'];
			    $data_sequence['tabla'] =  $tables_in;

					$this->SaveSequence( $data_sequence);

	    	}

	    	if ( substr( $field['Type'], 0, 3) == 'int')
	    	{
	    		$field['Type'] = 'int';
	    	}

	    	if ( substr( $field['Type'], 0, 7) == 'varchar')
	    	{
	    		$field['Type'] = 'varchar';
	    	}

				if ( substr( $field['Type'], 0, 8) == 'smallint')
	    	{
	    		$field['Type'] = 'int';
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


	 		file_put_contents(  ConfigClass::get("config.ruta_logs")['tables'] . $tables_in . ".sql", $schema_table);

	    echo "Create table: " . $tables_in . EOF;

	    # Execute sql scripts
	    #
	    $file_inserts = ConfigClass::get("config.ruta_logs")['data'] .  $tables_in . ".csv";


			$file_dump = "mysql -u " . $_config['username'] . " -p" . $_config['password'] . " " . $_config['dbname'] . " -e \"SELECT * FROM " .  $tables_in . " INTO OUTFILE '/tmp/" .  $tables_in . ".csv' CHARACTER SET utf8 FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'\" ";

			//$file_dump = "mysqldump -h " . $_config['hostname'] . " -u " . $_config['username'] . " -p" . $_config['password'] . " --no-create-db --no-create-info --compact --skip-quote-names --default-character-set=utf8mb4 " . $_config['dbname'] . " " . $tables_in . " > " . $file_inserts ;

			exec( $file_dump, $output);
			
			exec( "mv /tmp/". $tables_in . ".csv " . $file_inserts);

			exec( "chown postgres.postgres " . $file_inserts, $output2);
			
				
		  echo "Export data from table: " . $tables_in . EOF;

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
			$connection_pgsql = $this->GenericConnection( $_config_pg);

			# Create tables
			#
			$files_tables = scandir(ConfigClass::get("config.ruta_logs")['tables']);


	    foreach($files_tables as  $value)
	    {

	    	if ( $value != '.' And $value != '..')
	    	{


		    	$file = file_get_contents(ConfigClass::get("config.ruta_logs")['tables'] . $value);

		    	$params_t['query'] = $file;
			    $params_t['params'] = [];

			    $return = PDOClass2::Execute( $params_t, $connection_pgsql);


			    if ( $return['success'] == "1")
			    {
			    	echo "Create  table: " . $value . EOF;
			    }
			    else
			    {
			    	echo ( "Error creating table " . $value . " - " . $return['data']['errormsg'] . EOF);
			    }

	    	}

	    }

	    # Insert data
	    #
	    $files_data = scandir(ConfigClass::get("config.ruta_logs")['data']);

	    foreach($files_data as  $table_data)
	    {

	    	if ( $table_data != '.' And $table_data != '..')
	    	{

	    		try
	    		{
						$table_postgres = explode( ".", $table_data);
						
						$file_postgres = ConfigClass::get("config.ruta_logs")['data'] . $table_data;

						exec("PGPASSWORD=" . $_config_pg['password'] . " psql -U " . $_config_pg['username'] . " -d " . $_config_pg['dbname'] . " -c \"COPY ".$table_postgres[0]." FROM '".$file_postgres."'\"", $output_pg);

		    		echo "Insert data into table: " . $table_data . EOF;
	    		}
	    		catch ( Exception $e)
	    		{
	    			print_r( $e);
						echo EOF;
	    		}

	    	}

	    }


	    ## Import sequences
	    $files_sequences = scandir(ConfigClass::get("config.ruta_logs")['sequences']);

	    foreach($files_sequences as  $value_sequence)
	    {

	    	if ( $value_sequence != '.' And $value_sequence != '..')
	    	{

		    	$file_sequences = file_get_contents( ConfigClass::get("config.ruta_logs")['sequences'] . $value_sequence);

		    	$file_array = explode( ";", $file_sequences);

					foreach ( $file_array as $seq)
					{

						if ( trim( $seq) != "")
						{

							$params_sq['query'] = trim( $seq);
			    		$params_sq['params'] = [];

			    		$return = PDOClass2::Execute( $params_sq, $connection_pgsql);

			    		echo "Create  sequence: " . $value_sequence . EOF;
						}
					}
	    	}
	    }

	    echo EOF . "End migration data " . EOF . EOF;

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

	  echo EOF . "And this is the End." . EOF;

	}



}