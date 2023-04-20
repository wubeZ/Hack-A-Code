<?php

$plugin = array(
	'name' => 'Downloads',
	'description' => 'Automatically create a list of links from files in a folder',
	'admin' => array(
		'init' => array('function' => 'downloads_init'),
		'update' => array('function' => 'downloads_update'),
		'tab' => array('name' => 'Downloads', 'function' => 'downloads_tab')
		),
	'events' => array('onAfterContent' => 'downloads_display_after')
	);

function downloads_display_after($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_DOWNLOADS WHERE page_id=".$id;
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $downloads_enabled = $data['downloads_enabled'];
      if ($downloads_enabled == 0)
         return;

      $downloads_folder = $data['downloads_folder'];
      $downloads_width = $data['downloads_width'];
      $downloads_filter = explode(',', $data['downloads_filter']);
      $downloads_bullet = $data['downloads_bullet'];
      $downloads_no_files = "No files to show";

      $files = array();
      $handle = opendir($downloads_folder);
      while ($filename = readdir($handle))
      {
         if ($filename != "." && $filename != ".." && is_file($downloads_folder.$filename))
         {
            $ext = (pathinfo($downloads_folder.$filename));
            if(in_array(strtolower($ext['extension']), $downloads_filter))
            {
               $files[] = $filename;
            }
         }
      }
      closedir($handle);   

      if(empty($files))
      {
         $cms_content .= $downloads_no_files;
      }
      else
      {
         sort($files);
         foreach ($files as $filename)
         {
            $cms_content .= $downloads_bullet;
            $cms_content .= '<a href="'. $downloads_folder.$filename .'">'. $filename ."</a><br>\n";
         }
      }
   }
}

function downloads_tab()
{
   global $id, $db;

   $downloads_enabled = 0;
   $downloads_folder = './images/';
   $downloads_filter = 'jpg,mp3,pdf';
   $downloads_bullet = ' &bull; ';

   $sql = "SELECT * FROM CMS_DOWNLOADS WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $downloads_enabled = $data['downloads_enabled'];
      $downloads_folder = $data['downloads_folder'];
      $downloads_filter = $data['downloads_filter'];
      $downloads_bullet = $data['downloads_bullet'];
   }
 
   $html = '';
   $html .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
   $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable downloads:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"downloads-enabled\" size=\"1\"><option value=\"0\"";
   if ($downloads_enabled == 0)
   {
      $html .= " selected";
   }
   $html .= ">disabled</option><option value=\"1\"";
   if ($downloads_enabled == 1)
   {
      $html .= " selected";
   }
   $html .= ">enabled</option></select></td></tr>";
   $html .= "<tr><td><label>Folder:</label></td><td><input class=\"form-control\" name=\"downloads-folder\" style=\"width:100%;\" value=\"$downloads_folder\"></td></tr>";
   $html .= "<tr><td><label>Filter:</label></td><td><input class=\"form-control\" name=\"downloads-filter\" style=\"width:100%;\" value=\"$downloads_filter\"></td></tr>";
   $html .= "<tr><td><label>Bullet:</label></td><td><input class=\"form-control\" name=\"downloads-bullet\" style=\"width:100%;\" value=\"$downloads_bullet\"></td></tr>";

   $html .= "</table>\n";

   return $html;
}

function downloads_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_DOWNLOADS (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              downloads_enabled INT, 
              downloads_folder TEXT, 
              downloads_filter TEXT, 
              downloads_bullet TEXT, 
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function downloads_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['downloads-enabled']) && 
          isset($_REQUEST['downloads-folder']) &&
          isset($_REQUEST['downloads-filter']) &&
          isset($_REQUEST['downloads-bullet']))
      {
         $downloads_enabled = intval($_REQUEST['downloads-enabled']);
         $downloads_folder = $_REQUEST['downloads-folder'];
         $downloads_filter = $_REQUEST['downloads-filter'];
         $downloads_bullet = $_REQUEST['downloads-bullet'];
         if (get_magic_quotes_gpc())
         {
            $downloads_folder = stripslashes($downloads_folder);
            $downloads_filter = stripslashes($downloads_filter);
            $downloads_bullet = stripslashes($downloads_bullet);
         }
         $downloads_folder = mysqli_real_escape_string($db, $downloads_folder);
         $downloads_filter = mysqli_real_escape_string($db, $downloads_filter);
         $downloads_bullet = mysqli_real_escape_string($db, $downloads_bullet);

         $sql = "SELECT * FROM CMS_DOWNLOADS WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_DOWNLOADS (`downloads_enabled`, `downloads_folder`, `downloads_filter`, `downloads_bullet`,`page_id`) VALUES ('$downloads_enabled', '$downloads_folder', '$downloads_filter', '$downloads_bullet' ,'$id');";
         else
            $sql = "UPDATE CMS_DOWNLOADS SET downloads_enabled='$downloads_enabled', downloads_folder='$downloads_folder', downloads_filter='$downloads_filter', downloads_bullet='$downloads_bullet' WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}
?>