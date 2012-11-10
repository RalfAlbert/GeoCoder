<div class="geocoder wrap">
%nonce_field%
	<div style="float:left">
		<table class="formtable">
			<tr>
				<td><label for="geco_zip">%zip%</label>/<label for="geco_city">%city%:</label></td>
				<td><input size="10" type="text" value="%zip_val%" name="geco[zip]" id="geco_zip" /><input size="20" type="text" value="%city_val%" name="geco[city]" id="geco_city" /></td>
			</tr>
			
			<tr>
				<td><label for="geco_street">%street%: </label></td>
				<td><input size="20" type="text" value="%street_val%" name="geco[street]" id="geco_street" />
				<input type="button" class="button geco_clear" id="geco_clear_address" value="" /></td>
			</tr>
		</table>
	</div>
	
	<div style="float:left">
		<button type="button" class="button" value="%btn_convert%" name="geco_convert" id="geco_convert">%btn_convert%</button>
	</div>

	<div style="float:left">
		<table class="formtable">
			<tr>
				<td><label for="geco_lon">%lon%: </label></td>
				<td><input size="15" type="text" value="%lon_val%" name="geco[lon]" id="geco_lon" /></td>
			</tr>
			
			<tr>
				<td><label for="geco_lat">%lat%: </label></td>
				<td><input size="15" type="text" value="%lat_val%" name="geco[lat]" id="geco_lat" />
				<input type="button" class="button geco_clear" id="geco_clear_latlng" value="" /></td>
			</tr>
		</table>
	</div>

<div id="geco_msg" style="clear:both"></div>

<br style="clear:both">



	
	<!-- hidden values -->
	<input type="hidden" name="geco[apikey]"  id="geco_apikey"  value="%apikey%" />

</div><!-- end class wrap-->	
