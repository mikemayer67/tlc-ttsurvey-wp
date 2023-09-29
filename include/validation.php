<?php
namespace TLC\TTSurvey;

function validate_and_adjust_username(&$name,&$error=null)
{
  $name = stripslashes($name);              // resolve escaped characters
  $name = trim($name);                      // trim leading/trailing whitespace
  $name = preg_replace('/\s+/',' ',$name);  // condense multiple whitespace
  $name = preg_replace('/\s/',' ',$name);   // only use ' ' for whitespace
  $name = preg_replace('/\'+/',"'",$name);  // condense multiple apostrophes
  $name = preg_replace('/-+/',"-",$name);   // condense multiple hyphens
  $name = preg_replace('/~+/',"~",$name);   // consense multiple tildes

  $names = explode(' ',$name);
  if(count($names)<2) {
    $error = "Names must contain both first and last names";
    return null;
  }

  $valid_first = "A-Za-z\x{00C0}-\x{00FF}";
  $invalid_first = "'~-";
  $valid = $valid_first . $invalid_first;
  foreach($names as $n)
  {
    $m = array();
    if(preg_match("/([^$valid])/",$n,$m))
    {
      $error = "Names cannot contain '$m[1]'";
      return null;
    }
    if(preg_match("/^([$invalid_first])/",$n,$m))
    {
      $error = "Names cannot start with '$m[1]'";
      return null;
    }
  }
  return true;
}

function validate_and_adjust_userid(&$userid,&$error=null)
{
  $userid = stripslashes($userid);  // resolve escaped characters
  $userid = trim($userid);          // trim leading/trailing whitespace

  if(strlen($userid)<8 || strlen($userid)>16) 
  {
    $error = "Userids must be between 8 and 16 characters";
    return null;
  } 
  if(preg_match("/\s/",$userid))
  {
    $error = "Userids cannot contain spaces";
    return null;
  }
  if(!preg_match("/^[a-zA-Z]/",$userid)) 
  {
    $error = "Userids must be begin with a lettter";
    return null;
  }
  if(!preg_match("/^[a-zA-Z][a-zA-Z0-9]+$/",$userid)) {
    $error = "Userids may only contain letters and numbers";
    return null;
  }
  return true;
}

function validate_and_adjust_password(&$password,&$error=null)
{
  $password = stripslashes($password);              // resolve escaped characters
  $password = trim($password);                      // trim leading/trailing whitespace
  $password = preg_replace('/\s+/',' ',$password);  // condense multiple whitespace
  $password = preg_replace('/\s/',' ',$password);   // only use ' ' for whitespace

  if(strlen($password)<8 || strlen($password)>128) 
  {
    $error = "Password must be between 8 and 16 characters";
    return null;
  } 
  if(!preg_match("/[a-zA-Z]/",$password))
  {
    $error = "Passwords must contain at least one letter";
    return null;
  }
  if(!preg_match("/^[a-zA-Z0-9 !@%^*_=~,.-]+$/",$password)) 
  {
    $error = "Invalid character in password";
    return null;
  }
  return true;
}

function validate_and_adjust_email(&$email,&$error=null)
{
  if($email) {
    $email = stripslashes($email);              // resolve escaped characters
    $email = trim($email);                      // trim leading/trailing whitespace
    $email = filter_var($email,FILTER_VALIDATE_EMAIL);
    if(!$email)
    {
      if(!$is_null) {$error = "Invalid email address";}
      return null;
    }
  }
  return true;
}

