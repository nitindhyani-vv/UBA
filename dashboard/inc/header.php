<?php
error_reporting(E_ALL & ~E_NOTICE);  
include_once '../baseurl.php';
?>
<!DOCTYPE html>
<html class="fontawesome-i2svg-active fontawesome-i2svg-complete gr__bootstrapious_com"
    wtx-context="7612EC81-ACF6-40F0-8E26-828B9EA6EC17">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>
        <?php echo $title;?> | UBA</title>
    <link src="../css/custom.css" />

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">

    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.7/css/select.dataTables.min.css"> -->

    <!-- Font Awesome JS -->
    <script defer="" src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js"
        integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous">
    </script>
    <script defer="" src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js"
        integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous">
    </script>

    <link rel="icon" href="../images/favicon.ico" type="image/x-icon" />
    <style>
    #loaderbg {
        background-color: #a54c00;
        height: 100vh;
        z-index: 1000000;
        position: absolute;
        width: 100%;
    }

    #loaderbg img {
        margin: auto;
        display: block;
        max-width: 150px;
        position: relative;
        top: 15%;
    }

    .loader,
    .loader:before,
    .loader:after {
        background: #ffffff;
        -webkit-animation: load1 1s infinite ease-in-out;
        animation: load1 1s infinite ease-in-out;
        width: 1em;
        height: 4em;
    }

    .loader {
        color: #ffffff;
        text-indent: -9999em;
        margin: 88px auto;
        position: absolute;
        top: 30%;
        left: 50%;
        font-size: 11px;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
        /* -webkit-animation-delay: -0.16s; */
        /* animation-delay: -0.16s; */
    }

    .loader:before,
    .loader:after {
        position: absolute;
        top: 0;
        content: '';
    }

    .loader:before {
        left: -1.5em;
        /* -webkit-animation-delay: -0.32s; */
        /* animation-delay: -0.32s; */
    }

    .loader:after {
        left: 1.5em;
    }

    @-webkit-keyframes load1 {

        0%,
        80%,
        100% {
            box-shadow: 0 0;
            height: 4em;
        }

        40% {
            box-shadow: 0 -2em;
            height: 5em;
        }
    }

    @keyframes load1 {

        0%,
        80%,
        100% {
            box-shadow: 0 0;
            height: 4em;
        }

        40% {
            box-shadow: 0 -2em;
            height: 5em;
        }
        .disable-delete-btn{
            padding: 8px 30px;
            background-color: black;
            color: white !important;
            transition: 0.5s ease;
            -moz-transition: 0.5s ease;
            -o-transition: 0.5s ease;
            -webkit-transition: 0.5s ease;
        }
    </style>
</head>

<body data-gr-c-s-loaded="true">

    <div id="loaderbg">
        <div class="loader">Loading...</div>
        <img src="<?=$base_url;?>/images/UBA_logo.png">
    </div>

    <div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar" class="mCustomScrollbar _mCS_1 mCS-autoHide mCS_no_scrollbar" style="overflow: visible;">
            <div id="mCSB_1" class="mCustomScrollBox mCS-minimal mCSB_vertical mCSB_outside" style="max-height: none;"
                tabindex="0">
                <div id="mCSB_1_container" class="mCSB_container mCS_y_hidden mCS_no_scrollbar_y"
                    style="position:relative; top:0; left:0;" dir="ltr">
                    <div class="sidebar-header">
                        <img src="<?=$base_url;?>/images/UBA_logo.png" alt="UBA Logo">
                    </div>

                    <ul class="list-unstyled components">
                        <p style="text-transform: capitalize;">User:
                            <?php if($_SESSION['userrole'] == 'eventstaff') {echo 'Event Staff';} else { echo $_SESSION['userrole'];}; ?>
                        </p>
                        <li <?php if($title=='Home' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/home.php">Home </a>
                        </li>
                        <li>
                            <a href="<?=$base_url;?>/dashboard/search.php">Search Database</a>
                        </li>

                        <?php
                            if ($_SESSION['userrole'] == 'admin' || $_SESSION['userrole'] == 'staff') {
                        ?>
                        <li>
                            <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false"
                                class="dropdown-toggle">Add
                                Score</a>
                            <ul class="collapse list-unstyled" id="pageSubmenu">
                                <?php if($_SESSION['userrole'] == 'admin') {?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/addEvent.php">Add Event</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/editEvent.php">Edit Event</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/addscoreseason.php">Season Scores</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/addscoreevent.php">Event Scores</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/editEventName.php">Change Event Name</a>
                                </li>
                                <?php }?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/uploadscore.php">Upload Score Sheet</a>
                                </li>
                                <?php if($_SESSION['userrole'] == 'admin') {?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/scoreuploadformat.php">Format Sheet - Upload
                                        Scores</a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <li>
                            <a href="#bowlerMenu" data-toggle="collapse" aria-expanded="false"
                                class="dropdown-toggle">Bowlers</a>
                            <ul class="collapse list-unstyled" id="bowlerMenu">
                                <?php if($_SESSION['userrole'] == 'admin') {?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/addBowler.php">Add Bowler</a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/roster.php">Rosters</a>
                                </li>
                                <?php if($_SESSION['userrole'] == 'admin') {?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/submittedroster.php">Rosters Submitted</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/registrations.php">Registrations</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/bowlerDataExport.php">Bowlers Data</a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/rsbowler.php">Released Bowlers</a>
                                </li>
                                <?php if($_SESSION['userrole'] == 'admin') {?>

                                <li>
                                    <a href="<?=$base_url;?>/dashboard/updateBowlers.php">Update Bowlers - Upload</a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <li>
                            <a href="#teamData" data-toggle="collapse" aria-expanded="false"
                                class="dropdown-toggle">Team
                                Data</a>
                            <ul class="collapse list-unstyled" id="teamData">
                                <?php if($_SESSION['userrole'] == 'admin') {?>

                                <li>
                                    <a href="<?=$base_url;?>/dashboard/addTeam.php">Add Team</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/editTeam.php">Edit Team</a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/teamofficials.php">Presidents & Owners</a>
                                </li>

                            </ul>
                        </li>
                        <?php if($_SESSION['userrole'] == 'admin') {?>
                        <li>
                            <a href="#scoreEntries" data-toggle="collapse" aria-expanded="false"
                                class="dropdown-toggle">Scores</a>
                            <ul class="collapse list-unstyled" id="scoreEntries">

                                <li>
                                    <a href="<?=$base_url;?>/dashboard/scoreData.php">Score Entries</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/formatSheetExport.php">Format Sheet</a>
                                </li>

                            </ul>
                        </li>

                        <li>
                            <a href="<?=$base_url;?>/dashboard/calculateAverages.php">Averages</a>
                        </li>
                        <li>
                            <a href="<?=$base_url;?>/dashboard/eventRegistrations.php">Event Registrations</a>
                        </li>
                        <li>
                            <a href="<?=$base_url;?>/dashboard/users.php">Users</a>
                        </li>
                        <?php 
                        }
                    };
                ?>

                        <?php
                            if ($_SESSION['userrole'] == 'eventstaff') {
                        ?>
                        <li>
                            <a href="#scoreEntries" data-toggle="collapse" aria-expanded="false"
                                class="dropdown-toggle">Data</a>
                            <ul class="collapse list-unstyled" id="scoreEntries">

                                <li>
                                    <a href="<?=$base_url;?>/dashboard/roster.php">Rosters</a>
                                </li>
                                <li>
                                    <a href="<?=$base_url;?>/dashboard/bowlerDataExport.php">Bowlers Data</a>
                                </li>

                            </ul>
                        </li>
                        <?php
                            };
                        ?>

                        <?php
                    if ($_SESSION['userrole'] == 'president' || $_SESSION['userrole'] == 'owner'  || $_SESSION['userrole'] == 'secretary' || $_SESSION['userrole'] == 'vicepresident' || $_SESSION['userrole'] == 'captain' || $_SESSION['userrole'] == 'treasurer') {
                ?>
                        <li <?php if($title=='Team Roster' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/teamroster.php">Team Roster</a>
                        </li>
                        <!--li <?php if($title=='Submit Team Roster' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/submitroster.php">Submit Team Roster</a>
                        </li-->
                        <li <?php if($title=='Add Bowler to Team Roster' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/teamAddBowler.php">Transfer Bowler</a>
                        </li>
                        <li <?php if($title=='Add Bowler' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/addBowler.php">Add Bowler</a>
                        </li>
                        <?php
                    }
                ?>

                        <?php
                    if ($_SESSION['userrole'] == 'bowler' || $_SESSION['userrole'] == 'president' || $_SESSION['userrole'] == 'owner'  || $_SESSION['userrole'] == 'secretary' || $_SESSION['userrole'] == 'vicepresident' || $_SESSION['userrole'] == 'captain' || $_SESSION['userrole'] == 'treasurer') {
                ?>
                        <li <?php if($title=='Personal Details' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/details.php">Personal Details</a>
                        </li>
                        <?php
                    }
                ?>

                        <li <?php if($title=='Settings' ) { echo 'class="active"' ;}?>>
                            <a href="<?=$base_url;?>/dashboard/settings.php">Settings</a>
                        </li>
                        <li>
                            <a href="<?=$base_url;?>/dashboard/logout.php">Logout</a>
                        </li>

                    </ul>

                </div>
            </div>

            <div id="mCSB_1_scrollbar_vertical"
                class="mCSB_scrollTools mCSB_1_scrollbar mCS-minimal mCSB_scrollTools_vertical" style="display: none;">
                <div class="mCSB_draggerContainer">
                    <div id="mCSB_1_dragger_vertical" class="mCSB_dragger"
                        style="position: absolute; min-height: 50px; height: 0px; top: 0px;">
                        <div class="mCSB_dragger_bar" style="line-height: 50px;"></div>
                    </div>
                    <div class="mCSB_draggerRail"></div>
                </div>
            </div>
        </nav>

        <!-- Page Content  -->
        <div id="content">

            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">

                    <button type="button" id="sidebarCollapse" class="navbar-btn">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <svg class="svg-inline--fa fa-align-justify fa-w-14" aria-hidden="true" data-prefix="fas"
                            data-icon="align-justify" role="img" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512" data-fa-i2svg="">
                            <path fill="currentColor"
                                d="M0 84V44c0-8.837 7.163-16 16-16h416c8.837 0 16 7.163 16 16v40c0 8.837-7.163 16-16 16H16c-8.837 0-16-7.163-16-16zm16 144h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16zm0 256h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16zm0-128h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16z">
                            </path>
                        </svg><!-- <i class="fas fa-align-justify"></i> -->
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    Team: <?php echo $_SESSION['team'] ?? '--'; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>