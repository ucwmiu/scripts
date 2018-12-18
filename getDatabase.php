<?php
  //to, co mamy
  $servername = "localhost";
  $username = "awt_live";
  $password = "Ea1,MUb<Kk_0";
  $dbname = "awt_live";
  $phantom = "";

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

  echo "{$phantom}CREATE DATABASE $dbname;<br><br>";
  echo "USE $dbname;<br><br>";
  foreach ($tables as $key => $table) {
    //load columns of curent table into an array columns
    echo "{$phantom}CREATE TABLE {$table}(<br>";
    $table_structure_query = "DESCRIBE $table";
    $table_structure_result = mysqli_query($connection, $table_structure_query);
    $types = array("" => "");
    $nulls = array("" => "");
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
    //inserting insgle row to $table
    while($row = mysqli_fetch_assoc($select_all_from_table_result)) {
      echo "{$phantom}INSERT INTO $table VALUES ( ";
      $i = 0;
      foreach ($row as $key => $value) {
        if($types[$key] == "int(11) unsigned" || $types[$key] == "int(11)" || $types[$key] == "tinyint(1)" || $types[$key] == "decimal(9,2)") {
          if($i != 0) echo ",";
          if($value) echo " {$value}";
          else if($nulls[$key] == "NULL") echo " NULL";
          else echo "''";
        }
        else {
          if($i != 0) echo ",";
          if($value) echo " '{$value}'";
          else if($nulls[$key] == "NULL") echo " NULL";
          else echo "''";
        }
        $i = 1;
        ;//echo "{$key} -> {$value}, typeof({$key}) -> {$types[$key]} | "
      }
      echo "{$phantom});<br><br>";
    }
  }

  $connection->close();

?>
