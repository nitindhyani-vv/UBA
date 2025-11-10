<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';

$database = new Connection();
$db = $database->openConnection();

$bowlerId = $_GET['bowler_id'] ?? null;
$teamName = $_GET['team_name'] ?? null;
$type = $_GET['type'] ?? null;

if ($type === 'by_name' && $bowlerId) {
    $response = [
        "tour_game_count" => getCurrentTourGameCount($db, $bowlerId),
        "event_count" => getCurrentEventGameCount($db, $bowlerId),
        "bowler_id" => $bowlerId
    ];
} else {
    $response = processTeamBowlers($db, $teamName);
}

header('Content-Type: application/json');
echo json_encode($response);

function getCurrentTourGameCount($db, $bowlerId) {
    $currentYear = date("Y");
    $nextYear = date("y", strtotime("+1 year"));
    // $seasonYear = "$currentYear/$nextYear";
    $seasonYear = '2025/26';


    $sql = $db->prepare("
        SELECT 
            SUM(game1 IS NOT NULL AND game1 <> '') AS game1_count,
            SUM(game2 IS NOT NULL AND game2 <> '') AS game2_count,
            SUM(game3 IS NOT NULL AND game3 <> '') AS game3_count
        FROM bowlerdataseason 
        WHERE bowlerid = :bowlerId AND year = :seasonYear
    ");
    $sql->execute([':bowlerId' => $bowlerId, ':seasonYear' => $seasonYear]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    return array_sum($result);
}

function getCurrentEventGameCount($db, $bowlerId) {
    $currentYear = date("Y");
    $nextYear = date("y", strtotime("+1 year"));
    // $seasonYear = "$currentYear/$nextYear";
    $seasonYear = '2025/26';

    $sql = $db->prepare("
        SELECT 
            SUM(game1 IS NOT NULL AND game1 <> '') AS game1_count,
            SUM(game2 IS NOT NULL AND game2 <> '') AS game2_count,
            SUM(game3 IS NOT NULL AND game3 <> '') AS game3_count,
            SUM(game4 IS NOT NULL AND game4 <> '') AS game4_count,
            SUM(game5 IS NOT NULL AND game5 <> '') AS game5_count
        FROM bowlerdata 
        WHERE bowlerid = :bowlerId AND year = :seasonYear
    ");
    $sql->execute([':bowlerId' => $bowlerId, ':seasonYear' => $seasonYear]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    return array_sum($result);
}

function processTeamBowlers($db, $teamName) {
    $sql = $db->prepare("SELECT bowlerid FROM bowlers WHERE team = :teamName");
    $sql->execute([':teamName' => $teamName]);
    $teamBowlers = $sql->fetchAll(PDO::FETCH_ASSOC);

    $totalTourGames = 0;
    $totalEventGames = 0;
    $bowlerData = [];

    foreach ($teamBowlers as $bowler) {
        $bowlerId = $bowler['bowlerid'];
        $tourGameCount = getCurrentTourGameCount($db, $bowlerId);
        $eventGameCount = getCurrentEventGameCount($db, $bowlerId);

        $totalTourGames += $tourGameCount;
        $totalEventGames += $eventGameCount;

        $bowlerData[] = [
            "bowler_id" => $bowlerId,
            "tour_game_count" => $tourGameCount,
            "event_count" => $eventGameCount
        ];
    }

    return [
        "total_tour_game_count" => $totalTourGames,
        "total_event_count" => $totalEventGames,
        "bowlers" => $bowlerData
    ];
}
?>
