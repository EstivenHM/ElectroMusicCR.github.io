(() => {
    const status = document.querySelector('[data-trivia-status]');
    const openIdentityButton = document.querySelector('[data-open-identity]');
    const identityModal = document.querySelector('[data-identity-modal]');
    const playerForm = document.querySelector('[data-player-form]');
    const playerMessage = document.querySelector('[data-player-message]');
    const codeModal = document.querySelector('[data-code-modal]');
    const recoveryCode = document.querySelector('[data-recovery-code]');
    const confirmCode = document.querySelector('[data-confirm-code]');
    const messageModal = document.querySelector('[data-message-modal]');
    const modalTitle = document.querySelector('[data-modal-title]');
    const modalMessage = document.querySelector('[data-modal-message]');
    const triviaForm = document.querySelector('[data-trivia-form]');
    let csrfToken = '';

    const openModal = (modal) => {
        if (typeof modal.showModal === 'function' && !modal.open) {
            modal.showModal();
        } else {
            modal.setAttribute('open', '');
        }
    };

    const closeModal = (modal) => {
        if (typeof modal.close === 'function' && modal.open) {
            modal.close();
        } else {
            modal.removeAttribute('open');
        }
    };

    const showMessage = (title, message) => {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        openModal(messageModal);
    };

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.classList.toggle('status-message--error', isError);
    };

    const renderQuestions = (trivia) => {
        triviaForm.replaceChildren();
        trivia.questions.forEach((question, questionIndex) => {
            const fieldset = document.createElement('fieldset');
            const legend = document.createElement('legend');
            legend.textContent = `${questionIndex + 1}. ${question.question_text}`;
            fieldset.appendChild(legend);
            question.options.forEach((option) => {
                const label = document.createElement('label');
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = `question_${question.id}`;
                input.value = option.id;
                input.required = true;
                label.append(input, document.createTextNode(` ${option.option_text}`));
                fieldset.appendChild(label);
            });
            triviaForm.appendChild(fieldset);
        });
        const submit = document.createElement('button');
        submit.className = 'button button--primary';
        submit.type = 'submit';
        submit.textContent = 'Enviar respuestas';
        triviaForm.appendChild(submit);
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...(csrfToken ? { 'X-CSRF-Token': csrfToken } : {}),
                ...(options.headers || {}),
            },
            credentials: 'same-origin',
        });
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error('El servidor no devolvio una respuesta JSON. Revisa el enrutamiento de la aplicacion.');
        }
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.error || 'No se pudo completar la solicitud.');
        return payload;
    };

    const loadTrivia = async () => {
        try {
            const csrfPayload = await request('/api/csrf');
            csrfToken = csrfPayload.csrf_token || '';
            const payload = await request('/api/trivia/current');
            if (!payload.data) {
                setStatus('No hay una trivia disponible hoy.');
                return;
            }
            openIdentityButton.hidden = false;
            setStatus('La trivia esta disponible. Identificate para comenzar.');
            window.__currentTrivia = payload.data;
            renderQuestions(payload.data);
            openModal(identityModal);
        } catch (error) {
            setStatus(error.message, true);
            showMessage('No se pudo cargar la trivia', error.message);
        }
    };

    playerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        playerMessage.textContent = '';
        const formData = new FormData(playerForm);
        const nickname = String(formData.get('nickname') || '').trim();
        const code = String(formData.get('recovery_code') || '');
        if (!nickname) {
            playerMessage.textContent = 'Escribe un nickname.';
            return;
        }
        try {
            const payload = await request('/api/trivia/player', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname, recovery_code: code }),
            });
            const player = payload.data || {};
            if (player.recovery_code) {
                recoveryCode.textContent = player.recovery_code;
                closeModal(identityModal);
                openModal(codeModal);
                return;
            }
            closeModal(identityModal);
            openIdentityButton.hidden = true;
            triviaForm.hidden = false;
            setStatus('Identidad confirmada.');
        } catch (error) {
            playerMessage.textContent = error.message;
        }
    });

    confirmCode.addEventListener('click', () => {
        closeModal(codeModal);
        openIdentityButton.hidden = true;
        triviaForm.hidden = false;
        setStatus('Identidad confirmada.');
    });

    openIdentityButton.addEventListener('click', () => {
        playerMessage.textContent = '';
        openModal(identityModal);
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => closeModal(identityModal));
    });

    document.querySelector('[data-close-message]').addEventListener('click', () => closeModal(messageModal));

    triviaForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submitButton = triviaForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        const answers = {};
        new FormData(triviaForm).forEach((value, key) => {
            if (key.startsWith('question_')) answers[key.replace('question_', '')] = Number(value);
        });
        try {
            const payload = await request('/api/trivia/submissions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ trivia_id: window.__currentTrivia.id, answers }),
            });
            setStatus(`Intento ${payload.data.attempt_number} registrado. Puntos: ${payload.data.points}.`);
            submitButton.remove();
            showMessage('Respuesta registrada', `Intento ${payload.data.attempt_number} registrado. Puntos obtenidos: ${payload.data.points}.`);
        } catch (error) {
            submitButton.disabled = false;
            setStatus(error.message, true);
            showMessage('No se pudo registrar', error.message);
        }
    });

    loadTrivia();
})();
