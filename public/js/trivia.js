(() => {
    const status = document.querySelector('[data-trivia-status]');
    const identityModal = document.querySelector('[data-identity-modal]');
    const identityChoice = document.querySelector('[data-identity-choice]');
    const registerPlayerButton = document.querySelector('[data-register-player]');
    const loginPlayerButton = document.querySelector('[data-login-player]');
    const backIdentityButton = document.querySelector('[data-back-identity]');
    const identityDescription = document.querySelector('[data-identity-description]');
    const playerForm = document.querySelector('[data-player-form]');
    const playerMessage = document.querySelector('[data-player-message]');
    const recoveryLabel = document.querySelector('[data-recovery-label]');
    const recoveryInput = document.querySelector('#recovery-code');
    const codeModal = document.querySelector('[data-code-modal]');
    const recoveryCode = document.querySelector('[data-recovery-code]');
    const confirmCode = document.querySelector('[data-confirm-code]');
    const messageModal = document.querySelector('[data-message-modal]');
    const modalTitle = document.querySelector('[data-modal-title]');
    const modalMessage = document.querySelector('[data-modal-message]');
    const modalFeedback = document.querySelector('[data-modal-feedback]');
    const triviaIntro = document.querySelector('[data-trivia-intro]');
    const triviaImage = document.querySelector('[data-trivia-image]');
    const triviaTitle = document.querySelector('[data-trivia-title]');
    const triviaDescription = document.querySelector('[data-trivia-description]');
    const triviaForm = document.querySelector('[data-trivia-form]');
    const triviaResult = document.querySelector('[data-trivia-result]');
    let csrfToken = '';
    let currentTrivia = null;
    let identityMode = 'register';
    const nicknameStorageKey = 'electromusiccr_trivia_nickname';

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

    const renderModalFeedback = (feedback, showCorrectAnswer) => {
        modalFeedback.replaceChildren();
        if (!Array.isArray(feedback) || feedback.length === 0) {
            modalFeedback.hidden = true;
            return;
        }

        feedback.forEach((item) => {
            const article = document.createElement('article');
            article.className = 'modal-feedback__item';
            const question = document.createElement('strong');
            question.textContent = item.question;
            article.appendChild(question);
            if (showCorrectAnswer && item.correct_answer) {
                const answer = document.createElement('span');
                answer.textContent = `Respuesta correcta: ${item.correct_answer}`;
                article.appendChild(answer);
            }
            if (item.explanation) {
                const explanation = document.createElement('span');
                explanation.textContent = item.explanation;
                article.appendChild(explanation);
            }
            modalFeedback.appendChild(article);
        });
        modalFeedback.hidden = false;
    };

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.classList.toggle('status-message--error', isError);
    };

    const renderTriviaIntro = (trivia) => {
        triviaTitle.textContent = trivia.title || 'Trivia del dia';
        triviaDescription.textContent = trivia.description || '';
        triviaDescription.hidden = !trivia.description;
        if (trivia.image_path) {
            triviaImage.src = trivia.image_path;
            triviaImage.alt = trivia.title || 'Imagen de la trivia';
            triviaImage.dataset.fallbackSrc = trivia.image_path.replace('/public/images/', '/images/');
            triviaImage.hidden = false;
        } else {
            triviaImage.removeAttribute('src');
            triviaImage.hidden = true;
        }
        triviaIntro.hidden = false;
    };

    triviaImage.addEventListener('error', () => {
        const fallbackSrc = triviaImage.dataset.fallbackSrc || '';
        if (fallbackSrc && triviaImage.src !== new URL(fallbackSrc, window.location.href).href) {
            triviaImage.src = fallbackSrc;
            return;
        }
        triviaImage.hidden = true;
    });

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

    const renderFeedback = (feedback, showCorrectAnswer) => {
        if (!Array.isArray(feedback) || feedback.length === 0) return;
        triviaResult.replaceChildren();
        const heading = document.createElement('h2');
        heading.textContent = showCorrectAnswer ? 'Resultado de la trivia' : 'Explicacion de las respuestas';
        triviaResult.appendChild(heading);
        feedback.forEach((item) => {
            const article = document.createElement('article');
            article.className = 'trivia-feedback';
            const question = document.createElement('strong');
            question.textContent = item.question;
            article.appendChild(question);
            if (showCorrectAnswer && item.correct_answer) {
                const answer = document.createElement('span');
                answer.textContent = `Respuesta correcta: ${item.correct_answer}`;
                article.appendChild(answer);
            }
            if (item.explanation) {
                const explanation = document.createElement('span');
                explanation.textContent = item.explanation;
                article.appendChild(explanation);
            }
            triviaResult.appendChild(article);
        });
        triviaResult.hidden = false;
    };

    const renderParticipation = (trivia) => {
        const participation = trivia.participation || {};
        const statusByCode = {
            won: 'Ya acertaste la trivia de hoy.',
            lost: 'Ya utilizaste tus dos intentos de hoy.',
            second_attempt_available: 'El primer intento fallo. Puedes intentarlo una vez mas.',
            not_started: 'Identidad confirmada. Selecciona una respuesta por pregunta.',
        };
        setStatus(statusByCode[participation.status] || statusByCode.not_started);
        const isClosed = participation.status === 'won' || participation.status === 'lost';
        triviaForm.hidden = isClosed;
        const submit = triviaForm.querySelector('button[type="submit"]');
        if (submit) {
            submit.disabled = false;
            submit.textContent = participation.status === 'second_attempt_available'
                ? 'Enviar segundo intento'
                : 'Enviar respuestas';
        }
    };

    const showIdentityChoice = () => {
        identityMode = 'register';
        identityChoice.hidden = false;
        playerForm.hidden = true;
        playerMessage.textContent = '';
        openModal(identityModal);
    };

    const showIdentityForm = (mode) => {
        identityMode = mode;
        identityChoice.hidden = true;
        playerForm.hidden = false;
        playerMessage.textContent = '';
        recoveryInput.value = '';
        const isLogin = mode === 'login';
        identityDescription.textContent = isLogin
            ? 'Escribe tu nickname y el codigo de acceso que recibiste al registrarte.'
            : 'Crea un nickname nuevo para participar. Recibiras un codigo de acceso una sola vez.';
        recoveryLabel.hidden = !isLogin;
        recoveryInput.hidden = !isLogin;
        recoveryInput.required = isLogin;
        openModal(identityModal);
    };

    const addRankingLink = () => {
        if (triviaResult.querySelector('[data-ranking-link]')) return;
        const link = document.createElement('a');
        link.href = '/ranking';
        link.className = 'button button--ghost';
        link.dataset.rankingLink = 'true';
        link.textContent = 'Ver ranking';
        triviaResult.appendChild(link);
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

    const refreshCsrf = async () => {
        const csrfPayload = await request('/api/csrf');
        csrfToken = csrfPayload.csrf_token || '';
    };

    const loadTrivia = async () => {
        try {
            if (!csrfToken) {
                await refreshCsrf();
            }
            const payload = await request('/api/trivia/current');
            if (!payload.data) {
                setStatus('No hay una trivia disponible hoy.');
                return;
            }
            currentTrivia = payload.data;
            window.__currentTrivia = currentTrivia;
            renderTriviaIntro(currentTrivia);
            renderQuestions(currentTrivia);
            const rememberedNickname = localStorage.getItem(nicknameStorageKey);
            if (rememberedNickname && !playerForm.elements.nickname.value) {
                playerForm.elements.nickname.value = rememberedNickname;
            }
            const participation = currentTrivia.participation || {};
            if (participation.status && participation.status !== 'not_started') {
                renderParticipation(currentTrivia);
                if (participation.status === 'won' || participation.status === 'lost') {
                    triviaForm.hidden = true;
                    addRankingLink();
                }
                return;
            }
            triviaForm.hidden = false;
            setStatus(currentTrivia.player?.authenticated
                ? 'Selecciona una respuesta por pregunta.'
                : 'Responde la trivia y registrate o ingresa antes de enviar.');
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
            if (player.nickname) localStorage.setItem(nicknameStorageKey, player.nickname);
            await refreshCsrf();
            if (player.recovery_code) {
                recoveryCode.textContent = player.recovery_code;
                closeModal(identityModal);
                openModal(codeModal);
                return;
            }
            closeModal(identityModal);
            await loadTrivia();
        } catch (error) {
            playerMessage.textContent = error.message;
        }
    });

    confirmCode.addEventListener('click', () => {
        closeModal(codeModal);
        loadTrivia();
    });

    registerPlayerButton.addEventListener('click', () => showIdentityForm('register'));
    loginPlayerButton.addEventListener('click', () => showIdentityForm('login'));
    backIdentityButton.addEventListener('click', showIdentityChoice);

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => closeModal(identityModal));
    });

    document.querySelector('[data-close-message]').addEventListener('click', () => closeModal(messageModal));

    triviaForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!currentTrivia?.player?.authenticated) {
            showIdentityChoice();
            return;
        }
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
                body: JSON.stringify({ trivia_id: currentTrivia.id, answers }),
            });
            const result = payload.data || {};
            renderFeedback(result.feedback, result.status === 'lost');
            const statusMessage = result.status === 'second_attempt_available'
                ? 'El primer intento fallo. Tienes un segundo intento disponible.'
                : result.status === 'won'
                    ? 'Respuesta correcta. Tu participacion de hoy ha terminado.'
                    : 'Fallaste los dos intentos disponibles para hoy.';
            setStatus(`${statusMessage} Puntos: ${result.points}.`);
            if (result.status === 'second_attempt_available') {
                submitButton.disabled = false;
                submitButton.textContent = 'Enviar segundo intento';
            } else {
                triviaForm.hidden = true;
                addRankingLink();
            }
            renderModalFeedback(result.feedback, result.status === 'lost');
            showMessage('Resultado de la trivia', `${statusMessage} Puntos obtenidos: ${result.points}.`);
        } catch (error) {
            submitButton.disabled = false;
            renderModalFeedback([], false);
            setStatus(error.message, true);
            showMessage('No se pudo registrar', error.message);
        }
    });

    loadTrivia();
})();
