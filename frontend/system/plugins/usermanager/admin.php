<?php
   global $db; 
   $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
   if (!$authorized)
      exit;
   }
   if ($action == 'delete')
      $sql = "DELETE FROM CMS_USERS WHERE id = '$id'";
      mysqli_query($db, $sql) or die(mysqli_error($db));
      $action = "";
      $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
      if (!preg_match("/^.+@.+\..+$/", $email))
      {
         die ("Invalid email address!<br><a href='".MAIN_SCRIPT."'>back</a>");
      }
      $username = preg_replace('/[^a-z0-9A-Z]/', '', $username);
      $fullname = addslashes($fullname);
      if ($action == 'create')
         $sql = "SELECT cms_username FROM CMS_USERS WHERE cms_username = '$username'";
         $result = mysqli_query($db, $sql);
         else
         {
         }
      }  
      else
      {
         $sql = "UPDATE CMS_USERS SET `cms_username` = '$username', ";
      }
      $action = "";
   if (!empty($action))
         if ($action == 'edit')
         {
         }
      echo "<p><a href=\"" . MAIN_SCRIPT . "&amp;action=new\">Create new user</a></p>\n";
      echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
      $sql = "SELECT * FROM CMS_USERS";
   }