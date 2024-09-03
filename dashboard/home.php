<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

    include_once '../session.php';
    include_once '../connect.php';
    // include_once 'checkuser.php';

    $title = 'Home';
    include 'inc/header.php';

    $useremail = $_SESSION['useremail'];

    try {
        $database = new Connection();
        $db = $database->openConnection();

        if ($_SESSION['userrole'] == 'admin') {
            $approved = 1;

            $nickChanged = 0;

            $sql = $db->prepare("SELECT * FROM `presidency` WHERE `approved` = :approved");
            $sql->execute([':approved' => $approved]);
            $presidentClaims = $sql->fetchAll();

            $sql = $db->prepare("SELECT * FROM `ownership` WHERE `approved` = :approved");
            $sql->execute([':approved' => $approved]);
            $ownerClaims = $sql->fetchAll();

            $sql = $db->prepare("SELECT * FROM `bowlerTransfers` WHERE `approved` = :approved group by bowlerid ");
            $sql->execute([':approved' => $approved]);
            $transferClaims = $sql->fetchAll();

            $sql = $db->prepare("SELECT * FROM `bowlerupdates` WHERE `nicknameChanged` = :nickChanged");
            $sql->execute([':nickChanged' => $nickChanged]);
            $nicknameChange = $sql->fetchAll();

            $sql = $db->prepare("SELECT * FROM `bowlersreleased` WHERE `approved` = :approved AND `release_status` = :release_status AND `removedby` != 'Admin' ORDER BY id ASC ");
            $sql->execute([':approved' => '1',"release_status"=>'0']);
            $bowlersReleased = $sql->fetchAll();

            $nonactive = 0;

            $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `active` = :nonactive");
            $sql->execute([':nonactive' => $nonactive]);
            $nonactivebowlers = $sql->fetchAll();
        }

        

        if ($_SESSION['userrole'] != 'admin' || $_SESSION['userrole'] != 'staff') {
            $teamName = $_SESSION['team'];
            
            $sql = $db->prepare("SELECT * FROM `teams` WHERE `teamname` = :teamName ORDER BY `teamname` ASC");
            $sql->execute([':teamName' => $teamName]);
            $teamDeets = $sql->fetch();

            $sql = $db->prepare("SELECT * FROM `presidency` WHERE `team` = :teamName");
            $sql->execute([':teamName' => $teamName]);
            $presidency = $sql->fetch();
            $sql = $db->prepare("SELECT * FROM `ownership` WHERE `team` = :teamName");
            $sql->execute([':teamName' => $teamName]);
            $ownership = $sql->fetch();

            $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `uemail` = :useremail");
            $sql->execute([':useremail' => $useremail]);
            $bowlerDeets = $sql->fetch();
            
            $bowlerUBAID = $bowlerDeets['bowlerid'];
            $bowlerEnteringAvg = $bowlerDeets['enteringAvg'];
            $bowlerEnteringAvg = (int)$bowlerEnteringAvg;
            $ubaAvg = $bowlerDeets['ubaAvg'];
            // $seasonTourAvg = $bowlerDeets['seasontourAvg'];
    
            $sql = $db->prepare("SELECT * FROM `bowlerdata` WHERE `bowlerid` = '$bowlerUBAID'");
            $sql->execute();
            $dataFetchedEvents = $sql->fetchAll();
    
            if(isset($seasonTourYearFlag) == null){
                $currentYear =date("Y");
                $nextYear= date("Y" ,strtotime("+1 year"));
                $year = substr( $nextYear, -2);
                $sql = $db->prepare("SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerUBAID'");
                $sql_avg = $db->prepare("SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerUBAID' AND YEAR(eventdate) BETWEEN '$currentYear' AND '$nextYear' AND year='$currentYear/$year'");
            }else{
                echo "SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerUBAID' AND eventdate ='9-9-20'";
                $sql = $db->prepare("SELECT * FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerUBAID' AND eventdate ='9-9-20'");
            }
            
            $sql->execute();
            $dataFetchedSeasonTour = $sql->fetchAll();
            

            $sql = $db->prepare("SELECT distinct(year) as SeasonTourYear FROM `bowlerdataseason` WHERE `bowlerid` = '$bowlerUBAID'");
            $sql->execute();
            $dataFetchedSeasonTourYear = $sql->fetchAll();


            $sql = $db->prepare("SELECT distinct(year) as EventsTourYear FROM `bowlerdata` WHERE `bowlerid` = '$bowlerUBAID'");
            $sql->execute();
            $dataFetchedEventsYear = $sql->fetchAll();
			
		
			
			$gametotalValue = 0;

        	$sql_avg->execute();
            $dataFetchedSeasonTour_avg = $sql_avg->fetchAll();

        		$arrayCount = array(); $gamelenth = array();
        	$game1 = 0; $game2 = 0; $game3 = 0; $addAll = 0; $avrgss =0;
        	foreach ($dataFetchedSeasonTour_avg as $bowler) {
				$eventDate = date('Y-m-d ', strtotime($bowler['eventdate']));
				$sectValue1 = date('Y-m-d ', strtotime('09-01-2023'));
				$sectValue2 = date('Y-m-d ', strtotime('09-01-2024'));

					// if (($eventDate <= $sectValue2) && ($eventDate >=  $sectValue1)){
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
							$avrgss = 0;
						}
					// }else{
					// 	$avrgss = 0;
					// }
        	}

			$seasonTourAvg = $avrgss;
        }
        
    } catch (PDOException $e) {
        echo "There was some problem with the connection: " . $e->getMessage();
    }

    if (isset($_SESSION['success'])) {
        $msg = '<div class="col-12"><p class="successMsg">'.$_SESSION['success'].'</p></div>';
    } else if (isset($_SESSION['error'])) {
        $msg = '<div class="col-12"><p class="errorMsg">'.$_SESSION['error'].'</p></div>';
    } else {
        $msg = '';
    }

