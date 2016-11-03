<head> <meta charset="utf-8"> </head>
<?php
    require_once ('init.php');
    require_once ('codebird/src/codebird.php');

    $cb = \Codebird\Codebird::getInstance();
    $cb->setConsumerKey('XXXXXX', 'XXXXXX');
    $cb->setToken('XXXXXX', 'XXXXXX');
    $params = array('q'=>'Donald Trump','count'=>100);      
    $reply = (array) $cb->search_tweets($params);

    $data = (array) $reply['statuses'];
    $s = count($reply['statuses']);
    print "<pre>";
    print_r($reply['statuses']);
    print "</pre>";
    for($i=0; $i<$s;$i++)
    {
        $status = $data[$i];
    #echo $i."------".$status->created_at." : ".$status->user->location."--------".$status->text;
    #echo "<br><br><br>";
    $created_at = $status->created_at;
    $keyword = "Donald Trump";
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
    $q = 'trump';
    $query = $es->search([
            'body' => [
                'size'  => 100,
                'query' => [
                    'bool' => [
                        'should' => [
                            'match' => ['keyword' => $q]
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
    
   
    /*for ($a = 0; $a < $s; $a++) {

        $status = $data[$a];

        if ($status->retweeted_status != null) {

            echo $status->user->name . " (@" . $status->user->screen_name . ") retweeted:"; 
            echo "<br/>";
            $b = $status->retweeted_status;

        }

        else {

            $b = $status;

        }

        echo "<br/>";
        echo "<img src=\"" . $b->user->profile_image_url . "\"/> " . $b->user->name . " (@" . $b->user->screen_name . ")" . " at " . $b->created_at;
        echo "<br/>";
        echo $b->text;
        echo "<div style=\"height: 1px; width: 100%; background-color: orange;\"></div>";

        if ($b->entities->media[0] != null) {

                $media = $b->entities->media[0];
                echo "<br/>" . "<img src=\"" . $media->media_url_https . "\">";

        }

    }*/



?>
