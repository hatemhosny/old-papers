function showdiv(id, action){
	if(document.getElementById) {
		if(document.getElementById(id)) {
			document.getElementById(id).style.display=action;
		}
	} else if(document.layers) {
		document.id.display=action;
	} else {
		document.all.id.style.display=action;
	}
}

function togglediv(id) {
    var elem = document.getElementById(id);
    var vis = elem.style;
	if(vis.display == '' && elem.offsetWidth != undefined && elem.offsetHeight != undefined) {
		vis.display = (elem.offsetWidth !=0 && elem.offsetHeight != 0) ? 'block' : 'none';
    }
    vis.display = (vis.display == '' || vis.display == 'block') ? 'none' : 'block';
}

function roundNumber(num, dec) {
	var result = Math.round(num * Math.pow(10,dec)) / Math.pow(10,dec);
	return result;
}

function nfchange(box) {
	if(box.value == 0) {
		document.getElementById('item_price').innerHTML = roundNumber(item_price = def_price * (1 + (item_nofollow / 100)), 2);
	}
	if(box.value == 1) {
		document.getElementById('item_price').innerHTML = roundNumber(def_price, 2);
	}
}

function ad_details() {
	if(status == 0) {
		showdiv('ad-details', 'block');
		status = 1;
	} else {
		showdiv('ad-details', 'none');
		status = 0;
	}
}

function add_field(form_id, input_type, input_name, input_id, input_value) {
	if(document.getElementById(form_id)) {
		var f = document.getElementById(form_id);
		var h = document.createElement("input");
		h.setAttribute("type", input_type);
		h.setAttribute("name", input_name);
		h.setAttribute("id", input_id);
		h.setAttribute("value", input_value);
		f.appendChild(h);
	}
}