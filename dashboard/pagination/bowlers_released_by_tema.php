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
        1 => 'bowlerid',
        2 => 'bowler',
        3 => 'team',
        4 => 'currentstatus',
        5 => 'datesubmitted',
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
    $searchQuery = " AND (bowlerid LIKE :searchValue OR  bowler LIKE :searchValue OR team LIKE :searchValue OR 
            currentstatus LIKE :searchValue OR datesubmitted LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

$approved = 1;
$releaseStatus = 0;
// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM `bowlersreleased` WHERE `approved` = :approved AND `release_status` = :release_status AND `removedby` != 'Admin' $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
$stmt->bindParam(':release_status', $releaseStatus, PDO::PARAM_INT);
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

if(isset($_GET['exportType'])){
    $sql = "SELECT * FROM `bowlersreleased` WHERE `approved` = :approved AND `release_status` = :release_status AND `removedby` != 'Admin' $searchQuery ORDER BY $orderColumn $orderDir";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
    $stmt->bindParam(':release_status', $releaseStatus, PDO::PARAM_INT);
    $stmt->execute();
    $dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

}else{
 $sql = "SELECT * FROM `bowlersreleased` WHERE `approved` = :approved AND `release_status` = :release_status AND `removedby` != 'Admin' $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
    $stmt = $db->prepare($sql);
    if (!empty($searchValue)) {
        $stmt->bindParam(':searchValue', $searchValue);
    }
    $stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
    $stmt->bindParam(':release_status', $releaseStatus, PDO::PARAM_INT);
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
    $row['bowler_id'] = $rowData['bowlerid'] ?? '-';
    $row['name']     = $rowData['bowler'] ?? '-';
    $row['released_from'] = $rowData['team'] ?? '-';
    $row['status'] = $rowData['currentstatus'] ?? '-';
    $row['date'] = $rowData['datesubmitted'] ?? '-';
    $row['close'] =  "<a style='cursor: pointer;' onclick='approveRelease('".$rowData['bowlerid']."')'><i class='fas fa-times decline'></i></a>";
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
    header('Content-Disposition: attachment; filename="bowlers-released.csv"');
    $output = fopen("php://output", "w");
    $headers = ['Bowler Id', 'Name', 'Released From','Status','Date'];
    fputcsv($output, $headers);
    foreach ($data as $row) {
        $rowRaw = [$row['bowlerid'], $row['bowler'], $row['team'], $row['currentstatus'],$row['datesubmitted']];
        fputcsv($output, $rowRaw);
    }
    fclose($output);
}

function exportExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'Bowler ID');
    $sheet->setCellValue('C1', 'Name');
    $sheet->setCellValue('D1', 'Released From');
    $sheet->setCellValue('E1', 'Status');
    $sheet->setCellValue('F1', 'Date');

    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $sheet->setCellValue('A' . $rowNum, $i++);
        $sheet->setCellValue('B' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('C' . $rowNum, $row['bowler']);
        $sheet->setCellValue('D' . $rowNum, $row['team']);
        $sheet->setCellValue('E' . $rowNum, $row['currentstatus']);
        $sheet->setCellValue('F' . $rowNum, $row['datesubmitted']);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="bowlers-released.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

function exportPDF($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Set column headers
    $pdf->Cell(25, 10, 'Bowler ID', 1);
    $pdf->Cell(40, 10, 'Team', 1);
    $pdf->Cell(40, 10, 'Released From', 1);
    $pdf->Cell(30, 10, 'Status', 1);
    $pdf->Cell(28, 10, 'Date', 1);
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    $i=1;
    foreach ($data as $row) {
        $pdf->Cell(25, 10, $row['bowlerid'], 1);
        $pdf->Cell(40, 10, $row['bowler'], 1);
        $pdf->Cell(40, 10, $row['team'], 1);
        $pdf->Cell(30, 10, $row['currentstatus'], 1);
        $pdf->Cell(28, 10, $row['datesubmitted'], 1);
        $pdf->Ln();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="bowlers.pdf"');
    $pdf->Output('I', 'bowlers.pdf');
}

?>