$(document).ready(function () {
    if ($('#myTable').length) {
        new DataTable('#myTable', {
            paging: false,
            ordering: false,
        });
    }
});
