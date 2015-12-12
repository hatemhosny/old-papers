<?php  if ($_SERVER['QUERY_STRING']=='info'){$mode = 'info';}
 else {header('Content-Type: application/xml; charset=utf-8');
     $mode = 'query';
 }


//Default settings
$disable_imports = FALSE;
$retmax = '4000';           //max articles to retrieve
$has_abstract = TRUE;      //Add "has abstract"[Filter] 
$has_full_text = FALSE;
$has_free_full_text = FALSE;



//initialize variables
$pubmed_terms = '';
$filters = '';
$import_info = '';



// get article import settings
$disable_imports = get_field('disable_imports', 'option'); 
$has_abstract = get_field( 'has_abstract', 'option' ); 
$has_full_text = get_field( 'full_text_available', 'option' ); 
$has_free_full_text = get_field( 'free_full_text_available', 'option' ); 

if( get_field( 'max_articles_to_import', 'option' ) != ''){
    $retmax = get_field( 'max_articles_to_import', 'option' );
} 



//check request either from admin or localhost for import, else deny access
 if ((!current_user_can('administrator')) and ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR'])){
    echo ("<Div>");
    echo ("<h2>Access Denied!</h2>");
    echo ('<a href="' . wp_login_url() . '" title="Login">Login</a>');
    echo ("</Div>");

 }
 elseif ($disable_imports == TRUE){  //if import is disabled stop processing
    echo ("<div>");
    echo ("<h2>Importing is disabled</h2>");
    echo ("<p>Admin can enable importing from site settings.</p>");
    echo ("</div>");
}
else {  //else continue
    
    //Build filters

    //mark first filter
    $first_filter = TRUE;

    //filter: has abstract
    if ($has_abstract == TRUE){
        $filters .= 'hasabstract[text]';
        $first_filter = FALSE;
    }

    //filter: free full text
    if ($has_free_full_text == TRUE){
        if ($first_filter == FALSE){
            $filters .= ' AND ';
        }
        $filters .= '"loattrfree full text"[sb]';
        $first_filter = FALSE;
    }

    //filter: full text
    if ($has_full_text == TRUE){
        if ($first_filter == FALSE){
            $filters .= ' AND ';
        }
        $filters .= '"loattrfull text"[sb]';
        $first_filter = FALSE;
    }

    if ($filters != ''){
        $filters = ' AND (' . $filters . ")";
    }



    //format dates
    function get_date($date, $alt_date = ''){
        if ($date != ''){
            return date('Y/m/d', strtotime($date));
        }
        elseif ($alt_date != ''){
            return date('Y/m/d', strtotime($alt_date));
        }
        else {
            return '';
        }

    }


    //build response header
    function http_build_headers( $headers ) {

       $headers_brut = '';

       foreach( $headers as $name => $value ) {
           $headers_brut .= $name . ': ' . $value . "\r\n";
       }

       return $headers_brut;
    }


    //create POST search request
    function search_pubmed( $url, $fields ) {

    $content = http_build_query( $fields );

    // define headers

    $header = http_build_headers( array(
    'Content-Type' => 'application/x-www-form-urlencoded',
    'Content-Length' => strlen( $content) ) );

    // Define context

    $options = array( 'http' => array( 'user_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) Gecko/20061010 Firefox/2.0',
    'method' => 'POST',
    'content' => $content,
    'header' => $header ) );

    // Create context
    $contexte = stream_context_create( $options );

    // Send request
    return file_get_contents( $url, false, $contexte );

    }


    //search details of Journal import settings
    $args = array(
	    'post_type'   => 'journal_import',
        'meta_key'    => 'import_enabled',
        'meta_value'    => TRUE,
        );

    //construct query for the import settings
    //$query = new WP_Query( $args );

    //get all active imports
    $imports_array = get_posts( $args );

    //mark first import
    $first_term = TRUE;


    //loop through imports
    foreach ( $imports_array as $post ) : setup_postdata( $post ); 

        //if not the first term, add "OR"
        if ($first_term !== TRUE){
            $pubmed_terms .= " OR ";
            };

        //get import details (journal, start date and end date)
        $journal = get_post_meta(get_the_ID(),'journal', true);
        $start_date = get_date(get_post_meta(get_the_ID(),'start_date', true));
        $end_date = get_date(get_post_meta(get_the_ID(),'end_date', true), 'now');

        //build search term
        $pubmed_terms .= '(';
        $pubmed_terms .= '(' . $journal . '[Journal])';

        if ($start_date != ''){
            $pubmed_terms .= ' AND ("' . $start_date . '"[Date - Publication]';
            $pubmed_terms .= ' : "' . $end_date . '"[Date - Publication])';

        };
        $pubmed_terms .= ')';

        //mark as not first term
        $first_term = FALSE;


        //build import info
        $import_info .= '<p>';
        $import_info .= '<b>Journal ISSN:</b> ' . $journal . '<br />';
        $import_info .= '<b>Start Date:</b> ' . $start_date . '<br />';
        $import_info .= '<b>End Date:</b> ' . $end_date . '<br />';
        $import_info .= '</p>';

    endforeach; 


    wp_reset_postdata();

        
    $term = $pubmed_terms . $filters;   //pubmed search term + filters



    //parameters for ESearch
    $parameters = array(
		    'db' => 'pubmed',			//database
		    'term' => $term,			//search term
		    'sort' => 'pub+date',		//sort by publication date
		    'retmax' => $retmax,		//max number of articles to retrieve
		    'retmode' => 'xml' );

    //ESearch		
    $search_result = search_pubmed( 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', $parameters );

    //Parse XML result
    $xml=simplexml_load_string($search_result) or die("Error: Cannot create object");

    //convert xml to array
    $array = json_decode(json_encode((array)$xml->IdList->Id), TRUE);

    //Get article count
    $article_count = count($array);

    //convert array to string separates by commas
    $Ids = implode(",", $array);


    //display import info
    if($mode =='info'){


        echo('<html>');
        echo('<head><title>Active Imports</title></head>');
        echo('<body>');
        echo('<h2>Active Imports</h2>');
        echo('<p><b>Search Terms:</b><br />');
        echo($term);
        echo('</p>');

        echo("<a target='_blank' href='http://www.ncbi.nlm.nih.gov/pubmed/?term=" . $term . "'>See in PubMed</a> - ");
        echo('<a target="_blank" href=".">Run the query</a>');

        echo('<p><b>Article Count:</b> ');
        echo($article_count);
        echo('</p>');

        echo($import_info);

        echo('</body>');
        echo('</html>');

        }

    //else run the import
    else {

        //Parameters for EFetch
        $parameters = array(
		        'db' => 'pubmed',
		        'id' => $Ids,				//send Ids resulting from ESearch to EFetch
		        'retmode' => 'xml' );

        //EFetch		
        $search_result = search_pubmed( 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', $parameters );

        echo($search_result);
    };
};
?>


