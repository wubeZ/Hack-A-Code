<?php
   $asset_folder = './files/';
   $upload_result = '';
   $labelAction = "Action";
   $labelDelete = "Delete";
   $labelFileName = "Filename";
   $labelSize = "Size";
   $labelLastModified = "Last modified";
   $labelUploadFile = "Upload File:";
   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

   if (!$authorized)
   {
      exit;
   }
   if ($action == 'deletefile')
   {
      $filename = $asset_folder . $_REQUEST['file'];
      if (file_exists($filename))
      {
         unlink ($filename) or die ("Could not delete file");
      }
   }
   else
   if ($action == 'upload')
   {
      if (isset($_FILES['filename']))
      {
         $name  = $_FILES['filename']['name'];
         $tmp_name = $_FILES['filename']['tmp_name'];
         $error = $_FILES['filename']['error'];
         if ($error > 0)
         {
            die("File upload error: " . $error);
         }
         else
         {
            if (!move_uploaded_file($tmp_name, $asset_folder. $name))
            {
               die("File upload error: Upload failed, please verify the folder's permissions.");
            }
         }
      }
      else
      {
         die("File upload error: No filename specified!");
      }
   }
   echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";
   echo "<tr><th>$labelFileName</th>\n";
   echo "<th>$labelSize</th>\n";
   echo "<th>$labelLastModified</th>\n";
   echo "<th>$labelAction</th></tr>\n";
   if ($handle = opendir($asset_folder))
   {
      while (false !== ($filename = readdir($handle)))
      {
         if ($filename != "." && $filename != ".." && is_file($asset_folder.$filename))
         {
            echo "<tr>\n";
            echo "<td>" . $filename . "</td>\n";
            echo "<td>";
            $bytes = filesize($asset_folder.$filename);
            if ($bytes < 1024)
               echo $bytes.' B';
            elseif ($bytes < 1048576)
               echo round($bytes / 1024, 2).' KB';
            elseif ($bytes < 1073741824)
               echo round($bytes / 1048576, 2).' MB';
            echo "</td>\n";
            echo "<td>" . date("Y-m-d H:i:s", filemtime($asset_folder.$filename)) . "</td>\n";
            echo "<td>\n";
            echo "   <a href=\"" . MAIN_SCRIPT . "&amp;action=deletefile&file=" . $filename . "\">$labelDelete</a>\n";
            echo "</td>\n";
            echo "</tr>\n";
         }
      }
      closedir($handle);
   }
   echo "</table>\n";

   echo "<p><br>$labelUploadFile</p>\n";
   echo "<form enctype=\"multipart/form-data\" action=\"" . MAIN_SCRIPT . "\" method=\"post\">\n";
   echo "<input type=\"hidden\" name=\"action\" value=\"upload\">\n";
   echo "<input type=\"file\" size=\"50\" name=\"filename\"><br>\n";
   echo "<input type=\"submit\" name=\"submit\" value=\"Upload\">\n";
   echo "</form>";
?>