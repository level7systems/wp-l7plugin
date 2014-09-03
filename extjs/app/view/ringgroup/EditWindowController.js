/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.view.ringgroup.EditWindowController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.ringgroup-editwindow',
  
  requires: [
     'Ext.window.Toast'
   ],
   
  onSaveClick: function() {
    var form = this.lookupReference('form'),
      rec;
     
    if (form.isValid()) {
      rec = this.getViewModel().getData().theTicket;

      Ext.Msg.wait('Saving', 'Saving ticket...');
      rec.save({
        scope: this,
        callback: this.onComplete
      });
    }
  },

  onComplete: function() {
    Ext.Msg.hide();
    Ext.toast({
      title: 'Save',
      html: 'Ring group saved successfully',
      align: 't',
      bodyPadding: 10
    });
  },
  
  onCloseClick: function() {
  
  }
    
});
