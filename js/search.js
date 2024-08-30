// function searchpage(){
//     var name = $('#name').val();
//     var team = $('#team').val();
//     $.ajax({
//         url: 'pagination/search.php',
//         type: 'GET',
//         data: { name: name, team: team },
//         success: function(response) {
//             console.log('response',response);
//             for (let index = 0; index < response.length; index++) {
//                 // console.log('index',index);
//                 // console.log('value',response[index]['bowlerid']);

//                 // response[index]['bowlerid']
//                 var numberValue;
//                 var newIndex = index + 1;
//                 if (newIndex % 2 == 0) { numberValue =  'even'; } else { numberValue =  'odd';}
//                 var html = '<div class="row info" id="'+newIndex+'" data-bowler="'+response[index]['bowlerid']+'">';
//                 html +='<div class="col-12 col-md-2">';
//                 html += '<div class="name bowlerInfo">';
//                 html += '<span class="title">Player name</span>';
//                 html += '<h4>'+ response[index]['name']+'</h4>';
//                 html += '</div>';
//                 html += '</div>';

//                 html +='<div class="col-12 col-md-2">';
//                 html += '<div class="name bowlerInfo">';
//                 html += '<span class="title">Nickname</span>';
//                 html += '<h4>'+ response[index]['nickname1']+'</h4>';
//                 html += '</div>';
//                 html += '</div>';

//                 html +='<div class="col-12 col-md-2">';
//                 html += '<div class="name bowlerInfo">';
//                 html += '<span class="title">Team name</span>';
//                 html += '<h4>'+ response[index]['team']+'</h4>';
//                 html += '</div>';
//                 html += '</div>';

//                 html +='<div class="col-12 col-md-2">';
//                 html += '<div class="name bowlerInfo">';
//                 html += '<span class="title">ID #</span>';
//                 html += '<h4>'+ response[index]['bowlerid']+'</h4>';
//                 html += '</div>';
//                 html += '</div>';

//                 html +='<div class="col-12 col-md-2">';
//                 html += '<div class="name bowlerInfo">';
//                 html += '<span class="title">Current Tour game #</span>';
//                 html += '<h4>'+ response[index]['total_tour_game']+'</h4>';
//                 html += '</div>';
//                 html += '</div>';

//                 html +='<div class="col-12 col-md-2">';
//                 html += '<div class="name bowlerInfo">';
//                 html += '<span class="title">Current Event game #</span>';
//                 html += '<h4>'+ response[index]['event_game']+'</h4>';
//                 html += '</div>';
//                 html += '</div>';

//                 html += '<div class="row stats '+ numberValue +'" id="stats'+ newIndex +'" style="display: none;">';
//                 html += '<div class="col-12 col-md-12">';
//                 html += '<div class="bowlerStats">';
//                 html += '</div>';
//                 html += '</div>';
//                 html += '</div>';

//                 html += '</div>';
//                 $('.singlePlayer').append(html);
//             }
//         },
//         error: function() {
//             console.log('Error loading the current event game.');
//         }
//     });
// }

$(document).ready(function () {
  $(".stats").hide();
});

var allGames;

