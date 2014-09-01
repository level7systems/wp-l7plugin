/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
Ext.define('Ext.data.proxy.Rest',{
  override: 'Ext.data.proxy.Rest',
  
  baseUrl: 'http://api.l7dev.co.cc',
  buildUrl: function(request) {
    
    var me = this,
      url = me.getUrl(request);
  
    if (this.baseUrl) {
      url = this.baseUrl + url;
      request.setUrl(url);
    }

    return me.callParent([request]);
  }
});
*/