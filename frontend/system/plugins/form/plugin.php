<?php
$plugin=array(
	'name' => 'Form',
	'description' => 'Dynamically create forms online.',
	'admin' => array(
		'init' => array('function' => 'form_init'),
		'update' => array('function' => 'form_update'),
		'tab' => array('name' => 'Form', 'function' => 'form_tab')
	           ),
	'events' => array('onAfterContent' => 'form_after_content')
	);

define("FORM_INVALID_CAPTCHA", "CAPTCHA verification failed.");
define("FORM_FIELD_REQUIRED", " field is required.");
define("FORM_INVALID_EMAIL", " must be an email address.");
define("FORM_INVALID_OPTION", "You must choose one of the options in ");
define("FORM_SUBMIT", "Submit");

function form_after_content($id)
{
   global $cms_content, $db;
   $error = '';

   $sql = "SELECT * FROM CMS_FORM WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   $data = mysqli_fetch_array($result);
   if (!data)
      return;

   $form_enabled = $data['form_enabled'];
   if ($form_enabled == 0)
      return;

   $database_enabled = $data['database_enabled'];
   $email_enabled = $data['email_enabled'];
   $email_to = $data['email_to'];
   $email_from = $data['email_from'];
   $email_subject = $data['email_subject'];
   $email_message = $data['email_message'];   
   $captcha_enabled = $data['captcha_enabled'];
   $captcha_public_key = $data['captcha_public_key'];
   $captcha_private_key = $data['captcha_private_key'];

   if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'form_submit')
   {
      if ($captcha_public_key != '')
      {
         $recaptcha_valid = false;
         if (isset($_POST['g-recaptcha-response']))
         {
            $recaptcha_response = $_POST['g-recaptcha-response'];
            if (function_exists('curl_exec'))
            {
               $ch = curl_init();
               curl_setopt_array($ch, [
                  CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                  CURLOPT_POST => true,
                  CURLOPT_POSTFIELDS => ['secret' => $captcha_private_key, 'response' => $recaptcha_response],
                  CURLOPT_RETURNTRANSFER => true ]);
               $recaptcha = curl_exec($ch);
               curl_close($ch);
            }
            else
            {
               $recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captcha_private_key . '&response=' . $recaptcha_response);
            }
            $recaptcha_result = json_decode($recaptcha);
            if ($recaptcha_result->success == true)
            {
               $recaptcha_valid = true;
            }
         }
         if (!$recaptcha_valid)
         {
            $error .= FORM_INVALID_CAPTCHA;
            $error .= "<br>\n";
         }
      }
      $sql = "SELECT * FROM CMS_FORM_FIELDS WHERE page_id='$id' ORDER BY field_index ASC";
      $result = mysqli_query($db, $sql);
      $rows = array();
      while ($data = mysqli_fetch_array($result))
      {
         $rows[] = $data;
      }
      foreach($rows as $row)
      {
         $name = preg_replace('/[^a-zA-Z0-9_]/','',$row['field_name']);
      	 $value = ''; 
         if (isset($_POST[$name]))
         {
	    $value = $_POST[$name];
         }
         if ($row['field_required'] && !$value)
         {
            $error .= '&bull; "'.htmlspecialchars($row['field_name']).'"'.FORM_FIELD_REQUIRED.'<br>';
            continue;
         }
         if (!$value)
            continue;

         switch($row['field_type'])
         {
            case 'email':
            {
               if (preg_match("/^.+@.+\..+$/", $value))
                  continue;
               $error .= '&bull; "'.htmlspecialchars($row['field_name']).'"'.FORM_INVALID_EMAIL.'<br>';
               break;
            }
            case 'select':
            {
               $arr_options = explode("\n", htmlspecialchars($row['field_extra']));
               $found = false;
               foreach($arr_options as $option)
               {
                  if ($option == '')
                     continue;
                  if ($value == trim($option))
                  {
                     $found = true;
                  }
               }
               if ($found) 
                  continue;
               $error .= '&bull; '.FORM_INVALID_OPTION.'"'.htmlspecialchars($row['field_name']).'".<br>';
               break;
            }
         }
      }
 
      if (!$error) 
      {
         if ($email_enabled)
         {
            $message = $email_message . "\n";
            foreach($rows as $row)
            {
               $name = preg_replace('/[^a-zA-Z0-9_]/','', $row['field_name']);
               if (!isset($_POST[$name])) continue;
               
               $message .= $row['field_name'].": ";
               $message .= $_POST[$name];
               $message .= "\n";
            }
            $to = preg_replace('/^FIELD{|}$/', '', $email_to);
            $from = preg_replace('/^FIELD{|}$/', '', $email_from);

            if ($email_from != $from)
               $from = $_POST[preg_replace('/[^a-zA-Z0-9_]/','', $from)];

            if ($email_to != $to)
               $to = $_POST[preg_replace('/[^a-zA-Z0-9_]/','', $to)];

            mail($to, $email_subject, $message, "From: $from\nReply-to: $from");
         }
         if ($database_enabled)
         {
            $sql = "INSERT INTO CMS_FORM_DATA (`page_id`, `submit_date`) VALUES ('$id', now())";
            $result = mysqli_query($db, $sql);
            $form_data_id = mysqli_insert_id($db);
            foreach($rows as $row)
            {
               $name = preg_replace('/[^a-zA-Z0-9_]/','', $row['field_name']);
               if (isset($_POST[$name]))
                  $field_value = addslashes($_POST[$name]);
               else
                  $field_value = '';
               $field_name = addslashes($row['field_name']);
               $sql = "INSERT INTO CMS_FORM_VALUES (`form_data_id`, `field_name`, `field_value`) VALUES ('$form_data_id', '$field_name', '$field_value')";
               mysqli_query($db, $sql) or die(mysqli_error($db));
            }
         }
      }
   }

   if ($error)
   { 
      $cms_content .= $error;
   }

   if ($captcha_enabled)
   {
      $cms_content .= "\n<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>";
   }

   $cms_content .= "\n<form action=\"".MAIN_SCRIPT."\" method=\"post\">\n";
   $cms_content .= "<input name=\"page\" type=\"hidden\" value=\"".$id."\">\n";
   $cms_content .= "<input name=\"action\" type=\"hidden\" value=\"form_submit\">\n";

   $cms_content .= "<table style=\"width:100%\">\n";

   $sql = "SELECT * FROM CMS_FORM_FIELDS WHERE page_id='$id' ORDER BY field_index ASC";
   $result = mysqli_query($db, $sql);
   while ($data = mysqli_fetch_array($result))
   {
      $field_name = preg_replace('/[^a-zA-Z0-9_]/','', $data['field_name']);

      $cms_content .= "   <tr><td>";
      $cms_content .= htmlspecialchars($data['field_name']);
      $cms_content .= "</td><td>";

      if ($data['field_type'] == 'checkbox')
      {
         $cms_content .= "<input type=\"checkbox\" name=\"";
         $cms_content .= $field_name;
         $cms_content .= "\">";
      }
      else
      if ($data['field_type'] == 'email')
      {
         $cms_content .= "<input class=\"cms_input\" type=\"email\" name=\"";
         $cms_content .= $field_name;
         $cms_content .= "\" style=\"box-sizing:border-box;width:100%\">";
      }
      else
      if ($data['field_type'] == 'hidden')
      {
         $cms_content .= "<input type=\"hidden\" name=\"";
         $cms_content .= $field_name;
         $cms_content .= "\" value=\"";
         $cms_content .= htmlspecialchars($data['field_extra']);
         $cms_content .= "\">";
      }
      else
      if ($data['field_type'] == 'select')
      {
         $cms_content .= "<select class=\"cms_select\" name=\"";
         $cms_content .= $field_name;
         $cms_content .= "\" style=\"box-sizing:border-box;width:100%\">";
         $arr_options = explode("\n", htmlspecialchars($data['field_extra']));
         foreach ($arr_options as $option)
         {
            $cms_content .= "<option value=\"";
            $cms_content .= $option;
            $cms_content .= "\">";
            $cms_content .= $option;
            $cms_content .= "</option>";
         }
         $cms_content .= "</select>";
      }
      else
      if ($data['field_type'] == 'textarea')
      {
         $cms_content .= "<textarea class=\"cms_textarea\" name=\"";
         $cms_content .= $field_name;
         $cms_content .= "\" style=\"box-sizing:border-box;width:100%\"></textarea>";
      }
      else
      {
         $cms_content .= "<input class=\"cms_input\" type=\"text\" name=\"";
         $cms_content .= $field_name;
         $cms_content .= "\" style=\"box-sizing:border-box;width:100%\">";
      }
      $cms_content .= "</td></tr>\n";
   }
   if ($captcha_enabled)
   {
      $cms_content .= "   <tr><td>&nbsp;</td><td><div class=\"g-recaptcha\" data-sitekey=\"".$captcha_public_key."\"></div></td></tr>\n";
   }
   $cms_content .= "   <tr><td>&nbsp;</td><td><input class=\"cms_button\" name=\"submit\" type=\"submit\" value=\"".FORM_SUBMIT."\"></td></tr>\n";
   $cms_content .= "</table>\n</form>\n";
}

