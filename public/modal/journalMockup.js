$(document).ready(function () {
    if ($('#myTable').length) {
        new DataTable('#myTable', { order: [[2, 'desc']] });
    }
});
