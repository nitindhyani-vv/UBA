<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autoload.php');
require_once('../../fpdf/fpdf.php'); 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$useremail = $_SESSION['useremail'];

try{
    $database = new Connection();
    $db = $database->openConnection();

    $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `uemail` = :useremail");
    $sql->bindParam(':useremail', $useremail, PDO::PARAM_STR);
    $sql->execute();
    $bowlerDeets = $sql->fetch();

    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0; 
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $seasonYear = isset($_POST['seasonYear']) ? $_POST['seasonYear'] : null;

    $columns = [
        0 => 'id',
        1 => 'eventdate',
        2 => 'year',
        3 => 'event',
        4 => 'location',
        5 => 'team',
        6 => 'game1',
        7 => 'game2',
        8 => 'game3',
    ];

    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';
}catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (eventdate LIKE :searchValue OR year LIKE :searchValue OR event LIKE :searchValue OR location LIKE :searchValue OR team LIKE :searchValue OR 
    game1 LIKE :searchValue OR game2 LIKE :searchValue OR game3 LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

if($bowlerDeets['bowlerid']){
    if($seasonYear){
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM `bowlerdataseason` WHERE `bowlerid` = :bowlerid AND `year` = :years $searchQuery");
    }else{
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM `bowlerdataseason` WHERE `bowlerid` = :bowlerid $searchQuery");
    }
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }

    if($seasonYear){
        $stmt->bindParam(':years', $seasonYear, PDO::PARAM_STR);
    }
    $stmt->bindParam(':bowlerid', $bowlerDeets['bowlerid'], PDO::PARAM_STR);
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();


    if(isset($_POST['exportType'])){
        if($seasonYear){
            $sql = "SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = :bowlerid AND `year` = :years $searchQuery ORDER BY $orderColumn $orderDir";
        }else{
            $sql = "SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = :bowlerid $searchQuery ORDER BY $orderColumn $orderDir";
        }
        $stmt = $db->prepare($sql);

        if (!empty($searchValue)) {
            $stmt->bindParam(':searchValue', $searchValue);
        }

        if($seasonYear){
            $stmt->bindParam(':years', $seasonYear, PDO::PARAM_STR);
        }
        $stmt->bindParam(':bowlerid', $bowlerDeets['bowlerid'], PDO::PARAM_STR);
        $stmt->execute();
        $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }else{
        if($seasonYear){
            $sql = "SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = :bowlerid AND `year` = :years $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
        }else{
            $sql = "SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = :bowlerid $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
        }
        $stmt = $db->prepare($sql);

        if (!empty($searchValue)) {
            $stmt->bindParam(':searchValue', $searchValue);
        }

        if($seasonYear){
            $stmt->bindParam(':years', $seasonYear, PDO::PARAM_STR);
        }

        $stmt->bindParam(':bowlerid', $bowlerDeets['bowlerid'], PDO::PARAM_STR);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (isset($_POST['exportType'])) {
        $exportType = $_POST['exportType'];
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
    foreach ($dataFetched as $rowData) {
        $ymd = $rowData['eventdate'];
        $timestamp = strtotime($ymd);
        $dmy = date("m-d-Y", $timestamp);

        $row['no'] = $i++ ?? '-'; 
        $row['date'] = $dmy ?? '-';
        $row['year']     = $rowData['year'] ?? '-';
        $row['event_name'] = $rowData['event'] ?? '-';
        $row['event_type'] = $rowData['location'] ?? '-';
        $row['team'] = $rowData['team'] ?? '-';
        $row['game1'] = $rowData['game1'] ?? '-';
        $row['game2'] =  $rowData['game2'] ?? '-';
        $row['game3'] =  $rowData['game3'] ?? '-';
        $data[] = $row;
    }

}else{
    $data = [];
}


$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($response);

function exportCSV($data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="season-tour.csv"');
    $output = fopen("php://output", "w");
    $headers = ['Date', 'Year', 'Event Name','Event type','Team','Game1','Game2','Game3'];
    fputcsv($output, $headers);
    foreach ($data as $row) {
        $ymd = $row['eventdate'];
        $timestamp = strtotime($ymd);
        $dmy = date("m-d-Y", $timestamp);
        $rowRaw = [$dmy, $row['year'], $row['event'], $row['location'],$row['team'],$row['game1'],$row['game2'],$row['game3']];
        fputcsv($output, $rowRaw);
    }
    fclose($output);
}


function exportExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'Date');
    $sheet->setCellValue('B1', 'Year');
    $sheet->setCellValue('C1', 'Event Name');
    $sheet->setCellValue('D1', 'Event Type');
    $sheet->setCellValue('E1', 'Team');
    $sheet->setCellValue('F1', 'Game1');
    $sheet->setCellValue('G1', 'Game2');
    $sheet->setCellValue('H1', 'Game3');


    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $ymd = $row['eventdate'];
        $timestamp = strtotime($ymd);
        $dmy = date("m-d-Y", $timestamp);
        $sheet->setCellValue('A' . $rowNum, $dmy);
        $sheet->setCellValue('B' . $rowNum, $row['year']);
        $sheet->setCellValue('C' . $rowNum, $row['event']);
        $sheet->setCellValue('D' . $rowNum, $row['location']);
        $sheet->setCellValue('E' . $rowNum, $row['team']);
        $sheet->setCellValue('F' . $rowNum, $row['game1']);
        $sheet->setCellValue('G' . $rowNum, $row['game2']);
        $sheet->setCellValue('H' . $rowNum, $row['game3']);

        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="season-tour.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}


function exportPDF($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Set column headers
    $pdf->Cell(38, 10, 'Date', 1);
    $pdf->Cell(40, 10, 'Year', 1);
    $pdf->Cell(30, 10, 'Event Name', 1);
    $pdf->Cell(40, 10, 'Event Type', 1);
    $pdf->Cell(40, 10, 'Team', 1);
    $pdf->Cell(40, 10, 'Game1', 1);
    $pdf->Cell(40, 10, 'Game2', 1);
    $pdf->Cell(40, 10, 'Game3', 1);
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    $i=1;
    foreach ($data as $row) {
        $ymd = $row['eventdate'];
        $timestamp = strtotime($ymd);
        $dmy = date("m-d-Y", $timestamp);
        $pdf->Cell(38, 10, $dmy, 1);
        $pdf->Cell(40, 10, $row['year'], 1);
        $pdf->Cell(30, 10, $row['event'], 1);
        $pdf->Cell(40, 10, $row['location'], 1);
        $pdf->Cell(40, 10, $row['team'], 1);
        $pdf->Cell(40, 10, $row['game1'], 1);
        $pdf->Cell(40, 10, $row['game2'], 1);
        $pdf->Cell(40, 10, $row['game3'], 1);
        $pdf->Ln();
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="season-tour.pdf"');
    $pdf->Output('I', 'season-tour.pdf');
}




?>