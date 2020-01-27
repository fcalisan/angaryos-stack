import { Injectable } from '@angular/core';
import { BaseHelper } from './base';

declare var $: any;

@Injectable()
export class AeroThemeHelper 
{     
  public baseMenu = [];

    constructor( ) { }



    /****    GUI Features Functions    ****/

    public loadPageScripts()
    {
      setTimeout(() => 
      {
        $.getScript('assets/themes/aero/assets/bundles/libscripts.bundle.js', function()
        {
          $.getScript('assets/themes/aero/assets/bundles/vendorscripts.bundle.js', function()
          {
            $.getScript('assets/themes/aero/assets/bundles/mainscripts.bundle.js');
            $.getScript('assets/themes/aero/assets/plugins/jquery-inputmask/jquery.inputmask.bundle.js');

            
            $.getScript('assets/themes/aero/assets/bundles/jvectormap.bundle.js');
            $.getScript('assets/themes/aero/assets/bundles/sparkline.bundle.js');
            $.getScript('assets/themes/aero/assets/bundles/c3.bundle.js');

            $.getScript('assets/ext_modules/select2/select2.min.js');

            
            $.getScript('assets/themes/aero/assets/plugins/jquery-validation/jquery.validate.js');
            $.getScript('assets/themes/aero/assets/plugins/jquery-steps/jquery.steps.js');
          });     

          $.getScript('assets/ext_modules/ace-builds/src-min/ace.js', function()
          {
            $.getScript('assets/ext_modules/ace-builds/src-min/mode-php.js');
            $.getScript('assets/ext_modules/ace-builds/src-min/mode-sql.js');
            $.getScript('assets/ext_modules/ace-builds/src-min/theme-twilight.js');
            $.getScript('assets/ext_modules/ace-builds/src-min/theme-github.js');
          });
        });   
      }, 10);
    }
    
    public async addEventForFeature(name)
    {
        await BaseHelper.sleep(500);

        switch (name) 
        {
            case 'leftSideBar': return this.addLeftSideBarEvents();
            case 'mobileMenuButton': return this.addMobileMenuButtonEvents();
            case 'rightIconToggleButton': return this.addRightIconToggleButtonEvents();
            case 'standartElementEvents': return this.addStandartElementEvents();

            case 'layoutCommonEvents': 
                this.addMobileMenuButtonEvents();
                this.addRightIconToggleButtonEvents();
                return;

            default: return alert(name + " event bulunamadı");
        }
    }

    public addStandartElementEvents()
    {
      $('.tooltip-inner').remove();
      $('[data-toggle="tooltip"]').tooltip();
    }

    public addMobileMenuButtonEvents()
    {
        $(".mobile_menu").on("click", function() 
        {
            $(".sidebar").toggleClass("open")
        });
    }

    public addRightIconToggleButtonEvents()
    {
        $(".right_icon_toggle_btn").on("click", function() 
        {
            $("body").toggleClass("right_icon_toggle")
        });
    }

    public addLeftSideBarEvents()
    {
      if(typeof BaseHelper.addedScripts['leftSideBar'] != "undefined") return;
      
      $.each($(".menu-toggle.toggled"), function(a, b) 
      {
          $(b).next().slideToggle(0)
      });

      $(".menu-toggle").on("click", function(a) 
      {
          var b = $(this),
              c = b.next();

          if ($(b.parents("ul")[0]).hasClass("list")) 
          {
              var d = $(a.target).hasClass("menu-toggle") ? a.target : $(a.target).parents(".menu-toggle");
              $.each($(".menu-toggle.toggled").not(d).next(), function(a, b) {
                  $(b).is(":visible") && ($(b).prev().toggleClass("toggled"), $(b).slideUp())
              })
          }
          b.toggleClass("toggled"), c.slideToggle(320)
      });

      BaseHelper.addedScripts['leftSideBar'] = true;
    }



    /****   Base Menu Functions    ****/
    
    private controlSearchForTableItem(item, search)
    {
      if(search.length == 0) return true;

      search = search.toLocaleLowerCase();

      if(item.display_name.toLocaleLowerCase().indexOf(search) > -1) return true;
      if(item.name.toLocaleLowerCase().indexOf(search) > -1) return true;

      return false;
    }

    private getTablesMenuItem(search = "")
    {
      var tables =
      {
        title: 'Tablolar',
        icon: 'zmdi-grid',
        toggled: search.length > 0,
        children: []
      };

      var children = [];
      var userTables = BaseHelper.loggedInUserInfo.menu;
      for(var i = 0; i < userTables.length; i++)
      {
        if(!this.controlSearchForTableItem(userTables[i], search)) continue;

        var temp = 
        {
          title: userTables[i].display_name,
          link: '/'+BaseHelper.angaryosUrlPath+'/table/'+userTables[i].name
        };

        children.push(temp);
      }

      tables.children = children.sort(function(a, b) 
      { 
        if(a.title == null) a.title = a.link;
        if(b.title == null) b.title = b.link;

        return a.title.localeCompare(b.title);
      });
      return tables
    }

    public getHomePageMenuItem()
    {
      var homePage =
      {
        title: 'Anasayfa',
        icon: 'zmdi-home',
        link: '/',
      };
      return homePage;
    }

    public getDashboardPageMenuItem()
    {
      var homePage =
      {
        title: 'Göstergeler',
        icon: 'zmdi-copy',
        link: '/'+BaseHelper.angaryosUrlPath+'/dashboard',
      };
      return homePage;
    }

    public updateBaseMenu(search = "")
    {
      this.baseMenu =  [ ];

      this.baseMenu.push(this.getHomePageMenuItem());
      this.baseMenu.push(this.getDashboardPageMenuItem());
      this.baseMenu.push(this.getTablesMenuItem(search));
      
      setTimeout(() => 
      {
        delete BaseHelper.addedScripts['leftSideBar'];
        this.addEventForFeature("leftSideBar");
      }, 100);
    }
}