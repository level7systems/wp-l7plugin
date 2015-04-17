/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery(document).ready(function() {
  
  
  var $link = jQuery('a').filter(function(index) {
    return jQuery(this).text() == "Login";
  });
  
  $link.click(function() {
    
    var appUrl = "/wp-content/plugins/level7/extjs/index.html";
    var loginUrl = "http://api.l7dev.co.cc/login";
    var apiKey = localStorage.getItem('apiKey');
    if (apiKey) {
      window.location.href = appUrl;
      return false;
    }
    
    var html = "<div id=\"dialog-form\" title=\"Login\">" +
  		"<form id=\"login-form\">" +
  		"<p class=\"errors\"></p>" +
  		"<fieldset>" +
  		"<label for=\"name\">E-mail</label>" +
  		"<input type=\"email\" name=\"username\" id=\"username\" placeholder=\"E-mail address\" class=\"text ui-widget-content ui-corner-all\" required>" +
  	  "<label for=\"password\">Password</label>" +
  	  "<input type=\"password\" name=\"password\" id=\"password\" placeholder=\"Your password\" class=\"text ui-widget-content ui-corner-all\" required>" +
  	  "<input type=\"submit\" tabindex=\"-1\" style=\"position:absolute; top:-1000px\">" +
  	  "</fieldset>" +
  	  "</form>" +
  	  "</div>"
  	;
    
    dialog = jQuery(html).dialog({
      autoOpen: true,
      height: 300,
      width: 350,
      modal: true,
      buttons: {
        "Login": function() {
          
        // TODO: CORS with IE8 support
        var $form = jQuery('#login-form');
          jQuery.ajax({
            type: "POST",
            url: loginUrl,
            data: $form.serialize(),
            dataType: 'json',
            success: function(response, status, xhr) {
              jQuery('p.errors').html("");
              localStorage.setItem('apiKey', response.apiKey);
              localStorage.setItem('userId', response.userId);
              
              window.location.href = appUrl;
            },
            error: function(xhr, status, error) {
              
              jQuery('p.errors').html("").append(xhr.responseJSON.message);
            }
          });
        }
      }
    });
    
    return false;
  });
});
