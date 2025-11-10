<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autoload.php');
require_once('../../fpdf/fpdf.php'); 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try{
    $teamName = $_SESSION['team'];
    $active = 1;
    $limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0; 
    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $columns = [
        0 => 'id',
        1 => 'bowlerid',
        2 => 'name',
        2 => 'nickname1',
        2 => 'sanction',
        2 => 'enteringAvg',
        2 => 'ubaAvg',
        2 => 'officeheld',
    ];
    
    $database = new Connection();
    $db = $database->openConnection();
    
    $searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    $orderIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';
}catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (bowlerid LIKE :searchValue OR name LIKE :searchValue OR nickname1 LIKE :searchValue OR sanction LIKE :searchValue 
    OR enteringAvg LIKE :searchValue OR ubaAvg LIKE :searchValue OR officeheld LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

$stmt = $db->prepare("SELECT COUNT(*) as count FROM `bowlers` WHERE `team` = :teamName AND `active` = :active $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':teamName', $teamName, PDO::PARAM_STR);
$stmt->bindParam(':active', $active, PDO::PARAM_INT);
$stmt->execute();
$totalRecords = $stmt->fetchColumn();


if(isset($_GET['exportType'])){
    $sql = "SELECT * FROM `bowlers` WHERE `team` = :teamName AND `active` = :active $searchQuery ORDER BY $orderColumn $orderDir";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':teamName', $teamName, PDO::PARAM_STR);
    $stmt->bindParam(':active', $active, PDO::PARAM_INT);
    $stmt->execute();
    $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
}else{
    $sql = "SELECT * FROM `bowlers` WHERE `team` = :teamName AND `active` = :active $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':teamName', $teamName, PDO::PARAM_STR);
    $stmt->bindParam(':active', $active, PDO::PARAM_INT);
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
    }
    exit();
}



$data = [];
$i = $start + 1;
foreach ($dataFetched as $singleScoreData) {
    $getSeasonTourAvg = getSeasonTourAvg($singleScoreData['bowlerid']);
    $row['uba_id'] = $singleScoreData['bowlerid'] ?? '-';
    $row['name'] = $singleScoreData['name'] ?? '-';
    $row['nickname'] = $singleScoreData['nickname1'] ?? '-';
    $row['sanction_number'] = $singleScoreData['sanction'] ?? '-';
    $row['entering_avg'] = $singleScoreData['enteringAvg'] ?? '-';
    $row['uba_average'] = $singleScoreData['ubaAvg'] ?? '-';
    $row['season_tour_avg'] = $getSeasonTourAvg ?? '-';
    $row['office_held'] = $singleScoreData['officeheld'] ?? '-';
    $row['edit'] = "<a href='editBowler.php?id={$singleScoreData['id']}'><i class='fas fa-pen-square'></i></a>";
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

//  teamSelected 
function exportCSV($data) {
   header('Content-Type: text/csv');
   header('Content-Disposition: attachment; filename="team-roster.csv"');
   
   $output = fopen("php://output", "w");
   $headers = ['UBA ID', 'Name', 'Nickname', 'Sanction Number', 'Entering Avg', 'UBA Avg', 'Season Tour Avg', 'Office Held'];

   fputcsv($output, $headers);
   foreach ($data as $row) {
       $getSeasonTourAvg = getSeasonTourAvg($row['bowlerid']);
       $rowRaw = [$row['bowlerid'], $row['name'], $row['nickname1'], $row['sanction'], $row['enteringAvg'], $row['ubaAvg'], $getSeasonTourAvg, $row['officeheld']];
       fputcsv($output, $rowRaw);
   }
   fclose($output);
}

function exportExcel($data){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'UBA ID');
    $sheet->setCellValue('B1', 'Name');
    $sheet->setCellValue('C1', 'Nickname');
    $sheet->setCellValue('D1', 'Sanction #am');
    $sheet->setCellValue('E1', 'Entering Avg');
    $sheet->setCellValue('F1', 'UBA Avg');
    $sheet->setCellValue('G1', 'ST Avg');
    $sheet->setCellValue('H1', 'Office Held');

    $rowNum = 2;
    foreach ($data as $row) {
        $getSeasonTourAvg = getSeasonTourAvg($row['bowlerid']);
        $sheet->setCellValue('A' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('B' . $rowNum, $row['name']);
        $sheet->setCellValue('C' . $rowNum, $row['nickname1']);
        $sheet->setCellValue('D' . $rowNum, $row['sanction']);
        $sheet->setCellValue('E' . $rowNum, $row['enteringAvg']);
        $sheet->setCellValue('F' . $rowNum, $row['ubaAvg']);
        $sheet->setCellValue('G' . $rowNum, $getSeasonTourAvg);
        $sheet->setCellValue('H' . $rowNum, $row['officeheld']);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="team-roster.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}


function getSeasonTourAvg($bowlerID){
    $database = new Connection();
    $db = $database->openConnection();
    $currentYear = date("Y"); 
    $nextYear=date("Y",strtotime("-1 year"));
    $year = substr( $currentYear, -2);

    // echo "SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerID' AND YEAR(eventdate) BETWEEN '$preYear' AND '$currentYear' ORDER BY `eventdate` DESC";
    $avrgseason = $db->prepare("SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerID' AND YEAR(eventdate) BETWEEN '$nextYear' AND '$currentYear' AND year='$nextYear/$year' ORDER BY `eventdate` DESC");
    $avrgseason->execute();
    $avrgseasonAll = $avrgseason->fetchAll();

    $arrayCount = array(); $gamelenth = array();
    $game1 = 0; $game2 = 0; $game3 = 0; $addAll = 0; $avrgss =0;

    foreach ($avrgseasonAll as $seasonVal) {    
        $checkDate = new DateTime($seasonVal['eventdate']);
        $checkDateFormatted = $checkDate->format('Y-m-d');

        $sectValue1 = new DateTime("2023-09-01"); 
        $sectValue1Formatted = $sectValue1->format('Y-m-d');

        $sectValue2 = new DateTime("2024-09-01"); 
        $sectValue2Formatted = $sectValue2->format('Y-m-d');
        if ($checkDateFormatted <= $sectValue2Formatted && $checkDateFormatted >= $sectValue1Formatted) {
            if($seasonVal['game1'] > 1 ){
                $game1 = $game1 + $seasonVal['game1'];
                array_push($gamelenth, $seasonVal['game1']);
            }
            if($seasonVal['game2'] > 1 ){
                $game2 = $game2 + $seasonVal['game2'];
                array_push($gamelenth, $seasonVal['game2']);
            }
            
            if($seasonVal['game3'] > 1 ){
                $game3 = $game3 + $seasonVal['game3'];
                array_push($gamelenth, $seasonVal['game3']);
            }
            
            array_push($arrayCount, '1');
            
            if(sizeof($gamelenth) >= 9){
                $addAll = $game1 + $game2 + $game3;
                $avrgss = $addAll/sizeof($gamelenth);
            }else{
                $addAll = 0;
                $avrgss = 0.00;
            }
        }

    }
    return number_format($avrgss,2);
}
?>