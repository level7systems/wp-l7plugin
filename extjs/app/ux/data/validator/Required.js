/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Level7.ux.data.validator.Required', {
  extend: 'Ext.data.validator.Presence',
  alias: 'data.validator.required',
  
  type: 'required',
  
  config: {
    message: 'This field is required'
  }
});