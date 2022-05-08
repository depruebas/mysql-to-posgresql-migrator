<?php

#
# Common class for all classes
# Common methods that can to be used in all or some of the classes, for not repeat code
#

class CommonClass
{

	protected function GenericConnection( $config)
	{

		$conn_arr = PDOClass2::Connection( $config);

  	if ( $conn_arr['success'] == true)
  	{
  		return( $conn_arr['data']);
  	}
  	else
  	{
  		throw new Exception("Database '" . $config['dbname'] . "' not exists - Error Processing connection: " . $config['dsn'], 1);
  		die;

  		# Not exists database

  	}

	}

}