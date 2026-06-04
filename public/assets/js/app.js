$(function () {
    function initMobileTables() {
        if (!mobileMq.matches) {
            $('table').removeClass('table-mobile-stack');
            return;
        }
        $('table.datatable, .app-card .table, .table-hover').each(function () {
            var $table = $(this);
            if ($table.closest('.workload-schedule-table, .schedule-grid, .template-form-table').length) {
                return;
            }
            $table.addClass('table-mobile-stack');
            var headers = [];
            $table.find('thead th').each(function () {
                headers.push($(this).text().trim());
            });
            $table.find('tbody tr').each(function () {
                $(this).find('td').each(function (i) {
                    if (headers[i]) {
                        $(this).attr('data-label', headers[i]);
                    }
                });
            });
        });
    }

    var mobileMq = window.matchMedia('(max-width: 991.98px)');

    if ($.fn.DataTable && $('.datatable').length) {
        $('.datatable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/ru.json'
            },
            pageLength: 15,
            order: [],
            initComplete: initMobileTables,
            drawCallback: initMobileTables
        });
    }

    function initBackButton() {
        var $btn = $('#appBackBtn');
        if (!$btn.length || !mobileMq.matches) {
            $btn.addClass('d-none');
            return;
        }
        var homeUrl = document.body.getAttribute('data-home') || '/';
        var homePath = homeUrl;
        try {
            homePath = new URL(homeUrl, window.location.origin).pathname.replace(/\/+$/, '') || '/';
        } catch (e) { /* keep string */ }
        var path = window.location.pathname.replace(/\/+$/, '') || '/';
        var show = path !== homePath && path.indexOf(homePath + '/') === 0;
        if (show) {
            $btn.data('fallback', homeUrl);
            $btn.removeClass('d-none').off('click').on('click', function () {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = $btn.data('fallback') || '/';
                }
            });
        } else {
            $btn.addClass('d-none');
        }
    }

    initMobileTables();
    initBackButton();

    mobileMq.addEventListener('change', function () {
        initMobileTables();
        initBackButton();
    });

    /* Legacy sidebar (desktop-only now; kept for safety) */
    var $sidebar = $('#sidebar');
    var $backdrop = $('#sidebarBackdrop');

    function closeSidebar() {
        $sidebar.removeClass('show');
        $backdrop.removeClass('show');
        document.body.style.overflow = '';
    }

    $('#sidebarToggle').on('click', function () {
        if ($sidebar.hasClass('show')) {
            closeSidebar();
        } else {
            $sidebar.addClass('show');
            $backdrop.addClass('show');
            document.body.style.overflow = 'hidden';
        }
    });

    $backdrop.on('click', closeSidebar);

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    /* Tap feedback on mobile tiles */
    $('.app-tab, .app-sheet-tile, .workload-card').on('touchstart', function () {
        $(this).addClass('is-pressed');
    }).on('touchend touchcancel', function () {
        var el = $(this);
        setTimeout(function () {
            el.removeClass('is-pressed');
        }, 120);
    });
});
