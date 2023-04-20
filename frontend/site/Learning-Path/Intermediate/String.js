   
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
      $(".SlideMenu1-folder a").click(function()
      {
         var $popup = $(this).parent().find('ul');
         if ($popup.is(':hidden'))
         {
            $("#SlideMenu1 > ul > li > ul").slideUp();
            $popup.slideDown();
            $popup.attr('aria-expanded', 'true');
         }
         else
         {
            $popup.slideUp();
            $popup.attr('aria-expanded', 'false');
         }
      });
      $("#SlideMenu1").affix({offset:{top: $("#SlideMenu1").offset().top}});
      $("#ThemeableButton2").button({ icon: 'ui-primary', iconPosition: 'beginning' });
      $("#ThemeableButton1").button({ icon: 'ui-secondary', iconPosition: 'end' });
      $("#ThemeableButton3").button();
   });
