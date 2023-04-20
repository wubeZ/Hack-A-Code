<?php

$plugin = array(
	'name' => 'Discussion',
	'description' => 'Discussion',
	'admin' => array(
		'init' => array('function' => 'discussion_init'),
		'update' => array('function' => 'discussion_update'),
		'tab' => array('name' => 'Discussion', 'function' => 'discussion_tab')
		),
	'events' => array('onAfterContent' => 'discussion_display_after')
	);

define("DISCUSSION_TITLE", "Discussion");
define("DISCUSSION_COMMENTS", "Comments:");
define("DISCUSSION_EMPTY", "No topics");
define("DISCUSSION_NONAME", "[no name]");
define("DISCUSSION_NOTOPIC", "[no topic name]");
define("DISCUSSION_ROOT", "root:");
define("DISCUSSION_PARENT", "parent:");
define("DISCUSSION_REPLY", "Reply to this");
define("DISCUSSION_NEW_TOPIC", "New Topic");
define("DISCUSSION_TOPIC_INDEX", "Topic Index");
define("DISCUSSION_INVALID_NAME", "&bull; Please enter your name.");
define("DISCUSSION_INVALID_SUBJECT", "&bull; Please enter a subject.");
define("DISCUSSION_INVALID_DESCRIPTION", "&bull; Please enter a description.");
define("DISCUSSION_INVALID_CAPTCHA", "&bull; CAPTCHA verification failed.");
define("DISCUSSION_ENTER_COMMENTS", "Enter your comments:");
define("DISCUSSION_NAME", "Name:");
define("DISCUSSION_SUBJECT", "Subject:");
define("DISCUSSION_DESCRIPTION", "Description:");
define("DISCUSSION_SUBMIT", "Submit");

function display_children($pageid, $topic_id=0, $level=0)
{
   global $cms_content, $db;

   $count = 0;
   if (empty($topic_id)) 
   { 
      $topic_id = 0; 
   }
   $sql = "SELECT * FROM CMS_DISCUSSION_TOPICS WHERE parent_id = '$topic_id' ORDER BY topic_date, id";
   $result = mysqli_query($db, $sql);
   while ($data = mysqli_fetch_array($result))
   {
      if ($level)
      {
         if (!$count)
         {
            $cms_content .= "<ul>\n";
         }
         $cms_content .= "<li>";
      }
      else
      {
         if (!$count)
         {
            $cms_content .= "<b>".DISCUSSION_COMMENTS."</b><br>\n";
         }
         $cms_content .= "<table>\n";
         $cms_content .= "<tr bgcolor=\"skyblue\">\n";
         $cms_content .= "<td colspan=\"2\" width=\"500\">\n";
      }
      $count++;
      $topic_author = htmlspecialchars($data['topic_author']);
      if ($topic_author == "") 
      {
         $topic_author = DISCUSSION_NONAME; 
      }
      if ($data['id'] != $topic_id)
      {
         $cms_content .= "<a href=\"";
         $cms_content .= MAIN_SCRIPT;
         $cms_content .= "?page=$pageid&amp;topic_id=";
         $cms_content .= $data['id'];
         $cms_content .= "\">";
         $cms_content .= htmlspecialchars($data['topic_subject']);
         $cms_content .= "</a> by <strong>$topic_author</strong>";
      }
      else
      {
         $cms_content .= htmlspecialchars($data['topic_subject']);
         $cms_content .= " by <b>$topic_author</b>";
      }  
      if ($level) 
      { 
         $cms_content .="</li>\n"; 
      } 
      else 
      { 
         $cms_content .= "</td>\n";
         $cms_content .= "</tr>\n";
         $cms_content .= "<tr>\n";
         $cms_content .= "<td width=\"2px\">&nbsp;</td>";
         $cms_content .= "<td width=\"498px\">";
         $cms_content .= htmlspecialchars($data['topic_description']);
         $cms_content .= "</td>";
         $cms_content .= "</tr>\n";
         $cms_content .= "</table>\n";
      }
      $cms_content .= display_children($pageid, $data['id'], $level+1);
   } 
   if ($level && $count)
   {
      $cms_content .= "</ul>\n";
   }
}

