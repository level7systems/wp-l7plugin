/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.store.Users', {
  extend: 'Ext.data.Store',
  requires: [
    'Level7.model.User'
  ],
  model: 'Level7.model.User',
  proxy: {
    type: 'rest',
    url: '/users',
    format: 'json',
    reader: {
      type: 'json',
      rootProperty: 'users'
    }
  },
  autoLoad: true
});