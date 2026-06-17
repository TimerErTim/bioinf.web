/**
 * REST delete/patch helpers — server-side validation remains authoritative.
 */
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    async function mutate(method, url, confirmMsg) {
        if (confirmMsg && !window.confirm(confirmMsg)) {
            return;
        }

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-Token': csrf,
                Accept: 'text/html',
            },
            redirect: 'follow',
        });

        if (res.redirected) {
            window.location.href = res.url;
            return;
        }

        if (res.ok) {
            window.location.reload();
        }
    }

    document.querySelectorAll('[data-delete]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            mutate('DELETE', btn.dataset.delete, btn.dataset.confirm ?? 'Wirklich löschen?');
        });
    });

    document.querySelectorAll('[data-patch]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            mutate('PATCH', btn.dataset.patch, btn.dataset.confirm ?? null);
        });
    });

    document.querySelectorAll('[data-reply-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.replyToggle;
            const form = document.getElementById('reply-form-' + id);
            form?.classList.toggle('hidden');
            form?.querySelector('textarea')?.focus();
        });
    });
})();
