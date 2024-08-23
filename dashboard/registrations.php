<?php

    include_once '../session.php';
    include_once '../connect.php';

    if($_SESSION['userrole'] != 'admin'){
        header("Location: /dashboard/home.php");
    }

    $title = 'Registrations';
    include 'inc/header.php';
    if (isset($_SESSION['success'])) {
        $msg = '<div class="col-12"><p class="successMsg">'.$_SESSION['success'].'</p></div>';
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
                <h4>Registered Bowlers</h4>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <table id="registrationTable">
                            <thead>
                                <th>No.</th>
                                <th>UBA ID</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th>President</th>
                                <th>Entering Avg</th>
                                <th>Sanction #</th>
                                <th>Verified</th>
                                <th>Edit</th>
                                <th>Verify User</th>
                                <th>Resend Email</th>
                            </thead>
                        </table>
                    </div>
                </div>
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