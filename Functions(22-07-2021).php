<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style('child-theme', get_stylesheet_directory_uri() .'/style.css', array('parent-theme'));
		wp_enqueue_style('custom-css', get_stylesheet_directory_uri() .'/custom-css/custom-style.css');
}

function get_data($atts) {
	$filter = $atts['filter'];
	$curl = curl_init(); 
	curl_setopt_array($curl, array(
		#CURLOPT_URL => "https://aep.terraine.net/PropertyWare/propertywaredata.asmx/getalldata?location=".$filter,
		CURLOPT_URL => "http://142.93.6.31:8181/getalldata?location=".$filter,
	
 		//CURLOPT_URL => "http://40.112.177.166/propertywaredata.asmx/getalldata?location=".$filter,
  		#CURLOPT_URL => "http://142.93.6.31:8181/getalldata?location=".$filter,
  		CURLOPT_RETURNTRANSFER => true,
  		CURLOPT_ENCODING => "",
  		CURLOPT_MAXREDIRS => 10,
  		CURLOPT_TIMEOUT => 30,
  		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
 		CURLOPT_CUSTOMREQUEST => "GET",
  		CURLOPT_HTTPHEADER => array(
    		//"authorization: Basic amltQHRlcnJhaW5lLmNvbTpCZXJuaWVzZXo5MTkh",
    		"cache-control: no-cache",
    		"postman-token: 272c5a47-89b5-be4f-a870-794d1587dbca"
  		),
	));
	
	$response = curl_exec($curl);
	curl_close($curl);
	$encodedResponse = utf8_encode($response);
	$xml=simplexml_load_string($encodedResponse);
	$json = $xml[0];
	$err = curl_error($curl);
	$arr = json_decode(json_encode((array)$json), TRUE);
	
	$business_parks = '$filter';
	$tablestring = create_html($arr,$filter);
	return $tablestring;
}

function create_html($arr,$filter){
	$business_park = strtolower($filter);
	$html = 
	'<table>
		<thead><tr>
			<th scope="col">Unit</th>
			<th scope="col">Type</th>
			<th scope="col">Building Address</th>
			<th scope="col">Size</th>
			<th scope="col">Rent</th>
			<th scope="col">Deposit</th>
			<th scope="col">Floorplan</th>
		</tr></thead>
		<tbody>';
	$row='';
	$arr_count = array_map('count', $arr['getPublishedUnitListReturn'][0]);
	if($arr_count > 0){
		$arr = $arr['getPublishedUnitListReturn'];
	}else{
		
	}
	
	foreach($arr as $property){
			$address = $property['address'];
			if($property['address2']!==''){
			 	//$unit = explode('#',trim($property['address2']));
				$unit = trim($property['address2'],"#");
				$add= trim($property['address2'],"#");
			}else{
				$add = trim($property['name']);	
				if($address === $add){
					$add = '';				
				}
			}
		 	$rent = $property['status'] === 'Vacant'?'$'.number_format($property['targetRent']):'<font 				style="color:rgb(255,0,0)">Not Available</font>';
			$deposit= $property['status'] === 'Vacant'? $property['targetDeposit']: '<font 	style="color:rgb(255,0,0)">Not Available</font>';
			$totalArea = number_format($property['totalArea']);	
			$type = $property['type'];
			$uploaddir = wp_upload_dir();
		
			$floorplan=$uploaddir['baseurl'].'/floor-plans/'.$business_park.'/'.$address.' '.$add.'-	FloorPlan.pdf';
$pathssss =  $uploaddir['basedir'] .'/floor-plans/'.$business_park.'/'.$address.' '.$add.'-FloorPlan.pdf';
$floorplanlink = file_exists($pathssss) ? '<a href="'.$floorplan.'" target="_blank" >View</a>' : '&nbsp';
			$row.='<tr style="background-color: rgb(238, 238, 238);"><td scope="row">&nbsp'.$unit.'</td><td><font style="color:rgb(11, 166, 0)">'.$type.'</font></td><td>'.$address.'</td><td>'.$totalArea.'</td><td><b>'.$rent.'</b></td><td><b>'.$deposit.'</b></td><td>&nbsp<font class="floorplan">'.$floorplanlink.'</font></td></tr>';

	}
	$html = $html.$row.'</tbody></table>';
	return $html;
}
add_action( 'wp_ajax_nopriv_get_data', 'get_data' );
add_action( 'wp_ajax_get_data', 'get_data' );
add_shortcode('lorem', 'get_data');

?>

