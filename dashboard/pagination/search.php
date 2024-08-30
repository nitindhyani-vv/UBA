<?php 
  include_once '../../baseurl.php';
  include_once '../../session.php';
  include_once '../../connect.php';

  $database = new Connection();
  $db = $database->openConnection();

  $bowlerId = isset($_GET['bowler_id']) ? $_GET['bowler_id'] : null;
  $teamName = isset($_GET['team_name']) ? $_GET['team_name'] : null;
  $type = isset($_GET['type']) ? $_GET['type'] : null;

// try{
//     if ($bowlerName != '') {
//         $searchTerm = $bowlerName;
//         $column = 'name';
//         $sql = $db->prepare("SELECT team,nickname1,name,bowlerid FROM `bowlers` WHERE `$column` LIKE '%$searchTerm%' ORDER BY name ASC  ");
//         $sql->execute();
//     } else {
//         $searchTerm = $bowlerTeam;
//         $column = 'teamname';
//         $sql = $db->prepare("SELECT * FROM `teams` WHERE `$column` LIKE '%$searchTerm%' ORDER by teamname ASC ");
//         $sql->execute();
//     }
//     $dataFetched = $sql->fetchAll();
// } catch (PDOException $e) {
//     echo "There was some problem with the connection: " . $e->getMessage();
// }

// $data = [];
// if($bowlerName != '' && !empty($dataFetched)){
//     foreach ($dataFetched as $rowData) {
//         $sql_bowlersreleased = $db->prepare('SELECT currentstatus FROM `bowlersreleased` WHERE bowlerid="'.$rowData['bowlerid'].'"');
//         $sql_bowlersreleased->execute();
//         $release_fetch = $sql_bowlersreleased->fetch();
//         $check_release_status=$release_fetch['currentstatus'];
//         if($check_release_status=='Suspended')
//         {
//             $rowData['team']='Released Bowlers';
//         }
//         $rowData['total_tour_game'] = currentTourGame($rowData['bowlerid']);
//         $rowData['event_game'] = currentEventGame($rowData['bowlerid']);
//         $data[] = $rowData;

//     }
    
// }

if($type == 'by_name'){
    $response = [
        "tour_game_count" => currentTourGame($bowlerId),
        "event_count" => currentEventGame($bowlerId),
        "bowler_id" => $bowlerId
    ];

}else{
    $sql = $db->prepare("SELECT bowlerid FROM `bowlers` WHERE `team` = '$teamName'");
    // $teamName = "%$teamName%"; // Add wildcards to the team name
    $sql->execute();
    $teamBowlers = $sql->fetchAll();
    $tourcount = 0; $eventCount = 0;
    foreach ($teamBowlers as $bowler) {
        $tourcount += currentTourGame($bowler['bowlerid']);
        $eventCount += currentEventGame($bowler['bowlerid']);
    }

    $response = [
        "tour_game_count" => $tourcount?? 0,
        "event_count" => $eventCount ?? 0,
    ];
}

header('Content-Type: application/json');
echo json_encode($response);

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