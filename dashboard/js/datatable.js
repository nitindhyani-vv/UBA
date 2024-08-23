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
  $("#team_official").DataTable({
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
    buttons: [],
    order: [[0, "asc"]],
    pageLength: 10,
  });

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
  window.location.href = filePath + queryString;
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
const labelElement = document.querySelector(".uba-table .dataTables_wrapper .dataTables_length label");
const selectElement = document.querySelector(".uba-table .dataTables_wrapper .dataTables_length select");
const searchElement = document.querySelector(".uba-table .dataTables_wrapper .dataTables_filter label");
const searchInputElement = document.querySelector(".uba-table .dataTables_wrapper .dataTables_filter input");

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