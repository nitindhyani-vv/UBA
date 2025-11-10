<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autoload.php');
require_once('../../fpdf/fpdf.php'); 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try{
    $searchBowlers = $_SESSION['team'];
    $active = 1;
    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0; 
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $bowler = isset($_POST['bowlerName']) ? $_POST['bowlerName'] : null;
        
    $columns = [
        0 => 'id',
        1 => 'bowler',
        2 => 'bowlerid',
        2 => 'team'
    ];
    
    $database = new Connection();
    $db = $database->openConnection();
    
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';
}catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

$bowlerName = NUll;
if($bowler){
    $bowlerName = $bowler;
}

$currentstatus = 'Released';
$team = 'Released Bowlers';
$isTransferred = 0;

$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (bowler LIKE :searchValue OR bowlerid LIKE :searchValue OR team LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

$stmt = $db->prepare("SELECT COUNT(*) FROM `bowlersreleased` 
        WHERE `currentstatus` = :currentstatus 
        AND `team` = :team
        AND `isTransferred` = :istransferred 
        AND `bowler` LIKE :bowler $searchQuery");

if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':bowler', $bowlerName, PDO::PARAM_STR);
$stmt->bindParam(':team', $team, PDO::PARAM_STR);
$stmt->bindParam(':currentstatus', $currentstatus, PDO::PARAM_STR);
$stmt->bindParam(':istransferred', $isTransferred, PDO::PARAM_STR);
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

$sql = "SELECT * FROM `bowlersreleased` 
        WHERE `currentstatus` = :currentstatus 
        AND `team` = :team
        AND `isTransferred` = :istransferred 
        AND `bowler` LIKE :bowler 
        $searchQuery 
        ORDER BY $orderColumn $orderDir 
        LIMIT :start, :limit";

$stmt = $db->prepare($sql);
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}

$stmt->bindParam(':bowler', $bowlerName, PDO::PARAM_STR);
$stmt->bindParam(':team', $team, PDO::PARAM_STR);
$stmt->bindParam(':currentstatus', $currentstatus, PDO::PARAM_STR);
$stmt->bindParam(':istransferred', $isTransferred, PDO::PARAM_STR);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
$i = $start + 1;
foreach ($dataFetched as $singleScoreData) {
    if ($singleScoreData['team'] != $searchBowlers) {
        $row['no'] = $i ?? '-';
        $row['name'] = $singleScoreData['bowler'] ?? '-';
        $row['bowler_id'] = $presidentBowler['bowlerid'] ?? '-';
        $row['team'] = $singleScoreData['team'] ?? '-';
        $row['add_to_team'] = '<a href="process/addBowlertoTeam.php?id='.$singleScoreData['bowlerid'].'"><i class="fas fa-plus-square"></i></a>';
        $data[] = $row;
    }
}

$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($response);

?>