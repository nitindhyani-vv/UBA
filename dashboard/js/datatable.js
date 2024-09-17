// $(document).ready(function () {

// dashboard/rsbowler.php file datatable
$("#released_bowlers").DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "pagination/rsbowler.php", // Replace with the correct PHP file path
    type: "GET",
  },
  columns: [
    { data: "no", orderable: true },
    { data: "uba_id", orderable: true },
    { data: "name", orderable: true },
    { data: "team", orderable: true },
    { data: "date_submitted", orderable: true },
    { data: "removed_by", orderable: true },
    { data: "current_status", orderable: true },
    { data: "eligible_date", orderable: true },
    ...(userRole === 'admin' ? [{ data: 'reinstate', orderable: false, searchable: false }] : [])
  ],
  dom: "lBfrtip",
  buttons: [
    {
      extend: "csv",
      text: "Export CSV",
      action: function(e, dt, button, config) {
        exportData(dt,'csv',"pagination/rsbowler.php?");
      }
    },
    {
      extend: "excel",
      text: "Export Excel",
      action: function(e, dt, button, config) {
        exportData(dt,"excel","pagination/rsbowler.php?");

      }
    },
    {
      extend: "pdf",
      text: "Export PDF",
      action: function(e, dt, button, config) {
        exportData(dt,"pdf","pagination/rsbowler.php?");
      }
    }
  ],
  order: [[0, "asc"]], // Order by the first column (No.)
  pageLength: 10, // Number of records to display on each page
  
});

// dashboard/roster.php file database team Roster
$("#team_roster").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/bowler-roster.php", // Replace with the correct PHP file path
      type: "POST",
      data: function (d) {
        d.teamSelected = $('#teamSelected').val();; // Sending the value "testing" with the key "testKey"
        return d;
      },
      complete: function(xhr, status,dt) {
        completeExportData(xhr)
      }
    },
    columns: [
      { data: "no", orderable: true },
      { data: "bowlerid", orderable: true },
      { data: "name", orderable: true },
      { data: "nickname1", orderable: true },
      { data: "officeheld", orderable: true },
      { data: "sanction", orderable: true },
      { data: "enteringAvg", orderable: true },
      { data: "ubaAvg", orderable: true },
      { data: "ubaAvgseasontourAvg", orderable: false },
      ...(userRole === 'admin' ? [{ data: 'reinstate', orderable: false, searchable: false }] : [])
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
          bowlerRosterexportData(dt, 'csv', "pagination/bowler-roster.php")
          setTimeout(function() {
            dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
          }, 4000);
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
          bowlerRosterexportData(dt,"excel","pagination/bowler-roster.php");
          setTimeout(function() {
            dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
          }, 4000);
        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
          bowlerRosterexportData(dt,"pdf","pagination/bowler-roster.php");
          setTimeout(function() {
            dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
          }, 4000);
        }
      }
    ],
    order: [[0, "asc"]],
    pageLength: 10,
    
  });

// dashboard/roster.php file division Roster
$("#division_roster").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/bowler-roster.php", // Replace with the correct PHP file path
      type: "POST",
      data: function (d) {
        d.divisionSelected = $('#divisionSelected').val();; // Sending the value "testing" with the key "testKey"
        return d;
      }
    },
    columns: [
      { data: "no", orderable: true },
      { data: "bowlerid", orderable: true },
      { data: "name", orderable: true },
      { data: "nickname1", orderable: true },
      { data: "team", orderable: true },
      { data: "sanction", orderable: true },
      { data: "enteringAvg", orderable: true },
      { data: "ubaAvg", orderable: true },
      { data: "ubaAvgseasontourAvg", orderable: false },
      ...(userRole === 'admin' ? [{ data: 'reinstate', orderable: false, searchable: false }] : [])
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
          bowlerRosterexportData(dt,'csv',"pagination/bowler-roster.php");
          setTimeout(function() {
            dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
          }, 4000);
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
          bowlerRosterexportData(dt,"excel","pagination/bowler-roster.php");
          setTimeout(function() {
            dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
          }, 4000);
        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
          bowlerRosterexportData(dt,"pdf","pagination/bowler-roster.php");
          setTimeout(function() {
            dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
          }, 4000);
        }
      }
    ],
    order: [[4, "desc"]],
    pageLength: 10,
    
  });

   // dashboard/submittedroster.php file submitted roster
 $("#submittedrosters").DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "pagination/submitted-rosters.php", // Replace with the correct PHP file path
    type: "GET",
  },
  columns: [
    { data: "no", orderable: true },
    { data: "team", orderable: true },
    { data: "rosterSubmitted", orderable: true },
    { data: "submittedBy", orderable: true },
    { data: "submittedOn", orderable: true }
  ],
  dom: "lBfrtip",
  buttons: [ 
    {
      extend: "csv",
      text: "Export CSV",
      action: function(e, dt, button, config) {
        exportData(dt,'csv',"pagination/submitted-rosters.php?");
      }
    },
    {
      extend: "excel",
      text: "Export Excel",
      action: function(e, dt, button, config) {
        exportData(dt,"excel","pagination/submitted-rosters.php?");

      }
    },
    {
      extend: "pdf",
      text: "Export PDF",
      action: function(e, dt, button, config) {
        exportData(dt,"pdf","pagination/submitted-rosters.php?");
      }
    }
  ],
  order: [[4, "desc"]],
  pageLength: 10,
  
});

