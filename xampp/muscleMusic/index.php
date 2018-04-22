<html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


 <title>HackRU Muscle Music </title>

<body>

  <h1>Muscle Music!</h1>

  <div>
  	<button id="volumeUp" onclick="volumeUp()">Volume Up</button>
  	<button id="volumeDown" onclick="volumeDown()">Volume Down</button>
  </div>


  <div>
  	<button id="playButton" onclick="play()">Play</button>
  	<button id="togglePlayPause" onclick="togglePlayPause()">Toggle Play/Pause</button>
  	<button id="pauseButton" onclick="pause()">Pause</button>
  </div>

   <div>
  	<button id="nextTrack" onclick="nextTrack()">Next Track</button>
  	<button id="previousTrack" onclick="previousTrack()">Previous Track</button>
  </div>


  <div>
  	<button id="powerButton" onclick="togglePower()">Power</button>
  </div>


<script type="text/javascript">
	
var IP_ADDRESS_OF_SPEAKER = '192.168.1.87';
var baseURL = "http://" + IP_ADDRESS_OF_SPEAKER + ":8090";

var currentVolume;
getVolume();

function getVolume() {
  var getURL = baseURL + "/volume";
  $.get(getURL, {}).done(function(xml){
    currentVolume = $(xml).find("actualvolume").first().text();
    console.log(xml);
  });
} // end getVolume

function setVolume(volume){
  var postURL = baseURL + "/volume";
  var xml = "<volume>" + volume + "</volume>";
  $.post(postURL, xml, null, "xml");
}// end setVolume

function volumeUp(){
	setVolume(parseInt(currentVolume) + 5);
	console.log("Current Volume: " + currentVolume);
	getVolume();
}

function volumeDown(){
	setVolume(parseInt(currentVolume) - 5);
	console.log("Current Volume: " + currentVolume);
	getVolume();
}

function play(){
  	doOp('PLAY');
}

function togglePlayPause(){
  	doOp('PLAY_PAUSE');
}

function pause(){
  	doOp('PAUSE');
}

function nextTrack(){
	doOp('NEXT_TRACK');
}

function previousTrack(){
	doOp("PREV_TRACK");
}

function togglePower(){
	doOp('POWER');
}

function doOp(op){
	var data = "<key state='press' sender='Gabbo'>" + op + "</key>";
	$.ajax({
	    url: baseURL + "/key",
	    type: 'POST',
	    crossDomain: true,
	    data: data,
	    dataType: 'text',
	    success: function(result){
	      //Worked.
	      console.log(result);
	    },
	    error: function(jqXHR, transStatus, errorThrown) {
	      alert('Status: ' + jqXHR.status + ' ' + jqXHR.statusText + '.' +
	          'Response: ' + jqXHR.responseText);
	    }
  	});
}


</script>

<?php  

	$currentVolume = 0;
	$volumeScaler = 3;

	if(isset($_GET['operation'])){
		$op = $_GET['operation'];

		if($op == 'power'){
			doOperation("POWER");
		}
		elseif($op == 'play'){
			doOperation("PLAY");
		}
		elseif($op == 'pause'){
			doOperation("PAUSE");
		}
		elseif($op == 'up'){
			adjustVolume($volumeScaler);
		}
		elseif($op == 'down'){
			adjustVolume(-1*$volumeScaler);
		}
		elseif($op == 'togglePP'){
			doOperation("PLAY_PAUSE");
		}
		elseif($op == 'next'){
			doOperation("NEXT_TRACK");
		}
		elseif($op == 'prev'){
			doOperation("PREV_TRACK");
		}
		elseif($op == 'shuffle'){
			toggleShuffleState();
		}

	}

	function toggleShuffleState(){
		$IP_ADDRESS_OF_SPEAKER = '192.168.1.87';
		$baseURL = "http://" . $IP_ADDRESS_OF_SPEAKER . ":8090";
		$ch = curl_init($baseURL . '/now_playing');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$xml = simplexml_load_string($response);

		echo $xml->shuffleSetting;

		if(strcmp($xml->shuffleSetting, "SHUFFLE_ON") == 0){
			$shuffle = "SHUFFLE_OFF";
		}else{
			$shuffle = "SHUFFLE_ON";
		}

		$data = "<key state='press' sender='Gabbo'>". $shuffle ."</key>";
		curl_setopt($ch, CURLOPT_URL, $baseURL . '/key');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_exec($ch);
		curl_close($ch);
	}

	function doOperation($op){
		echo "doing: " . $op;
		$IP_ADDRESS_OF_SPEAKER = '192.168.1.87';
		$baseURL = "http://" . $IP_ADDRESS_OF_SPEAKER . ":8090";
		$ch = curl_init($baseURL.'/key');
		$data = "<key state='press' sender='Gabbo'>". $op ."</key>";
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close($ch);
	}

	function getVolume(){
		$IP_ADDRESS_OF_SPEAKER = '192.168.1.87';
		$baseURL = "http://" . $IP_ADDRESS_OF_SPEAKER . ":8090";
		$ch = curl_init($baseURL . '/volume');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$xml = simplexml_load_string($response);
		curl_close($ch);
		return $xml->actualvolume;
	}

	//Precondition: change MUST be 1 or -1 (1 to raise volume, -1 to lower)
	function adjustVolume($offset){
		$IP_ADDRESS_OF_SPEAKER = '192.168.1.87';
		$baseURL = "http://" . $IP_ADDRESS_OF_SPEAKER . ":8090";
		$ch = curl_init($baseURL.'/volume');

		$data = "<volume>". (getVolume() + $offset) ."</volume>";
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close($ch);
	}
?>
</body>
</html>