/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
Ext.define('Level7.Application', {
  extend: 'Ext.app.Application',
  
  requires: [
    'Level7.ux.*'
  ],
           
  name: 'Level7',

  controllers: [
      'Root@Level7.controller'
  ],
  stores: [
    'Customers',
    'Users',
    'RingGroups',
    'Mohs'
  ],
  
  onBeforeLaunch: function () {
    
    var apiKey = localStorage.getItem('apiKey');
    
    // TODO: get user data by apiKey
    // verify user data
    // log out
    if (!apiKey) {
      window.location.href = "/";
    }
  
    this.callParent();
  }
  
});