// dashboard/registrations.php file submitted roster 
$("#registrationTable").DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "pagination/registration-table.php", // Replace with the correct PHP file path
    type: "GET",
  },
  columns: [
    { data: "no", orderable: true },
    { data: "bowlerid", orderable: true },
    { data: "name", orderable: true },
    { data: "team", orderable: true },
    { data: "president", orderable: true },
    { data: "enteringAvg", orderable: true },
    { data: "sanction", orderable: true },
    { data: "verified", orderable: true },
    { data: "reinstate", orderable: false },
    { data: "verifiedUser", orderable: true },
    { data: "resendEmail", orderable: true }
  ],
  dom: "lBfrtip",
  buttons: [ 
    {
      extend: "csv",
      text: "Export CSV",
      action: function(e, dt, button, config) {
        exportData(dt,'csv',"pagination/registration-table.php?");
      }
    },
    {
      extend: "excel",
      text: "Export Excel",
      action: function(e, dt, button, config) {
        exportData(dt,"excel","pagination/registration-table.php?");

      }
    },
    {
      extend: "pdf",
      text: "Export PDF",
      action: function(e, dt, button, config) {
        exportData(dt,"pdf","pagination/registration-table.php?");
      }
    }
  ],
  order: [[0, "asc"]],
  pageLength: 10,
  
});

  // dashboard/submittedroster.php file submitted roster
  $("#teamOfficial").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/team-official.php", // Replace with the correct PHP file path
      type: "GET",
      data: function(d) {
        console.log(d);
      },
      complete: function(xhr, status) {
        completeExportData(xhr)
      }
    },
    columns: [
      { data: "no", orderable: true },
      { data: "team", orderable: true },
      { data: "president", orderable: false},
      { data: "owner", orderable: true },
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/team-official.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          exportData(dt,"excel","pagination/team-official.php?");

        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          exportData(dt,"pdf","pagination/team-official.php?");
        }
      }
    ],
    order: [[0, "desc"]],
    pageLength: 10,
  });

  // dashboard/eventRegistrations.php file
  $("#event_registrations_table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/event-registrations-table.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "event", orderable: true },
      { data: "squad", orderable: true },
      { data: "bowler", orderable: true },
      { data: "payment", orderable: false },
    ],
    dom: "lBfrtip",
    buttons: [],
    order: [[0, "asc"]],
    pageLength: 10,
  });

  // dashboard/users.php file
  $("#uba_users").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/uba-users.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "name", orderable: true },
      { data: "email", orderable: true },
      { data: "role", orderable: true },
      ...(userRole === 'admin' ? [{ data: "edit", orderable: false }] : [])
    ],
    dom: "lBfrtip",
    buttons: [],
    order: [[0, "asc"]],
    pageLength: 10,
  });

