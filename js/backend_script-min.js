var GeoCoderBackend,gcb,geocoderl10n,pagenow;if("undefined"===jQuery||"undefined"===google){var google=null;var jQuery=null}if(null!==(jQuery&&google)){jQuery(document).ready(function(a){GeoCoderBackend.init()})}(function(a){GeoCoderBackend={fields:{msg:a("#geco_msg"),lng:a("#geco_lon"),lat:a("#geco_lat"),zip:a("#geco_zip"),city:a("#geco_city"),street:a("#geco_street")},geocoder:new google.maps.Geocoder(),init:function(){gcb=this;a("#geco_convert").click(function(){gcb.switchConverter()});a("#geco_clear_address").click(function(){gcb.fields.zip.val("");gcb.fields.city.val("");gcb.fields.street.val("")});a("#geco_clear_latlng").click(function(){gcb.fields.lat.val("");gcb.fields.lng.val("")})},switchConverter:function(){var g="";for(var d in gcb.fields){if("undefined"!==typeof gcb.fields[d]){g=g+gcb.fields[d].val()}}if(g.length<1){gcb.fields.msg.text(geocoderl10n.error_no_data);return null}var c={zip:gcb.fields.zip.val(),city:gcb.fields.city.val(),street:gcb.fields.street.val()};var b={lat:gcb.fields.lat.val(),lng:gcb.fields.lng.val()};if((""===b.lat)|(""===b.lng)){gcb.convertAddr(c)}else{gcb.convertLatlng(b)}},convertLatlng:function(b){gcb.fields.msg.text(geocoderl10n.please_wait);var c=new google.maps.LatLng(parseFloat(b.lat),parseFloat(b.lng));gcb.geocoder.geocode({latLng:c},function(e,d){if(d===google.maps.GeocoderStatus.OK){var f=e[0].formatted_address.split(",",3);var g=a.trim(f[1]).split(" ");gcb.fields.street.val(f[0]);gcb.fields.zip.val(g[0]);gcb.fields.city.val(g[1]);gcb.fields.msg.text("")}else{gcb.fields.msg.text(geocoderl10n.gmaps_err+d)}})},convertAddr:function(c){var b=c.street+","+c.zip+" "+c.city;gcb.geocoder.geocode({address:b},function(e,d){if(d===google.maps.GeocoderStatus.OK){var f=e[0].formatted_address.split(",",3);var g=a.trim(f[1]).split(" ");gcb.fields.street.val(f[0]);gcb.fields.zip.val(g[0]);gcb.fields.city.val(g[1]);gcb.fields.lat.val(e[0].geometry.location.Ya);gcb.fields.lng.val(e[0].geometry.location.Za);if("settings_page_geocoder"===pagenow){gcb.fields.msg.text(geocoderl10n.save_data_msg)}else{gcb.fields.msg.text("")}}else{gcb.fields.msg.text(geocoderl10n.gmaps_err+d)}})}}})(jQuery);