<?php

$plugin = array(
	'name' => 'Slide Show',
	'description' => 'Slide Show',
	'admin' => array(
		'init' => array('function' => 'slideshow_init'),
		'update' => array('function' => 'slideshow_update'),
		'tab' => array('name' => 'Slide Show', 'function' => 'slideshow_tab')
		),
	'events' => array('onAfterContent' => 'slideshow_display_before')
	);

function slideshow_display_before($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_SLIDESHOW WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $slideshow_enabled = $data['slideshow_enabled'];
      if ($slideshow_enabled == 0)
         return;

      $slideshow_folder = $data['slideshow_folder'];
      $slideshow_width = $data['slideshow_width'];
      $slideshow_height = $data['slideshow_height'];
      $slideshow_url = $data['slideshow_url'];
      $slideshow_target = $data['slideshow_target'];
      $slideshow_effect = $data['slideshow_effect'];
      $slideshow_duration = $data['slideshow_duration'];
      $slideshow_interval = $data['slideshow_interval'];

      $cms_content .= "<script type=\"text/javascript\" src=\"./";
      $cms_content .= basename(dirname(dirname(__FILE__)));
      $cms_content .= "/";
      $cms_content .= basename(dirname(__FILE__));
      $cms_content .= "/cms.slideshow.min.js\"></script>\n";
      $cms_content .= "<script type=\"text/javascript\">\n";
      $cms_content .= "$(document).ready(function()\n";
      $cms_content .= "{\n";
      $cms_content .= "   $(\"#cms_slideshow\").cms_slideshow(\n";
      $cms_content .= "   {\n";
      $cms_content .= "      interval: $slideshow_interval,\n";
      $cms_content .= "      type: 'sequence',\n";
      $cms_content .= "      effect: '$slideshow_effect',\n";
      $cms_content .= "      effectlength: $slideshow_duration\n";
      $cms_content .= "   });\n";
      $cms_content .= "});\n";
      $cms_content .= "</script>\n";

      $cms_content .= "<div id=\"cms_slideshow\" style=\"position:relative;overflow:hidden;width:";
      $cms_content .= $slideshow_width;
      $cms_content .= "px;height:";
      $cms_content .= $slideshow_height;
      $cms_content .= "px;\">\n";

      if ($slideshow_url != '')
      {
         $cms_content .= "<a href=\"";
         $cms_content .= $slideshow_url;
         $cms_content .= "\" target=\"";
         $cms_content .= $slideshow_target;
         $cms_content .= "\">\n";
      }

      $count = 0;
      $dir = opendir($slideshow_folder);
      while ($filename = readdir($dir))
      {
         $ext = pathinfo($filename, PATHINFO_EXTENSION);
         if ($ext == 'gif' || $ext == 'jpeg' || $ext == 'jpg' || $ext == 'png')
         {
            $cms_content .= "<img alt=\"\" style=\"border-width:0;left:0px;top:0px;width:";
            $cms_content .= $slideshow_width;
            $cms_content .= "px;height:";
            $cms_content .= $slideshow_height;
            $cms_content .= "px;";
            if ($count != 0)
            {
               $cms_content .= "display:none;";
            }
            $cms_content .= "\" src=\"";
            $cms_content .= $slideshow_folder;
            $cms_content .= $filename;
            $cms_content .= "\">\r\n";
            $count++;
         }
      }
      if ($slideshow_url != '')
      {
         $cms_content .= "</a>\n";
      }
      $cms_content .= "</div>\n";
   }
}