function toggleBStats() {
  var div = $(this);

  $(".bowlerStats").empty();

  var divNum = div.attr("id");
  var bowlID = div.attr("data-bowler");

  var bowlerBox = "#stats" + divNum + " .bowlerStats ";

  var allGames;

  var formData = {
    bowlID: bowlID,
  };
  var enterAvg = 0;
  // process the form
  $.ajax({
    type: "POST", // define the type of HTTP verb we want to use (POST for our form)
    url: base_url+"/dashboard/ubaAvg.php", // the url where we want to POST
    data: formData, // our data object
    dataType: "json", // what type of data do we expect back from the server
    encode: true,
  })
    // using the done promise callback
    .done(function (data) {
      // console.log(data);
      var ubaAvg;
      var enterAvg;
      var stAvg;

      if (isNaN(data["ubaAvg"])) {
        ubaAvg = "-";
      } else {
        ubaAvg = data["ubaAvg"];
      }

      if (isNaN(data["enterAvg"])) {
        enterAvg = "-";
      } else {
        enterAvg = data["enterAvg"];
      }
      if (isNaN(data["stAvg"])) {
        stAvg = "-";
      } else {
        stAvg = data["stAvg"];
      }

      $(bowlerBox).empty();
      $(".showubaavrg").hide();
      $(bowlerBox).append(
        `<div class="card-header season" data-bowler="` +
          bowlID +
          `" data-event="2018/19" id="deux" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" 
                aria-expanded="true" aria-controls="collapseOne">
                  <h5 class="mb-0 text-center"> Season Tour  <i class="fa float-right" aria-hidden="true"></i> </h5>  </div>
                <div id="collapseOne" class="collapse " aria-labelledby="deux" data-parent="#accordion">
                  <div class="gamesList">
                  
                  </div>
                </div>`
      );

      $(bowlerBox).append(
        `<div class="card-header events" id="tre" class="btn btn-link collapsed" data-bowler="` +
          bowlID +
          `" data-event="events"  data-toggle="collapse" data-target="#collapseTwo" 
                aria-expanded="false" aria-controls="collapseTwo">
                  <h5 class="mb-0 text-center"> Events  <i class="fa float-right" aria-hidden="true"> </i></h5> 
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="tre" data-parent="#accordion">
                    <div class="eventgamesList">
                    
                    </div>
                </div>`
      );

      // $(bowlerBox).append('<ul class="headings season" data-bowler="'+bowlID+'" data-event="2018/19" id="deux"><li> Season Tour</li><li class="gameIcon"><span class="downBtn"><i class="fas fa-sort-down"></i></span><span class="upBtn"><i class="fas fa-sort-up"></i></span></li></ul>');
      // // $(bowlerBox).append('<ul class="headings season" data-bowler="'+bowlID+'" data-event="2019/20" id="deux"><li>19/20 Season Tour</li><li class="gameIcon"><span class="downBtn"><i class="fas fa-sort-down"></i></span><span class="upBtn"><i class="fas fa-sort-up"></i></span></li></ul>');
      // $(bowlerBox).append('<ul class="headings events" data-bowler="'+bowlID+'" data-event="events" id="tre"><li>Events</li><li class="gameIcon"><span class="downBtn"><i class="fas fa-sort-down"></i></span><span class="upBtn"><i class="fas fa-sort-up"></i></span></li></ul>');

      var sessionAvrgData = data["sessionAvrg"];
      var crntYear = new Date().getFullYear();
      var nextYear = new Date().getFullYear() + 1;
      // var sectValue1 = new Date('09/'+'01'+ '/' + (crntYear-1)); var sectValue2 = new Date('09/'+'01'+ '/' + (nextYear-1));

      var sectValue1 = new Date("09/" + "01" + "/" + "2024");
      var sectValue2 = new Date("09/" + "01" + "/" + "2025");
      var totalval = 0;
      var sessgameCount = [];
      var nidexx = 1;
      var avrg = "";
      var allSessionGames = [];
      for (let index = 0; index < sessionAvrgData.length; index++) {
        var checkDate = new Date(sessionAvrgData[index]["eventDate"]);
        if (checkDate <= sectValue2 && checkDate >= sectValue1) {
          if (sessionAvrgData[index]["game 1"] > 1) {
            sessgameCount.push(sessionAvrgData[index]["game 1"]);
          }
          if (sessionAvrgData[index]["game 2"] > 1) {
            sessgameCount.push(sessionAvrgData[index]["game 2"]);
          }

          if (sessionAvrgData[index]["game 3"] > 1) {
            sessgameCount.push(sessionAvrgData[index]["game 3"]);
          }
          totalval =
            parseInt(totalval) + parseInt(sessionAvrgData[index]["pinfall"]);
          // console.log('sessgameCount',sessgameCount);
          if (sessgameCount.length >= 9) {
            avrg = totalval / sessgameCount.length;
            // ubaAvg = ubaAvg;
          }
        }

        if (sessionAvrgData[index]["game 1"] > 1) {
          allSessionGames.push(sessionAvrgData[index]["game 1"]);
        }
        if (sessionAvrgData[index]["game 2"] > 1) {
          allSessionGames.push(sessionAvrgData[index]["game 2"]);
        }

        if (sessionAvrgData[index]["game 3"] > 1) {
          allSessionGames.push(sessionAvrgData[index]["game 3"]);
        }
      }

      var evnetAvrgData = data["eventAvrg"];
      var eventgameCount = [];
      var y = 1;
      for (let y = 0; y < evnetAvrgData.length; y++) {
        if (evnetAvrgData[y]["game 1"] > 1) {
          eventgameCount.push(evnetAvrgData[y]["game 1"]);
        }
        if (evnetAvrgData[y]["game 2"] > 1) {
          eventgameCount.push(evnetAvrgData[y]["game 2"]);
        }

        if (evnetAvrgData[y]["game 3"] > 1) {
          eventgameCount.push(evnetAvrgData[y]["game 3"]);
        }
        if (evnetAvrgData[y]["game 4"] > 1) {
          eventgameCount.push(evnetAvrgData[y]["game 4"]);
        }
        if (evnetAvrgData[y]["game 5"] > 1) {
          eventgameCount.push(evnetAvrgData[y]["game 4"]);
        }
      }

      var totelLength = eventgameCount.length + allSessionGames.length;

      // console.log('totelLength',totelLength);

      if (totelLength >= 9) {
        ubaAvg = ubaAvg;
      } else {
        ubaAvg = "0.00";
      }

      if (avrg == "") {
        avrg = 0;
        // ubaAvg = '0.00';
      }

      console.log("avrg", avrg);
      console.log("sessgameCount", sessgameCount.length);

      if (ubaAvg > enterAvg) {
        $(bowlerBox).append(
          '<ul class="uba" data-bowler="' +
            bowlID +
            '"><li>UBA Average</li><li class="highlight">' +
            ubaAvg +
            "</li><li>Entering Avg</li><li>" +
            enterAvg +
            '</li><li>Season Tour Avg</li><li> <span class="original 1">' +
            avrg.toFixed(2) +
            '</span><span class="fillterAvg"></span></li></ul>'
        );
      } else {
        $(bowlerBox).append(
          '<ul class="uba" data-bowler="' +
            bowlID +
            '"><li>UBA Average</li><li>' +
            ubaAvg +
            '</li><li class="highlight">Entering Avg</li><li>' +
            enterAvg +
            '</li><li>Season Tour Avg</li><li> <span class="original 2">' +
            avrg.toFixed(2) +
            '</span><span class="fillterAvg"></span></li></ul>'
        );
      }

      allSeasonGames = Array.from(document.querySelectorAll(".season"));
      allSeasonGames.forEach((team) =>
        team.addEventListener("click", toggleSStats)
      );

      allEventsGames = Array.from(document.querySelectorAll(".events"));
      allEventsGames.forEach((team) =>
        team.addEventListener("click", toggleEStats)
      );
    });

  function toggleSStats() {
    var div = $(this);
    var elID = div.attr("id");
    var type = "session";
    var eventType = div.attr("data-event");
    var formData = {
      eventType: eventType,
      bowlerID: bowlID,
      sessionType: type,
    };

    $.ajax({
      type: "POST", // define the type of HTTP verb we want to use (POST for our form)
      url: base_url+"/dashboard/games.php", // the url where we want to POST
      data: formData, // our data object
      dataType: "json", // what type of data do we expect back from the server
      encode: true,
    }).done(function (data) {
      // console.log('dasddasdsa',data);
      if (data.length > 0) {
        var all_session = "";
        all_session +=
          `<div class="games gamesHeading">
                         <div class="sesson-year"><label for="cars">Please select the year:</label>
                              <select name="cars" id="cars" >
                                <option value="allSession">All Session</option>
                                <option value="2024/25">2024/25</option>
                                <option value="2023/24">2023/24</option>
                                <option value="2022/23">2022/23</option>
                                <option value="2021/22">2021/22</option>
                                <option value="2020/21">2020/21</option>
                                <option value="2019/20">2019/20</option>
                                <option value="2018/19">2018/19</option>
                              </select> 
                              <input type="button" value="Submit" onclick="myyear('` +
          bowlID +
          `','cars')" />
                           </div>
                         
                         <table class="table table-striped" id="sessionTable" >
                        <thead><tr><th>Sn No</th><th>Tour Stop</th><th>Location</th><th>Team</th><th>Date</th><th>Game 1</th><th>Game 2</th>
                        <th>Game 3</th><th>Total</th></tr></thead><tbody>`;
        var nidexx = 1;
        data = data.sort().reverse();
        for (let index = 0; index < data.length; index++) {
          var tdlist = "";
          if (nidexx < 10) {
            var numbrrr = "0" + nidexx;
          } else {
            var numbrrr = nidexx;
          }
          if (numbrrr % 2 == 0) {
            tdlist = "evenn";
          } else {
            tdlist = "oddd";
          }
          all_session +=
            `<tr class="session ` +
            tdlist +
            `"><td>` +
            numbrrr +
            `</td><td>` +
            data[index]["tourStop"] +
            `</td><td>` +
            data[index]["location"] +
            `</td>
                                <td>` +
            data[index]["team"] +
            `</td><td>` +
            data[index]["eventDate"] +
            `</td>
                                <td>` +
            data[index]["game 1"] +
            `</td><td>` +
            data[index]["game 2"] +
            `</td>
                                <td>` +
            data[index]["game 3"] +
            `</td><td>` +
            data[index]["pinfall"] +
            `</td></tr>`;
          nidexx++;
        }
        all_session += `</tbody></table></div>`;

        $(".gamesList").html(all_session);
      } else {
        $(".gamesList")
          .html(`<div class="games gamesHeading"><table class="table table-striped "><tbody>
                        <tr class="odd"><td valign="top" colspan="9" style="font-size: 17px;font-weight: bold;text-align: center">
                        No  Data </td></tr></tbody></table></div>`);
      }
    });
  }

  function toggleEStats() {
    var div = $(this);
    var elID = div.attr("id");

    var eventType = div.attr("data-event");
    var formData = {
      eventType: eventType,
      bowlerID: bowlID,
    };

    $.ajax({
      type: "POST", // define the type of HTTP verb we want to use (POST for our form)
      url: base_url+"/dashboard/games.php", // the url where we want to POST
      data: formData, // our data object
      dataType: "json", // what type of data do we expect back from the server
      encode: true,
    })
    .done(function (data) {
      if (data.length > 0) {
        var all_event = "";
        all_event += `<div class="games gamesHeading"><table class="table table-striped ">
                            <thead><tr><th>Sn No</th><th style="width: 27%;">Tournament</th><th>Date</th><th>Game 1</th><th>Game 2</th>
                            <th>Game 3</th><th>Game 4</th> <th>Game 5</th><th>Total</th></tr></thead><tbody>`;

        var evntnumm = 1;
        for (let index = 0; index < data.length; index++) {
          var evntlist = "";
          if (evntnumm < 10) {
            var enumbrrr = "0" + evntnumm;
          } else {
            var enumbrrr = evntnumm;
          }
          if (evntnumm % 2 == 0) {
            tdlist = "evenn";
          } else {
            tdlist = "oddd";
          }

          all_event +=
            `<tr class="session ` +
            tdlist +
            `"><td>` +
            enumbrrr +
            `</td><td>` +
            data[index]["event"] +
            `</td>
                                <td>` +
            data[index]["eventDate"] +
            `</td>
                                <td>` +
            data[index]["game 1"] +
            `</td><td>` +
            data[index]["game 2"] +
            `</td>
                                <td>` +
            data[index]["game 3"] +
            `</td><td>` +
            data[index]["game 4"] +
            `</td>
                                <td>` +
            data[index]["game 5"] +
            `</td><td>` +
            data[index]["pinfall"] +
            `</td></tr>`;
          evntnumm++;
        }
        all_event += `</tbody></table></div>`;

        $(".eventgamesList").html(all_event);
      } else {
        $(".eventgamesList")
          .html(`<div class="games gamesHeading"><table class="table table-striped "><tbody>
                        <tr class="odd"><td valign="top" colspan="9" style="font-size: 17px;font-weight: bold;text-align: center">
                        No  Data </td></tr> </tbody></table></div>`);
      }
    });
  }

  var target = "#stats" + divNum;
  $(target).toggle();
}

