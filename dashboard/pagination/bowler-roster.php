<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';
require_once('../phpspreadsheet/vendor/autoload.php');
require_once('../../fpdf/fpdf.php'); 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$database = new Connection();
$db = $database->openConnection();

$limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0; 
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

if(isset($_POST['searchValue'])){
    $searchValue = isset($_POST['searchValue']) ? $_POST['searchValue'] : '';
}else{
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';
}

try {
    $database = new Connection();
    $db = $database->openConnection();

    $searchQuery = "";

    if (isset($_POST['teamSelected'])) {
        $columns = [
            0 => 'id',
            1 => 'bowlerid',
            2 => 'name',
            3 => 'nickname1',
            4 => 'officeheld',
            5 => 'sanction',
            6 => 'enteringAvg',
            7 => 'ubaAvg',
        ];
        
        if (!empty($searchValue)) {
            $searchValue = "%$searchValue%";
            $searchQuery = " AND (bowlerid LIKE :searchValue OR  name LIKE :searchValue OR nickname1 LIKE :searchValue OR 
            officeheld LIKE :searchValue OR sanction LIKE :searchValue OR enteringAvg LIKE :searchValue OR ubaAvg LIKE :searchValue)";
        }
        $orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

        $teamName = $_POST['teamSelected'];
        if(isset($_POST['exportType'])){
            $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `team` = :teamName AND `active` > 0 $searchQuery ORDER BY $orderColumn $orderDir");
            $sql->bindParam(':teamName', $teamName, PDO::PARAM_STR);
                if (!empty($searchValue)) {
                    $sql->bindParam(':searchValue', $searchValue);
                }
            $sql->execute();
            $teamDeets = $sql->fetchAll();
        }else{
            $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `team` = :teamName AND `active` > 0 $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit");
            $sql->bindParam(':teamName', $teamName, PDO::PARAM_STR);
            $sql->bindParam(':start', $start, PDO::PARAM_INT);
            $sql->bindParam(':limit', $limit, PDO::PARAM_INT);
                if (!empty($searchValue)) {
                    $sql->bindParam(':searchValue', $searchValue);
                }
            $sql->execute();
            $teamDeets = $sql->fetchAll();
        }

        // get total count
        $teamSelectedcount = $db->prepare("SELECT COUNT(*) as count FROM bowlers WHERE `team` = :teamName AND `active` > 0 $searchQuery");
        $teamSelectedcount->bindParam(':teamName', $teamName, PDO::PARAM_STR);
            if (!empty($searchValue)) {
            $teamSelectedcount->bindParam(':searchValue', $searchValue);
            }
        $teamSelectedcount->execute();
        $totalRecords = $teamSelectedcount->fetchColumn();

        if (isset($_POST['exportType'])) {
            $exportType = $_POST['exportType'];
            switch ($exportType) {
                case 'csv':
                    exportCSV($teamDeets,'team-roster',$teamName);
                    break;
                case 'excel':
                    exportExcel($teamDeets,'team-roster',$teamName);
                    break;
                case 'pdf':
                    exportPDF($teamDeets,'team-roster',$teamName);
                    break;
            }
            exit();
        }
    }
    
    if (isset($_POST['divisionSelected'])) {
        $columns = [
            0 => 'id',
            1 => 'bowlerid',
            2 => 'name',
            3 => 'nickname1',
            4 => 'team',
            5 => 'sanction',
            6 => 'enteringAvg',
            7 => 'ubaAvg',
        ];
        if (!empty($searchValue)) {
            $searchValue = "%$searchValue%";
            $searchQuery = " AND (bowlerid LIKE :searchValue OR name LIKE :searchValue OR nickname1 LIKE :searchValue OR 
                        team LIKE :searchValue OR sanction LIKE :searchValue OR enteringAvg LIKE :searchValue OR 
                        ubaAvg LIKE :searchValue)";
        }
        $orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';
        // $column = 'team.'.$orderColumn;
        $divisionSelected = $_POST['divisionSelected'];
        if(isset($_POST['exportType'])){
            $sql = $db->prepare(" SELECT bowlers.* FROM `teams` INNER JOIN `bowlers` ON bowlers.team = teams.teamname WHERE 
            teams.division = :divisionSelected AND bowlers.active > 0 $searchQuery  ORDER BY $orderColumn $orderDir");
        }else{
            $sql = $db->prepare(" SELECT bowlers.* FROM `teams` INNER JOIN `bowlers` ON bowlers.team = teams.teamname WHERE 
            teams.division = :divisionSelected AND bowlers.active > 0 $searchQuery  ORDER BY $orderColumn $orderDir LIMIT :start, :limit ");
            //     echo " SELECT bowlers.* FROM `teams` INNER JOIN `bowlers` ON bowlers.team = teams.teamname WHERE 
            // teams.division = :divisionSelected AND bowlers.active > 0 $searchQuery  ORDER BY $column $orderDir LIMIT :start, :limit ";
            //     die();
            $sql->bindParam(':start', $start, PDO::PARAM_INT);
            $sql->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
            $sql->bindParam(':divisionSelected', $divisionSelected, PDO::PARAM_STR);
            
            if (!empty($searchValue)) {
                $sql->bindParam(':searchValue', $searchValue);
            }
            $sql->execute();
            $allBowlers = $sql->fetchAll();

            // get count
            $countQuery = $db->prepare("SELECT COUNT(*) as count FROM `teams` INNER JOIN `bowlers` ON bowlers.team = teams.teamname
                WHERE teams.division = :divisionSelected AND bowlers.active > 0 $searchQuery ");
            $countQuery->bindParam(':divisionSelected', $divisionSelected, PDO::PARAM_STR);
                if (!empty($searchValue)) {
                    $countQuery->bindParam(':searchValue', $searchValue, PDO::PARAM_STR);
                }
            $countQuery->execute();
            $totalRecords = $countQuery->fetchColumn();

        if (isset($_POST['exportType'])) {
            $exportType = $_POST['exportType'];
            switch ($exportType) {
                case 'csv':
                    exportCSV($allBowlers,'division-roster',$divisionSelected);
                    break;
                case 'excel':
                    exportExcel($allBowlers, 'division-roster',$divisionSelected);
                    break;
                case 'pdf':
                    exportPDF($allBowlers, 'division-roster',$divisionSelected);
                    break;
            }
            exit();
        }

    }

} catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

if (isset($_POST['teamSelected']) || isset($_SESSION['rosterSelected'])) {
    $data = [];
    $i = $start + 1;
    
    foreach ($teamDeets as $bowlers) {
        $seasontourAvg = getSeasonTourAvg($bowlers['bowlerid']);
        $row['no'] = $i++;  // Key should match the 'data' property in JS
        $row['bowlerid'] = $bowlers['bowlerid']?? 0;
        $row['name'] = htmlspecialchars($bowlers['name']) ?? 0;
        $row['nickname1'] = htmlspecialchars($bowlers['nickname1'], ENT_QUOTES) ?? 0;
        $row['officeheld'] = $bowlers['officeheld']?? 0;
        $row['sanction'] = $bowlers['sanction']?? 0;
        $row['enteringAvg'] = $bowlers['enteringAvg']?? 0;
        $row['ubaAvg'] = $bowlers['ubaAvg']?? 0;
        $row['ubaAvgseasontourAvg'] = $seasontourAvg ?? 0;
            if ($_SESSION['userrole'] == 'admin') {
                // $row['reinstate'] = "<a href='editBowler.php?id={$bowlers['id']}'><i class='fas fa-pen-square'></i></a>";
                $row['reinstate'] = "<a href='editBowler.php?id={$bowlers['id']}'><i class='fas fa-pen-square'></i></a>";
            }
        $data[] = $row;
    }  
}



if (isset($_POST['divisionSelected']) || isset($_SESSION['divisionSelected'])) {
    $data = [];
    $i = $start + 1;
    foreach ($allBowlers as $bowlers) {
        // $bowlerID = $bowlers['bowlerid'];
        $seasontourAvg = getSeasonTourAvg($bowlers['bowlerid']);

        $row['no'] = $i++;  
        $row['bowlerid'] = $bowlers['bowlerid']?? 0;
        $row['name'] = $bowlers['name']?? 0;
        $row['nickname1'] = $bowlers['nickname1']?? 0;
        $row['team'] = $bowlers['team']?? 0;
        $row['sanction'] = $bowlers['sanction']?? 0;
        $row['enteringAvg'] = $bowlers['enteringAvg']?? 0;
        $row['ubaAvg'] = $bowlers['ubaAvg']?? 0;
        $row['ubaAvgseasontourAvg'] = $seasontourAvg ?? 0;
            if ($_SESSION['userrole'] == 'admin') {
                $row['reinstate'] = "<a href='editBowler.php?id={$bowlers['id']}'><i class='fas fa-pen-square'></i></a>";
            }
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


//  teamSelected 
function exportCSV($data, $type,$name) {
     $fileName = $type.'('.$name.')';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$fileName.'.csv"');
    
    $output = fopen("php://output", "w");
    $headers = $type === 'division-roster' 
        ? ['UBA ID', 'Name', 'Nickname', 'Team', 'Sanction #', 'Entering Avg', 'UBA Avg', 'ST Avg']
        : ['UBA ID', 'Name', 'Nickname', 'Office Held', 'Sanction #', 'Entering Avg', 'UBA Avg', 'ST Avg'];

    fputcsv($output, $headers);
    foreach ($data as $row) {
        $getSeasonTourAvg = getSeasonTourAvg($row['bowlerid']);
        $rowRaw = $type === 'division-roster' 
            ? [$row['bowlerid'], $row['name'], $row['nickname1'], $row['team'], $row['sanction'], $row['enteringAvg'], $row['ubaAvg'], $getSeasonTourAvg]
            : [$row['bowlerid'], $row['name'], $row['nickname1'], $row['officeheld'], $row['sanction'], $row['enteringAvg'], $row['ubaAvg'], $getSeasonTourAvg];
        
        fputcsv($output, $rowRaw);
    }
    fclose($output);
}


function exportExcel($data,$type,$name){
    $fileName = $type.'('.$name.')';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set column headers
    $sheet->setCellValue('A1', 'UBA ID');
    $sheet->setCellValue('B1', 'Name');
    $sheet->setCellValue('C1', 'Nickname');
    if($type === 'division-roster'){
        $sheet->setCellValue('D1', 'Team');
    }else{
        $sheet->setCellValue('D1', 'Office Held');
    }
    $sheet->setCellValue('E1', 'Sanction #');
    $sheet->setCellValue('F1', 'Entering Avg');
    $sheet->setCellValue('G1', 'UBA Avg');
    $sheet->setCellValue('H1', 'ST Avg');

    $rowNum = 2;
    foreach ($data as $row) {
        $getSeasonTourAvg = getSeasonTourAvg($row['bowlerid']);
        $sheet->setCellValue('A' . $rowNum, $row['bowlerid']);
        $sheet->setCellValue('B' . $rowNum, $row['name']);
        $sheet->setCellValue('C' . $rowNum, $row['nickname1']);
        if($type === 'division-roster'){
            $sheet->setCellValue('D' . $rowNum, $row['team']);
        }else{
            $sheet->setCellValue('D' . $rowNum, $row['officeheld']);
        }
        $sheet->setCellValue('E' . $rowNum, $row['sanction']);
        $sheet->setCellValue('F' . $rowNum, $row['enteringAvg']);
        $sheet->setCellValue('G' . $rowNum, $row['ubaAvg']);
        $sheet->setCellValue('H' . $rowNum, $getSeasonTourAvg);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$fileName.'.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

function exportPDF($data,$type,$name){
    $fileName = $type.'('.$name.')';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Define fixed column widths
    // $columnWidths = array(
    //     'UBA ID' => 25,
    //     'Name' => 45,
    //     // 'Nickname' => 30,
    //     if($type === 'division-roster'){
    //     'Team' => 25,
    //     }else{
    //     'Office Held' => 25,
    //     }
    //     'Sanction #' => 40,
    //     'Entering Avg' => 15,
    //     'UBA Avg' => 15,
    //     'ST Avg' => 15
    // );
    $columnWidths['UBA ID'] = 25;
    $columnWidths['Name'] = 45;
    if ($type === 'division-roster') {
        $columnWidths['Team'] = 25;
    } else {
        $columnWidths['Office Held'] = 25;
    }
    $columnWidths['Sanction #'] = 40;
    $columnWidths['Entering Avg'] = 15;
    $columnWidths['UBA Avg'] = 15;
    $columnWidths['ST Avg'] = 15;

    // Set the column headers
    foreach ($columnWidths as $header => $width) {
        $pdf->Cell($width, 10, $header, 1);
    }
    $pdf->Ln();

    // Set font for data rows
    $pdf->SetFont('Arial', '', 12);

    // Function to wrap text within the cell width
    function wrapText($pdf, $text, $width) {
        $wrappedText = '';
        $words = explode(' ', $text);
        
        foreach ($words as $word) {
            $testLine = $wrappedText . $word . ' ';
            if ($pdf->GetStringWidth($testLine) > $width) {
                $wrappedText .= "\n" . $word . ' ';
            } else {
                $wrappedText .= $word . ' ';
            }
        }
        return trim($wrappedText);
    }

    // Add data rows
    foreach ($data as $row) {
        $getSeasonTourAvg = getSeasonTourAvg($row['bowlerid']);
        // $rowData = array(
        //     'UBA ID' => $row['bowlerid'],
        //     'Name' => $row['name'],
        //     // 'Nickname' => $row['nickname1'],
        //     'Office Held' => $row['officeheld'],
        //     'Sanction #' => $row['sanction'],
        //     'Entering Avg' => $row['enteringAvg'],
        //     'UBA Avg' => $row['ubaAvg'],
        //     'ST Avg' => $getSeasonTourAvg
        // );

        $rowData['UBA ID'] = $row['bowlerid'];
        $rowData['Name'] = $row['name'];
        if ($type === 'division-roster') {
        $rowData['Team'] = $row['team'];
        } else {
        $rowData['Office Held'] = $row['officeheld'];
        }
        $rowData['Sanction #'] = $row['sanction'];
        $rowData['Entering Avg'] = $row['enteringAvg'];
        $rowData['UBA Avg'] = $row['ubaAvg'];
        $rowData['ST Avg'] = $getSeasonTourAvg;

        // Determine the maximum height of the row
        $maxHeight = 10;
        foreach ($rowData as $key => $text) {
            $wrappedText = wrapText($pdf, $text, $columnWidths[$key]);
            $lineCount = substr_count($wrappedText, "\n") + 1;
            $maxHeight = max($maxHeight, $lineCount * 10);
        }
        // Print each cell in the row
        foreach ($rowData as $key => $text) {
            // Save the current position
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Draw the cell with wrapped text
            $wrappedText = wrapText($pdf, $text, $columnWidths[$key]);
            $pdf->MultiCell($columnWidths[$key], 10, $wrappedText, 1);

            // Move the cursor to the right of the cell
            $pdf->SetXY($x + $columnWidths[$key], $y);
        }
        
        // Move the cursor to the next line after the row
        $pdf->Ln($maxHeight);
    }

    // Set the headers before outputting the PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$fileName.'.pdf"');

    // Output the PDF
    $pdf->Output('I', 'bowler-roster.pdf');
}


//  Division Selected export section
// function divisionSelectedexportCSV($data){
//     header('Content-Type: text/csv');
//     header('Content-Disposition: attachment; filename="bowler-roster.csv"');
//     $output = fopen("php://output", "w");
//     fputcsv($output, array('UBA ID', 'Name', 'Nickname','Team','Sanction #','Entering Avg','UBA Avg','ST Avg'));
//     foreach ($data as $row) {
//         $getSeasonTourAvg = getSeasonTourAvg($row['bowlerid']);
//         $rowRaw = [$row['bowlerid'],$row['name'],$row['nickname1'],$row['team'],$row['sanction'],$row['enteringAvg'],$row['ubaAvg'],$getSeasonTourAvg];
//         fputcsv($output, $rowRaw);
//     }
//     fclose($output);
// }


function getSeasonTourAvg($bowlerID){
    // $bowlerID = $bowlers['bowlerid'];
    $database = new Connection();
    $db = $database->openConnection();
    $currentYear = date("Y");
    $preYear = date("Y", strtotime("-1 year"));
    $year = substr($currentYear, -2);
    $avrgseason = $db->prepare("SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerID' AND YEAR(eventdate) BETWEEN '2024' AND '2025' AND year='2024/25' ORDER BY `eventdate` DESC ");
    $avrgseason->execute();
    $avrgseasonAll = $avrgseason->fetchAll();

    $arrayCount = array();
    $gamelenth = array();
    $game1 = 0;
    $game2 = 0;
    $game3 = 0;
    $addAll = 0;
    $avrgss = 0;
        foreach ($avrgseasonAll as $seasonVal) {
            $checkDate = new DateTime($seasonVal['eventdate']);
            $checkDateFormatted = $checkDate->format('Y-m-d');

            $sectValue1 = new DateTime("2024-09-01");
            $sectValue1Formatted = $sectValue1->format('Y-m-d');

            $sectValue2 = new DateTime("2025-09-01");
            $sectValue2Formatted = $sectValue2->format('Y-m-d');

            if ($checkDateFormatted <= $sectValue2Formatted && $checkDateFormatted >= $sectValue1Formatted) {
                if ($seasonVal['game1'] > 1) {
                    $game1 = $game1 + $seasonVal['game1'];
                    array_push($gamelenth, $seasonVal['game1']);
                }
                if ($seasonVal['game2'] > 1) {
                    $game2 = $game2 + $seasonVal['game2'];
                    array_push($gamelenth, $seasonVal['game2']);
                }

                if ($seasonVal['game3'] > 1) {
                    $game3 = $game3 + $seasonVal['game3'];
                    array_push($gamelenth, $seasonVal['game3']);
                }
                array_push($arrayCount, '1');
                if (sizeof($gamelenth) >= 9) {
                    $addAll = $game1 + $game2 + $game3;
                    $avrgss = $addAll / sizeof($gamelenth);
                } else {
                    $addAll = 0;
                    $avrgss = 0.00;
                }
            }
        }
    $seasontourAvg = number_format($avrgss, 2);
    return $seasontourAvg;
}



?>