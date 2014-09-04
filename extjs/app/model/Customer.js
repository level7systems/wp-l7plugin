/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.model.Customer', {
  extend: 'Ext.data.Model',
  
  fields: [
    { name: 'name', type: 'auto' },
    { name: 'address', type: 'auto' },
    { name: 'postcode', type: 'auto' },
    { name: 'city', type: 'auto' },
    { name: 'state', type: 'auto' },
    { name: 'country', type: 'auto' },
    { name: 'timezone', type: 'auto' },
    { name: 'areaCode', type: 'auto' },
    { name: 'tel', type: 'auto' },
    { name: 'telCode', type: 'auto' },
    { name: 'telVerified', type: 'int' },
    { name: 'trialActivatedAt', type: 'auto' },
    { name: 'culture', type: 'auto' },
    { name: 'balance', type: 'auto' },
    { name: 'balanceDisabled', type: 'int' },
    { name: 'minAllowance', type: 'int' },
    { name: 'autoTopup', type: 'int' },
    { name: 'autoTopupCardId', type: 'int' },
    { name: 'autoTopupAmount', type: 'int' },
    { name: 'vat', type: 'auto' },
    { name: 'ddiLocalFormat', type: 'int' },
    { name: 'isConfirmed', type: 'int' },
    { name: 'escStatus', type: 'auto' },
    { name: 'escDDi', type: 'auto' },
    { name: 'language', type: 'auto' },
    { name: 'maxOutCalls', type: 'auto' },
    { name: 'mobileFrom', type: 'auto' },
    { name: 'dataCenter', type: 'auto' },
    { name: 'billingMode', type: 'auto' },
    { name: 'billingEmail', type: 'auto' },
    { name: 'fraudCheck', type: 'int' },
    { name: 'expensiveCalls', type: 'int' },
    { name: 'ftpEnabled', type: 'int' },
    { name: 'ftpPassword', type: 'auto' },
    { name: 'createdAt', type: 'auto' }
  ],
  
  proxy: {
    type: 'rest',
    url: '/customers.json',
    reader: {
      type: 'json',
      rootProperty: 'customers'
    }
  }
});
