<?php
   global $db;

   if (isset($_REQUEST['page_id']))
   {
      $page_id = (int)$_REQUEST['page_id'];
      
      header('Content-type: application/octet-stream');
      header('Content-Disposition: attachment; filename="form-'.$page_id.'-export.csv"');
      
      $arr_form_data = array();
      $sql = "SELECT id, submit_date FROM CMS_FORM_DATA WHERE page_id=$page_id ORDER BY submit_date";
      $result = mysqli_query($db, $sql);
      while ($data = mysqli_fetch_array($result))
      {
         $arr_form_data[$data['id']] = $data['submit_date'];
      }

      $arr_form_fields = array();
      $sql = "SELECT field_name FROM CMS_FORM_FIELDS WHERE page_id=$page_id ORDER BY field_index";
      $result = mysqli_query($db, $sql);
      while ($data = mysqli_fetch_array($result))
      {
         $arr_form_fields[] = $data['field_name'];
      }

      echo '"Date","';
      echo join('","', $arr_form_fields).'"'."\r\n";

      foreach($arr_form_data as $id=>$date)
      {
         echo '"'.$date.'",';
	 for ($i=0; $i<count($arr_form_fields); ++$i)
         {
	    $sql = "SELECT field_value FROM CMS_FORM_VALUES WHERE form_data_id=".$id." AND field_name='".addslashes($arr_form_fields[$i])."'";
            $result = mysqli_query($db, $sql);
            if  ($data = mysqli_fetch_array($result))
            {
               echo '"'.str_replace('\\"','""',addslashes($data['field_value'])).'"';
               if ($i<count($arr_form_fields)-1)
                  echo ',';
               else 
                  echo "\r\n";
            }
         }
      }
   }
?>