/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
Ext.define('Level7.ux.data.writer.Json', {
  extend: 'Ext.data.writer.Json',
  
  config: {
    excludeFields: [],
    rootProperty: false
  },
  
  getRecordData: function(record, operation) {
    var me = this,
      excludeFields = me.config.excludeFields;
    
    var data = this.callParent([record]);
    // unset property id
    
    console.log(excludeFields);
    
    Ext.each(excludeFields, function(property) {
      delete data[property];
    });
    
    return data;
  },
  transform: function(data, request) {
    var me = this,
      rootProperty = me.config.rootProperty;
    
    console.log(rootProperty);
    
    if (!rootProperty) {
      return data;
    }
    return { rootProperty: data};
  }

});