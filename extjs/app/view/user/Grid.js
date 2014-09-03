/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define("Level7.view.user.Grid",{
  extend: "Ext.grid.Panel",
  alias: "widget.user-grid",
  
  controller: "user-grid",
  viewModel: {
      "type": "user-grid"
  },
  
  store: 'Users',
  columns: [
    {
      xtype: 'actioncolumn',
      width: 20,
      handler: 'onEditClick',
      stopSelection: false,
      items: [{
        tooltip: 'Edit user',
        iconCls: 'edit'
      }]
    }, {
      text: 'First Name', 
      dataIndex: 'firstName'
    }, {
      text: 'Last Name', 
      dataIndex: 'lastName'
    }, {
      text: 'E-mail', 
      dataIndex: 'email'
    }, {
      text: 'Ext.', 
      dataIndex: 'ext'
    }, {
      text: 'DDI number', 
      dataIndex: 'email'
    }, {
      text: 'CLI', 
      dataIndex: 'cli'
    }
  ],
  tbar: [
    '->',
    {
      text: 'Add',
      handler: 'onAddClick'
    }
  ]
    
});
