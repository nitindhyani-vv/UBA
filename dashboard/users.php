<?php	
	include_once '../baseurl.php';
    include_once '../session.php';
    include_once '../connect.php';

    if($_SESSION['userrole'] != 'admin'){
        header("Location: ".$base_url."/dashboard/home.php");
    }

    $title = 'Users';
    include 'inc/header.php';

    if (isset($_SESSION['success'])) {
        $msg = '<div class="col-12"><p style="color: #009a00; font-weight: 500;">'.$_SESSION['success'].'</p></div>';
    } else if (isset($_SESSION['error'])) {
        $msg = '<div class="col-12"><p style="color: #df3200; font-weight: 500;">'.$_SESSION['error'].'</p></div>';
    } else {
        $msg = '';
    }
?>

    <div class="users">
        <div class="col-12 uba-table">
            <h4 class="p-3">Users <span><a href="addUser.php" class="adduser"><i class="fas fa-plus"></i> Add User</a></span></h4>
            <table id="uba_users">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                
            </table>
        </div>
    </div>

<?php

include 'inc/footer.php';

?>
