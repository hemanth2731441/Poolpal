// Call the dataTables jQuery plugin
$(document).ready(function() {
  $('#dataTable').DataTable({
    "order": [[0, "asc"]],
    "pageLength": 10,
    "responsive": true,
    "language": {
      "lengthMenu": "Show _MENU_ entries per page",
      "zeroRecords": "No records found",
      "info": "Showing page _PAGE_ of _PAGES_",
      "infoEmpty": "No records available",
      "infoFiltered": "(filtered from _MAX_ total records)"
    }
  });
}); 