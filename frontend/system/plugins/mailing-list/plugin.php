<?php

$plugin = array(
	'name' => 'Mailing List',
	'description' => 'Collect and manage email in mailing List',
	'admin' => array(
		'init' => array('function' => 'mailinglist_init'),
		'update' => array('function' => 'mailinglist_update'),
		'tab' => array('name' => 'Mailing List', 'function' => 'mailinglist_tab')
		),
	'events' => array('onAfterContent' => 'mailinglist_after_content')
	);

define("MAILINGLIST_TITLE", "Mailing list");
define("MAILINGLIST_INVALID_EMAIL", "&bull; Please enter a valid email address.");
define("MAILINGLIST_INVALID_NAME", "&bull; Please enter your name.");
define("MAILINGLIST_INVALID_PHONE", "&bull; Please enter your phone number.");
define("MAILINGLIST_INVALID_LINK", "Invalid link!");
define("MAILINGLIST_EMAIL_EXIST", "&bull; Your email address is already in the mailing list!");
define("MAILINGLIST_THANKYOU", "Thank you for your submission. You will receive a confirmation email within a few minutes.");
define("MAILINGLIST_CONFIRM", "Thank you, your email has been added to the mailing list.");

define("MAILINGLIST_NAME", "Name:");
define("MAILINGLIST_EMAIL", "Email:");
define("MAILINGLIST_PHONE", "Phone number:");
define("MAILINGLIST_SUBMIT", "Submit");

function mailinglist_after_content($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_MAILINGLIST WHERE page_id=".$id;
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   if (!$data)
      return;

   $mailinglist_enabled = $data['mailinglist_enabled'];
   if ($mailinglist_enabled == 0)
      return;

   $mailinglist_from = $data['mailinglist_from'];
   $mailinglist_display_name = $data['mailinglist_display_name'];
   $mailinglist_display_phone = $data['mailinglist_display_phone'];

   $error = '';
   if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'mailinglist_submit')
   {
      $mailinglist_name = '';
      $mailinglist_email = '';
      $mailinglist_phone = '';

      if (isset($_REQUEST['mailinglist-email']))
      {
         if (preg_match("/^.+@.+\..+$/", $_REQUEST['mailinglist-email']))
         {
            $mailinglist_email = $_REQUEST['mailinglist-email'];
         }
         else
         {
            $error .= MAILINGLIST_INVALID_EMAIL."<br>";
         }
      }
      else
      {
         $error .= MAILINGLIST_INVALID_EMAIL."<br>";
      }
      if ($mailinglist_display_name)
      {
         if (isset($_REQUEST['mailinglist-name']) && $_REQUEST['mailinglist-name'] != '')
         {
            $mailinglist_name = $_REQUEST['mailinglist-name'];
         }
         else
         {
            $error .= MAILINGLIST_INVALID_NAME."<br>";
         }
      }
  
      if ($mailinglist_display_phone)
      {
         if (isset($_REQUEST['mailinglist-phone']) && $_REQUEST['mailinglist-phone'] != '')
         {
            $mailinglist_phone = $_REQUEST['mailinglist-phone'];
         }
         else
         {
            $error .= MAILINGLIST_INVALID_PHONE."<br>";    
         }
      }
      if ($error == '')
      {
         $sql = "SELECT * FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE page_id='$id' AND mailinglist_email='$mailinglist_email'";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $error = MAILINGLIST_EMAIL_EXIST."<br>";
         }
         else
         {
            $hash = mt_rand().mt_rand().mt_rand();
            $sql = "INSERT CMS_MAILINGLIST_SUBSCRIBERS (`mailinglist_name`, `mailinglist_email`, `mailinglist_phone`, `mailinglist_hash`, `mailinglist_status`, `page_id`) VALUES ('$mailinglist_name', '$mailinglist_email', '$mailinglist_phone', '$hash', '0', '$id')";
            mysqli_query($db, $sql) or die(mysqli_error($db));
            $error = MAILINGLIST_THANKYOU;

            $message = "You have just subscribed to the mailing list on: http://";
            $message .= $_SERVER['SERVER_NAME'];
            $message .= "\nTo avoid spam, we perform email verification.\nTo activate your post, please click on the following link:\n";
            $message .= "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?page=$id&action=mailinglist_confirm&hash=$hash";

            $subject = "Mailing list verification required";
            $header  = 'From: '.$mailinglist_from."\n";
            $header .= 'Reply-To: '.$mailinglist_from."\n";
            $header .= 'MIME-Version: 1.0'."\n";
            $header .= 'X-Mailer: PHP v'.phpversion()."\n";

            mail($mailinglist_email, $subject, $message, $header);
         }
      }
   }
   else
   if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'mailinglist_confirm' && isset($_REQUEST['hash']))
   {
      $hash = mysqli_real_escape_string($db, $_REQUEST['hash']);
      $sql = "SELECT * FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE mailinglist_hash='$hash'";
      $result = mysqli_query($db, $sql);
      if (mysqli_num_rows($result) != 0)
      {
         $sql = "UPDATE CMS_MAILINGLIST_SUBSCRIBERS SET `mailinglist_status`='1' WHERE mailinglist_hash = '$hash'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
         $cms_content = MAILINGLIST_CONFIRM;
         return;
      }
      else
      {
         $cms_content = MAILINGLIST_INVALID_LINK;
         return;
      }
   }
 
   $cms_content .= "<h3>".MAILINGLIST_TITLE."</h3>\n";
   if ($error)
   { 
      $cms_content .= $error;
   }
   $cms_content .= "<form action=\"".MAIN_SCRIPT."\" method=\"post\">\n";
   $cms_content .= "<input name=\"page\" type=\"hidden\" value=\"".$id."\">\n";
   $cms_content .= "<input name=\"action\" type=\"hidden\" value=\"mailinglist_submit\">\n";
   $cms_content .= "<table style=\"width:100%\">\n";
   if ($mailinglist_display_name)
   {
      $cms_content .= "<tr><td>".MAILINGLIST_NAME."</td><td><input class=\"cms_input\" name=\"mailinglist-name\" style=\"box-sizing:border-box;width:100%\"></td></tr>\n";
   }
   $cms_content .= "<tr><td>".MAILINGLIST_EMAIL."</td><td><input class=\"cms_input\" type=\"email\" name=\"mailinglist-email\" style=\"box-sizing:border-box;width:100%\"></td></tr>\n";
   if ($mailinglist_display_phone)
   {
      $cms_content .= "<tr><td>".MAILINGLIST_PHONE."</td><td><input class=\"cms_input\" name=\"mailinglist-phone\" style=\"box-sizing:border-box;width:100%\"></td></tr>\n";
   }
   $cms_content .= "<tr><td>&nbsp;</td><td><input class=\"cms_button\" name=\"submit\" type=\"submit\" value=\"".MAILINGLIST_SUBMIT."\"></td></tr>\n";
   $cms_content .= "</table>\n</form>\n";
}