function display_topic($id, $topic_id=0, $show_children=1, $level=0)
{
   global $cms_content, $db;

   if (empty($topic_id)) 
   { 
      $topic_id = 0; 
   }
   $sql = "SELECT DISTINCT CURRENT.id, CURRENT.parent_id, CURRENT.root_id, CURRENT.topic_subject, CURRENT.topic_description, CURRENT.topic_author, CURRENT.topic_date, CURRENT.topic_modified";
   if (!$topic_id)
   {
      $sql .= " FROM CMS_DISCUSSION_TOPICS CURRENT, CMS_DISCUSSION_TOPICS CHILD WHERE CURRENT.id = CHILD.root_id AND CURRENT.page_id=$id";

      $result = mysqli_query($db, $sql);
      while ($data = mysqli_fetch_array($result))
      {
         $cms_content .= "<p><a href=\"";
         $cms_content .= MAIN_SCRIPT;
         $cms_content .= "?page=$id&amp;topic_id=";
         $cms_content .= $data['id'];
         $cms_content .= "\">";
         $cms_content .= htmlspecialchars($data['topic_subject']);
         $cms_content .= "</a></p>";
      }
      return array();
   }

   $sql .= ", PARENT.topic_subject as PARENT_NAME, ROOT.topic_subject AS ROOT_NAME FROM CMS_DISCUSSION_TOPICS CURRENT LEFT JOIN CMS_DISCUSSION_TOPICS AS PARENT ON CURRENT.parent_id = PARENT.id LEFT JOIN CMS_DISCUSSION_TOPICS AS ROOT ON CURRENT.root_id = ROOT.id WHERE CURRENT.id = $topic_id AND CURRENT.page_id=$id";

   $result = mysqli_query($db, $sql);
   $num_rows = mysqli_num_rows($result);
   if ($num_rows == 0)
   {
      $cms_content .= DISCUSSION_EMPTY;
      return array();
   }
   list($topic_id, $parent_id, $root_id, $topic_subject, $topic_description, $topic_author, $topic_date, $topic_modified, $parent_name, $root_name) = mysqli_fetch_row($result);
   if ($topic_author == "") 
   { 
      $topic_author = DISCUSSION_NONAME; 
   }
   if ($root_id != $topic_id && $root_id != $parent_id)
   {
      if ($root_name == "") 
      { 
         $root_name = DISCUSSION_NOTOPIC; 
      }
      $cms_content .= "<p><b>".DISCUSSION_ROOT."</b><a href=\"";
      $cms_content .= MAIN_SCRIPT;
      $cms_content .= "?page=$id&amp;topic_id=";
      $cms_content .= $root_id;
      $cms_content .= "\">";
      $cms_content .= $root_name;
      $cms_content .= "</a></p>";
   }
   if (!empty($parent_name))
   {
      $cms_content .= "<p><b>".DISCUSSION_PARENT."</b><a href=\"";
      $cms_content .= MAIN_SCRIPT;
      $cms_content .= "?page=$id&amp;topic_id=";
      $cms_content .= $parent_id;
      $cms_content .= "\">";
      $cms_content .= $parent_name;
      $cms_content .= "</a></p>";
   }
   $cms_content .= "<p><b>$topic_subject</b> by <b>$topic_author</b> on <b>$topic_date</b></p>";
   $cms_content .= "<p>$topic_description</p>";
   if ($show_children)
   {
      $cms_content .= "<p><a href=\"";
      $cms_content .= MAIN_SCRIPT;
      $cms_content .= "?page=$id&amp;action=discussion_new&amp;topic_id=";
      $cms_content .= $topic_id;
      $cms_content .= "\"><b>".DISCUSSION_REPLY."</b></a></p>";

      $cms_content .= "<p>";
      $cms_content .= display_children($id, $topic_id, $level);
      $cms_content .= "</p>";
   }
   return array($root_id, $parent_id, $topic_subject);
}

