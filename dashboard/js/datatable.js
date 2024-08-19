// dashboard/rsbowler.php file datatable
$("#released_bowlers").DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "pagination/rsbowler.php", // Replace with the correct PHP file path
    type: "GET",
  },
  columns: [
    { data: "no", orderable: false },
    { data: "uba_id", orderable: false },
    { data: "name", orderable: false },
    { data: "team", orderable: false },
    { data: "date_submitted", orderable: false },
    { data: "removed_by", orderable: false },
    { data: "current_status", orderable: false },
    { data: "eligible_date", orderable: false },
    ...(userRole === 'admin' ? [{ data: 'reinstate', orderable: false, searchable: false }] : [])
  ],
  dom: "lBfrtip",
  buttons: [
    {
      extend: "csv",
      text: "Export CSV", // Add CSS class for button styling
    },
    {
      extend: "excel",
      text: "Export Excel", // Add CSS class for button styling
    },
    {
      extend: "pdf",
      text: "Export PDF", // Add CSS class for button styling
    },
  ],
  order: [[0, "asc"]], // Order by the first column (No.)
  pageLength: 10, // Number of records to display on each page
  
});

// dashboard/roster.php file database
$("#teamRoster").DataTable({
    processing: true,
    serverSide: true,
    // ajax: {
    //   url: "pagination/rsbowler.php", // Replace with the correct PHP file path
    //   type: "GET",
    // },
    columns: [
      { data: "no", orderable: false },
      { data: "bowlerid", orderable: false },
      { data: "name", orderable: false },
      { data: "nickname1", orderable: false },
      { data: "officeheld", orderable: false },
      { data: "sanction", orderable: false },
      { data: "enteringAvg", orderable: false },
      { data: "ubaAvgseasontourAvg", orderable: false },
      ...(userRole === 'admin' ? [{ data: 'reinstate', orderable: false, searchable: false }] : [])
    ],
    dom: "lBfrtip",
    buttons: [ 
      {
        extend: "csv",
        text: "Export CSV",
      },
      {
        extend: "excel",
        text: "Export Excel",
      },
      {
        extend: "pdf",
        text: "Export PDF",
      },
    ],
    order: [[0, "asc"]],
    pageLength: 10,
    
  });





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
