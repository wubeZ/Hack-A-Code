<?php

$plugin = array(
	'name' => 'Photo Album',
	'description' => 'Photo Album',
	'admin' => array(
		'init' => array('function' => 'photoalbum_init'),
		'update' => array('function' => 'photoalbum_update'),
		'tab' => array('name' => 'Photo Album', 'function' => 'photoalbum_tab')
		),
	'events' => array('onAfterContent' => 'photoalbum_display_before', 'onPageHeader' => 'photoalbum_page_header')
	);

function photoalbum_display_before($id)
{
   global $cms_content, $db;

   $sql = "SELECT * FROM CMS_PHOTOALBUM WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $album_enabled = $data['album_enabled'];
      if ($album_enabled == 0)
         return;

      $photoalbum_images_folder = $data['album_folder'];
      $photoalbum_columns = $data['album_columns'];
      $photoalbum_rows = $data['album_rows'];
      $photoalbum_thumbnail_size = $data['album_size'];

      $first = isset($_REQUEST['first']) ? $_REQUEST['first'] : 0;
      $img  = isset($_REQUEST['img']) ? $_REQUEST['img'] : "";

      if ($img)
      {
         $cms_content .= "<img alt=\"\" style=\"margin:10px\" border=\"0\" src=\"$photoalbum_images_folder$img\">\r\n";
      }
      else
      {
         $images = 0;
         $current_col = 0;
         $count = $photoalbum_rows * $photoalbum_columns;
         $last = $count + $first;
         $cms_content .= "<table style=\"width:100%\" border=\"0\" cellspacing=\"10\" cellpadding=\"0\">\r\n";
         $dir = opendir($photoalbum_images_folder);
         while ($filename = readdir($dir))
         {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext == 'gif' || $ext == 'jpeg' || $ext == 'jpg' || $ext == 'png')
            {
               if ($images >= $first && $images < $last)
               {
                  if ($current_col == 0) 
                  {
                     $cms_content .= "<tr>\r\n";
                  }
                  list($width, $height) = getimagesize($photoalbum_images_folder.$filename);
                  $link = "<a href=\"$photoalbum_images_folder$filename\" target=\"_blank\">";
                  $image_ratio = $width/$height;
                  if ($image_ratio > 1)
                  {
                     $thumbnail_width = $photoalbum_thumbnail_size;
                     $thumbnail_height = $photoalbum_thumbnail_size / $image_ratio;
                  }
                  else
                  {
                     $thumbnail_width = $photoalbum_thumbnail_size * $image_ratio;
                     $thumbnail_height = $photoalbum_thumbnail_size;
                  }
                  $cms_content .= "<td align=\"center\" valign=\"bottom\">$link<img src=\"".$photoalbum_images_folder.$filename."\" alt=\"\" class=\"photoalbum_img\" style=\"width:".$thumbnail_width."px;height:".$thumbnail_height."px\"></a></td>\r\n";
                  $current_col++;
                  if ($current_col == $photoalbum_columns)
                  {
                     $current_col = 0;
                     $cms_content .= "</tr>\r\n";
                  }
               }
               $images++;
            }
         }
         $plugin_dir = basename(dirname(__FILE__));
         $pages = ceil($images/$count);
         for ($i=0; $i<$pages; $i++)
         {
            $page = $i * $count;
            $current_page = $i + 1;
            if ($page == $first)
            {
               $pagination .= " $current_page ";
            }
            else
            {
               $pagination .= " <a href=\"".MAIN_SCRIPT."?page=$id&amp;action=photoalbum&amp;plugin=$plugin_dir&amp;first=$page\">$current_page</a>\r\n ";
            }
         }
         $cms_content .= "<tr><td align=\"center\" colspan=\"$photoalbum_columns\">$pagination</td></tr>\r\n";
         $cms_content .= "</table>\r\n";
      }
   }
}

