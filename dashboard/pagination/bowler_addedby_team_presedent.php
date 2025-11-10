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
        2 => 'name',
        3 => 'team',
        4 => 'nickname1',
        5 => 'sanction',
        6 => 'create_at',

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
    $searchQuery = " AND (bowlerid LIKE :searchValue OR  name LIKE :searchValue OR team LIKE :searchValue OR 
            nickname1 LIKE :searchValue OR sanction LIKE :searchValue OR create_at LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

$nonactive = 0;

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
    $row['name']     = $singleScoreData['name'] ?? '-';
    $row['teamName'] = $singleScoreData['team'] ?? '-';
    $row['nickname'] = $singleScoreData['nickname1'] ?? '-';
    $row['sanction'] = $singleScoreData['sanction'] ?? '-';
    $row['createAt'] = $singleScoreData['create_at'] ?? '-';
    $row['approve'] =  "<a style='cursor: pointer;' class='approve' onclick='showConfirmationAddBowler(`add`,`".$singleScoreData['bowlerid']."`)'><i class='fas fa-check'></i></a>";
    $row['decline'] =  "<a class='decline' href='process/activateBowler.php?id=n&bowler={$singleScoreData['bowlerid']}'><i class='fas fa-times'></i></a>";
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
    header('Content-Disposition: attachment; filename="bowlers.csv"');
    $output = fopen("php://output", "w");
    $headers = ['UBA ID', 'Name', 'Team','Nickname','Senction','Create At'];
    fputcsv($output, $headers);
    foreach ($data as $row) {
        $rowRaw = [$row['bowlerid'], $row['name'], $row['team'], $row['nickname1'],$row['sanction'],$row['create_at']];
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
    $sheet->setCellValue('E1', 'Nickname');
    $sheet->setCellValue('F1', 'Senction');
    $sheet->setCellValue('G1', 'Create At');

    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $sheet->setCellValue('A' . $rowNum, $i++);
        $sheet->setCellValue('B' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('C' . $rowNum, $row['name']);
        $sheet->setCellValue('D' . $rowNum, $row['team']);
        $sheet->setCellValue('E' . $rowNum, $row['nickname1']);
        $sheet->setCellValue('F' . $rowNum, $row['sanction']);
        $sheet->setCellValue('G' . $rowNum, $row['create_at']);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="bowlers.xlsx"');
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
    $pdf->Cell(30, 10, 'Nickname', 1);
    $pdf->Cell(28, 10, 'Senction', 1);
    $pdf->Cell(25, 10, 'Create At', 1);
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    $i=1;
    foreach ($data as $row) {
        $pdf->Cell(25, 10, $row['bowlerid'], 1);
        $pdf->Cell(40, 10, $row['name'], 1);
        $pdf->Cell(40, 10, $row['team'], 1);
        $pdf->Cell(30, 10, $row['nickname1'], 1);
        $pdf->Cell(28, 10, $row['sanction'], 1);
        $pdf->Cell(25, 10, $row['create_at'], 1);
        $pdf->Ln();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="bowlers.pdf"');
    $pdf->Output('I', 'bowlers.pdf');
}

?>