?>
<style type="text/css">
.modal-backdrop {
    position: inherit;
    top: 0;
    right: 0;
    bottom: 0;
    /* left: 0; */
    /* z-index: 1040; */
    background-color: #000;
}

.modal {
    position: sticky;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1050;
    display: none;
    overflow: hidden;
    outline: 0;
}

.modal.show .modal-dialog {
    -webkit-transform: translate(0, 0);
    transform: translate(0, 0);
    max-width: 75%;
}

button.btn {
    font-size: 15px;
}
.dataTables_length label{
    display:none !important;
}

.dataTables_length label .pagination-drop {
    margin-right: 9px !important;
    height: 40px !important;
}

table th {
    background-color: #a54c00;
    color: white;
    border-color: #a54c00;
}
</style>

<!-- bootsrap modal -->

<div class="modal" id="bowlerTransferModal" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bowler Transfer Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to transfer a bowler?</p>
            </div>
            <input type="text" id="bowlerId" name="" hidden="hidden">
            <input type="text" id="tableId" name="" hidden="hidden">
            <div class="modal-footer">
                <button type="button" onclick="saveAndAddToTheRoster()" class="btn btn-primary">Yes</button>
                <button type="button" data-dismiss="modal" class="btn btn-secondary">No</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap modal end -->

<!-- For add bowler -->

<div class="modal" id="addBowlerModal" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Bowler Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to add a bowler?</p>
            </div>
            <input type="text" id="bowlerIdForAdd" name="" hidden="hidden">
            <div class="modal-footer">
                <button type="button" onclick="addAndAddToTheRoster()" class="btn btn-primary">Yes</button>
                <button type="button" data-dismiss="modal" class="btn btn-secondary">No</button>
            </div>
        </div>
    </div>
</div>


