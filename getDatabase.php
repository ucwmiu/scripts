<?php

  /*
  I am assuming, that only numeric types are int(11), int(11) unsigned, tinyint(1) and decimal(9,2)
  */
  //your inputs...
  $servername = "localhost";
  $username = "username";
  $password = "password";
  $dbname = "database name";
  //if you want to add some numeric types, please insert them here
  $numeric = array("int(11)", "int(11) unsigned", "tinyint(1)", "decimal(9,2)");

  //create connect
  $connection = mysqli_connect($servername, $username, $password, $dbname);
  //if it fails
  if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $tables = array();
  $show_tables_query = "SHOW TABLES FROM $dbname";
  $show_tables_result = mysqli_query($connection,$show_tables_query);

  while ($row = mysqli_fetch_row($show_tables_result)) {
      $tables[] = $row[0];
  }

  echo "CREATE DATABASE $dbname;<br><br>";
  echo "USE $dbname;<br><br>";
  foreach ($tables as $key => $table) {
    //load columns of curent table into an array columns
    echo "CREATE TABLE {$table}(<br>";
    $table_structure_query = "DESCRIBE $table";
    $table_structure_result = mysqli_query($connection, $table_structure_query);
    $types = array("" => ""); //to check if input's type is numeric
    $nulls = array("" => ""); //and if input is nullable
    //add each table to the database...
    $ctrl = 0;

    while($field = mysqli_fetch_array($table_structure_result)) {
      $types[$field['Field']] = $field['Type'];
      if($ctrl != 0) echo ",";
      echo "{$field['Field']} {$field['Type']} ";
      if($field['Null'] == "NO") {
        echo "NOT NULL ";
        $nulls[$field['Field']] = "NOT NULL";
      }
      else {
        $nulls[$field['Field']] = "NULL";
        echo "NULL ";
      }
      echo "{$field['Extra']}<br>";
      if($field['Key'] == "PRI") {
        echo ",PRIMARY KEY ({$field['Field']}) ";
      }
      $ctrl = 1;
    }
    echo ");<br><br>" ;
    //now we can take data from $table
    $select_all_from_table_query = "SELECT * FROM $table";
    $select_all_from_table_result = mysqli_query($connection, $select_all_from_table_query);
    //inserting single row to $table
    while($row = mysqli_fetch_assoc($select_all_from_table_result)) {
      echo "INSERT INTO $table VALUES ( ";
      $i = 0;
      //need to check wheter field is nullable or not...
      foreach ($row as $key => $value) {
        if(in_array($numeric ,$types[$key])) {
          if($i != 0) echo ",";
          if($value) {
	  	echo " {$value}"; 
	  } else if($nulls[$key] == "NULL") {
		 echo " NULL";
	  } else { 
		echo "''";
	  }
        }
        else {
          if($i != 0) echo ",";
          if($value) {
		echo " '{$value}'";
	  } else if($nulls[$key] == "NULL") {
		 echo " NULL";
	  } else { 
		echo "''"; 
	  }
        }
        $i = 1;
      }
      echo ");<br>";
    }
  }

  $connection->close();

?>
