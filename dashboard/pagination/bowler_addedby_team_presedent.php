<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autolopad.php');
require_once('../../fpdf/fpdf.php'); 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try{
    $database = new Connection();
    $db = $database->openConnection();

    $limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0; 
    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $columns = [
        0 => 'id',
        1 => 'bowlerid',
        2 => 'name',
        2 => 'team',
        2 => 'nickname1',
        2 => 'sanction',
        2 => 'create_at',
        2 => 'name',

    ];
    
    $searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    $orderIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';
}catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (teamname LIKE :searchValue OR owner LIKE :searchValue OR president LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bowlers WHERE `active` = :nonactive $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':nonactive', $nonactive, PDO::PARAM_INT);
$stmt->execute();
$totalRecords = $stmt->fetchColumn();


if(isset($_GET['exportType'])){
    $sql = "SELECT * FROM `bowlers` WHERE `active` = :nonactive $searchQuery ORDER BY $orderColumn $orderDir";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':nonactive', $nonactive, PDO::PARAM_INT);
    $stmt->execute();
    $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

}else{
 $sql = "SELECT * FROM `bowlers` WHERE `active` = :nonactive $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':nonactive', $nonactive, PDO::PARAM_INT);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['exportType'])) {
    $exportType = $_GET['exportType'];
    switch ($exportType) {
        case 'csv':
            exportCSV($dataFetched);
            break;
        case 'excel':
            exportExcel($dataFetched);
            break;
        case 'pdf':
            exportPDF($dataFetched);
            break;
    }
    exit();
}


$data = [];
$i = $start + 1;
foreach ($dataFetched as $singleScoreData) {
    $row['no'] = $i++ ?? '-'; 
    $row['bowlerID'] = $singleScoreData['bowlerid'] ?? '-';
    $row['name']     = $presidentBowler['name'] ?? '-';
    $row['teamName'] = $singleScoreData['team'] ?? '-';
    $row['nickname'] = $singleScoreData['nickname1'] ?? '-';
    $row['sanction'] = $singleScoreData['sanction'] ?? '-';
    $row['createAt'] = $singleScoreData['create_at'] ?? '-';
    $row['approve'] =  "<a style='cursor: pointer;' onclick='showConfirmationAddBowler('add','".$singleScoreData['bowlerid']."')'><i class='fas fa-check'></i></a>";
    $row['decline'] =  "<a href='process/activateBowler.php?id=n&bowler={$singleScoreData['bowlerid']}'><i class='fas fa-times'></i></a>";
    $data[] = $row;
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