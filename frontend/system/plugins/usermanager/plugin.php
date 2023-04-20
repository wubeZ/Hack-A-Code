<?php

$plugin = array(
	'name' => 'User Manager',
	'description' => 'User Manager',
	'admin' => array(
		'init' => array('function' => 'usermanager_init'),
          	'menu' => array('User Manager' => 'admin')
		)
	);

function usermanager_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_USERS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              cms_username TEXT,
              cms_fullname TEXT,
              cms_password TEXT,
              cms_email TEXT,
              cms_active INT,
              cms_group INT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function usermanager_login()
{
   global $authorized, $db, $username;

   if (isset($_REQUEST['admin_username']) && isset($_REQUEST['admin_password']))
   {
      $sql = "SELECT cms_password, cms_active FROM CMS_USERS WHERE cms_username = '".mysqli_real_escape_string($db, $_REQUEST['admin_username'])."'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $crypt_pass = md5($_REQUEST['admin_password']);
         if ($crypt_pass == $data['cms_password'] && $data['cms_active'] != 0)
         {
            $authorized = true;
            $username = $_REQUEST['admin_username'];
            $_SESSION['cms_user'] = $username;
         }
      }
   }
}

?>