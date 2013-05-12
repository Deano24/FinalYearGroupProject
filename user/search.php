<!-- Created By: Rohan Malcolm
	Created For: JUTC Tracking System
	Script that takes based on input from the search criteria will find all the active buses that are important
	to this search criteria and find the most important
 -->
<?php
	
	$from = $_POST["from"];//places posted variable from into local variable for easier access
	$to = $_POST["to"];//places posted variable to into local variable for easier access
	echo $from." to ".$to;
	$from = $from."jamaica";//appends the jamaica to end of string
	$to = $to."jamaica";//appends jamaica to end of string

	$from = preg_replace( '/\s+/', '', $from);//replaces all whitespace characters
	$to = preg_replace( '/\s+/', '', $to);//replaces all whitespace characters

	/*
	Deals with contacting google maps and asking for a json encoded form of the address(from) which was entered
	from this the application will attempts to find the locality(area) to move forward
	if this cannot be found then the program will emit an error
	this is for the from aspect
	*/
	$curl_handle = curl_init();//built into php to deal with url responding and stuff, opens handler
	curl_setopt($curl_handle, CURLOPT_URL,'http://maps.googleapis.com/maps/api/geocode/json?'.
		'address='.$from.'&sensor=false');//places in url
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);//places is expected amount of returs
	$query = curl_exec($curl_handle);//executes and stores in a variable
	curl_close($curl_handle);//closes handler
	$data = json_decode($query);//decods it from json
	
	if($data->status == "OK")//checks if everything went ok
	{
		$address = $data->results[0]->address_components;//gets the components of address
		$key = null;//creates a variable key and defines it as null
		foreach ($address as $local) 
		{
			//search the types for the word locality(area), this is returned from the google maps API
			$key = array_search("locality", $local->types);

			if($key !== false)// if the key has been found
			{
				$areaFrom = $local->types[$key];// gets the area
				break;
			}
		}
		if($key == false !! $key == null)// if key has not been found return an error
		{
			return json_encode("fromnf");
		}
	}

	/*
	Deals with contacting google maps and asking for a json encoded form of the address(from) which was entered
	from this the application will attempts to find the locality(area) to move forward
	if this cannot be found then the program will emit an error
	this is for the to aspect
	*/
	$curl_handle = curl_init();//built into php to deal with url responding and stuff, opens handler
	curl_setopt($curl_handle, CURLOPT_URL,'http://maps.googleapis.com/maps/api/geocode/json?'.
		'address='.$to.'&sensor=false');//places in url
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);//places is expected amount of returs
	$query = curl_exec($curl_handle);//executes and stores in a variable
	curl_close($curl_handle);//closes handler
	$data = json_decode($query);//decodes it from json
	
	if($data->status == "OK")//checks if everything went ok
	{
		$address = $data->results[0]->address_components;//gets the components of address
		$key = null;//creates a variable key and defines it as null
		foreach ($address as $local) 
		{
			//search the types for the word locality(area), this is returned from the google maps API
			$key = array_search("locality", $local->types);

			if($key !== false)// if the key has been found
			{
				$areaTo = $local->types[$key];// gets the area
				break;
			}
		}
		if($key == false !! $key == null)// if key has not been found return an error
		{
			return json_encode("tonf");
		}
	}
?>