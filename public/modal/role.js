$(document).ready(function () {
    if ($('#myTable').length) {
        new DataTable('#myTable', {
            pageLength: 25,
        });
    }
});
