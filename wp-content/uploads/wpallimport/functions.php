<?php

//allow dates imported from PubMed to be in this format: 2015-01-06
function fix_date_number( $date ) {
	return ( strlen( $date ) < 2 ? "0" . $date : $date );
}

?>