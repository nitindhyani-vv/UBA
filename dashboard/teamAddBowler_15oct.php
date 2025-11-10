<?php
	include_once '../baseurl.php';
    include_once '../session.php';
    include_once '../connect.php';

    if($_SESSION['userrole'] == 'admin' || $_SESSION['userrole'] == 'staff' || $_SESSION['userrole'] == 'bowler'  || $_SESSION['userrole'] == 'eventstaff'){
        header("Location: ".$base_url."/dashboard/home.php");
    }

    try {
        $database = new Connection();
        $db = $database->openConnection();

        if (isset($_POST['bowlerName'])) {

            $bowlerName = $_POST['bowlerName'];
            $searchBowlers = $_SESSION['team'];

            $sql = $db->prepare("SELECT `id`,`bowlerid`, `bowler`,`team` FROM `bowlersreleased` WHERE `bowler` LIKE '%".$bowlerName."%' AND `currentstatus` = 'Released' AND `team` = 'Released Bowlers' AND `isTransferred` = '0'");
            $sql->execute();
            $independentBowlers = $sql->fetchAll();
        }

        
    } catch (PDOException $e) {
        echo "There was some problem with the connection: " . $e->getMessage();
    }

    $title = 'Add Bowler to Team Roster';

    include 'inc/header.php';

    if (isset($_SESSION['success'])) {
        $msg = '<div class="col-12"><p class="successMsg">'.$_SESSION['success'].'</p></div>';
    } else if (isset($_SESSION['error'])) {
        $msg = '<div class="col-12"><p class="errorMsg">'.$_SESSION['error'].'</p></div>';
    } else {
        $msg = '';
    }

?>
<style> 
.bowler-button{
    background-color: #0a0a0a;
    color: #fff;
    width: 100px;
    cursor: pointer;
}

.backTeamBtn{
    color: #fff !important; 
    cursor: pointer;
}
</style>
<div class="users roster">
    <?php echo $msg; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12" id="searchBowlerSection">
                <form method="POST" >
                    <h4>Search Bowlers</h4>
                    <hr/>
                    <input type="text" name="bowlerName" id="bowlerName" placeholder="Search by bowler name" require>
                        <br>
                    <input type="button" class="bowler-button" value="Search" onClick=searchBowler()>
                </form>
            </div>


            <div class="col-12 uba-table" id="tableBowlerSection">
                <h4>Add Bowler to Team</h4>
                <p>Bowlers would appear in the Team Roster after the Admin approves the Transfer</p>
                <a class="backTeamBtn" onclick="resetSearch()">Reset Search</a>
                <hr>
                <table id="independentBowlerList">
                    <thead>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Bowler ID</th>
                        <th>Team</th>
                        <th>Add to Team</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
unset($_SESSION['success']);
unset($_SESSION['error']);
unset($_SESSION['teamName']);
include 'inc/footer.php';
?>