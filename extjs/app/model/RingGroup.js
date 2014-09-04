/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.model.RingGroup', {
  extend: 'Ext.data.Model',
  fields: [
    { name: 'customerId', reference: 'Customer' },
    { name: 'mohId', reference: 'Moh' },
    { name: 'name', type: 'string'},
    { name: 'users', type: 'int'},
    { name: 'strategy', type: 'string'},
    { name: 'finalDestTzpe', type: 'string'},
    { name: 'finalDestId', type: 'int'},
    { name: 'ringTime', type: 'int'},
    { name: 'cliPrefix', type: 'int'},
    { name: 'number', type: 'string'}
  ],
  
  proxy: {
    type: 'rest',
    url: '/ringgroups.json',
    reader: {
      type: 'json',
      rootProperty: 'ring_groups'
    }
  }
});