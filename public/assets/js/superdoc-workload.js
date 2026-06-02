/**
 * SuperDoc — редактирование docx внутри страницы (без Docker).
 */
(function () {
    const cfg = window.__workloadEditor;
    if (!cfg || !cfg.docUrl) return;

    let superdocInstance = null;
    const container = document.getElementById('superdoc-editor');

    async function loadEditor() {
        if (!container) return;

        container.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border"></div><p class="mt-2">Загрузка документа...</p></div>';

        try {
            const res = await fetch(cfg.docUrl, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Не удалось загрузить файл');
            const blob = await res.blob();
            const file = new File(
                [blob],
                'grafik.docx',
                { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' }
            );

            container.innerHTML = '';
            const { SuperDoc } = await import(cfg.cdnJs);

            superdocInstance = new SuperDoc({
                selector: '#superdoc-editor',
                document: file,
                documentMode: cfg.readonly ? 'viewing' : 'editing',
            });

            window.__superdocInstance = superdocInstance;
        } catch (e) {
            container.innerHTML =
                '<div class="alert alert-danger m-3">Ошибка редактора: ' +
                (e.message || e) +
                '. Используйте вкладку «Word на компьютере».</div>';
            console.error(e);
        }
    }

    async function exportBlob() {
        if (!superdocInstance) throw new Error('Редактор не загружен');
        if (typeof superdocInstance.export !== 'function') {
            throw new Error('Экспорт недоступен');
        }
        const result = await superdocInstance.export({ triggerDownload: false });
        if (result instanceof Blob) return result;
        if (result && result.blob instanceof Blob) return result.blob;
        throw new Error('Не удалось получить файл');
    }

    async function saveToServer(action) {
        const blob = await exportBlob();
        const form = new FormData();
        form.append('grafik', blob, 'grafik.docx');
        form.append('action', action);
        form.append('_csrf', cfg.csrf);
        const comment = document.getElementById('superdoc-comment');
        if (comment) form.append('comment', comment.value);

        const res = await fetch(cfg.uploadUrl, {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
        });
        if (res.redirected) {
            window.location.href = res.url;
            return;
        }
        if (!res.ok) throw new Error('Ошибка сохранения');
        window.location.reload();
    }

    document.getElementById('superdoc-save-draft')?.addEventListener('click', async function () {
        this.disabled = true;
        try {
            await saveToServer('draft');
        } catch (e) {
            alert(e.message || 'Ошибка');
            this.disabled = false;
        }
    });

    document.getElementById('superdoc-submit')?.addEventListener('click', async function () {
        if (!confirm('Отправить график администратору?')) return;
        this.disabled = true;
        try {
            await saveToServer('submit');
        } catch (e) {
            alert(e.message || 'Ошибка');
            this.disabled = false;
        }
    });

    if (cfg.superdocEnabled) {
        loadEditor();
    }
})();
