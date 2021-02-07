<?php


	return array(

    'mysql_to_postgres' => array(
      'int' => 'integer',
      'smallint' => 'smallint',
      'bigint' => 'bigint',
      'bit' => 'bit',
      'boolean' => 'boolean',
      'real' => 'float',
      'varchar' => 'character varying',
      'datetime' => 'timestamp',
      'timestamp' => 'timestamp',
      'text' => 'text',
    ),

	);


/*
SMALLINT	SMALLINT
BIGINT	BIGINT
SERIAL	INT	Sets AUTO_INCREMENT in its table definition.
SMALLSERIAL	SMALLINT	Sets AUTO_INCREMENT in its table definition.
BIGSERIAL	BIGINT	Sets AUTO_INCREMENT in its table definition.
BIT	BIT
BOOLEAN	TINYINT(1)
REAL	FLOAT
DOUBLE PRECISION	DOUBLE
NUMERIC	DECIMAL
DECIMAL	DECIMAL
MONEY	DECIMAL(19,2)*/


/*DROP TABLE IF EXISTS "areas";
CREATE TABLE "public"."areas" (
    "id" integer NOT NULL,
    "domain_id" integer NOT NULL,
    "parent_id" integer NOT NULL,
    "name" character varying(100) NOT NULL,
    "slug" character varying(50) NOT NULL,
    "image" character varying(200) NOT NULL,
    "created" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "enabled" integer NOT NULL,
    "orden" integer NOT NULL,
    "meta_description" text NOT NULL,
    "meta_keyworks" text NOT NULL
) WITH (oids = false);*/