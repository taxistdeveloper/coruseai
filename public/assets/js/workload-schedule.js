(function () {
    const form = document.getElementById('workloadScheduleForm');
    const tbody = document.getElementById('schedule-entries-body');
    const template = document.getElementById('schedule-row-template');
    const addBtn = document.getElementById('btn-add-schedule-row');
    if (!tbody) return;

    const limit = form ? parseInt(form.dataset.hoursLimit || '0', 10) : 0;
    let holidaySet = new Set();
    try {
        const raw = form?.dataset.holidayDates || '[]';
        JSON.parse(raw).forEach((d) => holidaySet.add(d));
    } catch (e) {
        holidaySet = new Set();
    }

    function isWeekend(dateStr) {
        if (!dateStr) return false;
        const d = new Date(dateStr + 'T12:00:00');
        const day = d.getDay();
        return day === 0 || day === 6;
    }

    function isHoliday(dateStr) {
        return holidaySet.has(dateStr);
    }

    function exclusionReason(dateStr) {
        if (!dateStr) return null;
        if (isWeekend(dateStr)) return 'weekend';
        if (isHoliday(dateStr)) return 'holiday';
        return null;
    }

    function exclusionLabel(reason) {
        if (reason === 'weekend') return 'не считается: суббота или воскресенье';
        if (reason === 'holiday') return 'не считается: праздник РК';
        return '';
    }

    function parseTimeToMinutes(time) {
        if (!time) return null;
        const m = String(time).match(/^(\d{1,2}):(\d{2})/);
        if (!m) return null;
        return parseInt(m[1], 10) * 60 + parseInt(m[2], 10);
    }

    function calcDurationHours(timeStart, timeEnd) {
        const s = parseTimeToMinutes(timeStart);
        const e = parseTimeToMinutes(timeEnd);
        if (s === null || e === null || e <= s) return 0;
        return Math.round(((e - s) / 60) * 10) / 10;
    }

    function setHint(hint, text, classes) {
        if (!hint) return;
        if (!text) {
            hint.textContent = '';
            hint.className = 'small text-muted row-hours-hint mt-1 d-none';
            return;
        }
        hint.textContent = text;
        hint.className = 'small row-hours-hint mt-1 ' + classes;
    }

    function recalcHours() {
        let counted = 0;
        let excluded = 0;

        tbody.querySelectorAll('.schedule-entry-row').forEach((row) => {
            const dateEl = row.querySelector('.entry-date, [data-field="date"]');
            const startEl = row.querySelector('.entry-time-start, [data-field="time_start"]');
            const endEl = row.querySelector('.entry-time-end, [data-field="time_end"]');
            const display = row.querySelector('.entry-hours-display');
            const hint = row.querySelector('.row-hours-hint');

            const date = dateEl?.value || '';
            const timeStart = startEl?.value || '';
            const timeEnd = endEl?.value || '';
            const hours = calcDurationHours(timeStart, timeEnd);
            const reason = exclusionReason(date);

            row.classList.remove('schedule-row-excluded', 'schedule-row-counted');

            if (display) {
                display.textContent = hours > 0 ? hours.toFixed(1) : '—';
                display.className = 'entry-hours-display fw-semibold ' + (hours > 0 ? 'text-success' : 'text-muted');
            }

            if (hours > 0 && date) {
                if (reason) {
                    excluded += hours;
                    row.classList.add('schedule-row-excluded');
                    setHint(hint, exclusionLabel(reason), 'text-warning');
                } else {
                    counted += hours;
                    row.classList.add('schedule-row-counted');
                    setHint(hint, '+' + hours.toFixed(1) + ' ч. в зачёт', 'text-success');
                }
            } else if (timeStart && timeEnd && timeEnd <= timeStart) {
                setHint(hint, 'окончание должно быть позже начала', 'text-danger');
            } else {
                setHint(hint, '', '');
            }
        });

        const percent = limit > 0 ? Math.min(100, Math.round((counted / limit) * 100)) : 0;
        const over = counted > limit;

        const countedEl = document.getElementById('hours-counted-text');
        const excludedEl = document.getElementById('hours-excluded-text');
        const percentEl = document.getElementById('hours-percent-badge');
        const bar = document.getElementById('hours-progress-bar');

        if (countedEl) countedEl.textContent = counted.toFixed(1);
        if (excludedEl) excludedEl.textContent = excluded.toFixed(1);
        const excludedWrap = document.getElementById('hours-excluded-wrap');
        if (excludedWrap) {
            excludedWrap.classList.toggle('d-none', excluded <= 0);
        }
        if (percentEl) {
            percentEl.textContent = percent + '%';
            percentEl.className = 'badge ' + (over ? 'bg-danger' : percent >= 100 ? 'bg-success' : 'bg-primary');
        }
        if (bar) {
            bar.style.width = Math.min(100, percent) + '%';
            bar.className = 'progress-bar ' + (over ? 'bg-danger' : percent >= 100 ? 'bg-success' : 'bg-primary');
        }

        const submitBtn = document.getElementById('btn-submit-schedule');
        if (submitBtn && limit > 0) {
            submitBtn.disabled = over || counted < limit - 0.05;
            submitBtn.title = over
                ? 'Слишком много часов'
                : counted < limit
                    ? 'Нужно набрать ' + limit + ' ч.'
                    : '';
        }
    }

    function reindexRows() {
        const rows = tbody.querySelectorAll('.schedule-entry-row');
        rows.forEach((row, idx) => {
            const num = row.querySelector('.row-num');
            if (num) num.textContent = String(idx + 1);
            row.dataset.rowIndex = String(idx);
            row.querySelectorAll('[data-field]').forEach((el) => {
                const field = el.getAttribute('data-field');
                if (!field) return;
                el.name = field === 'is_dot' ? `entries[${idx}][is_dot]` : `entries[${idx}][${field}]`;
            });
            row.querySelectorAll('input[name^="entries["]').forEach((el) => {
                if (el.getAttribute('data-field')) return;
                const match = el.name && el.name.match(/\[(\w+)\]$/);
                if (match) el.name = `entries[${idx}][${match[1]}]`;
            });
        });
        recalcHours();
    }

    function bindDotToggle(row) {
        const cb = row.querySelector('.dot-checkbox');
        const place = row.querySelector('.place-input');
        if (!cb || !place) return;
        const sync = () => {
            if (cb.checked) {
                place.classList.add('is-dot-required');
                place.placeholder = 'Платформа, ссылка (обязательно для ДОТ)';
            } else {
                place.classList.remove('is-dot-required');
                place.placeholder = 'Аудитория / адрес';
            }
        };
        cb.addEventListener('change', sync);
        sync();
    }

    tbody.querySelectorAll('.schedule-entry-row').forEach(bindDotToggle);

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-remove-row');
        if (!btn) return;
        const rows = tbody.querySelectorAll('.schedule-entry-row');
        if (rows.length <= 1) return;
        btn.closest('tr')?.remove();
        reindexRows();
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.closest('.field-hours-trigger, .entry-date, .entry-time-start, .entry-time-end')) {
            recalcHours();
        }
    });
    tbody.addEventListener('change', (e) => {
        if (
            e.target.classList?.contains('dot-checkbox')
            || e.target.classList?.contains('entry-date')
            || e.target.classList?.contains('entry-time-start')
            || e.target.classList?.contains('entry-time-end')
        ) {
            recalcHours();
        }
    });

    addBtn?.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('tr');
        if (!row) return;
        tbody.appendChild(row);
        const idx = tbody.querySelectorAll('.schedule-entry-row').length - 1;
        row.querySelectorAll('[data-field]').forEach((el) => {
            const field = el.getAttribute('data-field');
            el.name = field === 'is_dot' ? `entries[${idx}][is_dot]` : `entries[${idx}][${field}]`;
        });
        bindDotToggle(row);
        reindexRows();
    });

    recalcHours();
})();
