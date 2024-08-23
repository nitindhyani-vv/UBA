<?php
	include_once '../baseurl.php';
    include_once '../session.php';
    include_once '../connect.php';

    if($_SESSION['userrole'] != 'admin'){
        header("Location: ".$base_url."/dashboard/home.php");
    }

    // try {
    //     $database = new Connection();
    //     $db = $database->openConnection();

    //     $sql = $db->prepare("SELECT * FROM `teams` ORDER BY `rostersubmissiondate` DESC");
    //     $sql->execute();
    //     // $sql->execute();
    //     $teamDeets = $sql->fetchAll();


    // } catch (PDOException $e) {
    //     echo "There was some problem with the connection: " . $e->getMessage();
    // }

    $title = 'Submitted Team Rosters';

    include 'inc/header.php';

    if (isset($_SESSION['success'])) {
        $msg = '<div class="col-12"><p class="successMsg">"'.$_SESSION['teamName'].'" '.$_SESSION['success'].'</p></div>';
    } else if (isset($_SESSION['error'])) {
        $msg = '<div class="col-12"><p class="errorMsg">'.$_SESSION['error'].'</p></div>';
    } else {
        $msg = '';
    }
?>

<div class="users">
    <?php echo $msg; ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12  uba-table">
                <h4>Team Rosters - Submitted1</h4>
                <p>Click on the Team name to view the submitted roster for that team</p>
                <hr>

                <table class="display" id="submittedrosters">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Team</th>
                            <th>Roster Submitted</th>
                            <th>Submitted By</th>
                            <th>Submitted On</th>
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
unset($_SESSION['teamName']);
include 'inc/footer.php';
?>