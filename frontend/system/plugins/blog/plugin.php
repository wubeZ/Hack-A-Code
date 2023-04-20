<?php

$plugin = array(
	'name' => 'Blog',
	'description' => 'Blog',
	'admin' => array(
		'init' => array('function' => 'blog_init'),
		'update' => array('function' => 'blog_update'),
		'tab' => array('name' => 'Blog', 'function' => 'blog_tab')
		),
	'events' => array('onAfterContent' => 'blog_after_content', 'onPageHeader' => 'blog_page_header')
	);

define("BLOG_HEADING", "Blog");
define("BLOG_ITEM", "Edit Blog Item");
define("BLOG_NEXT_PAGE", "Next page &gt;");
define("BLOG_PREV_PAGE", "&lt; Previous page");

function blog_after_content($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_BLOG WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   $data = mysqli_fetch_array($result);
   if (!$data)
      return;

   $blog_enabled = $data['blog_enabled'];
   if ($blog_enabled == 0)
      return;

   $cms_content .= "<h3>".BLOG_HEADING."</h3>\n";

   $offset = 0;
   $blog_limit = $data['blog_limit'];
   $blog_dateformat = $data['blog_dateformat'];
   if (isset($_REQUEST['offset']))
   {
      $offset = (int) $_REQUEST['offset'];
   }
   $sql = "SELECT * FROM CMS_BLOG_ITEMS WHERE page_id='$id' ORDER BY blog_index ASC LIMIT $offset,$blog_limit";
   $result = mysqli_query($db, $sql);
   while ($data = mysqli_fetch_array($result))
   {
      $last_update = date($blog_dateformat, strtotime($data['blog_date']));
      $cms_content .= "<div class=\"blogitem\">\n";
      $cms_content .= "<div class=\"blogtitle\">".htmlspecialchars($data['blog_title'])."<span class=\"blogdate\">".$last_update."</span></div>\n";
      $cms_content .= "<div class=\"blogtext\">".$data['blog_text']."</div>\n";
      if (!empty($data['blog_image']))
      {
         $cms_content .= "<img class=\"blogimage\" src=\"".$data['blog_image']."\">\n";
      }
      $cms_content .= "</div>\n";
   }
   
   $this_page = MAIN_SCRIPT;
   $record_count = 0;
   $prev_page = $offset - $blog_limit;
   $next_page = $offset + $blog_limit;
    	
   $sql = 'SELECT COUNT(*) FROM CMS_BLOG_ITEMS';
   $result = mysqli_query($db, $sql);
   list($record_count) = mysqli_fetch_array($result);
   	
   $cms_content .= "<div class=\"blognavigation\">";
   if ($offset > 0)
   {
      $cms_content .= "<a href=\"$this_page?page=$id&amp;offset=$prev_page\">".BLOG_PREV_PAGE."</a> &nbsp;  &nbsp;";
   }
   if ($next_page < $record_count)
   {
      $cms_content .= "<a href=\"$this_page?page=$id&amp;offset=$next_page\">".BLOG_NEXT_PAGE."</a> &nbsp;  &nbsp;";
   }
   $cms_content .= "</div>";
}