function form_tab()
{
   global $id, $db, $plugin;

   $field_id = isset($_REQUEST['fieldid']) ? $_REQUEST['fieldid'] : -1;
   $field_action = isset($_REQUEST['field-action']) ? $_REQUEST['field-action'] : '';
   $anchor = "#tab-Form";
   $plugin_dir = basename(dirname(__FILE__));
 
   $form_enabled = 0;
   $captcha_enabled = 0;
   $database_enabled = 0;
   $email_enabled = 1;
   $email_to = '';
   $email_from = '';
   $email_subject = '';
   $email_message = '';
   $captcha_public_key = '';
   $captcha_private_key = '';

   $html = '';

   if ($field_action == 'edit' || $field_action == 'new')
   {
      $field_name = '';
      $field_type = 'input';
      $field_extra = '';
      $field_required = 0;

      if ($field_id >= 0)
      {
         $sql = "SELECT * FROM CMS_FORM_FIELDS WHERE id = '$field_id'";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $field_name = $data['field_name'];
            $field_type = $data['field_type'];
            $field_extra = $data['field_extra'];
            $field_required = $data['field_required'];
         }
      }
      $html .= "<script type=\"text/javascript\">\n";
      $html .= "$(document).ready(function()\n";
      $html .= "{\n";
      $html .= "   $('input[name=field-save]').click(function(e)\n";
      $html .= "   {\n";
      $html .= "      e.preventDefault();\n";      
      $html .= "      jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"field-save\", fieldid: \"$field_id\", pageid: \"$id\", field_name: $('input[name=field-name]').val(), field_type: $('select[name=field-type]').val(), field_extra: $('textarea[name=field-extra]').val(), field_required: $('input:checkbox[name=field-required]:checked').val() ? 1 : 0 }, function(result)\n";
      $html .= "      {\n";
      $html .= "         window.location = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      });\n";
      $html .= "   });\n";
      $html .= "});\n";
      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
      $html .= "<tr>\n";
      $html .= "<td><label>Name:</label></td><td><input class=\"form-control\" type=\"text\" name=\"field-name\" value=\"" .htmlspecialchars($field_name). "\" style=\"width:100%\" size=\"50\"></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr>\n";
      $html .= "<td><label>Type:</label></td><td><select class=\"form-control\" name=\"field-type\" size=\"1\">";

      $fields_types = array('input', 'email', 'textarea', 'checkbox', 'select', 'hidden');
      foreach ($fields_types as $type)
      {
         $html .= "<option value=\"";
         $html .= $type;
         $html .= "\"";
         if ($type == $field_type)
         {
            $html .= " selected=\"selected\"";
         }
         $html .= ">";
         $html .= $type;
         $html .= "</option>";
      }
      $html .= "</select></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr>\n";
      $required = '';
      if ($field_required == 1)
      {
         $required = " checked=\"checked\"";
      }
      $html .= "<td><label>Required:</label></td><td><input type=\"checkbox\" name=\"field-required\"".$required."></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr>\n";
      $html .= "<td style=\"width:10%;white-space:nowrap;\"><label>Options (for 'select' only):&nbsp;&nbsp;</label></td><td><textarea class=\"form-control\" name=\"field-extra\" style=\"width:100%\" cols=\"50\" rows=\"5\">".htmlspecialchars($field_extra)."</textarea></td>\n";
      $html .= "</tr>\n";

      $html .= "<tr>\n";
      $html .= "<td>&nbsp;</td><td>\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" name=\"field-save\"value=\"Save Field\">\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" value=\"Back to overview\" onclick=\"window.location='" .MAIN_SCRIPT."?action=edit&id=".$id.$anchor."'\">\n";
      $html .= "</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
   }
   else
   {
      $sql = "SELECT * FROM CMS_FORM WHERE page_id='$id'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $form_enabled = $data['form_enabled'];
         $database_enabled = $data['database_enabled'];
         $email_enabled = $data['email_enabled'];
         $email_to = $data['email_to'];
         $email_from = $data['email_from'];
         $email_subject = $data['email_subject'];
         $email_message = $data['email_message'];   
         $captcha_enabled = $data['captcha_enabled'];
         $captcha_public_key = $data['captcha_public_key'];
         $captcha_private_key = $data['captcha_private_key'];
      }
 
      $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
      $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable form for this page:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"form-enabled\" size=\"1\"><option value=\"0\"";
      if ($form_enabled == 0)
      {
         $html .= " selected";
      }
      $html .= ">disabled</option><option value=\"1\"";
      if ($form_enabled == 1)
      {
         $html .= " selected";
      }
      $html .= ">enabled</option></select></td></tr>";
      $html .= "<tr><td><label>Enable database:</label></td><td><select class=\"form-control\" name=\"database-enabled\" size=\"1\"><option value=\"0\"";
      if ($database_enabled == 0)
      {
         $html .= " selected";
      }
      $html .= ">disabled</option><option value=\"1\"";
      if ($database_enabled == 1)
      {
         $html .= " selected";
      }
      $html .= ">enabled</option></select>&nbsp;&nbsp;<a href=\"".MAIN_SCRIPT."?plugin=".$plugin_dir."&amp;page_id=".$id."&amp;action=form-export\">Export to CSV</a></td></tr>";
      $html .= "<tr><td><label>Enable email:</label></td><td><select class=\"form-control\" name=\"email-enabled\" size=\"1\"><option value=\"0\"";
      if ($email_enabled == 0)
      {
         $html .= " selected";
      }
      $html .= ">disabled</option><option value=\"1\"";
      if ($email_enabled == 1)
      {
         $html .= " selected";
      }
      $html .= ">enabled</option></select></td></tr>";
      $html .= "<tr><td><label>Email recipient:</label></td><td><input class=\"form-control\" name=\"email-to\" style=\"width:100%;\" value=\"$email_to\"></td></tr>";
      $html .= "<tr><td><label>Email reply to:</label></td><td><input class=\"form-control\" name=\"email-from\" style=\"width:100%;\" value=\"$email_from\"></td></tr>";
      $html .= "<tr><td><label>Email subject:</label></td><td><input class=\"form-control\" name=\"email-subject\" style=\"width:100%;\" value=\"$email_subject\"></td></tr>";
      $html .= "<tr><td><label>Email message:</label></td><td><input class=\"form-control\" name=\"email-message\" style=\"width:100%;\" value=\"$email_message\"></td></tr>";
      $html .= "<tr><td><label>Enable reCAPTCHA:</label></td><td><select class=\"form-control\" name=\"captcha-enabled\" size=\"1\"><option value=\"0\"";
      if ($captcha_enabled == 0)
      {
         $html .= " selected";
      }
      $html .= ">disabled</option><option value=\"1\"";
      if ($captcha_enabled == 1)
      {
         $html .= " selected";
      }
      $html .= ">enabled</option></select></td></tr>";
      $html .= "<tr><td><label>reCAPTCHA v2 site key:</label></td><td><input class=\"form-control\" name=\"form-publickey\" style=\"width:100%;\" value=\"$captcha_public_key\"></td></tr>";
      $html .= "<tr><td><label>reCAPTCHA v2 secret key:</label></td><td><input class=\"form-control\" name=\"form-privatekey\" style=\"width:100%;\" value=\"$captcha_private_key\"></td></tr>";
      $html .= "</table><br>\n";

      $html .= "<p><a href=\"".MAIN_SCRIPT."?action=edit&id=".$id."&field-action=new".$anchor."\">Add New Field</a></p>";

      $sql = "SELECT * FROM CMS_FORM_FIELDS WHERE page_id='$id' ORDER BY field_index ASC";
      $result = mysqli_query($db, $sql);

      $html .= "<script type=\"text/javascript\">\n";
      $html .= "function field_delete(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"field-delete\", fieldid: id }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";
      $html .= "function field_moveup(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"field-moveup\", fieldid: id, pageid: \"$id\" }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";
      $html .= "function field_movedown(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"field-movedown\", fieldid: id, pageid: \"$id\" }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n<tr><th>Name</th><th>Type</th><th>Action</th></tr>\n";
      $num_rows = mysqli_num_rows($result);
      if ($num_rows == 0)
      {
         $html .= "<tr><td>No fields yet</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
      else 
      {
         while ($data = mysqli_fetch_array($result))
         {
            $html .= "<tr><td>";
            $html .= $data['field_name'];
            $html .= "</td><td>";
            $html .= $data['field_type'];
            $html .= "</td><td><a href=\"./".MAIN_SCRIPT."?action=edit&amp;id=".$id."&amp;fieldid=".$data['id']."&amp;field-action=edit".$anchor;
            $html .= "\">Edit</a> | <a href=\"javascript:field_delete(";
            $html .= $data['id'];
            $html .= ")\">Delete</a> | <a href=\"javascript:field_moveup(";
            $html .= $data['id'];
            $html .= ")\">Move Up</a> | <a href=\"javascript:field_movedown(";
            $html .= $data['id'];
            $html .= ")\">Move Down</a></td></tr>\n";
         }
      }
      $html .= "</table>\n";
   }
   return $html; 
}


