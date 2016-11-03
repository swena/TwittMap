<html>
<head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=visualization"></script>
    
</head>
<body>
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
        	<br>
        	<h3>Map Type</h3>
			<input type="radio" name="maptype" id="heatMap" checked="checked" value="heat"> Heat Map
	        <br>
	        <input type="radio" name="maptype" id="pinMap" value="pin">Pin Map
	        <br>
	        <input type="hidden" name="noextract" id="noextract" value="1">
	        <input type="submit" name="submit" id="submit" value="map">
	    </form>
	    <!--<div id="map-canvas" style="position:absolute;left:200px;top:100px;width:1050px;height:570px"> </div>-->

<?php
	require_once ('init.php');
    require_once ('codebird/src/codebird.php');

    $cb = \Codebird\Codebird::getInstance();
    $cb->setConsumerKey('XXXXXXX', 'XXXXXXX');
    $cb->setToken('XXXXXXX', 'XXXXXXX');
    $keylist = array('Donald Trump','New York','Manhattan','USA','Bill Gates','NetFlix','Hillary Clinton','White House','Mark Zuckerberg');
    if(!isset($_POST['noextract']))
    {
	    for($j = 0; $j < count($keylist); $j++)
	    {
		    $params = array('q'=>$keylist[$j],'count'=>100);      
		    $reply = (array) $cb->search_tweets($params);

		    $data = (array) $reply['statuses'];
		    $s = count($reply['statuses']);
		    //print "<pre>";
		    //print_r($reply['statuses']);
		    //print "</pre>";
		    for($i=0; $i<$s;$i++)
		    {
		        $status = $data[$i];
			    #echo $i."------".$status->created_at." : ".$status->user->location."--------".$status->text;
			    #echo "<br><br><br>";
			    $created_at = $status->created_at;
			    $keyword = $keylist[$j];
			    $location = $status->user->location;
			    $tweet = $status->text;
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
			     #if($indexed){
			     #   print $i;
			     #   print_r($indexed);
			     #   print "\n";
		    }
		}
	}
	if(isset($_POST["submit"]))
	{
		$key = $_POST["keyword"];
		$maptype = $_POST["maptype"];
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
	    for($i=0;$i<count($query['hits']['hits']);$i++)
	    {
	        print $query['hits']['hits'][$i]['_source']['location'];
	        print "--->";
	        print $query['hits']['hits'][$i]['_source']['created_at'];
	        print "--->";
	        print $query['hits']['hits'][$i]['_source']['tweet'];
	        print "<br><br>";
	    }
	}
?>
</body>
</html>
