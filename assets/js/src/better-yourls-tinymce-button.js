  /*
    File: better-yourls-tinymce-button.js
    Description: TinyMCE dialog and button for YOURLS integration
    Version: 1.0
    Author: Ken McDonald
    Author URI: https://generation.tech
  */

  /**
   * better-yourls-tinymce-button.js
   * Copyright (C) 2018 by Ken McDonald (ken@generation.tech)
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.
   *
   * This program is distributed in the hope that it will be useful, but
   * WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
   * Public License for more details.
   *
   * You should have received a copy of the GNU General Public License along
   * with this program. If not, see <http://www.gnu.org/licenses/>.
   **/

(function () {
    tinymce.PluginManager.add('better_yourls_tinymce_button', function (editor, url) {
        editor.addButton('better_yourls_tinymce_button', {
            title: 'Insert YOURLS link',
            icon: true,
            image: url + '/../images/yourls-favicon.gif',
            onclick: function () {
                // Open window
                editor.windowManager.open({
                    title: 'Insert YOURLS link',
                    body: [{
                      type:  'textbox',
                      name:  'link_url',
                      label: 'URL'
                    },
                    {
                      type:  'textbox',
                      name:  'link_text',
                      label: 'Text'
                    },
                    {
                      type:   'checkbox',
                      name:   'link_open',
                      label:  'Open in a new window',
                      checked: true
                    }],
                    onsubmit: function (e) {

                      var data = {
                        'action'	: 'yourls_get_shortlink',
                        'url'	: e.data.link_url
                      };
                      var res = jQuery.ajax({
                        type:     'POST',
                        url:      ajaxurl,
                        data:     data,
                        dataType: 'json',
                        success: function( response ) {

                                  values = response[0];
                                  link_text = e.data.link_text;
                                  if ( !link_text ) {
                                    if ( values.title != '' ) {
                                      link_text = values.title;
                                    }
                                  }

                                  if ( values.shortlink != 'invalid') {
//                                    var urltag = '<a title="' + e.data.link_text;   // use dialog title for link text
                                    var urltag = '<a title="' + e.data.link_url;      // use original long URL for link text
                                    urltag += '" href="' + values.shortlink;
                                    urltag += e.data.link_open ? '" target="_blank">' : '">';
                                    urltag += link_text + '</a>';
                                    editor.insertContent(urltag);
                                  } else {
                                    alert("Error creating YOURLS short URL");
                                  }
                                },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert("Error calling YOURLS short URL AJAX");},
                      });
                    },
                    width: 700,
                    height: 200
                });
            }
        });
    });
})();
