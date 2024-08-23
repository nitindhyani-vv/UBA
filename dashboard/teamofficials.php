<?php	
include_once '../baseurl.php';
include_once '../session.php';
include_once '../connect.php';

$userroleArray = ['admin','staff'];

if(in_array($userroleArray,$_SESSION['userrole'])){
    header("Location: /dashboard/home.php");
}

$title = 'Download Team Officials';

include 'inc/header.php';
?>

<div class="users roster">
    <?php echo $msg; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-12 uba-table">
                        <h4>Team Presidents & Owners</h4>
                        <table  class="display" id="team_official">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Team</th>
                                    <th>President</th>
                                    <th>Owner</th>
                                </tr>
                            </thead>
                        </table>
                        </form>
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