<div class="container-fluid">
    <div class="row"><?php echo $msg; ?></div>
    
    <?php if ($_SESSION['userrole'] == 'admin') {  ?>
    <div class="row">
        <div class="col-12 uba-table">
            <h4 class="claimRequests mb-4">Bowlers added by Team President/Owner</h4>
            <table id="bowlerAddedByTeamPresedent" class="display">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Bowler ID</th>
                        <th>Name</th>
                        <th>Team</th>
                        <th>Nickname</th>
                        <th>Sanction</th>
                        <th>Create At</th>
                        <th>Approve</th>
                        <th>Decline</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <hr/>

    <div class="row">
        <div class="col-12 uba-table">
            <h4 class="claimRequests mb-4">Bowlers released/suspended by Team President/Owner</h4>
            <table id="releasedBowlersTableHome" class="display">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Bowler ID</th>
                        <th>Name</th>
                        <th>Released From</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Close</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <hr/>

    <div class="row">
        <div class="col-12 uba-table">
            <h4 class="claimRequests">President Requests</h4>
            <table id="president_table_home" class="display">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Bowler ID</th>
                        <th>Name</th>
                        <th>Team</th>
                        <th>Approve</th>
                        <th>Decline</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <hr/>

    <div class="row">
        <div class="col-12 uba-table">
            <h4 class="claimRequests">Ownership Requests</h4>
            <table id="ownerTableHome" class="display">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Bowler ID</th>
                        <th>Name</th>
                        <th>Team</th>
                        <th>Approve</th>
                        <th>Decline</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <hr/>

    <div class="row">
        <div class="col-12 uba-table">
            <h4 class="claimRequests">Bowler Transfer Requests NEW</h4>
            <table id="transferTableHome" class="display">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Requested By</th>
                        <th>Bowler</th>
                        <th>Bowler ID</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Date & Time</th>
                        <th>Approve</th>
                        <th>Decline</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <hr/>
    <?php } ?>
    
    <div class="row col-12">
        <h4><?php echo $bowlerDeets['name']; ?></h4>
        <?php if ($_SESSION['userrole'] == 'bowler' || $_SESSION['userrole'] == 'president' || $_SESSION['userrole'] == 'owner') {
                    if (($teamDeets['president'] == '-' || $teamDeets['president'] == '') && !$presidency) { ?>
            <div class="claim presidentClaim">
                <a href="claimPresidency.php">Claim Presidency for
                    <?php echo $_SESSION['team'];?></a>
            </div>
        <hr/>
        <?php 
            }
            if (($teamDeets['owner'] == '-' || $teamDeets['owner'] == '') && !$ownership) {
        ?>
        <div class="claim ownerClaim">
            <a href="claimOwnership.php">Claim Ownership for
                <?php echo $_SESSION['team'];?></a>
        </div>
        <hr/>
        <?php }  } ?>

        <div class="col-12 averages">
            <span>UBA:<b> <span><?php echo $ubaAvg;?> </span></b></span>
            <span>Season Tour: <b><span id="showSeasonAvrg"><?php echo number_format($seasonTourAvg,2);?></span></b></span>
            <span>Entering Average: <b><span> <?php echo $bowlerEnteringAvg;?></span></b></span>
        </div>
    </div>
    <hr/>
    <div class="row mt-4">
        <div class="col-12 ">
            <h4 class="claimRequests">Season Tour</h4>
            <span class="mb-4">
                <label for="seasonYear">Please select the year:</label>
                <select name="seasonYear" id="seasonYear1">
                    <option value="">All Seasons</option>
                    <?php $current_year = date("Y")+1; $current_year_s = date("y")+1;
                        if(date("Y") >= date("Y")){ $count = 5; }else{ $count = 6; }
                        for ($s = 1; $s <= $count; $s++) {
                            if($s == 1){ $styear = $current_year-$s; $endyear = $current_year_s;}
                            else{ $styear = $current_year-$s; $endyear = $current_year_s-$s+1;} ?>
                            <option value="<?=$styear.'/'.$endyear;?>"><?=$styear.'/'.$endyear;?></option>
                    <?php } ?>
                </select>
            </span>
            <button class="btn btn-info btn-sm" id="homeSeasonsTour"> Submit</button>
                <div class="col-12 uba-table">
                    <table id="homeSeasonsTourHome" class="display mt-4">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th style="width: 81px;">Date</th>
                                <th>Year</th>
                                <th style="width: 461px;">Event Name</th>
                                <th>Event Type</th>
                                <th>Team</th>
                                <th>Game 1</th>
                                <th>Game 2</th>
                                <th>Game 3</th>
                            </tr>
                        </thead>
                    </table>
                </div>
        </div>        
    </div>

    <hr/>
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="claimRequests">Events</h4>
            <span class="mb-4">
                <label for="seasonYear">Please select the year:</label>
                <select name="seasonYear" id="seasonYear2">
                    <option value="">All Seasons</option>
                    <?php $current_year = date("Y")+1; $current_year_s = date("y")+1;
                        if(date("Y") >= date("Y")){ $count = 5; }else{ $count = 6; }
                        for ($s = 1; $s <= $count; $s++) {
                            if($s == 1){ $styear = $current_year-$s; $endyear = $current_year_s;}
                            else{ $styear = $current_year-$s; $endyear = $current_year_s-$s+1;} ?>
                            <option value="<?=$styear.'/'.$endyear;?>"><?=$styear.'/'.$endyear;?></option>
                    <?php } ?>
                </select>
            </span>
            <button class="btn btn-info btn-sm" id="homeEvent"> Submit</button>
            <div class=" col-12 uba-table">
                <table id="homeEventHome" class="display mt-4">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th style="width: 82px;">Date</th>
                            <th>Year</th>
                            <th style="width: 250px;">Event Name</th>
                            <th>Event Type</th>
                            <th>Team</th>
                            <th>Game 1</th>
                            <th>Game 2</th>
                            <th>Game 3</th>
                            <th>Game 4</th>
                            <th>Game 5</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>        
    </div>