function photoalbum_tab()
{
   global $id, $db, $action;

   $html = '';
   $photoalbum_enabled = 0;
   $photoalbum_folder = './images/';
   $photoalbum_columns = 3;
   $photoalbum_rows = 3;
   $photoalbum_size = 120;

   $sql = "SELECT * FROM CMS_PHOTOALBUM WHERE page_id='$id'";
   $result = mysqli_query($db, $sql);
   if ($data = mysqli_fetch_array($result))
   {
      $photoalbum_enabled = $data['album_enabled'];
      $photoalbum_folder = $data['album_folder'];
      $photoalbum_columns = $data['album_columns'];
      $photoalbum_rows = $data['album_rows'];
      $photoalbum_size = $data['album_size'];
   }
 
   $html .= "<table width=\"50%\" cellspacing=\"0\" cellpadding=\"2\">\n";
   $html .= "<tr><td style=\"width:10%;white-space:nowrap;\"><label>Enable photo album:&nbsp;&nbsp;</label></td><td><select class=\"form-control\" name=\"photoalbum-enabled\" size=\"1\"><option value=\"0\"";
   if ($photoalbum_enabled == 0)
   {
      $html .= " selected";
   }
   $html .= ">disabled</option><option value=\"1\"";
   if ($photoalbum_enabled == 1)
   {
      $html .= " selected";
   }
   $html .= ">enabled</option></select></td></tr>";
   $html .= "<tr><td><label>Images folder:</label></td><td><input class=\"form-control\" name=\"photoalbum-folder\" style=\"width:100%;\" value=\"$photoalbum_folder\"></td></tr>";
   $html .= "<tr><td><label>Columns:</label></td><td><input class=\"form-control\" name=\"photoalbum-columns\" size=\"4\" value=\"$photoalbum_columns\"></td></tr>";
   $html .= "<tr><td><label>Rows:</label></td><td><input class=\"form-control\" name=\"photoalbum-rows\" size=\"4\" value=\"$photoalbum_rows\"></td></tr>";
   $html .= "<tr><td><label>Thumbnail size:</label></td><td><input class=\"form-control\" name=\"photoalbum-size\" size=\"4\" value=\"$photoalbum_size\"></td></tr>";
   $html .= "</table>\n";

   return $html;
}

function photoalbum_init()
{
   global $authorized, $db;
   if ($authorized)
   {
      $sql = "CREATE TABLE IF NOT EXISTS CMS_PHOTOALBUM (
              id INT UNSIGNED NOT NULL AUTO_INCREMENT,
              page_id INT, 
              album_enabled INT, 
              album_folder TEXT, 
              album_columns INT, 
              album_rows INT, 
              album_size INT,
              PRIMARY KEY(id));";
      mysqli_query($db, $sql) or die(mysqli_error($db));
   }
}

function photoalbum_update($id)
{
   global $authorized, $db;
   if ($authorized)
   {
      if (isset($_REQUEST['photoalbum-enabled']) && 
          isset($_REQUEST['photoalbum-folder']) &&
          isset($_REQUEST['photoalbum-columns']) &&
          isset($_REQUEST['photoalbum-rows']) &&
          isset($_REQUEST['photoalbum-size']))
      {
         $photoalbum_enabled = $_REQUEST['photoalbum-enabled'];
         $photoalbum_folder = $_REQUEST['photoalbum-folder'];
         $photoalbum_columns = $_REQUEST['photoalbum-columns'];
         $photoalbum_rows = $_REQUEST['photoalbum-rows'];
         $photoalbum_size = $_REQUEST['photoalbum-size'];

         $sql = "SELECT * FROM CMS_PHOTOALBUM WHERE page_id=".$id;
         $result = mysqli_query($db, $sql);
         if (mysqli_num_rows($result) == 0)
            $sql = "INSERT INTO CMS_PHOTOALBUM (`album_enabled`, `album_folder`, `album_columns`, `album_rows`, `album_size`, `page_id`) VALUES ('$photoalbum_enabled', '$photoalbum_folder', '$photoalbum_columns', '$photoalbum_rows', '$photoalbum_size', '$id');";
         else
            $sql = "UPDATE CMS_PHOTOALBUM SET album_enabled='$photoalbum_enabled', album_folder='$photoalbum_folder', album_columns='$photoalbum_columns', album_rows='$photoalbum_rows', album_size='$photoalbum_size' WHERE page_id='$id'";
         mysqli_query($db, $sql) or die(mysqli_error($db));
      }
   }
}

function photoalbum_page_header()
{
   global $cms_header;
   $cms_header .= "<link rel=\"stylesheet\" href=\"";
   $cms_header .= basename(dirname(dirname(__FILE__)));
   $cms_header .= "/";
   $cms_header .= basename(dirname(__FILE__));
   $cms_header .= "/photoalbum.css\" type=\"text/css\">\n";
}
?>