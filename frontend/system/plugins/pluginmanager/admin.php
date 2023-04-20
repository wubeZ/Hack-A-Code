<?php
   global $plugins;

   $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
   $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
   $plugin_folder = "./".basename(dirname(dirname(__FILE__)))."/";

   if (!$authorized)   {
      exit;
   }
   if (($action == 'disable' || $action == 'enable') && $name != '')   {
      if ($action == 'enable')
         $newname = ltrim($name, '_');
      else
         $newname = "_".$name;
     
      rename($plugin_folder.$name, $plugin_folder.$newname);    
      $plugins[$newname] = $plugins[$name]; 
      unset($plugins[$name]); 
   }
   echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\">\n";   echo "<tr><th>Plugin Name</th><th>Description</th><th>Action</th>\n";
   foreach($plugins as $pluginname=>$p)
   {
     echo "<tr><td>";
     echo $p['name'];
     echo "</td><td>";
     echo $p['description'];
     echo "</td><td>";
     if (substr($pluginname, 0, 1) != '_')
     {
        echo "<a href=\"" . MAIN_SCRIPT . "&amp;action=disable&amp;name=" . $pluginname . "\">Disable</a>\n";
     }
     else
     {
        echo "<a href=\"" . MAIN_SCRIPT . "&amp;action=enable&amp;name=" . $pluginname . "\">Enable</a>\n";
     }
     echo "</td></tr>\n";
   }   echo "</table>\n";?>