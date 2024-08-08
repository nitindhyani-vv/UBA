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
                // $sheetname = 'STATS';
                // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                // // $reader->setLoadSheetsOnly(["STATS", "Standings", "Total Pins", "Match Information", "Match1", "Match2", "Match3", "Match4", "Match5", "Match6", "recap1", "recap2", "recap3", "recap4", "recap5", "recap6", "All scores", "All scores-"]);
                // $reader->setLoadSheetsOnly("STATS");
                // $spreadsheet = $reader->load("formatsheets/BLANK RECAP FILE.xlsx");
                // $sheetData = $spreadsheet->getActiveSheet()->toArray();
				// "SELECT teamname.teamname,bowlers.bowlerid, bowlers.team, bowlers.name, bowlers.enteringAvg, bowlers.sanction, bowlers.ubaAvg, 
				// bowlers.seasontourAvg FROM teamname INNER JOIN bowlers ON teamname.teamname = bowlers.team"					
                try {
                    $database = new Connection();
                    $db = $database->openConnection();
                    $division = $_POST['district_division'];
                    
                    $sql = $db->prepare("SELECT teams.teamname,bowlers.bowlerid,teams.division, bowlers.team, bowlers.name, bowlers.enteringAvg,
                    bowlers.sanction, bowlers.ubaAvg, bowlers.seasontourAvg FROM teams INNER JOIN bowlers ON teams.teamname = bowlers.team  WHERE teams.division ='$division'
                    ORDER BY teams.teamname  ASC ");
                  

                    // $sql = $db->prepare("SELECT teams.teamname,bowlers.bowlerid,teams.division, bowlers.team, bowlers.name, bowlers.enteringAvg,
                    // bowlers.sanction, bowlers.ubaAvg, bowlers.seasontourAvg FROM teams INNER JOIN bowlers ON teams.teamname = bowlers.team  
                    // ORDER BY teams.teamname  ASC ");
                    $sql->execute();
                    $allTeams = $sql->fetchAll();

                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
    
                    //Specify the properties for this document
                    $spreadsheet->getProperties()
                    ->setTitle($type)
                    ->setSubject($type . 'Format Sheet')
                    ->setDescription('Format Sheet for UBA score entry')
                    ->setCreator('UBA System');
                    // ->setLastModifiedBy('php-download.com');
    
                    // Adding data to the excel sheet
                    $spreadsheet->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'UBA ID')
                        ->setCellValue('B1', 'Team')
                        ->setCellValue('C1', 'Name')
                        ->setCellValue('D1', '')
                        ->setCellValue('E1', 'Entering Avg')
                        ->setCellValue('F1', 'UBA Avg')
                        ->setCellValue('G1', 'Season Tour Avg')
                    	->setCellValue('H1', 'Division');
                    	// ->setCellValue('I1', 'Demo Total length')
                    	// ->setCellValue('J1', 'demo avrge');
                    	
                    	$i = 2;
							foreach ($allTeams as $team) {
								$bowlerId = $team['bowlerid'];
								$divisionnn = $team['division'];
                      
								$bow = $db->prepare("SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerId' ");
	                        	$bow->execute();
	            				$dataFetched = $bow->fetchAll();
	            				$arrayCount = array(); $gamelenth = array();
	            				// $eventName = ''; 
	            				  //$addAll = 0; 
                              
	            				  $game1 = 0; $game2 = 0; $game3 = 0; $addAll = 0; $avrgss =0;
	                        		foreach ($dataFetched as $bowler) {
	                        			
	                        				$eventDate = date('Y-m-d ', strtotime($bowler['eventdate']));
	                        				$sectValue1 = date('Y-m-d ', strtotime('09-01-2021'));
	                        				$sectValue2 = date('Y-m-d ', strtotime('09-01-2022'));
	                        				if (($eventDate <= $sectValue2) && ($eventDate >=  $sectValue1)){
	                        					
	                        					if($bowler['game1'] > 1 ){
	                        						$game1 = $game1 + $bowler['game1'];
					                        		array_push($gamelenth, $bowler['game1']);
					                        	}
					                        	if($bowler['game2'] > 1 ){
					                        		$game2 = $game2 + $bowler['game2'];
					                        		array_push($gamelenth, $bowler['game2']);
					                        	}
					                        	
					                        	if($bowler['game3'] > 1 ){
					                        		$game3 = $game3 + $bowler['game3'];
					                        		array_push($gamelenth, $bowler['game3']);
					                        	}
					                        	
					                        	array_push($arrayCount, '1');
					                        	
					                        	if(sizeof($gamelenth) >= 9){
					                        		$addAll = $game1 + $game2 + $game3;
					                        		$avrgss = $addAll/sizeof($gamelenth);
					                        	}else{
					                        		$addAll = 0;
						                        	}
		                        			}
		                        			
		                        		}
                        		
                        		
								$spreadsheet->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$i, $bowlerId)
                                    ->setCellValue('B'.$i, $team['team'])
                                    ->setCellValue('C'.$i, $team['name'])
                                    ->setCellValue('D'.$i, '')
                                    ->setCellValue('E'.$i, $team['enteringAvg'])
                                    ->setCellValue('F'.$i, $team['ubaAvg'])
                                    ->setCellValue('G'.$i, number_format($avrgss,2))
                                    ->setCellValue('H'.$i, $divisionnn);
                                    // ->setCellValue('I'.$i, '$counttt')
                                    // ->setCellValue('J'.$i, '$avrgss');
                                    
                                    $i++;
							}
							
                    	

                        $filename = "UBA Bowler Data(".$division.").xlsx";
    
                        $writer = IOFactory::createWriter($spreadsheet, "Xlsx"); //Xls is also possible
                        // $writer->setPreCalculateFormulas(false);
                        $writer->save($filename);
                        $sz= filesize(basename($filename));
                            // Process download
                            if(file_exists($filename)) {
                                    /* (E2) OR FORCE DOWNLOAD   */
                                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                                header('Content-Disposition: attachment;filename="'.basename($filename).'"');
                                header("Cache-Control: max-age=0");
                                header("Expires: Fri, 11 Nov 2011 11:11:11 GMT");
                                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                header("Cache-Control: cache, must-revalidate");
                                header('Content-Length: "'.$sz.'"');
                                header("Pragma: public");
                                ob_clean();
                                flush(); // Flush system output buffer
                                readfile($filename);                         
                             
                            }
                        unlink($filename);
                        // // header('Location: '.$_SERVER['PHP_SELF']);
                    
                } catch (PDOException $e) {
                    echo "There was some problem with the connection: " . $e->getMessage();
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
        <?php echo $msg; ?>
        
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4>Download Bowler Data</h4>
                    <form action="" method="post" name="bowler_form" enctype="multipart/form-data" onsubmit="return export_bowler()">
                        <!--<div class="form-group">-->
                            <!-- <label for="username">User's Name</label> -->
                            <!-- <input type="text" name ="username" id="username" required placeholder="Enter User's Name"> -->
                        <!--</div>-->
                        
                        <div class="form-group">
                        <label for="eventSelected">Select District</label>
                        <select class="" name="district_division" id ="district_division"  style="width: 24%;">
                            <option value="">Select District</option>
                            <?php foreach ($division as $eventval) { ?>
                        	 <option value="<?=$eventval['division'];?>"><?=$eventval['division'];?></option>
                        	 <?php }?>
                        </select>
                        	<span style="color:#b30505;" id="district_divisionerror"></span>
                        </div>
                        
                        <div class="form-group">
                            <input type="submit" value="Download Bowler Data" name="submit">
                            <!--<img src="<?=$base_url?>/images/loading-screen-loading.gif" class="loader" alt="loder"/>-->
                        </div>
                    </form>
                    
                    <div class="text-center content" style="display:none;">Please wait. File is getting ready to download. It may take a minute or more.</div>
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