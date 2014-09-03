/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define("Level7.view.ringgroup.EditWindow",{
  extend: "Ext.window.Window",
  
  controller: "ringgroup-editwindow",

  width: 300,
  minHeight: 250,
  height: 450,
  bodyPadding: 10,
  layout: {
      type: 'vbox',
      align: 'stretch'
  },
  
  initComponent: function() {

    this.items = [{
      xtype: 'form',
      layout: 'anchor',
      reference: 'form',
      bodyPadding: 10,
      border: false,
      frame: true,
      items: [
        {
          xtype: 'textfield',
          name: 'name',
          fieldLabel: 'Name',
          labelWidth: 90,
          anchor: '100%'
        }, {
          xtype: 'checkbox',
          name: 'cli',
          boxLabel: 'CLI Prefix',
          margin: '0 5 0 0'
        }, {
          xtype: 'box',
          autoEl: {
            cls: 'divider'
          }
        }, {
          xtype: 'checkbox',
          name: 'has_reminder',
          boxLabel: 'Reminder',
          margin: '0 5 0 0'
        }, {
          xtype: 'datefield',
          name: 'reminder_date',
          margin: '0 5 0 0',
          disabled: true,
          editable: false
        }, {
          xtype: 'timefield',
          name: 'reminder_time',
          disabled: true,
          editable: false
        }, {
          xtype: 'htmleditor',
          name: 'note',
          anchor: '100% -90'
        }, {
          xtype: 'hiddenfield',
          name: 'reminder'
        }, {
          xtype: 'hiddenfield',
          name: 'done'
        }
      ],
      buttons: [
        {
          text: 'Save',
          handler: 'onSaveClick'
        }, {
          text: 'Cancel',
          listeners: {
            click: 'closeView'
          }
        }
      ]
    }];
  
    this.callParent(arguments);
  
  }
    
});
