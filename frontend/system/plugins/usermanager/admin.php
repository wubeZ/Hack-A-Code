<?php
   global $db; 
   $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
   if (!$authorized)   {
      exit;
   }
   if ($action == 'delete')   {
      $sql = "DELETE FROM CMS_USERS WHERE id = '$id'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
      $action = "";   }   else   if ($action == 'create' || $action == 'update' )   {
      $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';      $fullname = isset($_REQUEST['fullname']) ? $_REQUEST['fullname'] : '';      $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';      $active = isset($_REQUEST['active']) ? $_REQUEST['active'] : 0;
      if (!preg_match("/^.+@.+\..+$/", $email))
      {
         die ("Invalid email address!<br><a href='".MAIN_SCRIPT."'>back</a>");
      }
      $username = preg_replace('/[^a-z0-9A-Z]/', '', $username);
      $fullname = addslashes($fullname);
      if ($action == 'create')      {
         $sql = "SELECT cms_username FROM CMS_USERS WHERE cms_username = '$username'";
         $result = mysqli_query($db, $sql);         if ($data = mysqli_fetch_array($result))         {              die ("This user already exists!<br><a href='".MAIN_SCRIPT."'>back</a>");         }
         else
         {            $crypt_pass = md5($_POST['password']);            $sql = "INSERT CMS_USERS (`cms_username`, `cms_password`, `cms_fullname`, `cms_email`, `cms_active`) VALUES ('$username', '$crypt_pass', '$fullname', '$email', $active)";            mysqli_query($db, $sql) or die(mysqli_error($db));
         }
      }  
      else
      {
         $sql = "UPDATE CMS_USERS SET `cms_username` = '$username', ";         if (!empty($_POST['password']))         {            $crypt_pass = md5($_POST['password']);            $sql = $sql . "`cms_password` = '$crypt_pass',";         }         $sql = $sql . " `cms_fullname` = '$fullname', `cms_email` = '$email', `cms_active` = $active WHERE id = '$id'";         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
      $action = "";   }
   if (!empty($action))   {      if (($action == 'edit') || ($action == 'new'))      {         $username_value = '';         $fullname_value = '';         $email_value = '';         $active_value = '';
         if ($action == 'edit')
         {            $sql = "SELECT * FROM CMS_USERS WHERE id = '$id'";            $result = mysqli_query($db, $sql);            if ($data = mysqli_fetch_array($result))            {               $username_value = $data['cms_username'];               $fullname_value = $data['cms_fullname'];               $email_value = $data['cms_email'];               $active_value = $data['cms_active'];            }
         }         echo "<form action=\"" . MAIN_SCRIPT . "\" method=\"post\">\n";         echo "<table style=\"border-width:0;width:100%\">\n";         if ($action == 'new')         {            echo "<input type=\"hidden\" name=\"action\" value=\"create\">\n";         }         else         {            echo "<input type=\"hidden\" name=\"action\" value=\"update\">\n";         }         echo "<input type=\"hidden\" name=\"id\" value=\"". $id . "\">\n";         echo "<tr><td><label>Username:</label></td>\n";         echo "<td><input class=\"form-control\" type=\"text\" size=\"50\" style=\"width:100%\" name=\"username\" value=\"" . $username_value . "\"></td></tr>\n";         echo "<tr><td><label>Password:</label></td>\n";         echo "<td><input class=\"form-control\" type=\"password\" size=\"50\" style=\"width:100%\" name=\"password\" value=\"\"></td></tr>\n";         echo "<tr><td><label>Fullname:</label></td>\n";         echo "<td><input class=\"form-control\" type=\"text\" size=\"50\" style=\"width:100%\" name=\"fullname\" value=\"" . $fullname_value . "\"></td></tr>\n";         echo "<tr><td><label>Email:</label></td>\n";         echo "<td><input class=\"form-control\" type=\"text\" size=\"50\" style=\"width:100%\" name=\"email\" value=\"" . $email_value . "\"></td></tr>\n";         echo "<tr><td><label>Active:</label></td>\n";         echo "<td style=\"text-align:left\"><select class=\"form-control\" name=\"active\" size=\"1\"><option " . ($active_value == "0" ? "selected " : "") . "value=\"0\">Not active</option><option " . ($active_value != "0" ? "selected " : "") . "value=\"1\">Active</option></select></td></tr>\n";         echo "<tr><td>&nbsp;</td><td style=\"text-align:left\"><input class=\"btn\" type=\"submit\" name=\"cmdSubmit\" value=\"Save\">";         echo "&nbsp;&nbsp;";         echo "<input class=\"btn\" type=\"button\" name=\"cmdBack\" value=\"Back to overview\" onclick=\"location.href='" . MAIN_SCRIPT . "'\"></td></tr>\n";         echo "</table>\n";         echo "</form>\n";      }   }   else   {
      echo "<p><a href=\"" . MAIN_SCRIPT . "&amp;action=new\">Create new user</a></p>\n";
      echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";      echo "<tr><th>Username</th><th>Fullname</th><th>Email</th><th>Active</th><th>Action</th></tr>\n"; 
      $sql = "SELECT * FROM CMS_USERS";      $result = mysqli_query($db, $sql);      while ($data = mysqli_fetch_array($result))      {         echo "<tr>\n";         echo "<td>" . $data['cms_username'] . "</td>\n";         echo "<td>" . $data['cms_fullname'] . "</td>\n";         echo "<td>" . $data['cms_email'] . "</td>\n";         echo "<td>" . ($data['cms_active'] == "0" ? "not active" : "active") . "</td>\n";         echo "<td>\n";         echo "   <a href=\"" . MAIN_SCRIPT . "&amp;action=edit&id=" . $data['id'] . "\">Edit</a> | \n";         echo "   <a href=\"" . MAIN_SCRIPT . "&amp;action=delete&id=" . $data['id'] . "\">Delete</a>\n";         echo "</td>\n";         echo "</tr>\n";      }      echo "</table>\n";
   }?>