// home page pagination
  $("#bowlerAddedByTeamPresedent").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/bowler_addedby_team_presedent.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "bowlerID", orderable: true },
      { data: "name", orderable: true },
      { data: "teamName", orderable: true },
      { data: "nickname", orderable: true },
      { data: "sanction", orderable: true },
      { data: "createAt", orderable: true },
      { data: "approve", orderable: false },
      { data: "decline", orderable: false },
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/bowler_addedby_team_presedent.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
        exportData(dt,"excel","pagination/bowler_addedby_team_presedent.php?");

        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          exportData(dt,"pdf","pagination/bowler_addedby_team_presedent.php?");
        }
      }
    ],
    order: [[0, "asc"]],
    pageLength: 10,
  });


  $("#releasedBowlersTableHome").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/bowlers_released_by_tema.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "bowler_id", orderable: true },
      { data: "name", orderable: true },
      { data: "released_from", orderable: true },
      { data: "status", orderable: true },
      { data: "date", orderable: true },
      { data: "close", orderable: false },
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/bowlers_released_by_tema.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          exportData(dt,"excel","pagination/bowlers_released_by_tema.php?");

        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          exportData(dt,"pdf","pagination/bowlers_released_by_tema.php?");
        }
      }
    ],
    order: [[0, "desc"]],
    pageLength: 10,
  });

  $("#president_table_home").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/president_request.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "bowler_id", orderable: true },
      { data: "name", orderable: true },
      { data: "team", orderable: true },
      { data: "approve", orderable: false },
      { data: "decline", orderable: false },
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/president_request.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          exportData(dt,"excel","pagination/president_request.php?");

        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          exportData(dt,"pdf","pagination/president_request.php?");
        }
      }
    ],
    order: [[0, "desc"]],
    pageLength: 10,
  });


  $("#ownerTableHome").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/owner_request.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "bowler_id", orderable: true },
      { data: "name", orderable: true },
      { data: "team", orderable: true },
      { data: "approve", orderable: false },
      { data: "decline", orderable: false },
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/owner_request.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          exportData(dt,"excel","pagination/owner_request.php?");

        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          exportData(dt,"pdf","pagination/owner_request.php?");
        }
      }
    ],
    order: [[0, "desc"]],
    pageLength: 10,
  });


  $("#transferTableHome").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/bowlers_transfer.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "no", orderable: true },
      { data: "requested_by", orderable: true },
      { data: "bowler", orderable: true },
      { data: "bowler_id", orderable: true },
      { data: "from", orderable: true },
      { data: "to", orderable: true },
      { data: "date_time", orderable: true },
      { data: "approve", orderable: false },
      { data: "decline", orderable: false },
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/bowlers_transfer.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          exportData(dt,"excel","pagination/bowlers_transfer.php?");

        }
      },
      {
        extend: "pdf",
        text: "Export PDF",
        action: function(e, dt, button, config) {
          exportData(dt,"pdf","pagination/bowlers_transfer.php?");
        }
      }
    ],
    order: [[0, "desc"]],
    pageLength: 10,
  });

  $(document).ready(function() {
    // Function to initialize DataTable and reload data
    function loadSeasonTourTable(selectedYear) {
      $("#homeSeasonsTourHome").DataTable({
        processing: true,
        serverSide: true,
        destroy: true, // This ensures the table is reinitialized
        ajax: {
          url: "pagination/homeSeasonTour.php", // Replace with the correct PHP file path
          type: "POST",
          data: function(d) {
            var sessionYear = $('#seasonYear1').val();
            d.seasonYear = selectedYear || sessionYear; // Send selected year to server
          }
        },
        columns: [
          { data: "no", orderable: true },
          { data: "date", orderable: true },
          { data: "year", orderable: true },
          { data: "event_name", orderable: true },
          { data: "event_type", orderable: true },
          { data: "team", orderable: true },
          { data: "game1", orderable: true },
          { data: "game2", orderable: false },
          { data: "game3", orderable: false },
        ],
        dom: "lBfrtip",
        buttons: [ 
          {
            extend: "csv",
            text: "Export CSV",
            action: function(e, dt, button, config) {
              dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
              bowlerRosterexportData(dt,'csv',"pagination/homeSeasonTour.php");
              setTimeout(function() {
                dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
              }, 4000);
            }
          },
          {
            extend: "excel",
            text: "Export Excel",
            action: function(e, dt, button, config) {
              dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
              bowlerRosterexportData(dt,"excel","pagination/homeSeasonTour.php");
              setTimeout(function() {
                dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
              }, 4000);
            }
          },
          {
            extend: "pdf",
            text: "Export PDF",
            action: function(e, dt, button, config) {
              dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
              bowlerRosterexportData(dt,"pdf","pagination/homeSeasonTour.php");
              setTimeout(function() {
                dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
              }, 4000);
            }
          }
        ],
        order: [[0, "desc"]],
        pageLength: 10,
      });
    }
  
    loadSeasonTourTable();
  
    $('#homeSeasonsTour').on('click', function() {
      var sessionYear = $('#seasonYear1').val();
      loadSeasonTourTable(sessionYear);
    });


    function loadEventTable(selectedYear) {
      $("#homeEventHome").DataTable({
        processing: true,
        serverSide: true,
        destroy: true, // This ensures the table is reinitialized
        ajax: {
          url: "pagination/homeEvent.php", // Replace with the correct PHP file path
          type: "POST",
          data: function(d) {
            var sessionYear = $('#seasonYear2').val();
            d.seasonYear = selectedYear || sessionYear; // Send selected year to server
          }
        },
        columns: [
          { data: "no", orderable: true },
          { data: "date", orderable: true },
          { data: "year", orderable: true },
          { data: "event_name", orderable: true },
          { data: "event_type", orderable: true },
          { data: "team", orderable: true },
          { data: "game1", orderable: true },
          { data: "game2", orderable: false },
          { data: "game3", orderable: false },
          { data: "game4", orderable: false },
          { data: "game5", orderable: false },
        ],
        dom: "lBfrtip",
        buttons: [ 
          {
            extend: "csv",
            text: "Export CSV",
            action: function(e, dt, button, config) {
              dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
              bowlerRosterexportData(dt,'csv',"pagination/homeEvent.php");
              setTimeout(function() {
                dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
              }, 4000);
            }
          },
          {
            extend: "excel",
            text: "Export Excel",
            action: function(e, dt, button, config) {
              dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
              bowlerRosterexportData(dt,"excel","pagination/homeEvent.php");
              setTimeout(function() {
                dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
              }, 4000);
            }
          },
          {
            extend: "pdf",
            text: "Export PDF",
            action: function(e, dt, button, config) {
              dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], true); 
              bowlerRosterexportData(dt,"pdf","pagination/homeEvent.php");
              setTimeout(function() {
                dt.settings()[0].oApi._fnProcessingDisplay(dt.settings()[0], false);
              }, 4000);
            }
          }
        ],
        order: [[0, "desc"]],
        pageLength: 10,
      });
    }

    loadEventTable();

    $('#homeEvent').on('click', function() {
      var sessionYear = $('#seasonYear2').val();
      loadSeasonTourTable(sessionYear);
    });

  });


  $("#teamRosterTwo").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/teamroster-table.php", // Replace with the correct PHP file path
      type: "GET",
    },
    columns: [
      { data: "uba_id", orderable: true },
      { data: "name", orderable: true },
      { data: "nickname", orderable: true },
      { data: "sanction_number", orderable: true },
      { data: "entering_avg", orderable: true },
      { data: "uba_average", orderable: true },
      { data: "season_tour_avg", orderable: true },
      { data: "office_held", orderable: true },
      { data: "edit", orderable: true },
    ],
    dom: "lBfrtip",
    buttons: [
      {
        extend: "csv",
        text: "Export CSV",
        action: function(e, dt, button, config) {
          exportData(dt,'csv',"pagination/teamroster-table.php?");
        }
      },
      {
        extend: "excel",
        text: "Export Excel",
        action: function(e, dt, button, config) {
          exportData(dt,"excel","pagination/teamroster-table.php?");
  
        }
      }
    ],
    order: [[0, "asc"]],
    pageLength: 10, 
  });

  function searchBowler(){
    var bowlerName = $('#bowlerName').val();
    independentBowler(bowlerName);
    $('#searchBowlerSection').hide();
    $('#tableBowlerSection').show();
  }
  $('#tableBowlerSection').hide();
  function resetSearch(){
    $('#searchBowlerSection').show();
    $('#tableBowlerSection').hide();
  }


 function independentBowler(bowlerName){
  var bowlernames = null;
  if(bowlerName){
    bowlernames = bowlerName
  }
  if ($.fn.DataTable.isDataTable('#independentBowlerList')) {
    $('#independentBowlerList').DataTable().clear().destroy();
  }
  console.log('dasdadsada')
  
  $("#independentBowlerList").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "pagination/teamroster-table-bowler.php",
      type: "POST",
      data: function(d) {
        d.bowlerName = bowlernames;
      }
    },
    columns: [
      { data: "no", orderable: true },
      { data: "name", orderable: true },
      { data: "bowler_id", orderable: true },
      { data: "team", orderable: true },
      { data: "add_to_team", orderable: true },
    ],
    dom: "lBfrtip",
    buttons: [],
    order: [[0, "asc"]],
    pageLength: 10, 
  });
 }
  
  

  
  // Reusable function to handle exports using post method
