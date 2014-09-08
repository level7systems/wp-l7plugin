/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.model.User', {
  extend: 'Ext.data.Model',
  
  fields: [
    { name: 'locationId', type: 'int' },
    { name: 'vmAnnouncementId', type: 'int' },
    { name: 'puckupGroupId', type: 'int' },
    { name: 'email', type: 'auto' },
    { name: 'username', type: 'auto' },
    { name: 'firstName', type: 'auto' },
    { name: 'lastName', type: 'auto' },
    { name: 'mobile', type: 'auto' },
    { name: 'mobileCode', type: 'auto' },
    { name: 'ext', type: 'auto' },
    { name: 'cli', type: 'auto' },
    { name: 'escCli', type: 'auto' },
    { name: 'cc', type: 'int' },
    { name: 'areaCode', type: 'auto' },
    { name: 'ringTime', type: 'int' },
    { name: 'finalDstType', type: 'auto' },
    { name: 'finalDstId', type: 'int' },
    { name: 'vm', type: 'int' },
    { name: 'vmPin', type: 'auto' },
    { name: 'vmToEmail', type: 'int' },
    { name: 'vmToSms', type: 'int' },
    { name: 'cw', type: 'int' },
    { name: 'dnd', type: 'int' },
    { name: 'recAllowed', type: 'int' },
    { name: 'fm', type: 'int' },
    { name: 'fmOnlyDirect', type: 'int' },
    { name: 'fmInitRingTime', type: 'int' },
    { name: 'fmAnnouncement', type: 'int' },
    { name: 'fmAnnouncementId', type: 'int' },
    { name: 'fmAllowed', type: 'int' },
    { name: 'fmTargets', type: 'auto' },
    { name: 'fmRingTime', type: 'auto' },
    { name: 'fmStrategy', type: 'auto' },
    { name: 'fmFinalDestType', type: 'auto' },
    { name: 'fmFinalDstId', type: 'int' },
    { name: 'nun', type: 'auto' },
    { name: 'nunOnlyDirect', type: 'int' },
    { name: 'nunRingTime', type: 'int' },
    { name: 'monitor', type: 'auto' },
    { name: 'language', type: 'auto' },
    { name: 'password', type: 'auto' },
    { name: 'apiKey', type: 'auto' },
    { name: 'roles', type: 'auto' },
    { name: 'active', type: 'int' },
    { name: 'visitedApps', type: 'auto' },
    { name: 'isSubscribed', type: 'boolean' },
    { name: 'isRoom', type: 'boolean' },
    { name: 'indications', type: 'auto' },
    { name: 'audioUpdatedAt', type: 'auto' },
    { name: 'createdAt', type: 'auto' }
  ],
  
  proxy: {
    type: 'rest',
    url: '/users',
    format: 'json',
    reader: {
      type: 'json',
      rootProperty: 'users'
    }
  }
});