function mailinglist_tab()
{
   global $id, $db, $plugin;

   $mailinglist_id = isset($_REQUEST['mailinglistid']) ? $_REQUEST['mailinglistid'] : -1;
   $mailinglist_action = isset($_REQUEST['mailinglist-action']) ? $_REQUEST['mailinglist-action'] : '';
   $anchor = "#tab-MailingList";
   $plugin_dir = basename(dirname(__FILE__));
 
   $mailinglist_enabled = 0;
   $mailinglist_display_name = 1;
   $mailinglist_display_phone = 1;
   $mailinglist_from = 'do-not-reply@website.com';

   $html = '';
   if ($mailinglist_action == 'edit' || $mailinglist_action == 'new')
   {
      $mailinglist_name = '';
      $mailinglist_email = '';
      $mailinglist_phone = '';

      if ($mailinglist_id >= 0)
      {
         $sql = "SELECT * FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE id = '$mailinglist_id'";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $mailinglist_name = $data['mailinglist_name'];
            $mailinglist_email = $data['mailinglist_email'];
            $mailinglist_phone = $data['mailinglist_phone'];
         }
      }
      $html .= "<script type=\"text/javascript\">\n";
      $html .= "$(document).ready(function()\n";
      $html .= "{\n";
      $html .= "   $('input[name=mailinglist-save]').click(function(e)\n";
      $html .= "   {\n";
      $html .= "      e.preventDefault();\n";
      $html .= "      jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"mailinglist-save\", mailinglistid: \"$mailinglist_id\", pageid: \"$id\", mailinglist_name: $('input[name=mailinglist-name]').val(), mailinglist_email: $('input[name=mailinglist-email]').val() , mailinglist_phone: $('input[name=mailinglist-phone]').val() }, function(result)\n";
      $html .= "      {\n";
      $html .= "         window.location = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      });\n";
      $html .= "   });\n";
      $html .= "});\n";
      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
      $html .= "<tr>\n";
      $html .= "<td><label>Name:</label></td><td><input class=\"form-control\" type=\"text\" name=\"mailinglist-name\" value=\"" .$mailinglist_name. "\" style=\"width:100%;\"></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "<td><label>Email:</label></td><td><input class=\"form-control\" type=\"text\" name=\"mailinglist-email\" value=\"" .$mailinglist_email. "\" style=\"width:100%;\"></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "<td><label>Phone:</label></td><td><input class=\"form-control\" type=\"text\" name=\"mailinglist-phone\" value=\"" .$mailinglist_phone. "\" style=\"width:100%;\"></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "<td>&nbsp;</td><td>\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" name=\"mailinglist-save\" value=\"Save Subscriber\">\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" value=\"Back to overview\" onclick=\"window.location='" .MAIN_SCRIPT."?action=edit&id=".$id.$anchor."'\">\n";
      $html .= "</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
   }
   else
   if ($mailinglist_action == 'message')
   {
      $sql = "SELECT * FROM CMS_MAILINGLIST WHERE page_id='$id'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $mailinglist_from = $data['mailinglist_from'];
      }
      $html .= "<script type=\"text/javascript\">\n";
      $html .= "$(document).ready(function()\n";
      $html .= "{\n";
      $html .= "   $('input[name=mailinglist-sendmail]').click(function(e)\n";
      $html .= "   {\n";
      $html .= "      e.preventDefault();\n";
      $html .= "      $('input[name=mailinglist-sendmail]').attr('disabled', 'disabled');\n";
      $html .= "      jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"mailinglist-sendmail\", pageid: \"$id\", mailinglist_from: \"$mailinglist_from\", mailinglist_subject: $('input[name=mailinglist-subject]').val(), mailinglist_body: $('textarea[name=mailinglist-body]').val() }, function(result)\n";
      $html .= "      {\n";
      $html .= "         alert(result);\n";
      $html .= "      });\n";
      $html .= "   });\n";
      $html .= "});\n";
      $html .= "</script>\n";
      $html .= "<table>\n";
      $html .= "<tr><td><label>Subject:</label></td><td><input class=\"form-control\" name=\"mailinglist-subject\" style=\"width:100%;\"></td></tr>\n";
      $html .= "<tr><td><label>Message:</label></td><td><textarea class=\"form-control\" name=\"mailinglist-body\" style=\"width:100%;\" cols=\"100\" rows=\"10\"></textarea></td></tr>\n";

      $html .= "<tr>\n";
      $html .= "<td>&nbsp;</td><td>\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" name=\"mailinglist-sendmail\" value=\"Send Message\">\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" value=\"Back to overview\" onclick=\"window.location='" .MAIN_SCRIPT."?action=edit&id=".$id.$anchor."'\">\n";
      $html .= "</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
   }
  else
   {
      $sql = "SELECT * FROM CMS_MAILINGLIST WHERE page_id='$id'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $mailinglist_enabled = $data['mailinglist_enabled'];
         $mailinglist_from = $data['mailinglist_from'];
         $mailinglist_display_name = $data['mailinglist_display_name'];
         $mailinglist_display_phone = $data['mailinglist_display_phone'];
      }
 
      $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
      $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable mailinglist for this page:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"mailinglist-enabled\" size=\"1\"><option value=\"0\"";
      if ($mailinglist_enabled == 0)
      {
         $html .= " selected";
      }
      $html .= ">disabled</option><option value=\"1\"";
      if ($mailinglist_enabled == 1)
      {
         $html .= " selected";
      }
      $html .= ">enabled</option></select></td></tr>";
      $html .= "<tr><td><label>Email from:</label></td><td><input class=\"form-control\" name=\"mailinglist-from\" style=\"width:100%;\" value=\"$mailinglist_from\"></td></tr>";

      $html .= "<tr><td><label>Display name field:</label></td><td><select class=\"form-control\" name=\"mailinglist-display-name\" size=\"1\"><option value=\"0\"";
      if ($mailinglist_display_name == 0)
      {
         $html .= " selected";
      }
      $html .= ">No</option><option value=\"1\"";
      if ($mailinglist_display_name == 1)
      {
         $html .= " selected";
      }
      $html .= ">Yes</option></select></td></tr>";

      $html .= "<tr><td><label>Display phone field:</label></td><td><select class=\"form-control\" name=\"mailinglist-display-phone\" size=\"1\"><option value=\"0\"";
      if ($mailinglist_display_phone == 0)
      {
         $html .= " selected";
      }
      $html .= ">No</option><option value=\"1\"";
      if ($mailinglist_display_phone == 1)
      {
         $html .= " selected";
      }
      $html .= ">Yes</option></select></td></tr>";

      $html .= "</table><br>\n";

      $html .= "<p><a href=\"".MAIN_SCRIPT."?action=edit&id=".$id."&mailinglist-action=new".$anchor."\">Add New Subscriber</a>&nbsp;&nbsp;<a href=\"".MAIN_SCRIPT."?action=edit&id=".$id."&mailinglist-action=message".$anchor."\">Send Message to Subscribers</a></p>";

      $sql = "SELECT * FROM CMS_MAILINGLIST_SUBSCRIBERS WHERE page_id='$id' ORDER BY mailinglist_email ASC";
      $result = mysqli_query($db, $sql);

      $html .= "<script type=\"text/javascript\">\n";
      $html .= "function mailinglist_delete(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"mailinglist-delete\", mailinglistid: id }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";
      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n<tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Action</th></tr>\n";

      $num_rows = mysqli_num_rows($result);
      if ($num_rows == 0)
      {
         $html .= "<tr><td>No subscribers yet</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
      else 
      {
         while ($data = mysqli_fetch_array($result))
         {
            $html .= "<tr><td>";
            $html .= htmlspecialchars($data['mailinglist_name']);
            $html .= "</td><td>";
            $html .= htmlspecialchars($data['mailinglist_email']);
            $html .= "</td><td>";
            $html .= htmlspecialchars($data['mailinglist_phone']);
            $html .= "</td><td>";
            if ($data['mailinglist_status'])
            {
               $html .= "Verified";
            }
 	    else 
            {
               $html .= "Not verified";
            }
            $html .= "</td><td><a href=\"./".MAIN_SCRIPT."?action=edit&amp;id=".$id."&amp;mailinglistid=".$data['id']."&amp;mailinglist-action=edit".$anchor;
            $html .= "\">Edit</a> | <a href=\"javascript:mailinglist_delete(";
            $html .= $data['id'];
            $html .= ")\">Delete</a></td></tr>\n";
         }
      }
      $html .= "</table>\n";
   }
   return $html; 
}

