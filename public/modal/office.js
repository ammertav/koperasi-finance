$(document).ready(function () {
    if ($('#myTable').length) {
        new DataTable('#myTable', {});
    }

    // ========== Modal Open/Close ==========
    $('#addBtn').click(() => $('#addModal').removeClass('hidden'));
    $('#closeAddModal').click(() => $('#addModal').addClass('hidden'));
    $('#closeModal').click(() => $('#editModal').addClass('hidden'));

    $(window).click((e) => {
        if (e.target === $('#addModal')[0]) $('#addModal').addClass('hidden');
        if (e.target === $('#editModal')[0]) $('#editModal').addClass('hidden');
    });

    // ========== Edit Button ==========
    $(document).on('click', '.editBtn', function () {
        const btn = $(this);
        $('#editCode').val(btn.data('code'));
        $('#editName').val(btn.data('name'));
        $('#editType').val(btn.data('type'));
        $('#editParentId').val(btn.data('parent-id'));
        $('#editAddress').val(btn.data('address'));
        $('#editIsActive').prop('checked', btn.data('is-active') == 1);

        $('#editForm').attr('action', `/office/${btn.data('id')}/update`);
        $('#editModal').removeClass('hidden');
    });
});
