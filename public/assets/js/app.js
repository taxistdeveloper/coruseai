$(function () {
    if ($.fn.DataTable && $('.datatable').length) {
        $('.datatable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/ru.json'
            },
            pageLength: 15,
            order: []
        });
    }

    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('show');
    });
});