</div>
<?php
unset($_SESSION['success']);
unset($_SESSION['error']);
include 'inc/footer.php';
//var_dump($_SESSION['useremail']);exit();
?>
<!--<a href="process/approveRelease.php?id=n&bowler=<?php echo $singleScoreData['bowlerid'];?>"><i class="fas fa-times"></i></a>-->
<script>
function approveRelease(bowllerid) {
    console.log('bowllerid', bowllerid);
    if (window.confirm('Are you sure want to released this bowlers  ?')) {
        // They clicked Yes
        // alert('yes');
        location.href = "process/approveRelease.php?id=n&bowler=" + bowllerid;
    }


}
</script>

<script>
$(document).ready(function() {
    var bowlID = '<?=$bowlerUBAID;?>';
    console.log('bowlID', bowlID);

    var formData = {
        'bowlerID': bowlID
    };

    $.ajax({
            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url: '<?=$base_url?>/dashboard/fatchSeasonAvrg.php', // the url where we want to POST
            data: formData, // our data object
            dataType: 'json', // what type of data do we expect back from the server
            encode: true
        })
        .done(function(data) {
            var crntYear = new Date().getFullYear();
            var nextYear = new Date().getFullYear() + 1;

            var sectValue1 = new Date('09/' + '01' + '/' + crntYear);
            var sectValue2 = new Date('09/' + '01' + '/' + nextYear);
            console.log(sectValue1);
            var gameCount = [];
            var totalval = 0;
            var avrg = '';
            var counts = [];
            console.log(data);
            for (let index = 0; index < data.length; index++) {

                var checkDate = new Date(data[index]['eventDate']);
                if ((checkDate <= sectValue2) && (checkDate >= sectValue1)) {
                    if (data[index]['game 1'] > 1) {
                        counts.push(data[index]['game 1']);
                    }
                    if (data[index]['game 2'] > 1) {
                        counts.push(data[index]['game 2']);
                    }

                    if (data[index]['game 3'] > 1) {
                        counts.push(data[index]['game 3']);
                    }

                    totalval = parseInt(totalval) + parseInt(data[index]['pinfall']);
                    // counts.push('1');
                    if (counts.length >= 9) {
                        avrg = totalval / counts.length;
                    } else {
                        avrg = '0.00';
                    }

                }

            }
            $('#showSeasonAvrg').html(parseFloat(avrg).toFixed(2));
            // console.log('avrg',avrg);

        });
});
</script>