function discussion_display_after($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_DISCUSSION WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   $data = mysqli_fetch_array($result);
   if (!data)
      return;

   $discussion_enabled = $data['discussion_enabled'];
   if ($discussion_enabled == 0)
      return;

   $topic_id = isset($_REQUEST['topic_id']) ? $_REQUEST['topic_id'] : '';   

   $cms_content .= "<h3>".DISCUSSION_TITLE."</h3>\n";
   $cms_content .= "<p><a href=\"".MAIN_SCRIPT."?page=$id\">".DISCUSSION_TOPIC_INDEX."</a> | <a href=\"".MAIN_SCRIPT."?page=$id&amp;action=discussion_new\">".DISCUSSION_NEW_TOPIC."</a><br><br></p>";
   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

   $captcha_public_key = $data['captcha_public_key'];
   $captcha_private_key = $data['captcha_private_key'];
 
   if ($action == 'discussion_submit')
   {
      $message = '';
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
            $message .= DISCUSSION_INVALID_CAPTCHA;
            $message .= "<br>\n";
         }
      }
      if (!isset($_REQUEST['discussion-subject']) || $_REQUEST['discussion-subject'] =='')
      {
         $message .= DISCUSSION_INVALID_SUBJECT."<br>\n";
      }
      if (!isset($_REQUEST['discussion-author']) || $_REQUEST['discussion-author'] =='')
      {
         $message .= DISCUSSION_INVALID_NAME."<br>\n";
      }
      if (!isset($_REQUEST['discussion-description']) || $_REQUEST['discussion-description'] =='')
      {
         $message .= DISCUSSION_INVALID_DESCRIPTION."<br>\n";
      }
      if (!$message) 
      {
         $discussion_subject = addslashes($_REQUEST['discussion-subject']);
         $discussion_author = addslashes($_REQUEST['discussion-author']);
         $discussion_description = addslashes($_REQUEST['discussion-description']);
         $parent_id = intval($_REQUEST['parent_id']);
         $root_id = intval($_REQUEST['root_id']);

         $sql = "INSERT INTO CMS_DISCUSSION_TOPICS (page_id, topic_subject, topic_description, parent_id, root_id, topic_author) VALUES ('$id', '$discussion_subject', '$discussion_description', $parent_id , $root_id, '$discussion_author')";
         $result = mysqli_query($db, $sql);
         if (!$result)
         {
            die("Error: Insert failed!");
         }
         $topic_id = mysqli_insert_id($db);
         if (!empty($topic_id))
         {
            if ($root_id == 0)
            {
               $sql = "UPDATE CMS_DISCUSSION_TOPICS SET root_id = '$topic_id' WHERE id = '$topic_id' AND root_id = 0";
               mysqli_query($db, $sql) or die(mysqli_error($db));
            }
         }
         else
         {
            die("Error: Insert failed!");
         }
         header("Location: ".MAIN_SCRIPT."?page=$id&topic_id=$topic_id");
      }
      else
      {
         $cms_content .= $message;
         $cms_content .= "<br>";
         $action = 'discussion_new';
      }
   }

   if ($action == 'discussion_new')
   {
      if (!empty($topic_id)) 
      { 
         list($root_id, $parent_id, $topic_subject) = display_topic($id, $topic_id, 0);
         $cms_content .= "<p><hr><b>".DISCUSSION_ENTER_COMMENTS."</b></p>";
         $topic_subject = "Re: $topic_subject";
      }
      else
      {
         $topic_id = 0;
	 $root_id = 0;
	 $topic_subject = "";
      }

      if ($captcha_public_key != '')
      { 
         $cms_content .= "<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>\n";
      }
      $cms_content .= "<form action=\"".MAIN_SCRIPT."\" method=\"post\">\n";
      $cms_content .= "<input name=\"page\" type=\"hidden\" value=\"".$id."\">\n";
      $cms_content .= "<input name=\"parent_id\" type=\"hidden\" value=\"".$topic_id."\">\n";
      $cms_content .= "<input name=\"root_id\" type=\"hidden\" value=\"".$root_id."\">\n";
      $cms_content .= "<input name=\"action\" type=\"hidden\" value=\"discussion_submit\">\n";
      $cms_content .= "<table style=\"width:100%\">\n";
      $cms_content .= "<tr><td>".DISCUSSION_SUBJECT."</td><td><input class=\"cms_input\" name=\"discussion-subject\" style=\"box-sizing:border-box;width:100%\" value=\"$topic_subject\"></td></tr>\n";
      $cms_content .= "<tr><td>".DISCUSSION_NAME."</td><td><input class=\"cms_input\" name=\"discussion-author\" style=\"box-sizing:border-box;width:100%\"></td></tr>\n";
      $cms_content .= "<tr><td>".DISCUSSION_DESCRIPTION."</td><td><textarea class=\"cms_textarea\" name=\"discussion-description\" style=\"box-sizing:border-box;width:100%\" rows=\"10\"></textarea></td></tr>\n";

      if ($captcha_public_key != '')
      {
         $cms_content .= "<tr><td>&nbsp;</td><td><div class=\"g-recaptcha\" data-sitekey=\"".$captcha_public_key."\"></div></td></tr>\n";
      }
      $cms_content .= "<tr><td>&nbsp;</td><td><input class=\"cms_button\" name=\"submit\" type=\"submit\" value=\"".DISCUSSION_SUBMIT."\"></td></tr>\n";
      $cms_content .= "</table>\n</form>\n";
   }
   else
   {
      display_topic($id, $topic_id);
   }
}

