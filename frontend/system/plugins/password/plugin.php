<?php

$plugin = array(
	'name' => 'Password',
	'description' => 'Protect page with a password',
	'admin' => array(
		'init' => array('function' => 'password_init'),
		'update' => array('function' => 'password_update'),
		'tab' => array('name' => 'Password', 'function' => 'password_tab')
		),
	'events' => array('onOverwriteContent' => 'password_override_content')
	);

define("PASSWORD_TITLE", "Please enter the password");
define("PASSWORD_PASSWORD", "Password:");
define("PASSWORD_SUBMIT", "Login");

function password_override_content($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_PASSWORD WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   $data = mysqli_fetch_array($result);
   if (!$data)
      return;

   $password_enabled = $data['password_enabled'];
   if (!$password_enabled)
      return;

   $session = preg_replace('/[^a-z0-9A-Z]/', '', $data['password']);
   $value = isset($_SESSION[$session]) ? $_SESSION[$session] : '';
  
   if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'password_login')
   {
      $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
      if ($password == $data['password'])
      {
         $value = md5($data['password']);
         $_SESSION[$session] = $value;
      }
   }
 
   if ($value != md5($data['password']))
   {
      $cms_content = "<h3>".PASSWORD_TITLE."</h3>\n";
      $cms_content .= "<form action=\"".MAIN_SCRIPT."\" method=\"post\">\n";
      $cms_content .= "<input name=\"page\" type=\"hidden\" value=\"$id\">\n";
      $cms_content .= "<input name=\"action\" type=\"hidden\" value=\"password_login\">\n";
      $cms_content .= "<table>\n<tr><td>".PASSWORD_PASSWORD."</td><td><input class=\"cms_input\" type=\"password\" name=\"password\"></td></tr>\n";
      $cms_content .= "<tr><td>&nbsp;</td><td><input class=\"cms_button\" name=\"login\" type=\"submit\" value=\"".PASSWORD_SUBMIT."\"></td></tr>\n";
      $cms_content .= "</table>\n</form>\n";
   } 
}

function password_tab()
{
   global $id, $db;

   $password_enabled = 0;
   $password = '';

   $sql = "SELECT * FROM CMS_PASSWORD WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $password_enabled = $data['password_enabled'];
      $password = $data['password'];
   }
 
   $html = '';
   $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
   $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable password:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"password-enabled\" size=\"1\"><option value=\"0\"";
   if ($password_enabled == 0)
   {
      $html .= " selected";
   }
   $html .= ">disabled</option><option value=\"1\"";
   if ($password_enabled == 1)
   {
      $html .= " selected";
   }
   $html .= ">enabled</option></select></td></tr>";
   $html .= "<tr><td><label>Password:</label></td><td><input class=\"form-control\" name=\"password\" size=\"25\" style=\"width:100%\" value=\"$password\"></td></tr>";

   $html .= "</table>\n";

   return $html;
}

function password_init()
{
   global $authorized, $db;
   if ($authorized)
   {
       $sql = "CREATE TABLE IF NOT EXISTS CMS_PASSWORD (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              password_enabled INT, 
              password TEXT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function password_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['password-enabled']) && 
          isset($_REQUEST['password']))
      {
         $password_enabled = intval($_REQUEST['password-enabled']);
         $password = $_REQUEST['password'];

         if (get_magic_quotes_gpc())
         {
            $password = stripslashes($password);
         }
         $password = mysqli_real_escape_string($db, $password);

         $sql = "SELECT * FROM CMS_PASSWORD WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_PASSWORD (`password_enabled`, `password`, `page_id`) VALUES ('$password_enabled', '$password','$id');";
         else
            $sql = "UPDATE CMS_PASSWORD SET password_enabled='$password_enabled', password='$password' WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysql_error($db));
      }
   }
}

?>