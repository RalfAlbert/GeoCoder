<div class="geocoder wrap">

<div><h2>%title%</h2></div>

<form action="" method="post">
	<input type="hidden" name="option" value="update" />
	%nonce_field%

	<hr />
	<h3>%map_options%</h3>
	<p>%map_desc%</p>
	
	<table class="formtable geocoder_left">
		<tr>
			<th style="text-align:left" colspan="2"><h4>%size_desc%</h4></th>
		</tr>
		<tr>
			<th><label for="geco_small">%map_small%</label>:</th>
			<td><input type="text" name="geco[map_small_x]" id="geco_small_x" value="%map_small_x_val%" size="3" />x<input type="text" name="geco[map_small_y]" id="geco_small_y" value="%map_small_y_val%" size="3" /> px</td>
		</tr>
		<tr>
			<th><label for="geco_medium">%map_medium%</label>:</th>
			<td><input type="text" name="geco[map_medium_x]" id="geco_medium_x" value="%map_medium_x_val%" size="3" />x<input type="text" name="geco[map_medium_y]" id="geco_medium_y" value="%map_medium_y_val%" size="3" /> px</td>
		</tr>
		<tr>	
			<th><label for="geco_large">%map_large%</label>:</th>
			<td><input type="text" name="geco[map_large_x]" id="geco_large_x" value="%map_large_x_val%" size="3" />x<input type="text" name="geco[map_large_y]" id="geco_large_y" value="%map_large_y_val%" size="3" /> px</td>
		</tr>
	</table>
	
	<table class="formtable geocoder_left">
		<tr>
			<th style="text-align:left" colspan="2"><h4>%def_mapsize_title%</h4></th>
		</tr>
		<tr>
			<th><label for="geco_defmapsize">%def_mapsize_desc%</label>: </th>
			<td><select name="geco[def_mapsize]" id="geco_defmapsize">%def_mapsize_options%</select></td>
		</tr>
	</table>		
	
	<table class="formtable geocoder_left">
		<tr>
			<th style="text-align:left" colspan="2"><h4>%type_zoom_title%</h4></th>
		</tr>
		<tr>
			<th><label for="geco_maptype">%map_type_desc%</label>: </th>
			<td><select name="geco[def_maptype]" id="geco_maptype">%map_type_options%</select></td>
		</tr>
			
		<tr>
			<th><label for="geco_mapzoom">%map_zoom_desc%</label>: </th>
			<td><select name="geco[def_mapzoom]" id="geco_mapzoom">%map_zoom_options%</select></td>
		</tr>

	</table>

	<table class="formtable geocoder_left">
		<tr>
			<th style="text-align:left" colspan="2"><h4>%static_maps_title%</h4></th>
		</tr>
			
		<tr>
			<th><label for="geco_static_maps">%static_maps_desc%</label>: </th>
			<td><input type="checkbox" name="geco[static_maps]" id="geco_static_maps"%static_maps_checked% /></td>
		</tr>

	</table>

	<hr style="clear:both" />
	<h3>%rss_title%</h3>
	<p>%rss_desc%</p>

	<table class="formtable">
		<tr>
			<th><label for="geco_rss_geo"></label>%rss_geo%</th>
			<td><input type="checkbox" name="geco[rss_geo]" id="geco_rss_geo"%rss_geo_checked%/> <small>(%rss_geo_hint%)</small></td>
		</tr>
		
		<tr>
			<th><label for="geco_rss_icbm"></label>%rss_icbm%</th>
			<td><input type="checkbox" name="geco[rss_icbm]" id="geco_rss_icbm"%rss_icbm_checked% /></td>
		</tr>

		<tr>
			<th><label for="geco_rss_geourl"></label>%rss_geourl%</th>
			<td><input type="checkbox" name="geco[rss_geourl]" id="geco_rss_geourl"%rss_geourl_checked% /></td>
		</tr>
	</table>

	<hr />
	<h3>%key_title%</h3>
	<p>%key_desc%</p>

	<table class="formtable">
		<tbody>
		
			<tr>
				<th><label for="geco_apikey">%key_label%:</label></th>
				<td><input type="text" name="geco[apikey]" id="geco_apikey" size="50" value="%key_val%" />&nbsp;(<a href="%key_link%">%key_text%</a>)</td>
			</tr>
		</tbody>
	</table>

	<p><label for="update"><input class="button-primary" type="submit" value="%btn_update%" /></label></p>
        
     </form>
 </div>
