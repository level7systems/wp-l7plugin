Ext.define('Level7.model.Contact', {
    extend: 'Ext.data.Model',
    
    fields: [
        { name: 'id', type: 'int' },
        { name: 'groupName', type: 'auto' },
        { name: 'firstName', type: 'auto' },
        { name: 'lastName', type: 'auto' },
        { name: 'qqUuid', type: 'auto' },
        { name: 'createdAt', type: 'auto' }

    ]
});
