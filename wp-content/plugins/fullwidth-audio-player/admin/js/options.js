jQuery(document).ready(function() {
		
	//set colorpickers
	jQuery('input.colorpicker').spectrum({
		color: jQuery(this).val(),
		preferredFormat: "hex",
		showInput: true,
		chooseText: "Change Color",
		change: function(color) {
			jQuery(this).val(color.toHexString());
		}
	});
	
});

