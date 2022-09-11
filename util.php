<?php
function validatePos() {
  $final = false;
  $posrank = 1;
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;

    $final = true;

    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];

    $_SESSION['year'.$posrank] = $year;
    $_SESSION['desc'.$posrank] = $desc;
    $posrank++;

    if ( strlen($year) == 0 || strlen($desc) == 0 ) {
      $final = "All fields are required";
    }

    if ( ! is_numeric($year) ) {
      $final = "Position year must be numeric";
    }
  }
  return $final;
}

function validateEdu() {
  $final = false;
  $edurank = 1;
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['edu_year'.$i]) ) continue;
    if ( ! isset($_POST['edu_school'.$i]) ) continue;

    $final = true;

    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];

    $_SESSION['edu_year'.$edurank] = $year;
    $_SESSION['edu_school'.$edurank] = $school;
    $edurank++;

    if ( strlen($year) == 0 || strlen($school) == 0 ) {
      $final = "All fields are required";
    }

    if ( ! is_numeric($year) ) {
      $final = "Year must be numeric";
    }
  }
  return $final;
}