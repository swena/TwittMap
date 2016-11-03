<html>
<head>
	<!-- AIzaSyB45rLge0qJX25y20ejv_B9iJG-mHLwt5E -->
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=XXXXXXX&sensor=false"></script>
    <script type="text/javascript">
		var map, pointarray, heatmap;
		var latlongData = [];

		var cur_zoom = 2;
		var cur_centre = new google.maps.LatLng(40.52, 4.34);

		var is_bounds_changed = false;
    	function initialize(){
			if(map) cur_zoom = map.getZoom();
    		var latlng = new google.maps.LatLng(40.52, 4.34);
    		var myOptions = {
    				zoom: cur_zoom,
    				center: latlng,
    				mapTypeId: google.maps.MapTypeId.ROADMAP
    			};
    		map = new google.maps.Map(document.getElementById("map_canvas"),
    			myOptions);

    	}
		function createMarker(locs){
			var marker;
			var i;
			for(i=0;i<locs.length;i++)
			{
				if(!(locs[i][1]=='' && locs[i][2]=='')) {
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(locs[i][1], locs[i][2]),
						map: map,
						title: locs[i][0]
					});
				}
			}
		}
	</script>
	<style>
		body{
			background-color: black;
			color:white;
		}
		.heading{
			text-align: center;
		}
		.filters{
			width:15%;
			display: block;
			float:left;
		}
		#map_canvas{
			width:70%;
			display:inline-block;
		}
	</style>
</head>
<body>
	<div class="heading"><h1>TwittMap</h1></div>
	<div class="filters">
		<form name="form" id="form" method="post" action="">
			<h3> Keywords</h3>
			<select name="keyword" id="keyword">
				<option selected="true" value="- Select Keyword -"> Select Keyword  </option>
				<option value="Donald Trump" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='Donald Trump') echo 'selected=true';?>>Donald Trump</option>
				<option value="New York" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='New York') echo 'selected=true';?>>New York</option>
				<option value="Manhattan" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='Manhattan') echo 'selected=true';?>>Manhattan</option>
				<option value="USA" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='USA') echo 'selected=true';?>>USA</option>
				<option value="Bill Gates" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='Bill Gates') echo 'selected=true';?>>Bill Gates</option>
				<option value="NetFlix" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='NetFlix') echo 'selected=true';?>>NetFlix</option>
				<option value="Hillary Clinton" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='Hillary Clinton') echo 'selected=true';?>>Hillary Clinton</option>
				<option value="White House" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='White House') echo 'selected=true';?>>White House</option>
				<option value="Mark Zuckerberg" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='Mark Zuckerberg') echo 'selected=true';?>>Mark Zuckerberg</option>
			</select>

			<input type="hidden" name="noextract" id="noextract" value="1">
			<input type="submit" name="submit" id="submit" value="Pin">
		</form>
	</div>
	<div id="map_canvas" style="width:1200px; height:600px;"></div>

<?php

	require_once ('init.php');
    require_once ('codebird/src/codebird.php');

    $cb = \Codebird\Codebird::getInstance();
    $cb->setConsumerKey('XXXXXX', 'XXXXXX');
    $cb->setToken('XXXXXX', 'XXXXXX');
    $keylist = array('Donald Trump','New York','Manhattan','USA','Bill Gates','NetFlix','Hillary Clinton','White House','Mark Zuckerberg');
    $es = new Elasticsearch\Client();
    if(!isset($_POST['noextract']))
    {
	    for($j = 0; $j < count($keylist); $j++)
	    {
		    $params = array('q'=>$keylist[$j],'count'=>100);      
		    $reply = (array) $cb->search_tweets($params);

		    $data = (array) $reply['statuses'];
		    $s = count($reply['statuses']);
		    #print "<pre>";
		    #print_r($reply['statuses']);
		    #print "</pre>";
		    for($i=0; $i<$s;$i++)
		    {
		        $status = $data[$i];
			    $created_at = $status->created_at;
			    $keyword = $keylist[$j];
			    $location = $status->user->location;
			    $tweet = $status->text;
				if($location != '') {
					$indexed = $es->index([
						'index' => 'tweets',
						'type' => 'tweet',
						'body' => [
							'keyword' => $keyword,
							'location' => $location,
							'created_at' => $created_at,
							'tweet' => $tweet
						]
					]);
				}

		    }
		}
	}
	if(isset($_POST["submit"]))
	{
		$key = $_POST["keyword"];
		//$maptype = $_POST["maptype"];
		//$q = 'trump';
	    $query = $es->search([
	            'body' => [
	                'size'  => 100,
	                'query' => [
	                    'bool' => [
	                        'should' => [
	                            'match' => ['keyword' => $key]
	                        ] 
	                    ]
	                ]
	            ]
	        ]);
		?>
		<script>
			initialize();
			var locations = [];
			var j=1;
		<?php
	    for($i=0;$i<count($query['hits']['hits']);$i++)
	    {
			$location = $query['hits']['hits'][$i]['_source']['location'];
			$prepAddr = urlencode($location);
			$url = "https://maps.google.com/maps/api/geocode/json?key=AIzaSyB45rLge0qJX25y20ejv_B9iJG-mHLwt5E&address=".$prepAddr;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_a = json_decode($response);
			$lat = '';
			$long = '';
			if(isset($response_a->results[0])) {
				$lat = $response_a->results[0]->geometry->location->lat;
				$long = $response_a->results[0]->geometry->location->lng;
			}

		?>
			locations.push(['Tweet'+<?php echo $i;?>, '<?php echo $lat; ?>','<?php echo $long; ?>']);

			j++;

			<?php
			#print $query['hits']['hits'][$i]['_source']['location'];
	        #print "--->";
	        #print $query['hits']['hits'][$i]['_source']['created_at'];
	        #print "--->";
	        #print $query['hits']['hits'][$i]['_source']['tweet'];
	        #print "<br><br>";


	    }
			?>
			createMarker(locations);
		</script>
		<?php
	}

?>

</body>
</html>
