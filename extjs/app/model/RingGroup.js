/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var writer = Ext.create('Level7.ux.data.writer.Json', {
  writeAllFields: false,
  excludeFields: ['id', 'customer'],
  rootProperty: 'ringgroup'
});

Ext.define('Level7.model.RingGroup', {
  extend: 'Ext.data.Model',
  
  idProperty: 'id',
  fields: [
    { name: 'customer', type: 'int' },
    { name: 'moh', type: 'int' },
    { name: 'name', type: 'string'},
    { name: 'users', type: 'int'},
    { name: 'strategy', type: 'string'},
    { name: 'finalDstType', type: 'string', defaultValue: 'X'},
    { name: 'finalDstId', type: 'int', defaultValue: 0},
    { name: 'ringTime', type: 'int', defaultValue: 15},
    { name: 'cliPrefix', type: 'int', defaultValue: 0},
    { name: 'number', type: 'string'}
  ],
  
  validators: {
    name: 'required',
    strategy: 'required'
  },
  
  proxy: {
    type: 'rest',
    url: '/ringgroups.json',
    reader: {
      type: 'json',
      rootProperty: 'ring_groups'
    },
    writer: writer
  }
});