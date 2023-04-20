<?php

$plugin = array(
	'name' => 'Poll',
	'description' => 'Poll',
	'admin' => array(
		'init' => array('function' => 'poll_init'),
		'update' => array('function' => 'poll_update'),
		'tab' => array('name' => 'Poll', 'function' => 'poll_tab')
		),
	'events' => array('onAfterContent' => 'poll_display_after')
	);

define("POLL_VOTE", "Vote");
define("POLL_VIEWRESULTS", "View results");
define("POLL_RESULTS", "Poll results");
define("POLL_RETURN", "Return to page");

function poll_display_after($id)
{
   global $cms_content, $db;

   if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'poll_vote' && isset($_REQUEST['poll_choice']))
   {
      $poll_votes = 0;
      $poll_choice = intval($_REQUEST['poll_choice']);

      $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE page_id='$id' AND poll_index='$poll_choice'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $poll_votes = $data['poll_votes'];
         $poll_votes++;
         $sql = "UPDATE CMS_POLL_OPTIONS SET poll_votes='$poll_votes' WHERE id='".$data['id']."'";
         mysqli_query($db, $sql);
      }
   }

   if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'poll_results')
   {
      $cms_content = "<h3>".POLL_RESULTS."</h3>\n";

      $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE page_id='$id' ORDER BY poll_index ASC";
      $result = mysqli_query($db, $sql);
      $arr_labels = array();
      $arr_votes = array();
      $poll_total = 0;
      while ($data = mysqli_fetch_array($result))
      {
         $arr_labels[] = htmlspecialchars($data['poll_option']);
         $arr_votes[] = $data['poll_votes'];
         $poll_total += $data['poll_votes'];
      }

      for ($i=0; $i<count($arr_labels); ++$i)
      {
         if ($poll_total>0)
         {
            $percent = intval(($arr_votes[$i]*100)/$poll_total);
         }
         else
         { 
            $percent = 0;
         }    
         $cms_content .= "$arr_labels[$i]: $arr_votes[$i] ($percent%)<br>\n";

         $max = ($percent/10)+1;
         for ($j=1; $j<=$max; $j++)
         { 
             $cms_content .= "<img src=\"./";
             $cms_content .= basename(dirname(dirname(__FILE__)));
             $cms_content .= "/";
             $cms_content .= basename(dirname(__FILE__));
             $cms_content .= "/images/poll.gif\" alt=\"\" border=\"0\">";
         }
         $cms_content .= "<br>\n";
      }

      $cms_content .= "<p><a href=\"".MAIN_SCRIPT."?page=$id\">".POLL_RETURN."</a></p>";
      return;
   }

   $sql = "SELECT * FROM CMS_POLL WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $poll_enabled = $data['poll_enabled'];
      if ($poll_enabled == 0)
         return;

      $poll_title = $data['poll_title'];
      $poll_question = $data['poll_question'];
   
      $cms_content .= "<h3>$poll_title</h3>\n";
      $cms_content .= "<p><b>$poll_question</b></p>\n";
      $cms_content .= "<form action=\"".MAIN_SCRIPT."\" method=\"post\">\n";
      $cms_content .= "<input name=\"page\" type=\"hidden\" value=\"".$id."\">\n";
      $cms_content .= "<input name=\"action\" type=\"hidden\" value=\"poll_vote\">\n";
      $cms_content .= "<ul style=\"list-style:none;\" class=\"cms_poll\">\n";

      $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE page_id='$id' ORDER BY poll_index ASC";
      $result = mysqli_query($db, $sql);
      while ($data = mysqli_fetch_array($result))
      {
         $cms_content .= "<li><input type=\"radio\" name=\"poll_choice\" value=\"";
         $cms_content .= $data['poll_index'];
         $cms_content .= "\">";
         $cms_content .= htmlspecialchars($data['poll_option']);
         $cms_content .= "</li>\n";
      }
      $cms_content .= "</ul>\n";
      $cms_content .= "<input class=\"cms_button\" type=\"submit\" name=\"submit\" value=\"".POLL_VOTE."\">\n";
      $cms_content .= "</form>\n";
   }
   $cms_content .= "<p><a href=\"".MAIN_SCRIPT."?page=$id&amp;action=poll_results\">".POLL_VIEWRESULTS."</a></p>";
}

