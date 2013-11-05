<?php

// Function to test if string is valid JSON
function isJson($string) {
   json_decode($string);
   return (json_last_error() == JSON_ERROR_NONE);
}

// Helper function to format var_dump
function dump($variable) {
  echo "<pre>";
  var_dump($variable);
  echo "</pre>";
  exit(1);
}

// Function to clean input file generated by hydrasta
function cleanInputFile($filename){
  if(file_exists($filename)){
    $file = file_Get_contents($filename);

    // Strip header information
    $needle = 'JSON string:';
    $cleaning = strstr($file, $needle);
    if($cleaning){
      $first_instance = '{';
      $cleaning = strstr($cleaning, $first_instance);
      // Strip footer information
      $last_instance = strrpos($cleaning, '}');
      $json_string = substr_replace($cleaning, '', $last_instance+1, strlen($cleaning));
    } else {
      $json_string = $file;
    }
    // Seup JSON Object
    if(isJson($json_string)){
      $json = json_decode($json_string);
    }else {
      exit('File: '.$filename.' is not valid a valid JSON string');
    }
    return $json;
  }
  else {
    return FALSE;
  }
}

// Function to create processed directory
function processed_directory($directory = 'processed') {
  //Attempt to use private file system
  $path = variable_get('file_private_path');
  //if not set then use public
  if($path==NULL){
    $path = 'public://';
  } else {
    $path = 'private://';
  }
  $path .= $directory;
  $result = file_prepare_directory($path, FILE_CREATE_DIRECTORY);
  if($result){
    return $path;
  } else {
    return $result;
  }
}

// Function to grab all measurements for site of this particular measurement
// type
function loadSiteMeasurementByType($siteTax, $measurementTax) {

  $sql_query = 'SELECT  n.nid AS nid,
    n.title AS title,
    t.field_measurement_type_tid AS type,
    s.field_site_id_tid AS site
    FROM node AS n
    INNER JOIN field_data_field_measurement_type AS t ON n.nid = t.entity_id
    INNER JOIN field_data_field_site_id AS s ON n.nid = s.entity_id
    WHERE s.field_site_id_tid = :siteId
    AND t.field_measurement_type_tid = :measurementId';

  $result = db_query($sql_query, array('siteId' => $siteTax, 'measurementId' => $measurementTax));

  $nodes = array();

  foreach($result as $record) {
    $nodes[$record->title] = $record->nid;
  }
  return $nodes;
}

// Function to return Term ID. Drupal's internal function is too Query heavy
function getTID($termName, $vocabName) {
  $sql_query = "SELECT tid
    FROM taxonomy_term_data AS data
    INNER JOIN taxonomy_vocabulary AS vocab ON data.vid = vocab.vid
    WHERE vocab.machine_name= :vocabName
    AND  data.name = :termName";
  $result = db_query($sql_query, array('vocabName' => $vocabName, 'termName' => $termName));
  $tid = array();
  foreach ($result as $record) {
    $tid['tid'] = $record->tid;
  }

  return $tid;
}

