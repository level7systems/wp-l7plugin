/**
 * This class is the main view for the application. It is specified in app.js as the
 * "autoCreateViewport" property. That setting automatically applies the "viewport"
 * plugin to promote that instance of this class to the body element.
 *
 * TODO - Replace this content of this view to suite the needs of your application.
 */
Ext.define('Level7.view.main.Main', {
  extend: 'Ext.container.Container',

  xtype: 'app-main',
  
  controller: 'main',
  viewModel: {
    type: 'main'
  },

  layout: {
      type: 'border'
  },

  items: [{
    xtype: 'container',
    id: 'app-header',
    region: 'north',
    height: 60,
    layout: {
        type: 'hbox',
        align: 'middle'
    },
    items: [
      {
        xtype: 'component',
        id: 'app-header-logo'
      },{
        xtype: 'component',
        cls: 'app-header-text',
        // bind: '{currentOrg.name}',
        flex: 1
      },{
        xtype: 'component',
        id: 'app-header-username',
        cls: 'app-header-text',
        // bind: '{currentUser.name}',
        margin: '0 10 0 0'
      }
    ]
  }, {
    region: 'center',
    xtype: 'tabpanel',
    items:[{
      title: 'Dashboard',
      html: '<h2>Content appropriate for the current navigation.</h2>'
    }]
  }, {
    xtype: 'panel',
    bind: {
      title: '{name}'
    },
    region: 'west',
    width: 180,
    split: true
  }, {
    xtype: 'panel',
    region: 'east',
    layout: 'accordion',
    width: 180,
    split: true,
    items: [{
      xtype: 'ringgroup-grid'
    }]
  }, {
    region: 'south',
    split:    false,
    height:   30,
    margins:  '10 0 0 0',
    border:   false
  }]
});
