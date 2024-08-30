<?php
    include_once '../baseurl.php';
    include_once '../session.php';
    include_once '../connect.php';

    $bowlerName = $_POST['name'] ?? null;
    $bowlerTeam = $_POST['team'] ?? null;
    $searchTerm = '';
    $column = '';

    try {
        $database = new Connection();
        $db = $database->openConnection();

        if ($bowlerName != '') {
            $searchTerm = $bowlerName;
            $column = 'name';
            $sql = $db->prepare("SELECT * FROM `bowlers` WHERE `$column` LIKE '%$searchTerm%' ORDER BY name ASC  ");
            $sql->execute();

        } else {
            $searchTerm = $bowlerTeam;
            $column = 'teamname';
            $sql = $db->prepare("SELECT * FROM `teams` WHERE `$column` LIKE '%$searchTerm%' ORDER by teamname ASC ");
            $sql->execute();
        }

        $dataFetched = $sql->fetchAll();
        
    } catch (PDOException $e) {
        echo "There was some problem with the connection: " . $e->getMessage();
    }
    $nickname1='';
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>UBA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
        integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" media="screen" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="../css/animate.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/styleTwo.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/search.css" />
    <link rel="icon" href="<?=$base_url?>images/favicon.ico" type="image/x-icon" />
</head>

<body>

    <header>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="register">
                        <a href="home.php">Dashboard</a>
                    </div>
                    <div class="logo">
                        <img src="<?=$base_url;?>/images/UBA_logo.png" alt="UBA Logo">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="searchContainer">
                    <h4>Member Search:</h4>
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <label for="name">Name:</label>
                                <input type="text" name="name" id="name" placeholder="First or Last Name">
                            </div>
                            <div class="col-md-6 col-12">
                                <label for="team">Team name:</label>
                                <input type="text" name="team" id="team" placeholder="Team name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <input type="submit" value="Search" class="submitBtn">
                            </div>
                        </div>
                    </form>

                    <?php 
                        if ($bowlerName != '') {
                            $i = 1;
                            foreach ($dataFetched as $player) { 
                    
                            $sql_bowlersreleased = $db->prepare('SELECT currentstatus FROM `bowlersreleased` WHERE bowlerid="'.$player['bowlerid'].'"');
                            $sql_bowlersreleased->execute();
                            $release_fetch = $sql_bowlersreleased->fetch();
                            $check_release_status=$release_fetch['currentstatus'];
                            if($check_release_status=='Suspended')
                            {
                                $player['team']='Released Bowlers';
                            }
                    ?>
                        <div class="singlePlayer">
                            <div class="row info" id="<?php echo $i;?>" data-bowler="<?php echo $player['bowlerid'];?>">
                                <div class="col-12 col-md-2">
                                    <div class="name bowlerInfo">
                                        <span class="title">Player name</span>
                                        <h4>
                                            <?php echo strtolower($player['name']);?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="name bowlerInfo">
                                        <span class="title">Nickname</span>
                                        <h4>
                                            <?php echo $player['nickname1'];?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="name bowlerInfo">
                                        <span class="title">Team name</span>
                                        <h4>
                                            <?php echo $player['team'];?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="name bowlerInfo">
                                        <span class="title">ID #</span>
                                        <h4>
                                            <?php echo $player['bowlerid'];?>
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2">
                                    <div class="name bowlerInfo">
                                        <span class="title">Current Tour game #</span>
                                        <h4 id="currentTour<?php echo $player['bowlerid'];?>">
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2">
                                    <div class="name bowlerInfo">
                                        <span class="title">Current Event game #</span>
                                        <h4 id="currentEvent<?php echo $player['bowlerid'];?>">
                                        </h4>
                                    </div>
                                </div>

                            </div>

                            <div class="row stats <?php if ($i%2 == 0) { echo 'even'; } else { echo 'odd';}?>"
                                id="stats<?php echo $i;?>">
                                <div class="col-12 col-md-12">
                                    <div class="bowlerStats">

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                        $i++;
                            }
                        } elseif($bowlerTeam != '' ) {
                            $j = 1;
                            foreach ($dataFetched as $player) {
                    ?>
                    <div class="mainTeam">
                        <div class="team">
                            <div class="row teaminfo" id="team<?php echo $j;?>"
                                data-team="<?php echo $player['teamname'];?>">
                                <div class="col-12 col-md-3">
                                    <div class="name bowlerInfo">
                                        <span class="title">Team name</span>
                                        <h4>
                                            <?php echo $player['teamname'];?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="name bowlerInfo">
                                        <span class="title">Division</span>
                                        <h4>
                                            <?php echo strtolower($player['division']);?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="name bowlerInfo">
                                        <span class="title">Home House</span>
                                        <h4>
                                            <?php echo strtolower($player['homehouse']);?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="name bowlerInfo">
                                        <span class="title">President</span>
                                        <h4>
                                            <?php echo strtolower($player['president']);?>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="teamlist">

                            </div>

                        </div>
                    </div>
                    <?php
                        $j++;
                            }
                        }
                    ?>

                </div>
            </div>
        </div>
    </div>
    <script> const base_url = '<?=$base_url?>'; </script>
    <script src="../js/jquery.js"></script>
    <script src="../js/popper.js"></script>
    <script src="../js/bootstrap.js"></script>
    <script src="../js/search.js"></script>

    <script>
        var allBowlersDiv = Array.from(document.querySelectorAll(".info"));
        allBowlersDiv.forEach((bowler) => getCount.call(bowler));

        // Define the getCount function
        function getCount() {
            var div = $(this); 
            var divNum = div.attr("id");
            var bowlerId = div.attr("data-bowler");
            $.ajax({
                url: 'pagination/search.php',
                type: 'GET',
                data: { bowler_id: bowlerId,type:'by_name' },
                success: function(response) {
                    console.log('response',response);
                    const keys = Object.keys(response);
                    for (let index = 0; index < keys.length; index++) {
                        const key = keys[index];
                        const value = response[key]; 

                        if (key === 'bowler_id') {
                            $(`#currentTour${value}`).html(response['tour_game_count']);
                            $(`#currentEvent${value}`).html(response['event_count']);
                        }

                    }
                }
            });
        }


        // var byTeamData = Array.from(document.querySelectorAll(".teaminfo"));
        // byTeamData.forEach((team) => byTeamCount.call(team));

        // function byTeamCount(){
        //     var div = $(this); 
        //     var teamID = div.attr("data-team");
        //     console.log('teamID',teamID);

        //     $.ajax({
        //         url: 'pagination/search.php',
        //         type: 'GET',
        //         data: { team_name:teamID,type:'by_team' },
        //         success: function(response) {
        //             console.log('response',response);
        //             // const keys = Object.keys(response);
        //             // for (let index = 0; index < keys.length; index++) {
        //             //     const key = keys[index];
        //             //     const value = response[key]; 

        //             //     if (key === 'bowler_id') {
        //             //         $(`#currentTour${value}`).html(response['tour_game_count']);
        //             //         $(`#currentEvent${value}`).html(response['event_count']);
        //             //     }

        //             // }
        //         }
        //     });
        // }
 </script>
</body>

</html>