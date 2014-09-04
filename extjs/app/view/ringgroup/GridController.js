/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.view.ringgroup.GridController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.ringgroup-grid',
  
  editRingGroup: function (record) {
    var win = new Ext.create('Level7.view.ringgroup.EditWindow', {
      viewModel: {
        data: {
          user: record
        }
      }
    });
  
    win.show();
  },
  
  onAddClick: function (view, rowIdx, colIdx, item, e, rec) {
    
    var rec = new Level7.model.RingGroup();
    this.editRingGroup(rec);
  },
  
  onEditClick: function (view, rowIdx, colIdx, item, e, rec) {
  
    this.editRingGroup(record);
  }
    
});
