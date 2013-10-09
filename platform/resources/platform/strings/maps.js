

/** (mgConvertGMapGEOInfo) */
var mgConvertGMapGEOInfo = function(o) {
	var result = {formatted: o.formatted_address, streetnumber: false, street: false, address: false, city: false, zip: false, state: false, stateid: false, county: false, country: false};
	$.each(o.address_components, function(index, p) {
		$.each(p.types, function(i, t) {
			var ok = true;
			switch(t) {
				case "street_number": result.streetnumber = p.long_name; break;
				case "route": result.street = p.long_name; break;
				case "locality": result.city = p.long_name; break;
				case "postal_code": result.zip = p.long_name; break;
				case "administrative_area_level_1": result.state = p.long_name; result.stateid = p.short_name; break;
				case "country": result.country = p.long_name; break;
				case "administrative_area_level_2": result.county = p.long_name; break;
				default: ok = false; break;
			}
			if(ok) return true;
		});
	});
	result.address = sprintf("%s %s", result.streetnumber, result.street);
	return result;
};
