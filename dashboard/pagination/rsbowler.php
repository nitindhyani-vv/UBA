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

$database = new Connection();
$db = $database->openConnection();
$columns = [
    0 => 'id',
    1 => 'bowlerid',
    2 => 'bowler',
    3 => 'team',
    4 => 'datesubmitted',
    5 => 'removedby',
    6 => 'currentstatus',
    7 => 'eligibledate'
];

$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
$orderIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';
// Building the search query
$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (bowlerid LIKE :searchValue OR bowler LIKE :searchValue OR team LIKE :searchValue OR datesubmitted LIKE :searchValue OR 
                    removedby LIKE :searchValue OR currentstatus LIKE :searchValue OR eligibledate LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bowlersreleased WHERE 1=1 $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

// Fetch paginated records
if(isset($_GET['exportType'])){
    $sql = "SELECT * FROM bowlersreleased WHERE 1=1 $searchQuery ORDER BY $orderColumn $orderDir";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->execute();
    $bowlerDeets = $stmt->fetchAll(PDO::FETCH_ASSOC);   
}else{
    $sql = "SELECT * FROM bowlersreleased WHERE 1=1 $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $bowlerDeets = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['exportType'])) {
    $exportType = $_GET['exportType'];
    switch ($exportType) {
        case 'csv':
            exportCSV($bowlerDeets);
            break;
        case 'excel':
            exportExcel($bowlerDeets);
            break;
        case 'pdf':
            exportPDF($bowlerDeets);
            break;
    }
    exit();
}


$data = [];
$i = $start + 1;
foreach ($bowlerDeets as $bowlers) {
    $row['no'] = $i++;  // Key should match the 'data' property in JS
    $row['uba_id'] = $bowlers['bowlerid'];
    $row['name'] = $bowlers['bowler'];
    $row['team'] = $bowlers['team'];
    $row['date_submitted'] = $bowlers['datesubmitted'];
    $row['removed_by'] = $bowlers['removedby'];
    $row['current_status'] = $bowlers['currentstatus'];
    $row['eligible_date'] = $bowlers['eligibledate'];

    if ($_SESSION['userrole'] == 'admin') {
        if ($bowlers['currentstatus'] == 'Suspended' || $bowlers['currentstatus'] == 'Released') {
            $row['reinstate'] = "<a href='process/reinstatebowler.php?id={$bowlers['id']}'>Reinstate</a>";
        } else {
            $row['reinstate'] = '-';
        }
    }

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
    header('Content-Disposition: attachment; filename="registrations.csv"');
    $output = fopen("php://output", "w");
    $headers = ['UBA ID', 'Name', 'Team','Date Submitted','Removed by','Current Status','Eligible Date'];
    fputcsv($output, $headers);

    foreach ($data as $row) {
        $rowRaw = [$row['bowlerid'], $row['bowler'], $row['team'], $row['datesubmitted'],$row['removedby'],$row['currentstatus'],$row['eligibledate']];
        fputcsv($output, $rowRaw);
    }
    fclose($output);
}

function exportExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'UBA ID');
    $sheet->setCellValue('C1', 'Name');
    $sheet->setCellValue('D1', 'Team');
    $sheet->setCellValue('E1', 'Date Submitted');
    $sheet->setCellValue('F1', 'Removed by');
    $sheet->setCellValue('G1', 'Current Status');
    $sheet->setCellValue('H1', 'Eligible Date');

    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $sheet->setCellValue('A' . $rowNum, $i++);
        $sheet->setCellValue('B' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('C' . $rowNum, $row['bowler']);
        $sheet->setCellValue('D' . $rowNum, $row['team']);
        $sheet->setCellValue('E' . $rowNum, $row['datesubmitted']);
        $sheet->setCellValue('F' . $rowNum, $row['removedby']);
        $sheet->setCellValue('G' . $rowNum, $row['currentstatus']);
        $sheet->setCellValue('H' . $rowNum, $row['eligibledate']);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="registrations.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

function exportPDF($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Set column headers
    $pdf->Cell(25, 10, 'UBA ID', 1);
    $pdf->Cell(40, 10, 'Name', 1);
    $pdf->Cell(40, 10, 'Team', 1);
    $pdf->Cell(25, 10, 'Date Submitted', 1);
    $pdf->Cell(24, 10, 'Removed by', 1);
    $pdf->Cell(25, 10, 'Current Status', 1);
    $pdf->Cell(25, 10, 'Eligible Date', 1);
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    $i=1;
    foreach ($data as $row) {
        $pdf->Cell(25, 10, $row['bowlerid'], 1);
        $pdf->Cell(40, 10, $row['bowler'], 1);
        $pdf->Cell(40, 10, $row['team'], 1);
        $pdf->Cell(25, 10, $row['datesubmitted'], 1);
        $pdf->Cell(24, 10, $row['removedby'], 1);
        $pdf->Cell(25, 10, $row['currentstatus'], 1);
        $pdf->Cell(25, 10, $row['eligibledate'], 1);
        $pdf->Ln();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="team_officials.pdf"');
    $pdf->Output('I', 'team_officials.pdf');
}
?>
