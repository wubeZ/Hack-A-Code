<?php

$plugin = array(
	'name' => 'FAQ',
	'description' => 'Create a simple Frequently Asked Questions (FAQ) page.',
	'admin' => array(
		'init' => array('function' => 'faq_init'),
		'tab' => array('name' => 'FAQ', 'function' => 'faq_tab')
		),
	'events' => array('onAfterContent' => 'faq_display_after', 'onPageHeader' => 'faq_page_header')
	);

function faq_display_after($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_FAQ WHERE page_id='$id' ORDER BY faq_index ASC";
   $result = mysqli_query($db, $sql);
   $num_rows = mysqli_num_rows($result);
   if ($num_rows > 0)
   {
      $cms_content .= "<script type=\"text/javascript\">\n";
      $cms_content .= "$(document).ready(function()\n";
      $cms_content .= "{\n";
      $cms_content .= "   $('dd').hide();\n";
      $cms_content .= "   $('dt').bind('click', function()\n";
      $cms_content .= "   {\n";
      $cms_content .= "      $(this).toggleClass('open').next().slideToggle();\n";
      $cms_content .= "   });\n";
      $cms_content .= "});\n";
      $cms_content .= "</script>\n";

      $cms_content .= "<dl class=\"faq\">\n";
      while ($data = mysqli_fetch_array($result))
      {
         $cms_content .= "<dt>";
         $cms_content .= htmlspecialchars($data['faq_question']);
         $cms_content .= "</dt>\n";
         $cms_content .= "<dd>";
         $cms_content .= htmlspecialchars($data['faq_answer']);
         $cms_content .= "</dd>\n";
      }
      $cms_content .= "</dl>\n";
   }
}

function faq_tab()
{
   global $id, $db, $plugin;

   $faq_id = isset($_REQUEST['faqid']) ? $_REQUEST['faqid'] : -1;
   $faq_action = isset($_REQUEST['faq-action']) ? $_REQUEST['faq-action'] : '';
   $anchor = "#tab-FAQ";
   $plugin_dir = basename(dirname(__FILE__));

   $html = '';
   if ($faq_action == 'edit' || $faq_action == 'new')
   {
      $question = '';
      $answer = '';
      if ($faq_id >= 0)
      {
         $sql = "SELECT * FROM CMS_FAQ WHERE id = '$faq_id'";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $question = $data['faq_question'];
            $answer = $data['faq_answer'];
         }
      }

      $html .= "<script type=\"text/javascript\">\n";
      $html .= "$(document).ready(function()\n";
      $html .= "{\n";
      $html .= "   $('input[name=faq-save]').click(function(e)\n";
      $html .= "   {\n";
      $html .= "      e.preventDefault();\n";
      $html .= "      jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"faq-save\", faqid: \"$faq_id\", pageid: \"$id\", question: $('input[name=faq-question]').val(), answer: $('textarea[name=faq-answer]').val() }, function(result)\n";
      $html .= "      {\n";
      $html .= "         window.location = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      });\n";
      $html .= "   });\n";
      $html .= "});\n";
      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
      $html .= "<tr><td style=\"width:5%;white-space:nowrap;\"><label>Question:&nbsp;&nbsp;</label></td><td><input class=\"form-control\" type=\"text\" name=\"faq-question\" value=\"" .$question. "\" style=\"width:100%;\"></td></tr>\n";
      $html .= "<tr><td><label>Answer:</label></td><td><textarea class=\"form-control\" style=\"width:100%;height:250px\" name=\"faq-answer\">".$answer."</textarea></td></tr>\n";
      $html .=  "<tr><td>&nbsp;</td>\n";
      $html .= "<td align=left>\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" name=\"faq-save\"value=\"Save FAQ Item\">\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" value=\"Back to overview\" onclick=\"window.location='" .MAIN_SCRIPT."?action=edit&id=".$id.$anchor."'\">\n";
      $html .= "</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
   }
   else
   {
      $html .= "<p><a href=\"".MAIN_SCRIPT."?action=edit&id=".$id."&faq-action=new".$anchor."\">Create new FAQ item</a></p>";

      $sql = "SELECT * FROM CMS_FAQ WHERE page_id='$id' ORDER BY faq_index ASC";
      $result = mysqli_query($db, $sql);
      $num_rows = mysqli_num_rows($result);
      if ($num_rows > 0)
      {
         $html .= "<script type=\"text/javascript\">\n";
         $html .= "function faq_delete(id)\n";
         $html .= "{\n";
         $html .= "   jQuery.post('./";
         $html .= MAIN_SCRIPT;
         $html .= "?plugin=";
         $html .= $plugin_dir;
         $html .= "', { action: \"faq-delete\", faqid: id }, function(result)\n";
         $html .= "   {\n";
         $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
         $html .= "      window.location.reload(true);\n";
         $html .= "   });\n";
         $html .= "}\n";
         $html .= "function faq_moveup(id)\n";
         $html .= "{\n";
         $html .= "   jQuery.post('./";
         $html .= MAIN_SCRIPT;
         $html .= "?plugin=";
         $html .= $plugin_dir;
         $html .= "', { action: \"faq-moveup\", faqid: id, pageid: \"$id\" }, function(result)\n";
         $html .= "   {\n";
         $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
         $html .= "      window.location.reload(true);\n";
         $html .= "   });\n";
         $html .= "}\n";
         $html .= "function faq_movedown(id)\n";
         $html .= "{\n";
         $html .= "   jQuery.post('./";
         $html .= MAIN_SCRIPT;
         $html .= "?plugin=";
         $html .= $plugin_dir;
         $html .= "', { action: \"faq-movedown\", faqid: id, pageid: \"$id\" }, function(result)\n";
         $html .= "   {\n";
         $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
         $html .= "      window.location.reload(true);\n";
         $html .= "   });\n";
         $html .= "}\n";
         $html .= "</script>\n";

         $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n<tr><th>Question</th><th>Answer</th><th>Action</th></tr>\n";

         while ($data = mysqli_fetch_array($result))
         {
            $html .= "<tr><td>";
            $html .= htmlspecialchars($data['faq_question']);
            $html .= "</td><td>";
            $html .= htmlspecialchars($data['faq_answer']);
            $html .= "</td><td><a href=\"./".MAIN_SCRIPT."?action=edit&amp;id=".$id."&amp;faqid=".$data['id']."&amp;faq-action=edit".$anchor;
            $html .= "\">Edit</a> | <a href=\"javascript:faq_delete(";
            $html .= $data['id'];
            $html .= ")\">Delete</a> | <a href=\"javascript:faq_moveup(";
            $html .= $data['id'];
            $html .= ")\">Move Up</a> | <a href=\"javascript:faq_movedown(";
            $html .= $data['id'];
            $html .= ")\">Move Down</a></td></tr>\n";
         }
         $html .= "</table>\n";
      }
   }
   return $html;
}

function faq_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_FAQ (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              faq_index INT,
              faq_date TIMESTAMP,
              faq_question TEXT,
              faq_answer TEXT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function faq_page_header()
{
   global $cms_header;

   $cms_header .= "<link rel=\"stylesheet\" href=\"";
   $cms_header .= basename(dirname(dirname(__FILE__)));
   $cms_header .= "/";
   $cms_header .= basename(dirname(__FILE__));
   $cms_header .= "/faq.css\" type=\"text/css\">\n";
}
?>