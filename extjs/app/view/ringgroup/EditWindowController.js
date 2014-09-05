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
      rec = this.getViewModel().getData().ringgroup;
      
      Ext.Msg.wait('Saving', 'Saving ticket...');
      
      rec.save({
        scope: this,
        success: this.onSuccess,
        failure: this.onFailure
      });
    }
  },

  onSuccess: function(rec, operation) {
    
    Ext.Msg.hide();
    Ext.toast({
      title: 'Save',
      html: 'Ring group saved successfully',
      align: 't',
      bodyPadding: 10
    });
  },
  
  onFailure: function(rec, operation) {
    var response,
      errors;
    
    response = Ext.JSON.decode(operation.error.response.responseText);
    
    for (error in response.errors.children) {
      if (typeof(response.errors[error]) == 'object') {
        errors+= response.errors[error] + '<br/>';
      }
    }
    
    Ext.Msg.hide();
    Ext.Msg.show({
      title: 'Error',
      msg: errors,
      buttons: Ext.Msg.OK,
      icon: Ext.MessageBox.WARNING,
      minWidth: 400
    });
    
  },
  
  onCloseClick: function() {
  
  }
    
});
