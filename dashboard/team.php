<?php

    include_once '../connect.php';

    $teamName = $_POST['teamID'];

    try {
        $database = new Connection();
        $db = $database->openConnection();

        $data = array();
        $singleEvent = array();
        $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `team` = '$teamName'");
       // echo "SELECT * FROM `bowlers` WHERE `team` = '$teamName' GROUP BY `name` ";
        $sql->execute();
        $dataFetched = $sql->fetchAll();
        foreach ($dataFetched as $event) {
            $sql1 = $db->prepare('SELECT * FROM `bowlers` WHERE bowlerid="'.$event['bowlerid'].'"');
                        $sql1->execute();
                        $name_fetch = $sql1->fetch();
                        $sumbit_name=$name_fetch['name'];
                        $updated_at=$name_fetch['updated_at'] ?? null;
                        $next_month=date('Y-m-1 h:i:s', strtotime("+1 months", strtotime($updated_at)));
                        $now_days=date('Y-m-d h:i:s');
                        if($name_fetch && $now_days >= $next_month)
                        {                          
                            $nickname1=$name_fetch['nickname1'];
                            $name=$name_fetch['name'];

                        }
                        else
                        {
                        	$name=$event['name'];
                            $nickname1=$event['nickname1'];
                        }
                        if(!$nickname1)
                        {
                          $nickname1=$event['nickname1'];

                        }
                        $sql_bowlersreleased = $db->prepare('SELECT currentstatus FROM `bowlersreleased` WHERE bowlerid="'.$event['bowlerid'].'"');
                        $sql_bowlersreleased->execute();
                        $release_fetch = $sql_bowlersreleased->fetch();
                        $check_release_status=$release_fetch['currentstatus'];
                        if($check_release_status=='Suspended')
                        {
                            $event['team']='Released Bowlers';
                        }
            $singleEvent['bowlerID'] = $event['bowlerid'];
            $singleEvent['name'] = $name;
            $singleEvent['team'] = $event['team'];
            $singleEvent['nickname'] = $nickname1;
            $singleEvent['tour_game_count'] = currentTourGame($event['bowlerid']);
            $singleEvent['event_count'] = currentEventGame($event['bowlerid']);
            array_push($data, $singleEvent);

            $singleEvent = array();
        }

        echo json_encode($data);
        
    } catch (PDOException $e) {
        echo "There was some problem with the connection: " . $e->getMessage();
    }


    function currentTourGame($bowlerID){
        $database = new Connection();
        $db = $database->openConnection();
        // $sql = $db->prepare("SELECT bowlerid,game1,game2,game3 FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerID' ORDER BY `id` DESC  ");
        $sql = $db->prepare("SELECT
        bowlerid,
        COUNT(CASE WHEN game1 IS NOT NULL AND game1 <> '' THEN 1 END) AS game1_count,
        COUNT(CASE WHEN game2 IS NOT NULL AND game2 <> '' THEN 1 END) AS game2_count,
        COUNT(CASE WHEN game3 IS NOT NULL AND game3 <> '' THEN 1 END) AS game3_count
        FROM bowlerdataseason WHERE bowlerid = '$bowlerID' GROUP BY bowlerid ORDER BY id DESC");
        $sql->execute();
        $allTourGame = $sql->fetchAll();
    
        $count = 0; $finalData = [];
        if(!empty($allTourGame)){
            $count = (int) $allTourGame[0]['game1_count'] + (int) $allTourGame[0]['game2_count'] + $allTourGame[0]['game3_count'];
        }
        // return ["data"=>$finalData,"count"=>$count];
        return $count;
    }
    
    function currentEventGame($bowlerID){
        $database = new Connection();
        $db = $database->openConnection();
        // $sql = $db->prepare("SELECT bowlerid,game1,game2,game3,game4,game5 FROM `bowlerdata` WHERE `bowlerid` = '$bowlerID' ORDER BY `event` ASC");
        $sql = $db->prepare("SELECT
        bowlerid,
        COUNT(CASE WHEN game1 IS NOT NULL AND game1 <> '' THEN 1 END) AS game1_count,
        COUNT(CASE WHEN game2 IS NOT NULL AND game2 <> '' THEN 1 END) AS game2_count,
        COUNT(CASE WHEN game3 IS NOT NULL AND game3 <> '' THEN 1 END) AS game3_count,
        COUNT(CASE WHEN game4 IS NOT NULL AND game4 <> '' THEN 1 END) AS game4_count,
        COUNT(CASE WHEN game5 IS NOT NULL AND game5 <> '' THEN 1 END) AS game5_count
        FROM bowlerdata WHERE bowlerid = '$bowlerID' ORDER BY event DESC");
        $sql->execute();
        $allEventGame = $sql->fetchAll();
    
        $count = 0; $finalData = [];
        if(!empty($allEventGame)){
            $count = (int) $allEventGame[0]['game1_count'] + (int) $allEventGame[0]['game2_count'] + (int) $allEventGame[0]['game3_count'] + (int) $allEventGame[0]['game4_count']; + (int) $allEventGame[0]['game5_count'];
        }
    
        // return ["data"=>$finalData,"count"=>$count];
        return $count;
    }

?>