function slideshow_tab()
{
   global $id, $db;

   $slideshow_enabled = 0;
   $slideshow_folder = './images/';
   $slideshow_width = 300;
   $slideshow_height = 200;
   $slideshow_url = '';
   $slideshow_target = '';
   $slideshow_effect = 'none';
   $slideshow_duration = 3000;
   $slideshow_interval = 2000;

   $sql = "SELECT * FROM CMS_SLIDESHOW WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $slideshow_enabled = $data['slideshow_enabled'];
      $slideshow_folder = $data['slideshow_folder'];
      $slideshow_width = $data['slideshow_width'];
      $slideshow_height = $data['slideshow_height'];
      $slideshow_url = $data['slideshow_url'];
      $slideshow_target = $data['slideshow_target'];
      $slideshow_effect = $data['slideshow_effect'];
      $slideshow_duration = $data['slideshow_duration'];
      $slideshow_interval = $data['slideshow_interval'];
   }
 
   $html = '';
   $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
   $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable slide show:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"slideshow-enabled\" size=\"1\"><option value=\"0\"";
   if ($slideshow_enabled == 0)
   {
      $html .= " selected";
   }
   $html .= ">disabled</option><option value=\"1\"";
   if ($slideshow_enabled == 1)
   {
      $html .= " selected";
   }
   $html .= ">enabled</option></select></td></tr>";
   $html .= "<tr><td><label>Images folder:</label></td><td><input class=\"form-control\" name=\"slideshow-folder\" style=\"width:100%;\" value=\"$slideshow_folder\"></td></tr>";
   $html .= "<tr><td><label>Width:</label></td><td><input class=\"form-control\" name=\"slideshow-width\" size=\"4\" value=\"$slideshow_width\"></td></tr>";
   $html .= "<tr><td><label>Height:</label></td><td><input class=\"form-control\" name=\"slideshow-height\" size=\"4\" value=\"$slideshow_height\"></td></tr>";
   $html .= "<tr><td><label>URL:</label></td><td><input class=\"form-control\" name=\"slideshow-url\" style=\"width:100%;\" value=\"$slideshow_url\"></td></tr>";
   $html .= "<tr><td><label>Target:</label></td><td><input class=\"form-control\" name=\"slideshow-target\" style=\"width:100%;\" value=\"$slideshow_target\"></td></tr>";
   $html .= "<tr><td><label>Effect:</label></td><td><select class=\"form-control\" name=\"slideshow-effect\" size=\"1\"><option value=\"none\"";
   if ($slideshow_effect == 'none')
   {
      $html .= " selected";
   }
   $html .= ">none</option><option value=\"fade\"";
   if ($slideshow_effect == 'fade')
   {
      $html .= " selected";
   }
   $html .= ">fade</option></select></td></tr>";
   $html .= "<tr><td><label>Duration:</label></td><td><input class=\"form-control\" name=\"slideshow-duration\" size=\"4\" value=\"$slideshow_duration\"></td></tr>";
   $html .= "<tr><td><label>Interval:</label></td><td><input class=\"form-control\" name=\"slideshow-interval\" size=\"4\" value=\"$slideshow_interval\"></td></tr>";

   $html .= "</table>\n";

   return $html;
}

function slideshow_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_SLIDESHOW (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              slideshow_enabled INT, 
              slideshow_folder TEXT, 
              slideshow_width INT, 
              slideshow_height INT, 
              slideshow_url TEXT, 
              slideshow_target TEXT, 
              slideshow_effect TEXT, 
              slideshow_duration INT, 
              slideshow_interval INT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function slideshow_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['slideshow-enabled']) && 
          isset($_REQUEST['slideshow-folder']) &&
          isset($_REQUEST['slideshow-width']) &&
          isset($_REQUEST['slideshow-height']) &&
          isset($_REQUEST['slideshow-url']) &&
          isset($_REQUEST['slideshow-target']) &&
          isset($_REQUEST['slideshow-effect']) &&
          isset($_REQUEST['slideshow-duration']) &&
          isset($_REQUEST['slideshow-interval']))
      {
         $slideshow_enabled = $_REQUEST['slideshow-enabled'];
         $slideshow_folder = $_REQUEST['slideshow-folder'];
         $slideshow_width = $_REQUEST['slideshow-width'];
         $slideshow_height = $_REQUEST['slideshow-height'];
         $slideshow_url = $_REQUEST['slideshow-url'];
         $slideshow_target = $_REQUEST['slideshow-target'];
         $slideshow_effect = $_REQUEST['slideshow-effect'];
         $slideshow_duration = $_REQUEST['slideshow-duration'];
         $slideshow_interval = $_REQUEST['slideshow-interval'];

         $sql = "SELECT * FROM CMS_SLIDESHOW WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_SLIDESHOW (`slideshow_enabled`, `slideshow_folder`, `slideshow_width`, `slideshow_height`, `slideshow_url`, `slideshow_target`, `slideshow_effect`, `slideshow_duration`, `slideshow_interval`,`page_id`) VALUES ('$slideshow_enabled', '$slideshow_folder', '$slideshow_width', '$slideshow_height', '$slideshow_url',  '$slideshow_target', '$slideshow_effect', '$slideshow_duration',  '$slideshow_interval', '$id');";
         else
            $sql = "UPDATE CMS_SLIDESHOW SET slideshow_enabled='$slideshow_enabled', slideshow_folder='$slideshow_folder', slideshow_width='$slideshow_width', slideshow_height='$slideshow_height', slideshow_url='$slideshow_url', slideshow_target='$slideshow_target', slideshow_effect='$slideshow_effect', slideshow_duration=$slideshow_duration, slideshow_interval=$slideshow_interval WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}
?>