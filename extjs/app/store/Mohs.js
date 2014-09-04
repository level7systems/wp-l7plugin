/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.store.Mohs', {
  extend: 'Ext.data.Store',
  requires: [
    'Level7.model.Moh'
  ],
  model: 'Level7.model.Moh',
  autoLoad: true
});