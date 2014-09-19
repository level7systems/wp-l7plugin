Ext.define('Level7.controller.Root', {
    extend: 'Ext.app.Controller',
    
    requires: [
        //'Level7.view.main.Login',
        'Level7.view.main.Main',
        'Level7.model.User'
    ],
     
    loadingText: 'Loading...',
     
    onLaunch: function () {
        var me = this;
        
        if (Ext.isIE8) {
             Ext.Msg.alert('Not Supported', 'This app is not supported on Internet Explorer 8. Please use a different browser.');
             return;
        }
         
        var id = parseInt(localStorage.getItem('userId'));
        Level7.model.User.load(id, {
            success: function (user) {
              me.showUI(user);
            }
        });
        
        /*
        this.login = new Level7.view.main.Login({
            session: this.session,
            autoShow: true,
            listeners: {
                scope: this,
                login: 'onLogin'
            }
        });
        */
    },
           
    showUI: function(user) {
      
      this.viewport = new Level7.view.main.Main({
          viewModel: {
              data: {
                  currentUser: user
              }
          }
      });
  }
});
