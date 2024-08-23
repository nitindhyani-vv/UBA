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
$columns = [
    0 => 'id',
    1 => 'name',
    2 => 'userrole'
];
// Building the search query
$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = "%$searchValue%";
    $searchQuery = " AND (email LIKE :searchValue OR name LIKE :searchValue OR userrole LIKE :searchValue)";
}

$orderIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';
$orderColumn = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'id';

// Fetch total records count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE 1=1 $searchQuery");
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->execute();
$totalRecords = $stmt->fetchColumn();


$sql = "SELECT * FROM `users` WHERE 1=1 $searchQuery ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
$stmt = $db->prepare($sql);
if (!empty($searchValue)) {
    $stmt->bindParam(':searchValue', $searchValue);
}
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$dataFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);


$data = [];
$i = $start + 1;
foreach ($dataFetched as $user) {
    $row['no'] = $i++; 
    $row['name'] = $user['name'];
    $row['email'] = $user['email'];
    $row['role'] = ucfirst($user['userrole']);
        if ($_SESSION['userrole'] == 'admin') {
            $row['edit'] = "<a href='editUser.php?id={$user['id']}'><i class='fas fa-edit'></i></a>";
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