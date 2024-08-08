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
                        $updated_at=$name_fetch['updated_at'];
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

            array_push($data, $singleEvent);

            $singleEvent = array();
        }

        echo json_encode($data);
        
    } catch (PDOException $e) {
        echo "There was some problem with the connection: " . $e->getMessage();
    }

?>