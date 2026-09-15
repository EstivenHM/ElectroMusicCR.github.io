(() => {
    const rows = document.querySelector('[data-ranking-rows]');
    const status = document.querySelector('[data-ranking-status]');
    const pagination = document.querySelector('[data-ranking-pagination]');
    const pageLabel = document.querySelector('[data-ranking-page]');
    const previous = document.querySelector('[data-ranking-prev]');
    const next = document.querySelector('[data-ranking-next]');
    let page = 1;
    const limit = 20;

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.classList.toggle('status-message--error', isError);
    };

    const render = (payload) => {
        rows.replaceChildren();
        payload.items.forEach((item) => {
            const row = document.createElement('tr');
            [item.position, item.nickname, item.points].forEach((value) => {
                const cell = document.createElement('td');
                cell.textContent = String(value);
                row.appendChild(cell);
            });
            rows.appendChild(row);
        });
        pagination.hidden = false;
        pageLabel.textContent = `Pagina ${payload.page}`;
        previous.disabled = payload.page <= 1;
        next.disabled = payload.items.length < payload.limit;
        setStatus(payload.items.length ? '' : 'Todavia no hay participantes en el ranking.');
    };

    const load = async () => {
        setStatus('Cargando ranking...');
        try {
            const response = await fetch(`/api/ranking?page=${page}&limit=${limit}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const payload = await response.json();
            if (!response.ok || !payload.data) {
                throw new Error(payload.error || 'No se pudo cargar el ranking.');
            }
            render(payload.data);
        } catch (error) {
            setStatus(error.message || 'No se pudo cargar el ranking.', true);
        }
    };

    previous.addEventListener('click', () => { page -= 1; load(); });
    next.addEventListener('click', () => { page += 1; load(); });
    load();
})();
