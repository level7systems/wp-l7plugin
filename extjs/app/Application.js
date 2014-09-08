/**
 * The main application class. An instance of this class is created by app.js when it calls
 * Ext.application(). This is the ideal place to handle application launch and initialization
 * details.
 */
Ext.define('Level7.Application', {
  extend: 'Ext.app.Application',
  
  requires: [
    'Level7.ux.*'
  ],
           
  name: 'Level7',

  stores: [
    'Customers',
    'Users',
    'RingGroups',
    'Mohs'
  ],
  
  launch: function () {
      // TODO - Launch the application
    // Ext.data.Connection.prototype.useDefaultXhrHeader = false;
  }
});