function discussion_tab()
{
   global $id, $db, $plugin;

   $discussion_enabled = 0;
   $captcha_public_key = '';
   $captcha_private_key = '';

   $sql = "SELECT * FROM CMS_DISCUSSION WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $discussion_enabled = $data['discussion_enabled'];
      $captcha_public_key = $data['captcha_public_key'];
      $captcha_private_key = $data['captcha_private_key'];
   }

   $html = ''; 
   $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
   $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable discussion for this page:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"discussion-enabled\" size=\"1\"><option value=\"0\"";
   if ($discussion_enabled == 0)
   {
      $html .= " selected";
   }
   $html .= ">disabled</option><option value=\"1\"";
   if ($discussion_enabled == 1)
   {
      $html .= " selected";
   }
   $html .= ">enabled</option></select></td></tr>";
   $html .= "<tr><td><label>reCAPTCHA v2 site key:</label></td><td><input class=\"form-control\" name=\"discussion-publickey\" style=\"width:100%;\" value=\"$captcha_public_key\"></td></tr>";
   $html .= "<tr><td><label>reCAPTCHA v2 secret key:</label></td><td><input class=\"form-control\" name=\"discussion-privatekey\" style=\"width:100%;\" value=\"$captcha_private_key\"></td></tr>";
   $html .= "</table><br>\n";
 
   return $html;
}

function discussion_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_DISCUSSION (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              discussion_enabled INT, 
              captcha_public_key TEXT, 
              captcha_private_key TEXT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
      $sql = "CREATE TABLE IF NOT EXISTS CMS_DISCUSSION_TOPICS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              parent_id INT, 
              root_id INT, 
              topic_subject TEXT,
              topic_description TEXT,
              topic_date TIMESTAMP,
              topic_modified TIMESTAMP,
              topic_author TEXT,
              PRIMARY KEY(id));";
      $result = mysqli_query($db, $sql);
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function discussion_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['discussion-enabled']) && 
          isset($_REQUEST['discussion-publickey']) &&
          isset($_REQUEST['discussion-privatekey']))
      {
         $discussion_enabled = intval($_REQUEST['discussion-enabled']);
         $captcha_public_key = $_REQUEST['discussion-publickey'];
         $captcha_private_key = $_REQUEST['discussion-privatekey'];
   
         if (get_magic_quotes_gpc())
         {
            $captcha_public_key = stripslashes($captcha_public_key);
            $captcha_private_key = stripslashes($captcha_private_key);
         }
         $captcha_public_key = mysqli_real_escape_string($db, $captcha_public_key);
         $captcha_private_key = mysqli_real_escape_string($db, $captcha_private_key);

         $sql = "SELECT * FROM CMS_DISCUSSION WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_DISCUSSION (`discussion_enabled`, `captcha_public_key`, `captcha_private_key`, `page_id`) VALUES ('$discussion_enabled', '$captcha_public_key', '$captcha_private_key', '$id');";
         else
            $sql = "UPDATE CMS_DISCUSSION SET discussion_enabled='$discussion_enabled', captcha_public_key='$captcha_public_key', captcha_private_key='$captcha_private_key' WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}

?>