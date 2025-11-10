<?php
	include_once '../baseurl.php';
    include_once '../session.php';
    include_once '../connect.php';

    // $userroleArray = array('admin','staff');

    // if(in_array($userroleArray, $_SESSION['userrole'])){
    //     header("Location: /dashboard/home.php");
    // }

    // try {
    //     $database = new Connection();
    //     $db = $database->openConnection();

    //     $sql = $db->prepare("SELECT * FROM `bowlersreleased`");
    //     $sql->execute();
    //     // $sql->execute();
    //     $bowlerDeets = $sql->fetchAll(); 
        
    // } catch (PDOException $e) {
    //     echo "There was some problem with the connection: " . $e->getMessage();
    // }

    $title = 'Released/Suspended Bowlers';

    include 'inc/header.php';

    if (isset($_SESSION['success'])) {
        $msg = '<div class="col-12"><p class="successMsg">"'.$_SESSION['teamName'].'" '.$_SESSION['success'].'</p></div>';
    } else if (isset($_SESSION['error'])) {
        $msg = '<div class="col-12"><p class="errorMsg">'.$_SESSION['error'].'</p></div>';
    } else {
        $msg = '';
    }
?>

<div class="users roster">
    <?php echo $msg; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 uba-table">
                    <h4>Released/Suspended Bowlers List</h4>
                    <hr>
                    <table id="released_bowlers">
                        <thead> 
                            <tr>
                                <th>No.</th>
                                <th>UBA ID</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th>Date Submitted</th>
                                <th>Removed by</th>
                                <th>Current Status</th>
                                <th>Eligible Date</th>
                                <?php if ($_SESSION['userrole'] == 'admin') { ?>
                                <th>Reinstate</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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