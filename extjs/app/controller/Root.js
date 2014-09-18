Ext.define('Level7.controller.Root', {
    extend: 'Ext.app.Controller',
    
    requires: [
        //'Level7.view.login.Login',
        'Level7.view.main.Main'
    ],
     
    //loadingText: 'Loading...',
     
    onLaunch: function () {
         
        if (Ext.isIE8) {
             Ext.Msg.alert('Not Supported', 'This app is not supported on Internet Explorer 8. Please use a different browser.');
             return;
        }
         
        /*
        this.session = new Ext.data.Session({
            autoDestroy: false
        });
        */
        
        this.showUI();
         
        /*
        this.login = new Level7.view.login.Login({
            session: this.session,
            autoShow: true,
            listeners: {
                scope: this,
                login: 'onLogin'
            }
        });
        */
    },
           
    showUI: function() {
      this.viewport = new Level7.view.main.Main({});
  },
});
