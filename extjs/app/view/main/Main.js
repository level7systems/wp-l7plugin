/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    title: 'Users',
    xtype: 'user-grid'
  }, {
    xtype: 'panel',
    bind: {
      title: '{name}'
    },
    region: 'west',
    width: 180,
    margins:  '0 5 0 5',
    split: true
  }, {
    xtype: 'panel',
    region: 'east',
    layout: 'accordion',
    width: 250,
    split: true,
    items: [{
      title: 'Ring Groups',
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