function toggleTStats() {
    console.log('innnnnn-iinnn');
    $(".teamlist").html(`<span style="font-size: 17px;font-weight: bold;text-align: center;margin-left:600px"><img src="`+base_url+`/images/loading-screen-loading.gif" class="loader alt="loder"></span>`);
    // teamlist
  var div = $(this);
  var divNum = div.attr("id");
  var teamID = div.attr("data-team");
  var formData = {
    teamID: teamID,
  };
//   console.log('formData',formData);
  // process the form
  $.ajax({
    type: "POST",
    url: base_url+"/dashboard/team.php",
    data: formData,
    dataType: "json",
    encode: true,
  })
    // using the done promise callback
    .done(function (data) {
      console.log(data);
      $(".teamlist").empty();

      var j = 1;

      for (let index = 0; index < data.length; index++) {
        var bowlerName = data[index]["name"];
        var teamName = data[index]["team"];
        var bowlerID = data[index]["bowlerID"];
        var nickname = data[index]["nickname"];

        var tourGameCount = data[index]["tour_game_count"];
        var eventCount = data[index]["event_count"];


        var style;
        if (j % 2 == 0) {
          style = "even";
        } else {
          style = "odd";
        }

        var boxID = "#" + j;
        var indBox = ".player" + j;

        $(".teamlist").append(
          '<div class="singlePlayer player' +
            j +
            '"><div class="row info deets" id="' +
            j +
            '" data-bowler="' +
            bowlerID +
            '"></div></div>'
        );

        $(boxID).append(
          '<div class="col-12 col-md-2"><div class="name bowlerInfo"><span class="title">Player name</span><h4>' +
            bowlerName.toLowerCase() +
            "</h4></div></div>"
        );
        $(boxID).append(
          '<div class="col-12 col-md-2"><div class="name bowlerInfo"><span class="title">Nickname</span><h4>' +
            nickname +
            "</h4></div></div>"
        );
        $(boxID).append(
          '<div class="col-12 col-md-2"><div class="name bowlerInfo"><span class="title">Team name</span><h4>' +
            teamName +
            "</h4></div></div>"
        );
        $(boxID).append(
          '<div class="col-12 col-md-2"><div class="name bowlerInfo"><span class="title">ID #</span><h4>' +
            bowlerID +
            "</h4></div></div>"
        );
        $(boxID).append(
            '<div class="col-12 col-md-2"><div class="name bowlerInfo"><span class="title">Current Tour game #</span><h4>' +
            tourGameCount +
              "</h4></div></div>"
        );

        $(boxID).append(
        '<div class="col-12 col-md-2"><div class="name bowlerInfo"><span class="title">Current Event game #</span><h4>' +
        eventCount +
            "</h4></div></div>"
        );
        $(indBox).append(
          '<div class="row stats ' +
            style +
            '" id="stats' +
            j +
            '"><div class="col-12 col-md-12"><div class="bowlerStats"></div></div></div>'
        );

        j += 1;
      }
    });

  $(".teamlist").toggle();

  $(".teamlist").on("click", ".deets", toggleBStats);
}

