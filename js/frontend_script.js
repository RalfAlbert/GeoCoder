var GCF,GC,gm,l;if("undefined"===jQuery||"undefined"===google){var google=null;var jQuery=null}if(null!==(jQuery&&google)){jQuery(document).ready(function(e){GCF.init()})}(function(e){GCF={init:function(){gm=this;l=null;e(".geco_dynamic_map").each(function(){var t=e(this);if(""!==gm.gll(t)){gm.gcll(t)}else{gm.gcad(t)}})},se:function(t,n){if(typeof n==="undefined"){n="No error message was given"}e(t).html("<p>GoogleMaps failed with the following reason:<br><strong>"+n+"</strong></p>")},gid:function(t){var n=e(t).attr("ID");if(""!==n){return n}else{gm.se(t,"Element has no ID")}},gad:function(t){return e(t).attr("data-addr")},gll:function(e){return e.attr("data-latlng")},gllo:function(e){var t=gm.gll(e).split(",",2);return t.length<2?null:{lat:parseFloat(t[0]),lng:parseFloat(t[1])}},gmo:function(t,n){if(typeof n!=="object"){gm.se(t,"LatLng object is not type of object")}var r=e(t);var i=parseInt(r.attr("data-zoom"),10);if(typeof i==="undefined"){i=16}var s=r.attr("data-maptype").toUpperCase();if(false===s in google.maps.MapTypeId){s="ROADMAP"}return{zoom:i,center:new google.maps.LatLng(n.lat,n.lng),mapTypeId:google.maps.MapTypeId[s],panControl:true,zoomControl:true,mapTypeControl:true,scaleControl:true,streetViewControl:false,overviewMapControl:true}},am:function(t,n,r){var i;if(true===gm.igm(r)){gm.amm();i=e.parseJSON(gm.l)}else{i={A:n};i.A.title="";i.A.text="";i.A.link=""}gm.sm(t,i)},sm:function(t,n){var r=e.parseJSON(GC.gmap_icon);var i=new google.maps.MarkerImage(r.icon_url,new google.maps.Size(r.icon_size.width,r.icon_size.height),new google.maps.Point(r.icon_origin.x,r.icon_origin.y),new google.maps.Point(r.icon_anchor.x,r.icon_anchor.y));var s=new google.maps.MarkerImage(r.shadow_url,new google.maps.Size(r.shadow_size.width,r.shadow_size.height),new google.maps.Point(r.shadow_origin.x,r.shadow_origin.y),new google.maps.Point(r.shadow_anchor.x,r.shadow_anchor.y));var o={coord:[r.icon_origin.x,r.icon_origin.y,r.icon_origin.x,r.icon_size.height,r.icon_size.width,r.icon_size.height,r.icon_size.width,r.icon_origin.y],type:"poly"};for(var u in n){if("undefinde"!==typeof n[u]){var a=n[u];var f=new google.maps.LatLng(a.lat,a.lng);var l=new google.maps.Marker({position:f,map:t,shadow:s,icon:i,shape:o,title:a.title});if(""!==a.title){var c='<div id="content">'+'<div id="siteNotice">'+"</div>"+'<h1 id="firstHeading" class="firstHeading">'+a.title+"</h1>"+'<div id="bodyContent">'+"<p>"+a.text+"</p>"+'<p><a href="'+a.link+'">'+GC.readmore+"</a>"+"</div>"+"</div>";var h=new google.maps.InfoWindow({content:c});gm.aiw(t,l,h)}}}},aiw:function(e,t,n){google.maps.event.addlener(t,"click",function(){n.open(e,t)})},igm:function(t){var n=e(t).attr("data-generalmap");return 1===parseInt(n,10)},gcll:function(e){var t=gm.gllo(e);if(null!==t){var n=new google.maps.Map(document.getElementById(gm.gid(e)),gm.gmo(e,t));gm.am(n,t,e)}else{gm.se(e,"Map is not a general map and can not extract latitude/longitude from stored data")}},gcad:function(e){var t=gm.gad(e);if(""!==t){var n=new google.maps.GC;n.geocode({address:t},function(t,n){if(n===google.maps.GCStatus.OK){var r={lat:t[0].geometry.location.Ya,lng:t[0].geometry.location.Za};var i=new google.maps.Map(document.getElementById(gm.gid(e)),gm.gmo(e,r));gm.am(i,r,e)}else{gm.se(e,n)}})}},amm:function(){jQuery.ajax({type:"POST",url:GC.ajaxurl,data:{action:"geco_multimarker",_ajax_nonce:GC.nonce,async:false},success:function(e){gm.l=e},async:false})}}})(jQuery)