function poll_tab()
{
   global $id, $db, $plugin;

   $poll_id = isset($_REQUEST['pollid']) ? $_REQUEST['pollid'] : -1;
   $poll_action = isset($_REQUEST['poll-action']) ? $_REQUEST['poll-action'] : '';
   $anchor = "#tab-Poll";
   $plugin_dir = basename(dirname(__FILE__));
 
   $poll_enabled = 0;
   $poll_title = 'Poll';
   $poll_question = 'Enter your question here';

   $html = '';
   if ($poll_action == 'edit' || $poll_action == 'new')
   {
      $poll_option = '';
      $poll_votes = 0;

      if ($poll_id >= 0)
      {
         $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE id = '$poll_id' ORDER BY poll_index ASC";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $poll_option = $data['poll_option'];
            $poll_votes = $data['poll_votes'];
         }
      }
      $html .= "<script type=\"text/javascript\">\n";
      $html .= "$(document).ready(function()\n";
      $html .= "{\n";
      $html .= "   $('input[name=poll-save]').click(function(e)\n";
      $html .= "   {\n";
      $html .= "      e.preventDefault();\n";
      $html .= "      jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"poll-save\", pollid: \"$poll_id\", pageid: \"$id\", poll_option: $('input[name=poll-option]').val(), poll_votes: $('input[name=poll-votes]').val() }, function(result)\n";
      $html .= "      {\n";
      $html .= "         window.location = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      });\n";
      $html .= "   });\n";
      $html .= "});\n";
      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
      $html .= "<tr>\n";
      $html .= "<td><label>Option:</label></td><td><input class=\"form-control\" type=\"text\" name=\"poll-option\" value=\"" .$poll_option. "\" style=\"width:100%;\"></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "<td><label>Votes:</label></td><td><input class=\"form-control\" type=\"text\" name=\"poll-votes\" value=\"" .$poll_votes. "\" size=\"5\"></td>\n";
      $html .= "</tr>\n";
      $html .= "<tr>\n";
      $html .= "<td>&nbsp;</td><td>\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" name=\"poll-save\"value=\"Save Poll Option\">\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" value=\"Back to overview\" onclick=\"window.location='" .MAIN_SCRIPT."?action=edit&id=".$id.$anchor."'\">\n";
      $html .= "</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
   }
   else
   {
      $sql = "SELECT * FROM CMS_POLL WHERE page_id='$id'";
      $result = mysqli_query($db, $sql);
      if ($data = mysqli_fetch_array($result))
      {
         $poll_enabled = $data['poll_enabled'];
         $poll_title = $data['poll_title'];
         $poll_question = $data['poll_question'];
      }
 
      $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
      $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable poll for this page:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"poll-enabled\" size=\"1\"><option value=\"0\"";
      if ($poll_enabled == 0)
      {
         $html .= " selected";
      }
      $html .= ">disabled</option><option value=\"1\"";
      if ($poll_enabled == 1)
      {
         $html .= " selected";
      }
      $html .= ">enabled</option></select></td></tr>";
      $html .= "<tr><td><label>Poll title:</label></td><td><input class=\"form-control\" name=\"poll-title\" style=\"width:100%;\" value=\"$poll_title\"></td></tr>";
      $html .= "<tr><td><label>Question:</label></td><td><input class=\"form-control\" name=\"poll-question\" style=\"width:100%;\" value=\"$poll_question\"></td></tr>";
      $html .= "</table><br>\n";

      $html .= "<p><a href=\"".MAIN_SCRIPT."?action=edit&id=".$id."&poll-action=new".$anchor."\">Add New Poll Option</a></p>";

      $sql = "SELECT * FROM CMS_POLL_OPTIONS WHERE page_id='$id' ORDER BY poll_index ASC";
      $result = mysqli_query($db, $sql);

      $html .= "<script type=\"text/javascript\">\n";
      $html .= "function poll_delete(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"poll-delete\", pollid: id }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";
      $html .= "function poll_moveup(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"poll-moveup\", pollid: id, pageid: \"$id\" }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";
      $html .= "function poll_movedown(id)\n";
      $html .= "{\n";
      $html .= "   jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"poll-movedown\", pollid: id, pageid: \"$id\" }, function(result)\n";
      $html .= "   {\n";
      $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      window.location.reload(true);\n";
      $html .= "   });\n";
      $html .= "}\n";      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n<tr><th>Option</th><th>Votes</th><th>Action</th></tr>\n";

      $num_rows = mysqli_num_rows($result);
      if ($num_rows == 0)
      {
         $html .= "<tr><td>No options yet</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
      else 
      {
         while ($data = mysqli_fetch_array($result))
         {
            $html .= "<tr><td>";
            $html .= $data['poll_option'];
            $html .= "</td><td>";
            $html .= $data['poll_votes'];
            $html .= "</td><td><a href=\"./".MAIN_SCRIPT."?action=edit&amp;id=".$id."&amp;pollid=".$data['id']."&amp;poll-action=edit".$anchor;
            $html .= "\">Edit</a> | <a href=\"javascript:poll_delete(";
            $html .= $data['id'];
            $html .= ")\">Delete</a> | <a href=\"javascript:poll_moveup(";
            $html .= $data['id'];
            $html .= ")\">Move Up</a> | <a href=\"javascript:poll_movedown(";
            $html .= $data['id'];
            $html .= ")\">Move Down</a></td></tr>\n";
         }
      }
      $html .= "</table>\n";
   }
   return $html; 
}

function poll_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_POLL (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              poll_enabled INT, 
              poll_title TEXT, 
              poll_question TEXT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));

      $sql = "CREATE TABLE IF NOT EXISTS CMS_POLL_OPTIONS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              poll_index INT,
              poll_option TEXT, 
              poll_votes INT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function poll_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['poll-enabled']) && 
          isset($_REQUEST['poll-title']) &&
          isset($_REQUEST['poll-question']))
      {
         $poll_enabled = $_REQUEST['poll-enabled'];
         $poll_title = $_REQUEST['poll-title'];
         $poll_question = $_REQUEST['poll-question'];

         if (get_magic_quotes_gpc())
         {
            $poll_title = stripslashes($poll_title);
            $poll_question = stripslashes($poll_question);
         }
         $poll_title = mysqli_real_escape_string($db, $poll_title);
         $poll_question = mysqli_real_escape_string($db, $poll_question);

         $sql = "SELECT * FROM CMS_POLL WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_POLL (`poll_enabled`, `poll_title`, `poll_question`, `page_id`) VALUES ('$poll_enabled', '$poll_title', '$poll_question', '$id');";
         else
            $sql = "UPDATE CMS_POLL SET poll_enabled='$poll_enabled', poll_title='$poll_title', poll_question='$poll_question' WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}
?>