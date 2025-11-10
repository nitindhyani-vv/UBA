<?php
	include_once '../baseurl.php';
    include_once '../session.php';
    include_once '../connect.php';

    if($_SESSION['userrole'] == 'president' || $_SESSION['userrole'] == 'bowler' || $_SESSION['userrole'] == 'owner'  || $_SESSION['userrole'] == 'secretary'){
        header("Location: ".$base_url."/dashboard/home.php");
    }

    require_once('phpspreadsheet/vendor/autoload.php');

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\IOFactory;

    try {
        $database = new Connection();
        $db = $database->openConnection();
        
        $sqll = $db->prepare("SELECT division FROM `districtcodes`");
        $sqll->execute();
        $division = $sqll->fetchAll();
        
    } catch (PDOException $e) {
        echo "There was some problem with the connection: " . $e->getMessage();
    }

  
    if (isset($_POST['submit'])) {
        try {
            $database = new Connection();
            $db = $database->openConnection();
            $division = $_POST['district_division'];
    
            // Fetch all bowlers and their teams in one query
            $sql = $db->prepare("SELECT teams.teamname, bowlers.bowlerid, teams.division, bowlers.team, bowlers.name, 
                                bowlers.enteringAvg, bowlers.sanction, bowlers.ubaAvg, bowlers.seasontourAvg
                                  FROM teams 
                                  INNER JOIN bowlers ON teams.teamname = bowlers.team  
                                  WHERE teams.division = ?
                                  ORDER BY teams.teamname ASC ");
            $sql->execute([$division]);
            $allTeams = $sql->fetchAll(PDO::FETCH_ASSOC);
    
            // Fetch all bowler data in one query
            $bowlerIds = array_column($allTeams, 'bowlerid');
            $dataFetched = [];
    
            if (!empty($bowlerIds)) {
                $placeholders = implode(',', array_fill(0, count($bowlerIds), '?'));
                $bow = $db->prepare("SELECT * FROM bowlerdataseason WHERE bowlerid IN ($placeholders)");
                $bow->execute($bowlerIds);
                $dataFetched = $bow->fetchAll(PDO::FETCH_ASSOC);
            }
    
            // Process bowler data in memory
            $bowlerStats = [];
            foreach ($dataFetched as $bowler) {
                $bowlerId = $bowler['bowlerid'];
                $eventDate = strtotime($bowler['eventdate']);
                $start = strtotime('2025-09-01');
                $end = strtotime('2026-09-01');
    
                if ($eventDate >= $start && $eventDate <= $end) {
                    if (!isset($bowlerStats[$bowlerId])) {
                        $bowlerStats[$bowlerId] = ['game1' => 0, 'game2' => 0, 'game3' => 0, 'count' => 0];
                    }
                    
                    foreach (['game1', 'game2', 'game3'] as $game) {
                        if ($bowler[$game] > 1) {
                            $bowlerStats[$bowlerId][$game] += $bowler[$game];
                            $bowlerStats[$bowlerId]['count']++;
                        }
                    }
                }
            }
    
            // Create a new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            $spreadsheet->getProperties()
                ->setTitle('UBA Score Entry Document')
                ->setDescription('Format Sheet for UBA score entry')
                ->setCreator('UBA System');
    
            // Set header row
            $headers = ['UBA ID', 'Team', 'Name', '', 'Entering Avg', 'UBA Avg', 'Season Tour Avg', 'Division'];
            $sheet->fromArray($headers, null, 'A1');
    
            // Populate the sheet with data
            $row = 2;
            foreach ($allTeams as $team) {
                $bowlerId = $team['bowlerid'];
                $avg = isset($bowlerStats[$bowlerId]) && $bowlerStats[$bowlerId]['count'] >= 9
                    ? number_format(array_sum($bowlerStats[$bowlerId]) / $bowlerStats[$bowlerId]['count'], 2)
                    : '0.00';
    
                $data = [
                    $bowlerId,
                    $team['team'],
                    $team['name'],
                    '',
                    $team['enteringAvg'],
                    $team['ubaAvg'],
                    $avg,
                    $team['division']
                ];
                $sheet->fromArray($data, null, "A$row");
                $row++;
            }
    
            // Generate file
            $filename = "UBA_Bowler_Data_{$division}.xlsx";
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save($filename);
    
            // Serve file as download
            if (file_exists($filename)) {
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header("Content-Disposition: attachment; filename=\"$filename\"");
                header("Cache-Control: max-age=0");
                ob_clean();
                flush();
                readfile($filename);
                unlink($filename);
            }
    
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    
    // download function
    if (isset($_POST['submitAllData'])) {
        try {
            $database = new Connection();
            $db = $database->openConnection();
            
    
            // Fetch all bowlers and their teams in one query
            $sql = $db->prepare("SELECT * FROM BowlerTeamView ORDER BY teamname ASC");
            $sql->execute();
            $allTeams = $sql->fetchAll(PDO::FETCH_ASSOC);
            // print_r($allTeams);
            // die;
            // Fetch all bowler data in one query
            $bowlerIds = array_column($allTeams, 'bowlerid');
            if (!empty($bowlerIds)) {
                $placeholders = implode(',', array_fill(0, count($bowlerIds), '?'));
                $bow = $db->prepare("SELECT * FROM bowlerdataseason WHERE bowlerid IN ($placeholders)");
                $bow->execute($bowlerIds);
                $dataFetched = $bow->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $dataFetched = [];
            }
    
            // Process bowler data in memory
            $bowlerStats = [];
            foreach ($dataFetched as $bowler) {
                $bowlerId = $bowler['bowlerid'];
                $eventDate = strtotime($bowler['eventdate']);
                $sectValue1 = strtotime('2025-09-01');
                $sectValue2 = strtotime('2026-09-01');
                
                if ($eventDate >= $sectValue1 && $eventDate <= $sectValue2) {
                    if (!isset($bowlerStats[$bowlerId])) {
                        $bowlerStats[$bowlerId] = ['game1' => 0, 'game2' => 0, 'game3' => 0, 'games' => []];
                    }
                    
                    if ($bowler['game1'] > 1) {
                        $bowlerStats[$bowlerId]['game1'] += $bowler['game1'];
                        $bowlerStats[$bowlerId]['games'][] = $bowler['game1'];
                    }
                    if ($bowler['game2'] > 1) {
                        $bowlerStats[$bowlerId]['game2'] += $bowler['game2'];
                        $bowlerStats[$bowlerId]['games'][] = $bowler['game2'];
                    }
                    if ($bowler['game3'] > 1) {
                        $bowlerStats[$bowlerId]['game3'] += $bowler['game3'];
                        $bowlerStats[$bowlerId]['games'][] = $bowler['game3'];
                    }
                }
            }
    
            // Create Excel spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getProperties()
                ->setTitle('UBA Score Entry Document')
                ->setSubject('UBA Score Entry Format Sheet')
                ->setDescription('Format Sheet for UBA score entry')
                ->setCreator('UBA System');
    
            // Set headers
            $sheet->fromArray([
                ['UBA ID', 'Team', 'Name',  'Entering Avg', 'UBA Avg', 'Season Tour Avg', 'Division']
            ], null, 'A1');
    
            // Insert data
            $row = 2;
            foreach ($allTeams as $team) {
                $bowlerId = $team['bowlerid'];
                $divisionnn = $team['division'];
                
                $games = $bowlerStats[$bowlerId]['games'] ?? [];
                $totalGames = count($games);
                $avrgss = ($totalGames >= 9) ? array_sum($games) / $totalGames : 0.00;
                
                $sheet->fromArray([
                    [$bowlerId, $team['team'], $team['name'],  $team['enteringAvg'], $team['ubaAvg'], number_format($avrgss, 2), $divisionnn]
                ], null, 'A' . $row);
                $row++;
            }
    
            // Save and download
            $filename = "UBAAllBowlersData.xlsx";
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save($filename);
    
            if (file_exists($filename)) {
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header("Content-Disposition: attachment; filename=\"$filename\"");
                header("Cache-Control: max-age=0");
                ob_clean();
                flush();
                readfile($filename);
                exit;
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }








    
    $title = 'Bowler Data';

    include 'inc/header.php';

?>
<style>
	.loader{
			width: 55px;
			height: 51px;
        }
</style>
<div class="users">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h4>Download Bowler Data</h4>

                <div class="d-flex justify-content-between align-items-center">
                    <!-- Form for Download Bowler Data -->
                    <form action="" method="post" name="bowler_form" enctype="multipart/form-data" onsubmit="return export_bowler()">
                        <div class="form-group">
                            <label for="eventSelected">Select District</label>
                            <select class="form-control" name="district_division" id="district_division" style="width: 250px;">
                                <option value="">Select District</option>
                                <?php foreach ($division as $eventval) { ?>
                                    <option value="<?= $eventval['division']; ?>"><?= $eventval['division']; ?></option>
                                <?php } ?>
                            </select>
                            <span style="color:#b30505;" id="district_divisionerror"></span>
                        </div>

                        <div class="form-group mt-2">
                            <input type="submit" value="Download Bowler Data" name="submit" class="btn btn-primary">
                        </div>
                    </form>

                    <!-- Form for Download AllData (aligned to right) -->
                    <form action="" method="post" name="bowler_form_all" enctype="multipart/form-data">
                        <div class="form-group text-end">
                            <p class="fw-bold mb-1"><h4>Download All Bowler Data</h4></p>
                            <input type="submit" value="Download AllData" name="submitAllData" class="btn btn-dark">
                        </div>
                    </form>
                </div>

                <div class="text-center content mt-3" style="display:none;">
                    Please wait. File is getting ready to download. It may take a minute or more.
                </div>
            </div>
        </div>
    </div>
</div>


<script>
var interval;
	function export_bowler(){
		
		var district_division = document.forms["bowler_form"]["district_division"];
			// alert('district_division',district_division);
		
			if (district_division.value === "") { 
                 document.getElementById('district_divisionerror').innerHTML = 'Please select a value.';
                	district_division.focus();
                $('#district_division').addClass('alertclass');
                return false; 
                $('.content').hide();
            }
            else{
            	$('.content').show();
            	 interval = setInterval(doStuff, 15000); 
                $('#district_division').removeClass('alertclass');
                document.getElementById('district_divisionerror').style.display = "none";
            }
            
        
            
	}
	
	 
function doStuff() {
	$('.content').hide();
  setTimeout(interval, 1000);
}
</script>
<?php

include 'inc/footer.php';

?>