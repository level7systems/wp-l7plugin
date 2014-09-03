/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.view.main.MainController', {
  extend: 'Ext.app.ViewController',

  requires: [
      'Ext.MessageBox'
  ],

  alias: 'controller.main',

  onClickButton: function () {
      Ext.Msg.confirm('Confirm', 'Are you sure?', 'onConfirm', this);
  },

  onConfirm: function (choice) {
      if (choice === 'yes') {
          //
      }
  }
});