function mailinglist_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_MAILINGLIST (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              mailinglist_enabled INT, 
              mailinglist_from TEXT, 
              mailinglist_display_name INT, 
              mailinglist_display_phone INT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));

      $sql = "CREATE TABLE IF NOT EXISTS CMS_MAILINGLIST_SUBSCRIBERS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              mailinglist_name TEXT,
              mailinglist_email TEXT, 
              mailinglist_phone TEXT, 
              mailinglist_hash TEXT, 
              mailinglist_status INT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function mailinglist_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['mailinglist-enabled']) && 
          isset($_REQUEST['mailinglist-from']) &&
          isset($_REQUEST['mailinglist-display-name']) &&
          isset($_REQUEST['mailinglist-display-phone']))
      {
         $mailinglist_enabled = intval($_REQUEST['mailinglist-enabled']);
         $mailinglist_from = $_REQUEST['mailinglist-from'];
         $mailinglist_display_name = intval($_REQUEST['mailinglist-display-name']);
         $mailinglist_display_phone = intval($_REQUEST['mailinglist-display-phone']);

         if (get_magic_quotes_gpc())
         {
            $mailinglist_from = stripslashes($mailinglist_from);
         }
         $mailinglist_from = mysqli_real_escape_string($db, $mailinglist_from);

         $sql = "SELECT * FROM CMS_MAILINGLIST WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_MAILINGLIST (`mailinglist_enabled`, `mailinglist_from`, `mailinglist_display_name`, `mailinglist_display_phone`, `page_id`) VALUES ('$mailinglist_enabled', '$mailinglist_from', '$mailinglist_display_name', '$mailinglist_display_phone', '$id');";
         else
            $sql = "UPDATE CMS_MAILINGLIST SET mailinglist_enabled='$mailinglist_enabled', mailinglist_from='$mailinglist_from',mailinglist_display_name='$mailinglist_display_name', mailinglist_display_phone='$mailinglist_display_phone' WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}
?>