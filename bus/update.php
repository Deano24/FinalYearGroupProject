<!-- Created By: Rohan Malcolm
	Created For: JUTC Tracking System
	Script that takes lattitude and longitude from a gps tracker and finds the actual location on earth
	and updates the database by those values
 -->
<?php
	session_start();//creates a local session for this script to store variables between sessions
	//$lat = 18.050228;
	//$lng = -76.982596;
  	$lat = 17.952127;
  	$lng = -76.709306;
  	$id = null;
  	$speed = null;
  	$bus_number = "EX99";

  	if(isset($_SESSION['id']))//checks to see if an id exists for this session if so use it, else null
	{
	    $id = $_SESSION['id'];
	}

	//this block of code asks good maps for the locations based on given lattitude and longitude
	//and also asks for it to be sent back in json format so we cna handle it
 	$curl_handle = curl_init();//opens a handler for this process
	curl_setopt($curl_handle, CURLOPT_URL,'http://maps.googleapis.com/maps/api/geocode/json?'.
		'latlng='.trim($lat).','.trim($lng).'&sensor=false');//places in url
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);//places is expected amount of returs
	$query = curl_exec($curl_handle);//executes and stores in a variable
	curl_close($curl_handle);//closes handler
	$data = json_decode($query);//decods it from json


	if($data->status == "OK")//if data retrieval and parsing went well
	{
		$address = $data->results[0]->formatted_address;//gets the formatted address

	  	$con = mysqli_connect("localhost","root","","track"); // connects to database

	  	if (mysqli_connect_errno($con))//checks for error
		{
		 	die();
		}
		else
		{
			if($id == null)//means that this is the first time this device is being activated for today
			{
				//fetches the list of active buses for the day so far
				$result = mysqli_query($con,"SELECT * FROM active_bus");

				while($row = mysqli_fetch_array($result))//iterates through list if any
				{
					$id = $row['bus_id'];
				}

				//adds enough values to bus number
				while(strlen($bus_number) < 5)
				{
					$bus_number = "0".$bus_number;
				}

				if($id == null)//means that there is no previous data in our database so this will be the first
				{
					$id = "0000";	
				}
				else//another bus has existed so we will add this as the next bus
				{
					$id = substr($id, 0,4);
					$id = intval($id)+1;
				}

				$id = $id.$bus_number;//creates the compound id needed for identifying

				//adds this bus to the active bus database
				mysqli_query($con,"INSERT INTO active_bus (bus_id, location, speed) VALUES ('".$id."','".$address."',0)");
			}
			else
			{
				//updates the location for this bus
				mysqli_query($con,"UPDATE active_bus SET location = '".$address."' WHERE bus_id='".$id."'");
			}
		}

		mysqli_close($con);//closes database connection
		$_SESSION['id'] = $id; // updates the session id
	}

?>