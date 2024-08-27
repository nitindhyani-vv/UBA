<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autoload.php');
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
        1 => 'requestedby',
        2 => 'bowler',
        3 => 'bowlerid',
        4 => 'fromteam',
        5 => 'toteam',
        6 => 'claimtime',
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
    $searchQuery = " AND (requestedby LIKE :searchValue OR bowler LIKE :searchValue OR fromteam LIKE :searchValue OR toteam LIKE :searchValue OR claimtime LIKE :searchValue OR bowlerid LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

$approved = 0;
// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM `bowlerTransfers` WHERE `approved` = :approved $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

if(isset($_GET['exportType'])){
    $sql = "SELECT * FROM `bowlerTransfers` WHERE `approved` = :approved $searchQuery ORDER BY $orderColumn $orderDir";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
    $stmt->execute();
    $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

}else{
 $sql = "SELECT * FROM `bowlerTransfers` WHERE `approved` = :approved $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
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
foreach ($dataFetched as $rowData) {
    $row['no'] = $i++ ?? '-'; 
    $row['requested_by'] = $rowData['requestedby'] ?? '-';
    $row['bowler']     = $rowData['bowler'] ?? '-';
    $row['bowler_id'] = $rowData['bowlerid'] ?? '-';
    $row['from'] = $rowData['fromteam'] ?? '-';
    $row['to'] = $rowData['toteam'] ?? '-';
    $row['date_time'] = $rowData['claimtime'] ?? '-';
    $row['approve'] =  "<a class='approve' onclick='showConfirmation('transfer','".$rowData['bowlerid']."','".$rowData['id']."')'><i class='fas fa-check'></i></a>";
    $row['decline'] =  "<a class='decline' href='process/acceptTransfer.php?id=n&bowler={$rowData['bowlerid']}&tab={$rowData['id']}'><i class='fas fa-times'></i></a>";
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
    header('Content-Disposition: attachment; filename="bowlers-transfer.csv"');
    $output = fopen("php://output", "w");
    $headers = ['Requested By', 'Bowler', 'Bowler Id','From','To','Date Time'];
    fputcsv($output, $headers);
    foreach ($data as $row) {
        $rowRaw = [$row['requestedby'], $row['bowler'], $row['bowlerid'], $row['fromteam'],$row['toteam'],$row['claimtime']];
        fputcsv($output, $rowRaw);
    }
    fclose($output);
}


function exportExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'Requested By');
    $sheet->setCellValue('B1', 'Bowler');
    $sheet->setCellValue('C1', 'Bowler Id');
    $sheet->setCellValue('D1', 'From');
    $sheet->setCellValue('E1', 'To');
    $sheet->setCellValue('F1', 'Date Time');

    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $sheet->setCellValue('A' . $rowNum, $row['requestedby']);
        $sheet->setCellValue('B' . $rowNum, $row['bowler']);
        $sheet->setCellValue('C' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('D' . $rowNum, $row['fromteam']);
        $sheet->setCellValue('E' . $rowNum, $row['toteam']);
        $sheet->setCellValue('F' . $rowNum, $row['claimtime']);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="bowlers-transfer.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}


function exportPDF($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Set column headers
    $pdf->Cell(38, 10, 'Requested By', 1);
    $pdf->Cell(40, 10, 'Bpwler', 1);
    $pdf->Cell(30, 10, 'Bowler Id', 1);
    $pdf->Cell(40, 10, 'From', 1);
    $pdf->Cell(40, 10, 'To', 1);
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    $i=1;
    foreach ($data as $row) {
        $pdf->Cell(38, 10, $row['requestedby'], 1);
        $pdf->Cell(40, 10, $row['bowler'], 1);
        $pdf->Cell(30, 10, $row['bowlerid'], 1);
        $pdf->Cell(40, 10, $row['fromteam'], 1);
        $pdf->Cell(40, 10, $row['toteam'], 1);
        $pdf->Ln();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="bowlers.pdf"');
    $pdf->Output('I', 'bowlers.pdf');
}

?>