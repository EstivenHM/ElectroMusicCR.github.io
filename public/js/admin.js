(() => {
    const app = document.querySelector('[data-admin-app]');
    if (!app) return;

    const shellElement = document.querySelector('[data-admin-shell]');
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    const token = tokenElement?.content || '';
    const content = document.querySelector('[data-admin-content]');
    const title = document.querySelector('[data-admin-title]');
    const status = document.querySelector('[data-admin-status]');
    let dashboard = { news: [], events: [], trivia: null };

    function lockAdmin() {
        shellElement?.remove();
        window.location.replace('/admin/login');
    }

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[character]));

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: { 'X-CSRF-Token': token, ...(options.headers || {}) }
        });
        if (response.status === 401 || response.status === 403 || response.redirected) {
            lockAdmin();
            throw new Error('La sesión administrativa ya no es válida.');
        }
        const responseText = await response.text();
        let payload;
        try {
            payload = JSON.parse(responseText);
        } catch {
            throw new Error('El servidor devolvió una respuesta inválida. Revisa la configuración de la aplicación.');
        }
        if (!response.ok) throw new Error(payload.error || 'No fue posible completar la solicitud.');
        return payload.data;
    }

    function setStatus(message, isError = false) {
        status.textContent = message;
        status.classList.toggle('is-error', isError);
    }

    function shell(panelTitle, html) {
        title.textContent = panelTitle;
        content.innerHTML = html;
        setStatus('');
    }

    function renderHome() {
        shell('Resumen', `<div class="admin-stats">
            <article><strong>${dashboard.news.length}</strong><span>Novedades</span></article>
            <article><strong>${dashboard.events.length}</strong><span>Eventos</span></article>
            <article><strong>${dashboard.trivia ? '1' : '0'}</strong><span>Trivia configurada</span></article>
        </div><div class="admin-panel"><h2>Contenido reciente</h2><p>Selecciona una sección para crear o actualizar contenido del sitio.</p>${(dashboard.warnings || []).map((warning) => `<p class="form-message form-message--error">${escapeHtml(warning)}</p>`).join('')}</div>`);
    }

    function renderNews() {
        shell('Novedades', `<div class="admin-panel"><h2>Agregar novedad</h2><form data-form="news" class="admin-form admin-form--grid" enctype="multipart/form-data">
            <label>Título<input name="title" maxlength="180" required></label>
            <label class="admin-form__wide">Descripción<textarea name="description" rows="6" required></textarea></label>
            <label>Imagen<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
            <button class="button button--primary" type="submit">Guardar novedad</button>
        </form></div><div class="admin-panel"><h2>Registradas</h2><div class="admin-list">${dashboard.news.map((item) => `<article><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.status)}</span></article>`).join('') || '<p>No hay novedades todavía.</p>'}</div></div>`);
    }

    function renderEvents() {
        shell('Eventos', `<div class="admin-panel"><h2>Agregar evento</h2><form data-form="event" class="admin-form admin-form--grid" enctype="multipart/form-data">
            <label>Título<input name="title" maxlength="180" required></label>
            <label>Fecha<input name="event_date" type="date" required></label>
            <label>Hora<input name="event_time" type="time"></label>
            <label>Ubicación<input name="location" maxlength="180" required></label>
            <label>Enlace de entradas<input name="ticket_url" type="url"></label>
            <label>Imagen<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
            <label class="admin-form__wide">Descripción<textarea name="description" rows="5" required></textarea></label>
            <button class="button button--primary" type="submit">Guardar evento</button>
        </form></div><div class="admin-panel"><h2>Registrados</h2><div class="admin-list">${dashboard.events.map((item) => `<article><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.event_date)} · ${escapeHtml(item.status)}</span></article>`).join('') || '<p>No hay eventos todavía.</p>'}</div></div>`);
    }

    function questionTemplate(question = {}) {
        const options = [...(question.options || []), {}, {}, {}, {}].slice(0, 4);
        const optionGroup = `correct_${question.id || Math.random().toString(36).slice(2)}`;
        return `<fieldset class="trivia-question"><legend>Pregunta</legend><label>Texto<input name="question_text" value="${escapeHtml(question.text)}" required></label><label>Razón de la respuesta<textarea name="question_explanation" rows="3" maxlength="2000" placeholder="Explica brevemente por qué esta es la respuesta correcta">${escapeHtml(question.explanation)}</textarea></label><label>Puntos<input name="question_points" type="number" min="1" value="${question.points || 1}" required></label><div class="trivia-options">${options.map((option) => `<label>Opción<input name="option_text" value="${escapeHtml(option.text)}" required><span><input name="${optionGroup}" data-option-correct type="radio"> Correcta</span></label>`).join('')}</div><button type="button" class="button button--ghost" data-remove-question>Eliminar pregunta</button></fieldset>`;
    }

    function renderTrivia() {
        const trivia = dashboard.trivia || {};
        shell('Trivia', `<form data-form="trivia" class="admin-form" enctype="multipart/form-data"><input type="hidden" name="id" value="${trivia.id || ''}">
            <fieldset class="admin-panel"><legend>Configuración general</legend><label>Título<input name="title" value="${escapeHtml(trivia.title)}" ${trivia.title ? 'readonly' : ''} required></label>
            <label>Descripción<textarea name="description" rows="4" ${trivia.description ? 'readonly' : ''}>${escapeHtml(trivia.description)}</textarea></label>
            <label>Imagen<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label></fieldset>
            <div class="admin-form--grid"><label>Inicio<input name="starts_at" type="datetime-local" value="${escapeHtml(trivia.starts_at).replace(' ', 'T')}" required></label><label>Fin<input name="ends_at" type="datetime-local" value="${escapeHtml(trivia.ends_at).replace(' ', 'T')}"></label><label>Estado<select name="status"><option value="draft">Borrador</option><option value="active">Activa</option><option value="closed">Cerrada</option></select></label></div>
            <div data-questions>${(trivia.questions || []).map(questionTemplate).join('') || questionTemplate()}</div><button type="button" class="button button--ghost" data-add-question>Agregar pregunta</button><button class="button button--primary" type="submit">Guardar trivia</button><p class="form-message" data-trivia-form-message role="status"></p>
        </form>`);
        const select = content.querySelector('[name="status"]');
        if (select && trivia.status) select.value = trivia.status;
    }

    function renderProfile() {
        const user = dashboard.user || {};
        shell('Mi perfil', `<div class="admin-panel"><h2>Datos de la cuenta</h2><form data-form="profile" class="admin-form"><label>Usuario<input name="username" value="${escapeHtml(user.username)}" required></label><label>Contraseña actual<input name="current_password" type="password" autocomplete="current-password"></label><label>Nueva contraseña<input name="new_password" type="password" minlength="10" autocomplete="new-password"></label><button class="button button--primary" type="submit">Actualizar perfil</button></form></div>`);
    }

    function readTriviaForm(form) {
        return {
            id: Number(form.id.value || 0), title: form.title.value.trim(), description: form.description.value.trim(),
            starts_at: form.starts_at.value.replace('T', ' '), ends_at: form.ends_at.value.replace('T', ' '), status: form.status.value,
            questions: [...form.querySelectorAll('.trivia-question')].map((question) => ({ text: question.querySelector('[name="question_text"]').value.trim(), explanation: question.querySelector('[name="question_explanation"]').value.trim(), points: Number(question.querySelector('[name="question_points"]').value), options: [...question.querySelectorAll('[name="option_text"]')].map((input, index) => ({ text: input.value.trim(), correct: question.querySelectorAll('[data-option-correct]')[index].checked })) }))
        };
    }

    async function load() {
        try {
            shellElement?.removeAttribute('hidden');
            if (!token) {
                lockAdmin();
                return;
            }
            dashboard = await request('/api/admin/dashboard');
            dashboard.user = dashboard.user || {};
            if (![1, 2].includes(Number(dashboard.user.role)) || !dashboard.user.id) {
                lockAdmin();
                return;
            }
            renderHome();
        } catch (error) { setStatus(error.message, true); }
    }

    app.addEventListener('click', (event) => {
        const panel = event.target.closest('[data-admin-panel]');
        if (panel) {
            document.querySelectorAll('[data-admin-panel]').forEach((item) => item.classList.toggle('is-active', item === panel));
            ({ home: renderHome, news: renderNews, events: renderEvents, trivia: renderTrivia, profile: renderProfile }[panel.dataset.adminPanel])();
        }
        if (event.target.closest('[data-add-question]')) content.querySelector('[data-questions]').insertAdjacentHTML('beforeend', questionTemplate());
        if (event.target.closest('[data-remove-question]')) event.target.closest('.trivia-question').remove();
    });

    app.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-form]');
        if (!form) return;
        event.preventDefault();
        try {
            let result;
            if (form.dataset.form === 'news' || form.dataset.form === 'event') result = await request(`/api/admin/${form.dataset.form === 'news' ? 'news' : 'events'}`, { method: 'POST', body: new FormData(form) });
            if (form.dataset.form === 'trivia') {
                const trivia = readTriviaForm(form);
                const body = new FormData(form);
                body.set('questions', JSON.stringify(trivia.questions));
                result = await request('/api/admin/trivia', { method: 'POST', body });
            }
            if (form.dataset.form === 'profile') result = await request('/api/admin/profile', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.fromEntries(new FormData(form))) });
            setStatus('Cambios guardados correctamente.');
            dashboard = await request('/api/admin/dashboard');
            if (form.dataset.form === 'news') renderNews();
            if (form.dataset.form === 'event') renderEvents();
            if (form.dataset.form === 'trivia') renderTrivia();
            if (form.dataset.form === 'profile') renderProfile();
            if (form.dataset.form === 'trivia') content.querySelector('[data-trivia-form-message]').textContent = 'Trivia guardada correctamente.';
        } catch (error) {
            setStatus(error.message, true);
            if (form.dataset.form === 'trivia') {
                const message = form.querySelector('[data-trivia-form-message]');
                if (message) {
                    message.textContent = error.message;
                    message.classList.add('form-message--error');
                }
            }
        }
    });

    load();
})();