(() => {
    const rows = document.querySelector('[data-ranking-rows]');
    const status = document.querySelector('[data-ranking-status]');
    const pagination = document.querySelector('[data-ranking-pagination]');
    const pageLabel = document.querySelector('[data-ranking-page]');
    const previous = document.querySelector('[data-ranking-prev]');
    const next = document.querySelector('[data-ranking-next]');
    const header = document.querySelector('[data-ranking-header]');
    const image = document.querySelector('[data-ranking-image]');
    const title = document.querySelector('[data-ranking-title]');
    const description = document.querySelector('[data-ranking-description]');
    const countdown = document.querySelector('[data-countdown]');
    let page = 1;
    const limit = 20;
    const countdownEnd = new Date('2026-11-27T14:00:00');
    let timerInterval = null;

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.classList.toggle('status-message--error', isError);
    };

    const updateCountdown = () => {
        const now = new Date();
        const diff = countdownEnd.getTime() - now.getTime();
        if (diff <= 0) {
            countdown.hidden = true;
            if (timerInterval) clearInterval(timerInterval);
            return;
        }
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        countdown.textContent = `Finaliza en ${days}d ${hours}h ${minutes}min`;
        countdown.hidden = false;
    };

    const renderHeader = (settings) => {
        if (!settings) {
            header.hidden = true;
            return;
        }
        title.textContent = settings.title || 'Ranking de Trivia';
        description.textContent = settings.description || '';
        description.hidden = !settings.description;
        if (settings.image_path) {
            image.src = settings.image_path;
            image.alt = settings.title || 'Imagen de trivia';
            image.hidden = false;
        } else {
            image.hidden = true;
        }
        header.hidden = false;
    };

    const render = (payload) => {
        renderHeader(payload.settings);
        updateCountdown();
        if (!timerInterval) {
            timerInterval = setInterval(updateCountdown, 60000);
        }
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
