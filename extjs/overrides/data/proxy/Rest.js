/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Ext.define('Ext.overrides.data.proxy.Rest',{
  override: 'Ext.data.proxy.Rest',
  
  urlPrefix: '/wp-content/plugins/level7/api/proxy.php',
  buildUrl: function(request) {
    
    var me = this,
      url = me.getUrl(request);
  
    if (this.urlPrefix) {
      url = this.urlPrefix + url;
      request.setUrl(url);
    }

    return me.callParent([request]);
  }
});