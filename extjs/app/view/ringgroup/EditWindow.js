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
  
  requires: [
     'Ext.form.Panel',
     'Ext.form.FieldSet',
     'Ext.form.field.Text',
     'Ext.form.field.Date',
     'Ext.form.field.Time',
     'Ext.form.field.Checkbox',
     'Ext.form.field.Hidden'
   ],
         
  controller: "ringgroup-editwindow",

  width: 300,
  autoHeight: true,
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
      fieldDefauls: {
        labelWidth: 90
      },
      items: [
        {
          xtype: 'textfield',
          name: 'name',
          fieldLabel: 'Name'
        }, {
          xtype: 'checkbox',
          name: 'cliPrefix',
          fieldLabel: 'CLI Prefix',
          height: 40,
          boxLabel: '<span style="font-size: 11px;">Prefix Caller ID with Ring Group name</span>'
        }, {
          xtype: 'grid',
          boxLabel: 'Reminder',
          fieldLabel: 'Users',
          width: 180
        }, {
          xtype: 'combobox',
          name: 'startegy',
          fieldLabel: 'Ring strategy',
          store: {
            fields: ['id','name'],
            data: [
              ["A","Ring All"],
              ["H","Hunt"],
              ["M","Memory Hunt"]
            ]
          },
          displayField: 'name',
          valueField: 'id'
        }, {
          xtype: 'numberfield',
          name: 'ringTime',
          fieldLabel: 'Ring time',
          minValue: 5,
          maxValue: 60,
          step: 5,
          value: 15
        }, {
          xtype: 'combobox',
          fieldLabel: 'Music on Hold',
          allowBlank: false,
          forceSelection: true,
          queryMode: 'local',
          valueField: 'id',
          displayField: 'name',
          publishes: ['value'],
          store: Ext.create('Level7.store.Mohs'),
          bind: {
              value: '{ringgroup.mohId}'
          }
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
