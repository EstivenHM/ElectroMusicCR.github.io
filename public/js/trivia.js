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
    const codeHint = document.querySelector('[data-code-hint]');
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
    const curiousModal = document.querySelector('[data-curious-modal]');
    const curiousScore = document.querySelector('[data-curious-score]');
    const curiousSlides = document.querySelector('[data-curious-slides]');
    const curiousCounter = document.querySelector('[data-curious-counter]');
    const curiousPrev = document.querySelector('[data-curious-prev]');
    const curiousNext = document.querySelector('[data-curious-next]');
    const closeCurious = document.querySelector('[data-close-curious]');
    let csrfToken = '';
    let currentTrivia = null;
    let identityMode = 'register';
    let curiousData = [];
    let currentSlideIndex = 0;
    const nicknameStorageKey = 'electromusiccr_trivia_nickname';

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[character]));

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
        delete triviaImage.dataset.triedFallback;
        if (trivia.image_path && typeof trivia.image_path === 'string' && trivia.image_path.trim() !== '') {
            let path = trivia.image_path.trim();
            if (!path.startsWith('/') && !path.startsWith('http')) {
                path = '/' + path;
            }
            triviaImage.src = path;
            triviaImage.alt = trivia.title || 'Imagen de la trivia';
            triviaImage.hidden = false;
        } else {
            triviaImage.removeAttribute('src');
            triviaImage.hidden = true;
        }
        triviaIntro.hidden = false;
    };

    triviaImage.addEventListener('error', () => {
        const currentSrc = triviaImage.getAttribute('src') || '';
        if (currentSrc.includes('/public/images/') && !triviaImage.dataset.triedFallback) {
            triviaImage.dataset.triedFallback = 'true';
            triviaImage.src = currentSrc.replace('/public/images/', '/images/');
            return;
        }
        if (currentSrc.includes('/images/') && !triviaImage.dataset.triedFallback) {
            triviaImage.dataset.triedFallback = 'true';
            triviaImage.src = currentSrc.replace('/images/', '/public/images/');
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
        heading.textContent = showCorrectAnswer ? 'Resultado de la trivia' : 'Detalle de la respuesta';
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

    const updateCuriousView = () => {
        if (!curiousSlides) return;
        curiousSlides.replaceChildren();
        if (curiousData.length === 0) return;

        curiousData.forEach((item, index) => {
            const card = document.createElement('article');
            card.className = `curious-slide ${item.is_correct ? 'curious-slide--correct' : 'curious-slide--incorrect'}`;
            if (index !== currentSlideIndex) {
                card.hidden = true;
            }

            const q = document.createElement('div');
            q.className = 'curious-slide__q';
            q.textContent = `Pregunta ${index + 1}: ${item.question}`;
            card.appendChild(q);

            const ans = document.createElement('div');
            ans.className = 'curious-slide__ans';
            const selectedText = item.selected_answer || '';
            const correctText = item.correct_answer || '';
            if (item.is_correct) {
                ans.innerHTML = `<strong>✓ Tu respuesta:</strong> ${escapeHtml(selectedText)} (¡Correcta!)`;
            } else {
                ans.innerHTML = `<strong>✗ Tu respuesta:</strong> ${escapeHtml(selectedText)} <br><strong>✓ Respuesta correcta:</strong> ${escapeHtml(correctText)}`;
            }
            card.appendChild(ans);

            if (item.explanation) {
                const exp = document.createElement('div');
                exp.className = 'curious-slide__exp';
                exp.innerHTML = `💡 <strong>Dato curioso:</strong> ${escapeHtml(item.explanation)}`;
                card.appendChild(exp);
            }

            curiousSlides.appendChild(card);
        });

        if (curiousCounter) {
            curiousCounter.textContent = `${currentSlideIndex + 1} / ${curiousData.length}`;
        }
        if (curiousPrev) curiousPrev.disabled = currentSlideIndex === 0;
        if (curiousNext) curiousNext.disabled = currentSlideIndex === curiousData.length - 1;
    };

    const renderCuriousModal = (feedback, scoreText) => {
        curiousData = feedback || [];
        currentSlideIndex = 0;
        if (curiousScore) curiousScore.textContent = scoreText;
        updateCuriousView();
        openModal(curiousModal);
    };

    const renderParticipation = (trivia) => {
        const participation = trivia.participation || {};
        const statusByCode = {
            played: 'Ya participaste en la trivia de hoy.',
            not_started: 'Identidad confirmada. Selecciona una respuesta por pregunta.',
        };
        setStatus(statusByCode[participation.status] || statusByCode.not_started);
        const isClosed = participation.status === 'played';
        triviaForm.hidden = isClosed;
        const submit = triviaForm.querySelector('button[type="submit"]');
        if (submit) {
            submit.disabled = false;
            submit.textContent = 'Enviar respuestas';
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
            ? 'Escribe tu nickname y tu código de acceso (PIN de 6 dígitos o tu código anterior).'
            : 'Crea tu nickname y define un código de acceso de 6 dígitos numéricos para ingresar siempre.';
        recoveryLabel.textContent = isLogin ? 'Código de acceso' : 'Crea tu código de acceso (6 dígitos)';
        recoveryLabel.hidden = false;
        recoveryInput.hidden = false;
        recoveryInput.required = true;
        if (isLogin) {
            recoveryInput.placeholder = 'Tu PIN de 6 dígitos o código';
            recoveryInput.removeAttribute('maxlength');
            recoveryInput.removeAttribute('pattern');
            recoveryInput.type = 'password';
            if (codeHint) {
                codeHint.textContent = 'Ingresa tu PIN de 6 dígitos o el código que guardaste al registrarte.';
            }
        } else {
            recoveryInput.placeholder = '6 dígitos numéricos (Ej: 123456)';
            recoveryInput.maxLength = 6;
            recoveryInput.setAttribute('pattern', '\\d{6}');
            recoveryInput.inputMode = 'numeric';
            if (codeHint) {
                codeHint.textContent = 'Crea un código numérico de 6 dígitos para ingresar siempre con este nickname.';
            }
        }
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
                if (participation.status === 'played') {
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
        const code = String(formData.get('recovery_code') || '').trim();
        if (!nickname) {
            playerMessage.textContent = 'Escribe un nickname.';
            return;
        }
        if (!code) {
            playerMessage.textContent = 'Escribe tu código de acceso.';
            return;
        }
        if (identityMode === 'register' && !/^\d{6}$/.test(code)) {
            playerMessage.textContent = 'El código de acceso debe tener exactamente 6 dígitos numéricos.';
            return;
        }
        try {
            const payload = await request('/api/trivia/player', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname, recovery_code: code, mode: identityMode }),
            });
            const player = payload.data || {};
            if (player.nickname) localStorage.setItem(nicknameStorageKey, player.nickname);
            await refreshCsrf();
            closeModal(identityModal);
            if (player.created) {
                showMessage('¡Cuenta registrada!', `Tu nickname "${escapeHtml(player.nickname)}" ha sido registrado. Recuerda siempre tu código de acceso de 6 dígitos.`);
            }
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

    if (curiousPrev) {
        curiousPrev.addEventListener('click', () => {
            if (currentSlideIndex > 0) {
                currentSlideIndex--;
                updateCuriousView();
            }
        });
    }

    if (curiousNext) {
        curiousNext.addEventListener('click', () => {
            if (currentSlideIndex < curiousData.length - 1) {
                currentSlideIndex++;
                updateCuriousView();
            }
        });
    }

    if (closeCurious) {
        closeCurious.addEventListener('click', () => {
            closeModal(curiousModal);
        });
    }

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
            const correctCount = result.correct_count ?? 0;
            const points = result.points ?? 0;
            const statusMessage = `¡Acertaste ${correctCount} de ${result.total_questions || 3} preguntas! Ganaste ${points} ${points === 1 ? 'punto' : 'puntos'}.`;
            
            setStatus(`Participación registrada. ${statusMessage}`);
            triviaForm.hidden = true;
            addRankingLink();
            
            renderFeedback(result.feedback, true);
            renderCuriousModal(result.feedback, statusMessage);
        } catch (error) {
            submitButton.disabled = false;
            setStatus(error.message, true);
            showMessage('No se pudo registrar', error.message);
        }
    });

    loadTrivia();
})();
