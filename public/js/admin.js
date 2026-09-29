(() => {
    const app = document.querySelector('[data-admin-app]');
    if (!app) return;

    const shellElement = document.querySelector('[data-admin-shell]');
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    const token = tokenElement?.content || '';
    const content = document.querySelector('[data-admin-content]');
    const title = document.querySelector('[data-admin-title]');
    const status = document.querySelector('[data-admin-status]');
    let dashboard = { news: [], events: [], trivia: null, trivias: [] };
    let editingNews = null;
    let editingEvent = null;
    let editingTrivia = null;

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
            console.error('Invalid JSON response from', url, ':', responseText);
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
            <article><strong>${dashboard.trivias.length}</strong><span>Trivias registradas</span></article>
        </div><div class="admin-panel"><h2>Contenido reciente</h2><p>Selecciona una sección para crear o actualizar contenido del sitio.</p>${(dashboard.warnings || []).map((warning) => `<p class="form-message form-message--error">${escapeHtml(warning)}</p>`).join('')}</div>`);
    }

    function renderNews() {
        const isEditing = editingNews !== null;
        const newsItem = editingNews || {};
        shell('Novedades', `<div class="admin-panel"><h2>${isEditing ? 'Editar novedad' : 'Agregar novedad'}</h2><form data-form="news" class="admin-form admin-form--grid" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${newsItem.id || ''}">
            <label>Título<input name="title" maxlength="180" value="${escapeHtml(newsItem.title)}" required></label>
            <label>Estado<select name="status"><option value="draft" ${newsItem.status === 'draft' || !newsItem.status ? 'selected' : ''}>Borrador</option><option value="published" ${newsItem.status === 'published' ? 'selected' : ''}>Publicado</option><option value="archived" ${newsItem.status === 'archived' ? 'selected' : ''}>Archivado</option></select></label>
            <label class="admin-form__wide">Descripción<textarea name="description" rows="6" required>${escapeHtml(newsItem.description)}</textarea></label>
            <label>Imagen<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
            ${isEditing ? `<button type="button" class="button button--ghost" data-cancel-edit="news">Cancelar</button>` : ''}
            <button class="button button--primary" type="submit">${isEditing ? 'Actualizar' : 'Guardar'} novedad</button>
        </form></div><div class="admin-panel"><h2>Registradas</h2><div class="admin-list">${dashboard.news.map((item) => `<article>
            <div class="admin-list__info"><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.status)}</span></div>
            <div class="admin-list__actions">
                <button type="button" class="button button--ghost" data-edit-news="${item.id}">Editar</button>
                <button type="button" class="button button--ghost" data-delete-news="${item.id}">Eliminar</button>
            </div>
        </article>`).join('') || '<p>No hay novedades todavía.</p>'}</div></div>`);
    }

    function renderEvents() {
        const isEditing = editingEvent !== null;
        const eventItem = editingEvent || {};
        shell('Eventos', `<div class="admin-panel"><h2>${isEditing ? 'Editar evento' : 'Agregar evento'}</h2><form data-form="event" class="admin-form admin-form--grid" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${eventItem.id || ''}">
            <label>Título<input name="title" maxlength="180" value="${escapeHtml(eventItem.title)}" required></label>
            <label>Fecha<input name="event_date" type="date" value="${eventItem.event_date || ''}" required></label>
            <label>Hora<input name="event_time" type="time" value="${eventItem.event_time || ''}"></label>
            <label>Ubicación<input name="location" maxlength="180" value="${escapeHtml(eventItem.location)}" required></label>
            <label>Enlace de entradas<input name="ticket_url" type="url" value="${escapeHtml(eventItem.ticket_url)}"></label>
            <label>Estado<select name="status"><option value="draft" ${eventItem.status === 'draft' || !eventItem.status ? 'selected' : ''}>Borrador</option><option value="published" ${eventItem.status === 'published' ? 'selected' : ''}>Publicado</option><option value="cancelled" ${eventItem.status === 'cancelled' ? 'selected' : ''}>Cancelado</option></select></label>
            <label>Imagen<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
            <label class="admin-form__wide">Descripción<textarea name="description" rows="5" required>${escapeHtml(eventItem.description)}</textarea></label>
            ${isEditing ? `<button type="button" class="button button--ghost" data-cancel-edit="events">Cancelar</button>` : ''}
            <button class="button button--primary" type="submit">${isEditing ? 'Actualizar' : 'Guardar'} evento</button>
        </form></div><div class="admin-panel"><h2>Registrados</h2><div class="admin-list">${dashboard.events.map((item) => `<article>
            <div class="admin-list__info"><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.event_date)} · ${escapeHtml(item.status)}</span></div>
            <div class="admin-list__actions">
                <button type="button" class="button button--ghost" data-edit-event="${item.id}">Editar</button>
                <button type="button" class="button button--ghost" data-delete-event="${item.id}">Eliminar</button>
            </div>
        </article>`).join('') || '<p>No hay eventos todavía.</p>'}</div></div>`);
    }

    function questionTemplate(question = {}, index = 1) {
        const options = [...(question.options || []), {}, {}, {}, {}].slice(0, 4);
        const optionGroup = `correct_${question.id || Math.random().toString(36).slice(2)}`;
        return `<fieldset class="trivia-question"><legend>Pregunta ${index}</legend><label>Texto<input name="question_text" value="${escapeHtml(question.text)}" required></label><label>Dato curioso / Razón de la respuesta<textarea name="question_explanation" rows="3" maxlength="2000" placeholder="Explica el dato curioso o la razón de esta respuesta">${escapeHtml(question.explanation)}</textarea></label><input type="hidden" name="question_points" value="1"><div class="trivia-options">${options.map((option) => `<label>Opción<input name="option_text" value="${escapeHtml(option.text)}" required><span><input name="${optionGroup}" data-option-correct type="radio" ${option.correct ? 'checked' : ''}> Correcta</span></label>`).join('')}</div><button type="button" class="button button--ghost" data-remove-question>Eliminar pregunta</button></fieldset>`;
    }

    function renderTrivia() {
        const trivia = editingTrivia || {};
        const isEditing = editingTrivia !== null;
        const settings = dashboard.triviaSettings || {};
        const hasSettings = Boolean(settings.title);
        const metadataDisabled = hasSettings ? 'disabled' : '';
        const metadata = `<form data-form="trivia-metadata" class="admin-form" enctype="multipart/form-data">
            <fieldset class="admin-panel"><legend>Información de la trivia</legend><label>Título<input name="title" value="${escapeHtml(settings.title)}" ${metadataDisabled} required></label>
            <label>Descripción<textarea name="description" rows="4" ${metadataDisabled}>${escapeHtml(settings.description)}</textarea></label>
            <label>Imagen<input name="image" type="file" accept="image/jpeg,image/png,image/webp" ${metadataDisabled}></label></fieldset>
            ${hasSettings ? '<button type="button" class="button button--ghost" data-edit-trivia-metadata>Editar información</button>' : ''}
            <button type="submit" class="button button--primary" data-save-trivia-metadata ${hasSettings ? 'hidden' : ''}>Guardar información</button>
            <p class="form-message" data-trivia-metadata-message role="status"></p>
        </form>`;
        const questionsList = (trivia.questions && trivia.questions.length > 0) ? trivia.questions : [{}, {}, {}];
        const form = `<form data-form="trivia" class="admin-form" enctype="multipart/form-data"><input type="hidden" name="id" value="${trivia.id || 0}">
            <fieldset class="admin-panel"><legend>${isEditing ? 'Editar preguntas y programación' : 'Nueva trivia (3 preguntas por día)'}</legend>
            <div class="admin-form--grid"><label>Inicio<input name="starts_at" type="datetime-local" value="${escapeHtml(trivia.starts_at).replace(' ', 'T')}" required></label><label>Fin<input name="ends_at" type="datetime-local" value="${escapeHtml(trivia.ends_at).replace(' ', 'T')}"></label><label>Estado<select name="status"><option value="draft">Borrador</option><option value="active">Activa</option><option value="closed">Cerrada</option></select></label></div>
            <div data-questions>${questionsList.map((q, i) => questionTemplate(q, i + 1)).join('')}</div><button type="button" class="button button--ghost" data-add-question>Agregar pregunta</button>${isEditing ? '<button type="button" class="button button--ghost" data-cancel-trivia>Cancelar</button>' : ''}<button class="button button--primary" type="submit">${isEditing ? 'Actualizar preguntas' : 'Crear trivia'}</button><p class="form-message" data-trivia-form-message role="status"></p>
        </fieldset></form>`;
        const list = `<div class="admin-panel"><div class="admin-panel__heading"><h2>Trivias registradas</h2><button type="button" class="button button--primary" data-new-trivia>Nueva trivia</button></div><div class="admin-list">${dashboard.trivias.map((item) => `<article><div class="admin-list__info"><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.scheduled_date)} · ${escapeHtml(item.status)} · ${item.is_future ? 'Futura' : 'En curso / Pasada'} · ${item.question_count} preguntas · ${item.submission_count} participaciones</span></div><div class="admin-list__actions">${item.can_edit ? `<button type="button" class="button button--ghost" data-edit-trivia="${item.id}">Editar</button>` : `<span class="admin-help">${item.is_future ? 'Con participaciones' : 'Solo lectura (no futura)'}</span>`}</div></article>`).join('') || '<p>No hay trivias todavía.</p>'}</div></div>`;
        shell('Trivia', `${metadata}${form}${list}`);
        const select = content.querySelector('[name="status"]');
        if (select && trivia.status) select.value = trivia.status;
    }

    function renderProfile() {
        const user = dashboard.user || {};
        shell('Mi perfil', `<div class="admin-panel"><h2>Datos de la cuenta</h2><form data-form="profile" class="admin-form"><label>Usuario<input name="username" value="${escapeHtml(user.username)}" required></label><label>Contraseña actual<input name="current_password" type="password" autocomplete="current-password"></label><label>Nueva contraseña<input name="new_password" type="password" minlength="10" autocomplete="new-password"></label><button class="button button--primary" type="submit">Actualizar perfil</button></form></div>`);
    }

    function readTriviaForm(form) {
        const questions = [...form.querySelectorAll('.trivia-question')].map((question) => ({ text: question.querySelector('[name="question_text"]').value.trim(), explanation: question.querySelector('[name="question_explanation"]').value.trim(), points: 1, options: [...question.querySelectorAll('[name="option_text"]')].map((input, index) => ({ text: input.value.trim(), correct: question.querySelectorAll('[data-option-correct]')[index].checked })) }));
        if (questions.length !== 3) {
            throw new Error('La trivia debe contener exactamente 3 preguntas.');
        }
        return {
            id: Number(form.id.value || 0),
            starts_at: form.starts_at.value.replace('T', ' '), ends_at: form.ends_at.value.replace('T', ' '), status: form.status.value,
            questions
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

    app.addEventListener('click', async (event) => {
        const panel = event.target.closest('[data-admin-panel]');
        if (panel) {
            document.querySelectorAll('[data-admin-panel]').forEach((item) => item.classList.toggle('is-active', item === panel));
            editingNews = null;
            editingEvent = null;
            editingTrivia = null;
            ({ home: renderHome, news: renderNews, events: renderEvents, trivia: renderTrivia, profile: renderProfile }[panel.dataset.adminPanel])();
        }
        if (event.target.closest('[data-add-question]')) content.querySelector('[data-questions]').insertAdjacentHTML('beforeend', questionTemplate());
        if (event.target.closest('[data-remove-question]')) event.target.closest('.trivia-question').remove();

        if (event.target.closest('[data-new-trivia]')) {
            editingTrivia = null;
            renderTrivia();
        }

        const editTriviaBtn = event.target.closest('[data-edit-trivia]');
        if (editTriviaBtn) {
            const id = parseInt(editTriviaBtn.dataset.editTrivia, 10);
            editingTrivia = dashboard.trivias.find((item) => Number(item.id) === id) || null;
            if (editingTrivia) {
                if (editingTrivia.can_edit === false) {
                    setStatus('Solo se pueden modificar trivias futuras que aún no han entrado en período.', true);
                    return;
                }
                request(`/api/admin/trivia/${id}`).then((trivia) => {
                    editingTrivia = trivia;
                    renderTrivia();
                }).catch((error) => setStatus(error.message, true));
            }
        }

        if (event.target.closest('[data-cancel-trivia]')) {
            editingTrivia = null;
            renderTrivia();
        }

        const editNewsBtn = event.target.closest('[data-edit-news]');
        if (editNewsBtn) {
            const id = parseInt(editNewsBtn.dataset.editNews, 10);
            editingNews = dashboard.news.find((item) => Number(item.id) === id) || null;
            renderNews();
        }

        const deleteNewsBtn = event.target.closest('[data-delete-news]');
        if (deleteNewsBtn) {
            const id = parseInt(deleteNewsBtn.dataset.deleteNews, 10);
            if (confirm('¿Eliminar esta novedad?')) {
                try {
                    await request('/api/admin/news/delete', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
                    setStatus('Novedad eliminada.');
                    dashboard = await request('/api/admin/dashboard');
                    renderNews();
                } catch (error) {
                    setStatus(error.message, true);
                }
            }
        }

        const editEventBtn = event.target.closest('[data-edit-event]');
        if (editEventBtn) {
            const id = parseInt(editEventBtn.dataset.editEvent, 10);
            editingEvent = dashboard.events.find((item) => Number(item.id) === id) || null;
            renderEvents();
        }

        const deleteEventBtn = event.target.closest('[data-delete-event]');
        if (deleteEventBtn) {
            const id = parseInt(deleteEventBtn.dataset.deleteEvent, 10);
            if (confirm('¿Eliminar este evento?')) {
                try {
                    await request('/api/admin/events/delete', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
                    setStatus('Evento eliminado.');
                    dashboard = await request('/api/admin/dashboard');
                    renderEvents();
                } catch (error) {
                    setStatus(error.message, true);
                }
            }
        }

        const cancelBtn = event.target.closest('[data-cancel-edit]');
        if (cancelBtn) {
            const section = cancelBtn.dataset.cancelEdit;
            if (section === 'events') {
                editingEvent = null;
                renderEvents();
            } else {
                editingNews = null;
                renderNews();
            }
        }

        const editTriviaMetadataBtn = event.target.closest('[data-edit-trivia-metadata]');
        if (editTriviaMetadataBtn) {
            const metadataForm = editTriviaMetadataBtn.closest('[data-form="trivia-metadata"]');
            metadataForm.querySelectorAll('input:not([type="hidden"]), textarea').forEach((field) => { field.disabled = false; });
            editTriviaMetadataBtn.hidden = true;
            metadataForm.querySelector('[data-save-trivia-metadata]').hidden = false;
        }
    });

    app.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-form]');
        if (!form) return;
        event.preventDefault();
        try {
            let result;
            if (form.dataset.form === 'news') {
                const isEdit = editingNews !== null;
                const url = isEdit ? '/api/admin/news/update' : '/api/admin/news';
                result = await request(url, { method: 'POST', body: new FormData(form) });
                editingNews = null;
            }
            if (form.dataset.form === 'event') {
                const isEdit = editingEvent !== null;
                const url = isEdit ? '/api/admin/events/update' : '/api/admin/events';
                result = await request(url, { method: 'POST', body: new FormData(form) });
                editingEvent = null;
            }
            if (form.dataset.form === 'trivia') {
                const trivia = readTriviaForm(form);
                const body = new FormData(form);
                const metadataForm = content.querySelector('[data-form="trivia-metadata"]');
                if (trivia.id === 0) {
                    body.set('title', metadataForm.title.value.trim());
                    body.set('description', metadataForm.description.value.trim());
                }
                body.set('questions', JSON.stringify(trivia.questions));
                body.set('starts_at', form.starts_at.value.replace('T', ' '));
                body.set('ends_at', form.ends_at.value ? form.ends_at.value.replace('T', ' ') : '');
                result = await request('/api/admin/trivia', { method: 'POST', body });
                editingTrivia = null;
            }
            if (form.dataset.form === 'trivia-metadata') {
                result = await request('/api/admin/trivia/metadata', { method: 'POST', body: new FormData(form) });
            }
            if (form.dataset.form === 'profile') result = await request('/api/admin/profile', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.fromEntries(new FormData(form))) });
            setStatus('Cambios guardados correctamente.');
            dashboard = await request('/api/admin/dashboard');
            if (form.dataset.form === 'news') renderNews();
            if (form.dataset.form === 'event') renderEvents();
            if (form.dataset.form === 'trivia') renderTrivia();
            if (form.dataset.form === 'trivia-metadata') {
                dashboard = await request('/api/admin/dashboard');
                dashboard.user = dashboard.user || {};
                renderTrivia();
            }
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
            if (form.dataset.form === 'trivia-metadata') {
                const message = form.querySelector('[data-trivia-metadata-message]');
                if (message) {
                    message.textContent = error.message;
                    message.classList.add('form-message--error');
                }
            }
        }
    });

    load();
})();
