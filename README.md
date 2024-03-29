# mysql-to-posgresql-migrator
Script to migrate MySql database to postgreSQL database
<br><br>
spanish article https://www.netveloper.com/migrar-una-base-de-datos-de-mysql-a-postgresql

<h3>Requeriments</h3>
Create directories
<ul>
<li><b>mkdir output/data -p</b> for data files
<li><b>mkdir output/tables -p</b> for table scripts
<li><b>mkdir output/sequences -p</b> for the sequence scripts
</ul>

Needs database in postgreSQL created

Needs to executed in MySql Server because uses "INTO OUTFILE" and save files into /tmp directory

Execute script with user <b>root</b> because need change permission in <b>output/data</b> for the postgres user

Need too, modify permision in <b>/etc/postgresql/12/main/pg_hba.conf</b> and create a new line same this 
<pre>
<code>
  local   all             postgres_user                                   trust
</code>
</pre>
<b>postgres_user</b> is the same name of database postgresql user

<h3>How to run the script</h3>

<pre>
<code>php init.php MySqlToPostgreSQL Init 'import_sql=true'</code>
</pre>

When the script is executed, it creates the migration files in the directories:
<ul>
<li><b>output/data</b> for data files
<li><b>output/tables</b> for table scripts
<li><b>output/sequences</b> for the sequence scripts
</ul>

Sequences start at the last MySql autoincrement value

If the parameter 'import_sql = true' exists, the scripts will be executed in postgreSQL.
If this parameter does not exist or it's false, the scripts are not executed and the migration will have to be run manually, executing the scripts one by one.

<h3>Script configuration</h3>

In the file <b>config/config.php</b> is the configuration of the databases and the paths where the final files are left.

<h3>Database configuration</h3>
<pre><code>
'mysql' => array(
  'dsn' => 'mysql:host=127.0.0.1;dbname=PRUEBAS;charset=utf8mb4',
  'hostname' => '127.0.0.1',
  'username' => 'root',
  'password' => 'MyPassWord',
  'dbname' => 'PRUEBAS',
),

'postgres' => array(
  'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=PRUEBAS',
  'hostname' => '127.0.0.1',
  'username' => 'postgres',
  'password' => 'MyPassWord',
  'dbname' => 'PRUEBAS',
),
</pre></code>
The database on the postgreSQL server must exist for all the migration to be carried out automatically and the database must be called as the parameter <b>'dbname' => 'PRUEBAS'</b>, in this case it would be called tests.

The database name in dsn string has to be the same as <b>dbname</b> parameter

The file where all the migration logic it's in <b>modules/MySqlToPostgreSQL.php</b> so that you can check it.



