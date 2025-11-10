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
    2 => 'name',
    3 => 'team',
    4 => 'president',
    5 => 'enteringAvg',
    6 => 'sanction',
    7 => 'verified',
    9 => 'verified',
    10 => 'verified',
];

    $searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    $orderIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';

// Building the search query
$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (bowlerid LIKE :searchValue OR team LIKE :searchValue OR name LIKE :searchValue OR 
    verified LIKE :searchValue OR sanction LIKE :searchValue)";
}
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bowlers WHERE `uemail` != '' $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

    if(isset($_GET['exportType'])){
        $sql = "SELECT * FROM `bowlers` WHERE `uemail` != '' $searchQuery ORDER BY $orderColumn $orderDir";
        $stmt = $db->prepare($sql);
        if (!empty($searchValue)) {
            $stmt->bindParam(':searchValue', $searchValue);
        }
        $stmt->execute();
        $bowlerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }else{
        $sql = "SELECT * FROM `bowlers` WHERE `uemail` != '' $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
        $stmt = $db->prepare($sql);
        if (!empty($searchValue)) {
            $stmt->bindParam(':searchValue', $searchValue);
        }
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $bowlerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (isset($_GET['exportType'])) {
        $exportType = $_GET['exportType'];
        switch ($exportType) {
            case 'csv':
                exportCSV($bowlerData);
                break;
            case 'excel':
                exportExcel($bowlerData);
                break;
            case 'pdf':
                exportPDF($bowlerData);
                break;
        }
        exit();
    }


$data = [];
$i = $start + 1;
foreach ($bowlerData as $bowlers) {
    $president;
    $verified;
    $verifiedUser;
    $resendEmail;
    if($bowlers['president'] == 1){$president = 'Yes';} else {$president = 'No';} 
    if($bowlers['verified'] == 1){
        $verified = 'Yes';
        $verifiedUser = '--';
        $resendEmail = '--';
    } else {
        $verified = 'No';
        $verifiedUser = "<a href='verifyBowler.php?id={$bowlers['id']}'><i class='fas fa-check'></i></a>";;
        $resendEmail = "<a href='resendVerification.php?id={$bowlers['id']}'><i class='fas fa-envelope'></i></a>";;
    }
    

    $row['no'] = $i++; 
    $row['bowlerid'] = $bowlers['bowlerid'];
    $row['name'] = $bowlers['name'];
    $row['team'] = $bowlers['team'];
    $row['president'] = $president;
    $row['enteringAvg'] = $bowlers['enteringAvg'];
    $row['sanction'] = $bowlers['sanction'];
    $row['verified'] = $verified;
    $row['reinstate'] = "<a href='editBowler.php?id={$bowlers['id']}'><i class='fas fa-pen-square'></i></a>";
    $row['verifiedUser'] = $verifiedUser;
    $row['resendEmail'] = $resendEmail;
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
    $headers = ['UBA ID', 'Name', 'Team','President','Entering Avg','Sanction #','Verified'];
    fputcsv($output, $headers);

    foreach ($data as $row) {
        $president;
        $verified;
        if($row['president'] == 1){$president = 'Yes';} else {$president = 'No';} 
        if($row['verified'] == 1){
            $verified = 'Yes';
        } else {
            $verified = 'No';
        }
        $rowRaw = [$row['bowlerid'], $row['name'], $row['team'], $president,$row['enteringAvg'],$row['sanction'],$verified];
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
    $sheet->setCellValue('E1', 'President');
    $sheet->setCellValue('F1', 'Entering Avg');
    $sheet->setCellValue('G1', 'Sanction #');
    $sheet->setCellValue('H1', 'Verified');

    $rowNum = 2; $i = 1;
    foreach ($data as $row) {
        $president;
        $verified;
        if($row['president'] == 1){$president = 'Yes';} else {$president = 'No';} 
        if($row['verified'] == 1){
            $verified = 'Yes';
        } else {
            $verified = 'No';
        }

        $sheet->setCellValue('A' . $rowNum, $i++);
        $sheet->setCellValue('B' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('C' . $rowNum, $row['name']);
        $sheet->setCellValue('D' . $rowNum, $row['team']);
        $sheet->setCellValue('E' . $rowNum, $president);
        $sheet->setCellValue('F' . $rowNum, $row['enteringAvg']);
        $sheet->setCellValue('G' . $rowNum, $row['sanction']);
        $sheet->setCellValue('H' . $rowNum, $verified);
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

// Set initial column headers
$headers = [
    ['label' => 'UBA ID', 'width' => 20],
    ['label' => 'Name', 'width' => 5],
    ['label' => 'Team', 'width' => 20],
    ['label' => 'President', 'width' => 20],
    ['label' => 'Entering Avg', 'width' => 20],
    ['label' => 'Sanction #', 'width' => 20],
    ['label' => 'Verified', 'width' => 20],
];

// Calculate maximum widths
foreach ($data as $row) {
    $rowWidths = [
        $pdf->GetStringWidth($row['bowlerid']),
        $pdf->GetStringWidth($row['name']),
        $pdf->GetStringWidth($row['team']),
        $pdf->GetStringWidth($row['president'] == 1 ? 'Yes' : 'No'),
        $pdf->GetStringWidth($row['enteringAvg']),
        $pdf->GetStringWidth($row['sanction']),
        $pdf->GetStringWidth($row['verified'] == 1 ? 'Yes' : 'No'),
    ];

    foreach ($rowWidths as $index => $width) {
        if ($width > $headers[$index]['width']) {
            $headers[$index]['width'] = $width;
        }
    }
}

// Set column headers with adjusted widths
foreach ($headers as $header) {
    $pdf->Cell($header['width'], 6, $header['label'], 1);
}
$pdf->Ln();

// Add data rows
$pdf->SetFont('Arial', '', 12);
foreach ($data as $row) {
    $pdf->Cell($headers[0]['width'], 6, $row['bowlerid'], 1);
    $pdf->Cell($headers[1]['width'], 6, $row['name'], 1);
    $pdf->Cell($headers[2]['width'], 6, $row['team'], 1);
    $pdf->Cell($headers[3]['width'], 6, $row['president'] == 1 ? 'Yes' : 'No', 1);
    $pdf->Cell($headers[4]['width'], 6, $row['enteringAvg'], 1);
    $pdf->Cell($headers[5]['width'], 6, $row['sanction'], 1);
    $pdf->Cell($headers[6]['width'], 6, $row['verified'] == 1 ? 'Yes' : 'No', 1);
    $pdf->Ln();
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="team_officials.pdf"');
$pdf->Output('I', 'team_officials.pdf');

}

?>