function form_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_FORM (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              form_enabled INT, 
              captcha_enabled INT, 
              database_enabled INT, 
              email_enabled INT, 
              email_to TEXT, 
              email_from TEXT, 
              email_subject TEXT, 
              email_message TEXT, 
              captcha_public_key TEXT, 
              captcha_private_key TEXT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));

      $sql = "CREATE TABLE IF NOT EXISTS CMS_FORM_FIELDS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              field_index INT,
              field_name TEXT, 
              field_type TEXT, 
              field_extra TEXT, 
              field_required INT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));

      $sql = "CREATE TABLE IF NOT EXISTS CMS_FORM_DATA (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              submit_date TIMESTAMP, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));

      $sql = "CREATE TABLE IF NOT EXISTS CMS_FORM_VALUES (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              form_data_id INT, 
              field_name TEXT, 
              field_value TEXT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function form_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['form-enabled']) && 
          isset($_REQUEST['captcha-enabled']) &&
          isset($_REQUEST['database-enabled']) &&
          isset($_REQUEST['email-enabled']) &&
          isset($_REQUEST['email-to']) &&
          isset($_REQUEST['email-from']) &&
          isset($_REQUEST['email-subject']) &&
          isset($_REQUEST['email-message']) &&
          isset($_REQUEST['form-publickey']) &&
          isset($_REQUEST['form-privatekey']))
      {
         $form_enabled = intval($_REQUEST['form-enabled']);
         $captcha_enabled = intval($_REQUEST['captcha-enabled']);
         $database_enabled = intval($_REQUEST['database-enabled']);
         $email_enabled = intval($_REQUEST['email-enabled']);
         $email_to = $_REQUEST['email-to'];
         $email_from = $_REQUEST['email-from'];
         $email_subject = $_REQUEST['email-subject'];
         $email_message = $_REQUEST['email-message'];
         $captcha_public_key = $_REQUEST['form-publickey'];
         $captcha_private_key = $_REQUEST['form-privatekey'];
         if (get_magic_quotes_gpc())
         {
            $email_to = stripslashes($email_to);
            $email_from = stripslashes($email_from);
            $email_subject = stripslashes($email_subject);
            $email_message = stripslashes($email_message);
            $captcha_public_key = stripslashes($captcha_public_key);
            $captcha_private_key = stripslashes($captcha_private_key);
         }
         $email_to = mysqli_real_escape_string($db, $email_to);
         $email_from = mysqli_real_escape_string($db, $email_from);
         $email_subject = mysqli_real_escape_string($db, $email_subject);
         $email_message = mysqli_real_escape_string($db, $email_message);
         $captcha_public_key = mysqli_real_escape_string($db, $captcha_public_key);
         $captcha_private_key = mysqli_real_escape_string($db, $captcha_private_key);

         $sql = "SELECT * FROM CMS_FORM WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
	    $sql = "INSERT INTO CMS_FORM (`form_enabled`, `captcha_enabled`, `database_enabled`, `email_enabled`, `email_to`, `email_from`, `email_subject`, `email_message`, `captcha_public_key`, `captcha_private_key`, `page_id`) VALUES ('$form_enabled', '$captcha_enabled', '$database_enabled', '$email_enabled', '$email_to', '$email_from', '$email_subject', '$email_message', '$captcha_public_key', '$captcha_private_key', '$id');";
         else
            $sql = "UPDATE CMS_FORM SET form_enabled='$form_enabled', captcha_enabled='$captcha_enabled', database_enabled='$database_enabled', email_enabled='$email_enabled', email_to='$email_to', email_from='$email_from', email_subject='$email_subject', email_message='$email_message', captcha_public_key='$captcha_public_key', captcha_private_key='$captcha_private_key' WHERE page_id=$id";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}

?>