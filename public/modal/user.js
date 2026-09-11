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
        const roleIds = btn.data('roles') || [];

        $('#editName').val(btn.data('name'));
        $('#editEmail').val(btn.data('email'));
        $('#editOfficeId').val(btn.data('office-id'));
        $('#editPassword').val('');
        $('#editIsActive').prop('checked', btn.data('is-active') == 1);

        $('.editRole').each(function () {
            $(this).prop('checked', roleIds.includes(Number($(this).val())));
        });

        $('#editForm').attr('action', `/user/${btn.data('id')}/update`);
        $('#editModal').removeClass('hidden');
    });
});
