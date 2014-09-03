/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define("Level7.view.ringgroup.Grid",{
  extend: "Ext.grid.Panel",
  alias: "widget.ringgroup-grid",
  
  controller: "ringgroup-grid",
  viewModel: {
    type: "ringgroup-grid"
  },
  
  store: 'RingGroups',
  columns: [
    {
      xtype: 'actioncolumn',
      width: 20,
      handler: 'onRingGroupEditClick',
      stopSelection: false,
      items: [{
        tooltip: 'Edit ring group',
        iconCls: 'edit'
      }]
    }, {
      text: 'Name', 
      dataIndex: 'name',
      flex: 1
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