<script type="text/javascript">
function submitSeasonYear() {
    $("#loaderbg").fadeIn("slow");
    console.log($('#seasonYear :selected').text());
    var data = {
        "action": 'season',
        "year": $('#seasonYear :selected').text(),
        "useremail": "<?php echo $_SESSION['useremail'] ?>"
    }
    var url = "process/dashboardStatistics.php"
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
            console.log(response);
            var jsonObj = JSON.parse(response);

            //remove old value

            var tableSeasons = $('#homeSeasonsTourHome').dataTable();

            tableSeasons.fnClearTable();
            // tableSeasons.dataTable().fnAddData(jsonObj);


            var array_length = jsonObj.length;
            if (array_length > 0) {
                for (i = 0; i < array_length; i++) {
                    var values = [];
                    values.push(i + 1);
                    values.push(jsonObj[i]['eventdate']);
                    values.push(jsonObj[i]['year']);
                    values.push(jsonObj[i]['event']);
                    values.push(jsonObj[i]['location']);
                    values.push(jsonObj[i]['team']);
                    values.push(jsonObj[i]['game1']);
                    values.push(jsonObj[i]['game2']);
                    values.push(jsonObj[i]['game3']);
                    tableSeasons.fnAddData([values]);
                }

            }
            $("#loaderbg").fadeOut("slow");

        }
    });
}



function submitEventsYear() {
    $("#loaderbg").fadeIn("slow");
    console.log($('#eventsYear :selected').text());
    var data = {
        "action": 'events',
        "year": $('#eventsYear :selected').text(),
        "useremail": "<?php echo $_SESSION['useremail'] ?>"
    }
    var url = "process/dashboardStatistics.php"
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
            console.log(response);
            var jsonObj = JSON.parse(response);

            //remove old value

            var tableSeasons = $('#table_1_events_home').dataTable();

            tableSeasons.fnClearTable();
            // tableSeasons.dataTable().fnAddData(jsonObj);


            var array_length = jsonObj.length;
            if (array_length > 0) {
                for (i = 0; i < array_length; i++) {
                    var values = [];
                    values.push(i + 1);
                    values.push(jsonObj[i]['eventdate']);
                    values.push(jsonObj[i]['year']);
                    values.push(jsonObj[i]['event']);
                    values.push(jsonObj[i]['eventtype']);
                    values.push(jsonObj[i]['team']);
                    values.push(jsonObj[i]['game1']);
                    values.push(jsonObj[i]['game2']);
                    values.push(jsonObj[i]['game3']);
                    values.push(jsonObj[i]['game4']);
                    values.push(jsonObj[i]['game5']);

                    tableSeasons.fnAddData([values]);
                }

            }
            $("#loaderbg").fadeOut("slow");

        }
    });
}

function showConfirmation(type, bowlerId, tableId) {
    $('#bowlerTransferModal').modal('toggle');
    $('#tableId').val(tableId);
    $('#bowlerId').val(bowlerId);
}

function saveAndAddToTheRoster() {
    var bowlerid = $('#bowlerId').val();
    var tableid = $('#tableId').val();
    window.location = "process/acceptTransfer.php?id=y&bowler=" + bowlerid + "&tab=" + tableid +
        "&type=saveAndAddToTheRoster";
}

function onlyApproveTranfer() {
    var bowlerid = $('#bowlerId').val();
    var tableid = $('#tableId').val();
    window.location = "process/acceptTransfer.php?id=y&bowler=" + bowlerid + "&tab=" + tableid +
        "&type=onlyApproveTranfer";
}


function showConfirmationAddBowler(type, bowlerId) {
    $('#addBowlerModal').modal('toggle');
    $('#bowlerIdForAdd').val(bowlerId);
}

function addAndAddToTheRoster() {
    var bowlerid = $('#bowlerIdForAdd').val();
    window.location = "process/activateBowler.php?id=y&bowler=" + bowlerid + "&type=addAndAddToTheRoster";
}

function addOnly() {
    var bowlerid = $('#bowlerIdForAdd').val();
    window.location = "process/activateBowler.php?id=y&bowler=" + bowlerid + "&type=addOnly";
}
</script>