function blog_tab()
{
   global $id, $db, $plugin;

   $blog_id = isset($_REQUEST['blogid']) ? $_REQUEST['blogid'] : -1;
   $blog_action = isset($_REQUEST['blog-action']) ? $_REQUEST['blog-action'] : '';
   $anchor = "#tab-Blog";
   $plugin_dir = basename(dirname(__FILE__));
 
   $blog_enabled = 0;
   $blog_limit = 10;
   $blog_dateformat = "Y/m/d H:i:s";

   $html = '';
  
   $sql = "SELECT * FROM CMS_BLOG WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $blog_enabled = $data['blog_enabled'];
      $blog_limit = $data['blog_limit'];
      $blog_dateformat = $data['blog_dateformat'];
   }
 
   $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">\n";
   $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable blog:</label></td><td><select class=\"form-control\" name=\"blog-enabled\" size=\"1\"><option value=\"0\"";
   if ($blog_enabled == 0)
   {
      $html .= " selected";
   }
   $html .= ">disabled</option><option value=\"1\"";
   if ($blog_enabled == 1)
   {
      $html .= " selected";
   }
   $html .= ">enabled</option></select></td></tr>";
   $html .= "<tr><td><label>Items per page:</label></td><td><input class=\"form-control\" name=\"blog-limit\" size=\"4\" value=\"$blog_limit\"></td></tr>";
   $html .= "<tr><td><label>Date format:</label></td><td><input class=\"form-control\" name=\"blog-dateformat\" style=\"width:100%;\" value=\"$blog_dateformat\"></td></tr>";
   $html .= "</table><br>\n";

   if ($blog_action == 'edit' || $blog_action == 'new')
   {
      $title = '';
      $text = '';
      $image = '';
      if ($blog_id >= 0)
      {
         $sql = "SELECT * FROM CMS_BLOG_ITEMS WHERE id = '$blog_id'";
         $result = mysqli_query($db, $sql);
         if ($data = mysqli_fetch_array($result))
         {
            $title = $data['blog_title'];
            $text = $data['blog_text'];
            $text = str_replace("<br>", "\r\n", $text);
            $image = $data['blog_image'];
         }
      }

      $html .= "<script type=\"text/javascript\">\n";
      $html .= "$(document).ready(function()\n";
      $html .= "{\n";
      $html .= "   $('input[name=blog-save]').click(function(e)\n";
      $html .= "   {\n";
      $html .= "      e.preventDefault();\n";
      $html .= "      jQuery.post('./";
      $html .= MAIN_SCRIPT;
      $html .= "?plugin=";
      $html .= $plugin_dir;
      $html .= "', { action: \"blog-save\", blogid: \"$blog_id\", pageid: \"$id\", title: $('input[name=blog-title]').val(), text: $('textarea[name=blog-text]').val(), image: $('input[name=blog-image]').val() }, function(result)\n";
      $html .= "      {\n";
      $html .= "         window.location = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
      $html .= "      });\n";
      $html .= "   });\n";
      $html .= "});\n";
      $html .= "</script>\n";

      $html .= "<table width=\"100%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
      $html .= "<tr><td colspan=\"2\"style=\"font-size:1.1em;font-weight:bold\">".BLOG_ITEM."</td></tr>\n";
      $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Title:&nbsp;&nbsp;</label></td><td><input class=\"form-control\" type=\"text\" name=\"blog-title\" value=\"" .$title. "\" style=\"width:100%;\"></td></tr>\n";
      $html .= "<tr><td><label>Text:</label></td><td><textarea class=\"form-control\" style=\"width:100%;height:250px\" name=\"blog-text\">".$text."</textarea></td></tr>\n";
      $html .= "<tr><td><label>Image:</label></td><td><input class=\"form-control\" type=\"text\" name=\"blog-image\" value=\"" .$image. "\" style=\"width:100%;\"></td></tr>\n";
      $html .=  "<tr><td>&nbsp;</td>\n";
      $html .= "<td align=left>\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" name=\"blog-save\"value=\"Save blog item\">\n";
      $html .= "<input style=\"background-color:transparent;padding-left:0;text-decoration:underline;border-width:0;cursor:pointer;\" type=\"button\" value=\"Back to overview\" onclick=\"window.location='" .MAIN_SCRIPT."?action=edit&id=".$id.$anchor."'\">\n";
      $html .= "</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
   }
   else
   {
      $html .= "<p><a href=\"".MAIN_SCRIPT."?action=edit&id=".$id."&blog-action=new".$anchor."\">Create new blog item</a></p>";

      $sql = "SELECT * FROM CMS_BLOG_ITEMS WHERE page_id='$id' ORDER BY blog_index ASC";
      $result = mysqli_query($db, $sql);
      $num_rows = ($result ? mysqli_num_rows($result) : 0);
      if ($num_rows > 0)
      {
         $html .= "<script type=\"text/javascript\">\n";
         $html .= "function blog_delete(id)\n";
         $html .= "{\n";
         $html .= "   jQuery.post('./";
         $html .= MAIN_SCRIPT;
         $html .= "?plugin=";
         $html .= $plugin_dir;
         $html .= "', { action: \"blog-delete\", blogid: id }, function(result)\n";
         $html .= "   {\n";
         $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
         $html .= "      window.location.reload(true);\n";
         $html .= "   });\n";
         $html .= "}\n";
         $html .= "function blog_moveup(id)\n";
         $html .= "{\n";
         $html .= "   jQuery.post('./";
         $html .= MAIN_SCRIPT;
         $html .= "?plugin=";
         $html .= $plugin_dir;
         $html .= "', { action: \"blog-moveup\", blogid: id, pageid: \"$id\" }, function(result)\n";
         $html .= "   {\n";
         $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
         $html .= "      window.location.reload(true);\n";
         $html .= "   });\n";
         $html .= "}\n";
         $html .= "function blog_movedown(id)\n";
         $html .= "{\n";
         $html .= "   jQuery.post('./";
         $html .= MAIN_SCRIPT;
         $html .= "?plugin=";
         $html .= $plugin_dir;
         $html .= "', { action: \"blog-movedown\", blogid: id, pageid: \"$id\" }, function(result)\n";
         $html .= "   {\n";
         $html .= "      window.location.href = '".MAIN_SCRIPT."?action=edit&id=".$id.$anchor."';\n";
         $html .= "      window.location.reload(true);\n";
         $html .= "   });\n";
         $html .= "}\n";
         $html .= "</script>\n";

         $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">\n<tr><th>Last update</th><th>Title</th><th>Text</th><th>Action</th></tr>\n";

         while ($data = mysqli_fetch_array($result))
         {
            $html .= "<tr><td>";
            $html .= htmlspecialchars($data['blog_date']);
            $html .= "</td><td>";
            $html .= htmlspecialchars($data['blog_title']);
            $html .= "</td><td>";
            $html .= htmlspecialchars(strip_tags($data['blog_text']));
            $html .= "</td><td><a href=\"./".MAIN_SCRIPT."?action=edit&amp;id=".$id."&amp;blogid=".$data['id']."&amp;blog-action=edit".$anchor;
            $html .= "\">Edit</a> | <a href=\"javascript:blog_delete(";
            $html .= $data['id'];
            $html .= ")\">Delete</a> | <a href=\"javascript:blog_moveup(";
            $html .= $data['id'];
            $html .= ")\">Move Up</a> | <a href=\"javascript:blog_movedown(";
            $html .= $data['id'];
            $html .= ")\">Move Down</a></td></tr>\n";
         }
         $html .= "</table>\n";
      }
   }
   return $html;
}

