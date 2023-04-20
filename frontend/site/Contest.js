   
   $(document).ready(function()
   {
      $('#ThemeableMenu2 .dropdown-toggle').dropdown({popperConfig:{placement:'bottom-start',modifiers:{computeStyle:{gpuAcceleration:false}}}});
      $(document).on('click','.ThemeableMenu2-navbar-collapse.show',function(e)
      {
         if ($(e.target).is('a') && ($(e.target).attr('class') != 'dropdown-toggle')) 
         {
            $(this).collapse('hide');
         }
      });
      $('#ThemeableMenu3 .dropdown-toggle').dropdown({popperConfig:{placement:'bottom-start',modifiers:{computeStyle:{gpuAcceleration:false}}}});
      $(document).on('click','.ThemeableMenu3-navbar-collapse.show',function(e)
      {
         if ($(e.target).is('a') && ($(e.target).attr('class') != 'dropdown-toggle')) 
         {
            $(this).collapse('hide');
         }
      });
   });
   
   $(document).ready(function()
   {
      function waitForItUpdate()
      {
         // change the date here
         var dateFuture = new Date("May 31, 2023 12:00:00");
         var dateNow = new Date();
         var seconds = Math.floor((dateFuture - (dateNow))/1000);
         var minutes = Math.floor(seconds/60);
         var hours = Math.floor(minutes/60);
         var days = Math.floor(hours/24);
   
         hours = Math.round(hours-(days*24));
         minutes = Math.round(minutes-(days*24*60)-(hours*60));
         seconds = Math.round(seconds-(days*24*60*60)-(hours*60*60)-(minutes*60));
                                    
         $('#waitForItDays').html(days);
         $('#waitForItHours').html(hours);
         $('#waitForItMinutes').html(minutes);
         $('#waitForItSeconds').html(seconds);   
      }
      waitForItUpdate();
      setInterval(waitForItUpdate, 1000);
   });