var allBowlers = Array.from(document.querySelectorAll(".info"));
allBowlers.forEach((bowler) => bowler.addEventListener("click", toggleBStats));

var allTeams = Array.from(document.querySelectorAll(".teaminfo"));
allTeams.forEach((team) => team.addEventListener("click", toggleTStats));

function myyear(bowlerid, selectval) {
  console.log("yessss");
  $("#sessionTable tbody").html(
    `<tr class="odd loderclose"><td valign="top" colspan="9" style="font-size: 17px;font-weight: bold;text-align: center">
                <img src="`+base_url+`/images/loading-screen-loading.gif" class="loader alt="loder"> </td></tr>`
  );

  //console.log('bowlerid',bowlerid);
  // alert(bowlerid);
  var selectval = $("#" + selectval).val();
  var formData = {
    eventType: selectval,
    bowlerID: bowlerid,
    sessionType: "sessionFilter",
  };

  $.ajax({
    type: "POST", // define the type of HTTP verb we want to use (POST for our form)
    url: base_url+"/dashboard/games.php", // the url where we want to POST
    data: formData, // our data object
    dataType: "json", // what type of data do we expect back from the server
    encode: true,
  }).done(function (data) {
    $(".loderclose").hide();
    var crntYear = selectval.split("/");
    var crntMonth = new Date().getMonth();

    if (data.length > 0) {
      if (selectval == "allSession") {
        console.log("allSession");

        var all_session = "";
        var nidexx = 1;
        var totalval = 0;
        var avrg = "0.00";
        var gameCount = [];
        data = data.sort().reverse();

        var crntYear = new Date().getFullYear();
        var nextYear = new Date().getFullYear() + 1;
        var sectValue1 = new Date("09/" + "01" + "/" + crntYear);
        var sectValue2 = new Date("09/" + "01" + "/" + nextYear);
        for (let index = 0; index < data.length; index++) {
          var checkDate = new Date(data[index]["eventDate"]);

          // if (checkDate <= sectValue2 && checkDate >= sectValue1){
          if (data[index]["game 1"] > 1) {
            gameCount.push(data[index]["game 1"]);
          }
          if (data[index]["game 2"] > 1) {
            gameCount.push(data[index]["game 1"]);
          }

          if (data[index]["game 3"] > 1) {
            gameCount.push(data[index]["game 1"]);
          }

          totalval = parseInt(totalval) + parseInt(data[index]["pinfall"]);
          if (checkDate <= sectValue2 && checkDate >= sectValue1) {
            if (gameCount.length >= 9) {
              avrg = totalval / gameCount.length;
            } else {
              avrg = parseInt(0.0);
            }
          } else {
            avrg = parseInt(0.0);
          }
          // }

          var tdlist = "";
          if (nidexx < 10) {
            var numbrrr = "0" + nidexx;
          } else {
            var numbrrr = nidexx;
          }
          if (numbrrr % 2 == 0) {
            tdlist = "evenn";
          } else {
            tdlist = "oddd";
          }
          all_session +=
            `<tr class="session ` +
            tdlist +
            `"><td>` +
            numbrrr +
            `</td><td>` +
            data[index]["tourStop"] +
            `</td><td>` +
            data[index]["location"] +
            `</td>
                        <td>` +
            data[index]["team"] +
            `</td><td>` +
            data[index]["eventDate"] +
            `</td>
                        <td>` +
            data[index]["game 1"] +
            `</td><td>` +
            data[index]["game 2"] +
            `</td>
                        <td>` +
            data[index]["game 3"] +
            `</td><td>` +
            data[index]["pinfall"] +
            `</td></tr>`;
          nidexx++;
        }
        $(".original").html(avrg.toFixed(2));
        $(".original").show();
        $(".fillterAvg").hide();
      } else {
        var all_session = "";
        var nidexx = 1;
        var sectValue1 = new Date("09/" + "01" + "/" + "2024");
        var sectValue2 = new Date("09/" + "01" + "/" + "2025");
        var gameCount = [];
        var totalval = 0;
        var avrg = "";
        data = data.sort().reverse();
        for (let index = 0; index < data.length; index++) {
          if (data[index]["game 1"] > 1) {
            gameCount.push(data[index]["game 1"]);
          }
          if (data[index]["game 2"] > 1) {
            gameCount.push(data[index]["game 1"]);
          }

          if (data[index]["game 3"] > 1) {
            gameCount.push(data[index]["game 1"]);
          }

          totalval = parseInt(totalval) + parseInt(data[index]["pinfall"]);
          var checkDate = new Date(data[index]["eventDate"]);

          if (checkDate <= sectValue2 && checkDate >= sectValue1) {
            if (gameCount.length >= 9) {
              avrg = totalval / gameCount.length;
              $(".original").html(avrg.toFixed(2));
              $(".original").show();
              $(".fillterAvg").hide();
            } else {
              $(".original").hide();
              $(".fillterAvg").html("0.00");
              $(".fillterAvg").show();
            }
          } else {
            $(".original").hide();
            $(".fillterAvg").html("0.00");
            $(".fillterAvg").show();
          }

          var tdlist = "";
          if (nidexx < 9) {
            var numbrrr = "0" + nidexx;
          } else {
            var numbrrr = nidexx;
          }
          if (numbrrr % 2 == 0) {
            tdlist = "evenn";
          } else {
            tdlist = "oddd";
          }
          all_session +=
            `<tr class="session ` +
            tdlist +
            `"><td>` +
            numbrrr +
            `</td><td>` +
            data[index]["tourStop"] +
            `</td><td>` +
            data[index]["location"] +
            `</td>
                            <td>` +
            data[index]["team"] +
            `</td><td>` +
            data[index]["eventDate"] +
            `</td>
                            <td>` +
            data[index]["game 1"] +
            `</td><td>` +
            data[index]["game 2"] +
            `</td>
                            <td>` +
            data[index]["game 3"] +
            `</td><td>` +
            data[index]["pinfall"] +
            `</td></tr>`;
          nidexx++;
        }
      }

      $("#sessionTable tbody").html(all_session);
    } else {
      $(".original").hide();
      $(".fillterAvg").html("0.00");
      $(".fillterAvg").show();

      $("#sessionTable tbody")
        .html(`<tr class="odd"><td valign="top" colspan="9" style="font-size: 17px;font-weight: bold;text-align: center">
                No  Data </td></tr>`);
    }
  });
}