function blog_init()
{
   global $authorized, $db;
   if ($authorized)
   {
       $sql = "CREATE TABLE IF NOT EXISTS CMS_BLOG (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              blog_enabled INT, 
              blog_limit INT, 
              blog_dateformat TEXT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));

      $sql = "CREATE TABLE IF NOT EXISTS CMS_BLOG_ITEMS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              blog_title TEXT,
              blog_text TEXT,
              blog_image TEXT,
              blog_date TIMESTAMP,
              blog_index INT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function blog_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['blog-enabled']) && 
          isset($_REQUEST['blog-limit']) &&
          isset($_REQUEST['blog-dateformat']))
      {
         $blog_enabled = intval($_REQUEST['blog-enabled']);
         $blog_limit = intval($_REQUEST['blog-limit']);
         $blog_dateformat = $_REQUEST['blog-dateformat'];

         $sql = "SELECT * FROM CMS_BLOG WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_BLOG (`blog_enabled`, `blog_limit`, `blog_dateformat`, `page_id`) VALUES ('$blog_enabled', '$blog_limit', '$blog_dateformat','$id');";
         else
            $sql = "UPDATE CMS_BLOG SET blog_enabled='$blog_enabled', blog_limit='$blog_limit', blog_dateformat='$blog_dateformat' WHERE page_id=$id";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}

function blog_page_header()
{
   global $cms_header;

   $cms_header .= "<link rel=\"stylesheet\" href=\"";
   $cms_header .= basename(dirname(dirname(__FILE__)));
   $cms_header .= "/";
   $cms_header .= basename(dirname(__FILE__));
   $cms_header .= "/cms-blog.css\" type=\"text/css\">\n";
}
?>