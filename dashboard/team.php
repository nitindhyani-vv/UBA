<?php

include_once '../connect.php';

$teamName = $_POST['teamID'];

try {
    $database = new Connection();
    $db = $database->openConnection();

    // Use a single query to fetch all required data
    $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `active` > 0 AND `team` = ? ORDER BY `name` ASC");
    $sql->execute([$teamName]);
    $bowlers = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (empty($bowlers)) {
        echo json_encode([]);
        exit;
    }

    // Fetch all additional data in bulk
    $bowlerIDs = array_column($bowlers, 'bowlerid');
    $bowlerIDPlaceholders = implode(',', array_fill(0, count($bowlerIDs), '?'));

    $additionalDataQuery = $db->prepare("SELECT bowlerid, nickname1, name FROM `bowlers` WHERE bowlerid IN ($bowlerIDPlaceholders)");
    $additionalDataQuery->execute($bowlerIDs);
    $additionalData = $additionalDataQuery->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    // Fetch tour and event games in bulk
    // $currentYear = date("Y") . '/' . date("y", strtotime("+1 year"));
    $currentYear = '2025/26';

    $tourGamesQuery = $db->prepare("
        SELECT 
            bowlerid,
            SUM(CASE WHEN game1 IS NOT NULL AND game1 <> '' THEN 1 ELSE 0 END) AS game1_count,
            SUM(CASE WHEN game2 IS NOT NULL AND game2 <> '' THEN 1 ELSE 0 END) AS game2_count,
            SUM(CASE WHEN game3 IS NOT NULL AND game3 <> '' THEN 1 ELSE 0 END) AS game3_count
        FROM `bowlerdataseason`
        WHERE bowlerid IN ($bowlerIDPlaceholders)
        AND year = ?
        GROUP BY bowlerid
    ");
    $tourGamesQuery->execute(array_merge($bowlerIDs, [$currentYear]));
    $tourGames = $tourGamesQuery->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $eventGamesQuery = $db->prepare("
        SELECT 
            bowlerid,
            SUM(CASE WHEN game1 IS NOT NULL AND game1 <> '' THEN 1 ELSE 0 END) AS game1_count,
            SUM(CASE WHEN game2 IS NOT NULL AND game2 <> '' THEN 1 ELSE 0 END) AS game2_count,
            SUM(CASE WHEN game3 IS NOT NULL AND game3 <> '' THEN 1 ELSE 0 END) AS game3_count,
            SUM(CASE WHEN game4 IS NOT NULL AND game4 <> '' THEN 1 ELSE 0 END) AS game4_count,
            SUM(CASE WHEN game5 IS NOT NULL AND game5 <> '' THEN 1 ELSE 0 END) AS game5_count
        FROM `bowlerdata`
        WHERE bowlerid IN ($bowlerIDPlaceholders)
        AND year = ?
        GROUP BY bowlerid
    ");
    $eventGamesQuery->execute(array_merge($bowlerIDs, [$currentYear]));
    $eventGames = $eventGamesQuery->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    // Process and prepare response
    $data = [];
    $now = new DateTime();

    foreach ($bowlers as $event) {
        $bowlerID = $event['bowlerid'];
        $extraData = $additionalData[$bowlerID] ?? [];

        $updatedAt = $extraData['updated_at'] ?? null;
        $nickname1 = $extraData['nickname1'] ?? $event['nickname1'];
        $name = $event['name'];

        if ($updatedAt) {
            $nextMonth = (new DateTime($updatedAt))->modify('+1 month');
            if ($now >= $nextMonth) {
                $name = $extraData['name'] ?? $name;
                $nickname1 = $extraData['nickname1'] ?? $nickname1;
            }
        }

        $tourGameCount = isset($tourGames[$bowlerID]) 
            ? array_sum($tourGames[$bowlerID]) 
            : 0;

        $eventGameCount = isset($eventGames[$bowlerID]) 
            ? array_sum($eventGames[$bowlerID]) 
            : 0;

            $sql_bowlersreleased = $db->prepare('SELECT currentstatus FROM `bowlersreleased` WHERE bowlerid="'.$event['bowlerid'].'"');
            $sql_bowlersreleased->execute();
            $release_fetch = $sql_bowlersreleased->fetch();
            $check_release_status=$release_fetch['currentstatus'];
            if($check_release_status=='Suspended')
            {
                $event['team']='Released Bowlers';
            }

        $data[] = [
            'bowlerID' => $bowlerID,
            'name' => $name,
            'team' => $event['team'] ?? 'Released Bowlers',
            'nickname' => $nickname1,
            'tour_game_count' => $tourGameCount,
            'event_count' => $eventGameCount,
        ];
    }

    echo json_encode($data);

} catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

?>
