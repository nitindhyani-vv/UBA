<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autoload.php');
require_once('../../fpdf/fpdf.php'); 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0; 
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$columns = [
    0 => 'id',
    1 => 'teamname',
    2 => 'rostersubmitted',
    3 => 'submittedby',
    4 => 'rostersubmissiondate',
];
$database = new Connection();
$db = $database->openConnection();

$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
$orderIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';

// Building the search query
$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (teamname LIKE :searchValue OR submittedby LIKE :searchValue)";
}

$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM teams WHERE 1=1 $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

    if(isset($_GET['exportType'])){
        $sql = "SELECT * FROM `teams` WHERE 1=1 $searchQuery ORDER BY $orderColumn $orderDir";
        $stmt = $db->prepare($sql);
        if (!empty($searchValue)) {
         $stmt->bindParam(':searchValue', $searchValue);
        }
        $stmt->execute();
        $teamDeets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }else{
        $sql = "SELECT * FROM `teams` WHERE 1=1 $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
        $stmt = $db->prepare($sql);
        if (!empty($searchValue)) {
            $stmt->bindParam(':searchValue', $searchValue);
        }
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $teamDeets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (isset($_GET['exportType'])) {
        $exportType = $_GET['exportType'];
        switch ($exportType) {
            case 'csv':
                exportCSV($teamDeets);
                break;
            case 'excel':
                exportExcel($teamDeets);
                break;
            case 'pdf':
                exportPDF($teamDeets);
                break;
        }
        exit();
    }

$data = [];
$i = $start + 1;
foreach ($teamDeets as $team) {
    $rostersubmitted;
    $submittedby;
    if($team['rostersubmitted'] == 1) { $rostersubmitted = 'Yes';} else { $rostersubmitted = 'No';};
    if($team['submittedby'] == 'Team') {$submittedby= 'Team';} else {$submittedby= 'Auto';};

    $ymd = $team['rostersubmissiondate'];
    $timestamp = strtotime($ymd);
    $submittedOn = date("m-d-Y H:m:s", $timestamp);

    $row['no'] = $i++; 
    $row['team'] = $team['teamname'];
    $row['rosterSubmitted'] = $rostersubmitted;
    $row['submittedBy'] = $submittedby;
    $row['submittedOn'] = $submittedOn;
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


function exportCSV($data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="submitte-roster.csv"');
    $output = fopen("php://output", "w");
    $headers = ['Team', 'Roster Submitted', 'Submitted By','Submitted On'];
    fputcsv($output, $headers);

    foreach ($data as $row) {
        $rostersubmitted;
        $submittedby;
        if($row['rostersubmitted'] == 1) { $rostersubmitted = 'Yes';} else { $rostersubmitted = 'No';};
        if($row['submittedby'] == 'Team') {$submittedby= 'Team';} else {$submittedby= 'Auto';};

        $ymd = $row['rostersubmissiondate'];
        $timestamp = strtotime($ymd);
        $submittedOn = date("m-d-Y H:m:s", $timestamp);

        $rowRaw = [$row['teamname'], $rostersubmitted, $submittedby, $submittedOn];
        fputcsv($output, $rowRaw);
    }
    fclose($output);
}

function exportExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'Team');
    $sheet->setCellValue('C1', 'Roster Submitted');
    $sheet->setCellValue('D1', 'Submitted By');
    $sheet->setCellValue('E1', 'Submitted On');

    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $rostersubmitted;
        $submittedby;
        if($row['rostersubmitted'] == 1) { $rostersubmitted = 'Yes';} else { $rostersubmitted = 'No';};
        if($row['submittedby'] == 'Team') {$submittedby= 'Team';} else {$submittedby= 'Auto';};
        $ymd = $row['rostersubmissiondate'];
        $timestamp = strtotime($ymd);
        $submittedOn = date("m-d-Y H:m:s", $timestamp);
        $sheet->setCellValue('A' . $rowNum, $i++);
        $sheet->setCellValue('B' . $rowNum, $row['teamname']);
        $sheet->setCellValue('C' . $rowNum, $rostersubmitted);
        $sheet->setCellValue('D' . $rowNum, $submittedby);
        $sheet->setCellValue('E' . $rowNum, $submittedOn);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="submitte-roster.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

function exportPDF($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    // Set column headers
    $pdf->Cell(10, 10, 'No', 1);
    $pdf->Cell(60, 10, 'Team', 1);
    $pdf->Cell(40, 10, 'Roster Submitted', 1);
    $pdf->Cell(30, 10, 'Submitted By', 1);
    $pdf->Cell(50, 10, 'Submitted On', 1);
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    $i=1;
    foreach ($data as $row) {
        $rostersubmitted;
        $submittedby;
        if($row['rostersubmitted'] == 1) { $rostersubmitted = 'Yes';} else { $rostersubmitted = 'No';};
        if($row['submittedby'] == 'Team') {$submittedby= 'Team';} else {$submittedby= 'Auto';};
        $ymd = $row['rostersubmissiondate'];
        $timestamp = strtotime($ymd);
        $submittedOn = date("m-d-Y H:m:s", $timestamp);
        $pdf->Cell(10, 10, $i++, 1);
        $pdf->Cell(60, 10, $row['teamname'], 1);
        $pdf->Cell(40, 10, $rostersubmitted, 1);
        $pdf->Cell(30, 10, $submittedby, 1);
        $pdf->Cell(50, 10, $submittedOn, 1);
        $pdf->Ln();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="submitte-roster.pdf"');
    $pdf->Output('I', 'submitte-roster.pdf');
}

?>