function bowlerRosterexportData(dt, exportType,filePath) {
  var params = dt.ajax.params();
  params.exportType = exportType;
  var searchValue = dt.search();
  params.searchValue = searchValue;
  var form = $('<form></form>').attr('method', 'POST').attr('action', filePath);
  $.each(params, function(key, value) {
    $('<input>').attr('type', 'hidden').attr('name', key).attr('value', value).appendTo(form);
  });
  $(form).appendTo('body').submit().remove();
  return true
}


  // Reusable function to handle exports using get method
function exportData(dt, exportType,filePath) {
  var params = dt.ajax.params();
  params.exportType = exportType;
  var queryString = $.param(params);
  console.log('params',params);
  console.log('queryString',queryString);
  // window.location.href = filePath + queryString;
}


function completeExportData(xhr){
  var exportType = $("button.export-active").data("export-type");
    if (exportType) {
        $("button.export-active").removeClass("export-active");

        // Get the Content-Disposition header to determine filename
        var disposition = xhr.getResponseHeader('Content-Disposition');
        var filename = "exported_file";
        if (disposition && disposition.indexOf('attachment') !== -1) {
            var filenameMatch = disposition.match(/filename[^;=\n]*=(?:'([^']*)'|"([^"]*)"|([^;\n]*))/);
            if (filenameMatch != null && filenameMatch[1]) filename = filenameMatch[1];
            else if (filenameMatch != null && filenameMatch[2]) filename = filenameMatch[2];
            else if (filenameMatch != null && filenameMatch[3]) filename = filenameMatch[3];
        }

        // Determine the MIME type based on exportType
        var mimeType;
        switch (exportType) {
            case 'csv':
                mimeType = 'text/csv';
                break;
            case 'excel':
                mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'pdf':
                mimeType = 'application/pdf';
                break;
            default:
                mimeType = 'application/octet-stream';
                break;
        }

        // Create a blob with the appropriate MIME type
        var blob = new Blob([xhr.responseText], { type: mimeType });
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}


//  hide table some text and add table css
// Select all .uba-table elements on the page
document.addEventListener("DOMContentLoaded", function() {
  const tables = document.querySelectorAll(".uba-table");

  tables.forEach(table => {
      const labelElement = table.querySelector(".dataTables_wrapper .dataTables_length label");
      const selectElement = table.querySelector(".dataTables_wrapper .dataTables_length select");
      const searchElement = table.querySelector(".dataTables_wrapper .dataTables_filter label");
      const searchInputElement = table.querySelector(".dataTables_wrapper .dataTables_filter input");

      if (selectElement) {
          selectElement.style.marginRight = "9px";
          selectElement.style.height = "40px";
      }

      if (searchInputElement) {
          searchInputElement.style.height = "40px";
          searchInputElement.placeholder = "Search";
      }

      if (labelElement) {
          const showText = labelElement.childNodes[0];
          if (showText && showText.nodeType === Node.TEXT_NODE) {
              showText.textContent = "";
          }

          const entriesText = labelElement.childNodes[2];
          if (entriesText && entriesText.nodeType === Node.TEXT_NODE) {
              entriesText.textContent = "";
          }
      }

      if (searchElement) {
          const searchText = searchElement.childNodes[0];
          if (searchText.nodeType === Node.TEXT_NODE) {
              searchText.textContent = "";
          }
      }
  });
});

