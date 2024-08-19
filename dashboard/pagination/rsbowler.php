<?php
include_once '../../baseurl.php';
include_once '../../session.php';
include_once '../../connect.php';

$limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0; 
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

$database = new Connection();
$db = $database->openConnection();

$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Building the search query
$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (bowlerid LIKE :searchValue OR 
                           bowler LIKE :searchValue OR 
                           team LIKE :searchValue OR 
                           datesubmitted LIKE :searchValue OR 
                           removedby LIKE :searchValue OR 
                           currentstatus LIKE :searchValue OR 
                           eligibledate LIKE :searchValue)";
}

// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bowlersreleased WHERE 1=1 $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->execute();
$totalRecords = $stmt->fetchColumn();

// Fetch paginated records
$sql = "SELECT * FROM bowlersreleased WHERE 1=1 $searchQuery ORDER BY id desc LIMIT :start, :limit";
$stmt = $db->prepare($sql);
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$bowlerDeets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
?>
