<?php
include_once '../baseurl.php';
include_once '../session.php';
include_once '../connect.php';

if ($_SESSION['userrole'] == 'bowler' || $_SESSION['userrole'] == 'president' || $_SESSION['userrole'] == 'owner' || $_SESSION['userrole'] == 'secretary') {
    header("Location: " . $base_url . "/dashboard/home.php");
}

$reset = $_GET['reset'] ?? null;

if ($reset == 'y') {
    unset($_SESSION['rosterSelected']);
    unset($_SESSION['divisionSelected']);
}

try {
    $database = new Connection();
    $db = $database->openConnection();
    
    if(!isset($_POST['teamSelected']) && !isset($_POST['divisionSelected'])){
        $sql = $db->prepare("SELECT * FROM `teams` ORDER BY `teamname` ASC");
        $sql->execute();
        $dataFetched = $sql->fetchAll();

        $sql = $db->prepare("SELECT * FROM `teams` GROUP BY `division` ORDER BY `teamname` ASC ");
        $sql->execute();
        $divisionDeets = $sql->fetchAll();
    }else{
        if(isset($_POST['teamSelected'])){
            unset($_SESSION['divisionSelected']);
            $_SESSION['teamSelected'] = $_POST['teamSelected'];
        }

        if(isset($_POST['divisionSelected'])){
            unset($_SESSION['teamSelected']);
            $_SESSION['divisionSelected'] = $_POST['divisionSelected'];
        }
    }
    
} catch (PDOException $e) {
    echo "There was some problem with the connection: " . $e->getMessage();
}

$title = 'Team Roster';

include 'inc/header.php';

if (isset($_SESSION['success'])) {
    $msg = '<div class="col-12"><p class="successMsg">"' . $_SESSION['teamName'] . '" ' . $_SESSION['success'] . '</p></div>';
} else if (isset($_SESSION['error'])) {
    $msg = '<div class="col-12"><p class="errorMsg">' . $_SESSION['error'] . '</p></div>';
} else {
    $msg = '';
}
?>

<div class="users roster">
    <?php echo $msg; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php 
                    if (isset($_POST['teamSelected']) || isset($_SESSION['rosterSelected']) || isset($_POST['divisionSelected']) || isset($_SESSION['divisionSelected'])) {
                ?>

                    <div class="row">
                        <div class="col-12">
                            <a href="<?= $base_url ?>/dashboard/roster.php?reset=y" class="backTeamBtn">
                                <i class="fas fa-chevron-left"></i> Select Team</a>
                            <hr>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['teamSelected']) || isset($_SESSION['rosterSelected'])) { ?>
                        <input type="hidden" id="teamSelected" value="<?php echo $_SESSION['teamSelected'];?>">
                        <h4>Team Roster: '<?php echo $_SESSION['teamSelected']; ?>'
                        </h4>
                        <hr>
                        <div class="row">
                            <div class="col-12 uba-table">
                                <table id="team_roster">
                                    <thead>
                                        <th>No.</th>
                                        <th>UBA ID</th>
                                        <th>Name</th>
                                        <th>Nickname</th>
                                        <th>Office Held</th>
                                        <th>Sanction #</th>
                                        <th>Entering Avg</th>
                                        <th>UBA Avg</th>
                                        <th>ST Avg</th>
                                        <?php if($_SESSION['userrole'] == 'admin') { ?>
                                        <th>Edit</th>
                                        <?php } ?>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <?php
                    } else {?>
                        <input type="hidden" id="divisionSelected" value="<?php echo $_SESSION['divisionSelected'];?>">
                        <h4>Division Roster: '<?php echo $_SESSION['divisionSelected']; ?>'</h4>
                        <hr>
                        <div class="row">
                            <div class="col-12 uba-table">
                                <table id="division_roster">
                                    <thead>
                                        <th>No.</th>
                                        <th>UBA ID</th>
                                        <th>Name</th>
                                        <th>Nickname</th>
                                        <th>Team</th>
                                        <th>Sanction #</th>
                                        <th>Entering Avg</th>
                                        <th>UBA Avg</th>
                                        <th>ST Avg</th>
                                        <?php if($_SESSION['userrole'] == 'admin') { ?>
                                        <th>Edit</th>
                                        <?php } ?>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <?php
                    }
                } else { ?>
                    <div class="row">
                        <div class="col-6">
                            <h4>Team Roster</h4>
                            <form action="" method="post">
                                <div class="form-group">
                                    <label for="teamSelected">Select Team</label>
                                    <select name="teamSelected" id="teamSelected">
                                        <option value="-" disabled selected>Select</option>
                                        <?php foreach ($dataFetched as $team) { ?>
                                            <option value="<?php echo $team['teamname']; ?>">
                                                <?php echo $team['teamname']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <input type="submit" value="Select Team">
                                </div>
                            </form>
                        </div>

                        <div class="col-6">
                            <h4>Search by Division</h4>
                            <form action="" method="post">
                                <div class="form-group">
                                    <select name="divisionSelected" id="divisionSelected" required>
                                        <option value="-" disabled selected>-</option>
                                        <?php foreach ($divisionDeets as $division) { ?>
                                            <option value="<?php echo $division['division']; ?>">
                                                <?php echo $division['division']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <input type="submit" name="searchDB" value="Search">
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
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