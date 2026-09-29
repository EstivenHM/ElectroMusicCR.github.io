/**
 * Rating Widget – ElectroMusicCR
 * Widget flotante de calificación con estrellas.
 * - Se muestra solo una vez por sesión (sessionStorage).
 * - Envía via fetch a /api/rating (POST, JSON).
 * - Autocontenido: no depende de ningún otro script.
 */
(function () {
    'use strict';

    const SESSION_KEY = 'emcr_rated';
    const DELAY_MS    = 8000; // espera 8 segundos antes de aparecer el botón

    // No mostrar si ya calificó en esta sesión
    if (sessionStorage.getItem(SESSION_KEY)) return;

    // ── HTML ─────────────────────────────────────────────────────────────────
    const btnHTML = `
<button id="emcr-rating-btn" aria-label="Calificar esta página" aria-expanded="false" aria-controls="emcr-rating-panel">
    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M10 1l2.39 4.84 5.34.78-3.87 3.77.91 5.32L10 13.27l-4.77 2.44.91-5.32L2.27 6.62l5.34-.78L10 1z"/>
    </svg>
    Calificar
</button>`;

    const panelHTML = `
<div id="emcr-rating-panel" role="dialog" aria-modal="false" aria-label="Calificar la página">
    <div class="emcr-rating__header">
        <div>
            <h3>¿Qué te parece el sitio?</h3>
            <p>Tu opinión nos ayuda a mejorar.</p>
        </div>
        <button class="emcr-rating__close" id="emcr-rating-close" aria-label="Cerrar">✕</button>
    </div>

    <div class="emcr-stars" id="emcr-stars" role="group" aria-label="Selecciona una puntuación">
        ${[1,2,3,4,5].map(n => `
        <button type="button" data-value="${n}" aria-label="${n} estrella${n>1?'s':''}" title="${n} estrella${n>1?'s':''}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        </button>`).join('')}
    </div>

    <textarea
        class="emcr-rating__comment"
        id="emcr-rating-comment"
        placeholder="Comentario opcional..."
        maxlength="400"
        rows="3"
        aria-label="Comentario opcional"
    ></textarea>

    <p class="emcr-rating__error" id="emcr-rating-error" role="alert">
        Por favor selecciona al menos una estrella.
    </p>

    <button class="emcr-rating__submit" id="emcr-rating-submit" type="button">
        Enviar calificación
    </button>
</div>`;

    const thanksHTML = `
<div class="emcr-rating__thanks">
    <div class="emcr-thanks-icon">🎧</div>
    <strong>¡Gracias por tu opinión!</strong>
    <p>Tu calificación fue registrada.</p>
</div>`;

    // ── Inyectar en el DOM ────────────────────────────────────────────────────
    function inject() {
        const wrapper = document.createElement('div');
        wrapper.id = 'emcr-rating-root';
        wrapper.innerHTML = btnHTML + panelHTML;
        document.body.appendChild(wrapper);
        initWidget();
    }

    // ── Lógica ────────────────────────────────────────────────────────────────
    function initWidget() {
        const btn     = document.getElementById('emcr-rating-btn');
        const panel   = document.getElementById('emcr-rating-panel');
        const closeEl = document.getElementById('emcr-rating-close');
        const starsEl = document.getElementById('emcr-stars');
        const comment = document.getElementById('emcr-rating-comment');
        const submit  = document.getElementById('emcr-rating-submit');
        const errorEl = document.getElementById('emcr-rating-error');
        const starBtns = starsEl.querySelectorAll('button');

        let selectedRating = 0;

        // Abrir/cerrar panel
        btn.addEventListener('click', () => togglePanel(true));
        closeEl.addEventListener('click', () => togglePanel(false));

        function togglePanel(open) {
            panel.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', String(open));
        }

        // Estrellas – hover
        starBtns.forEach(star => {
            const val = parseInt(star.dataset.value, 10);

            star.addEventListener('mouseenter', () => highlightStars(val, 'hovered'));
            star.addEventListener('mouseleave', () => highlightStars(selectedRating, 'active'));
            star.addEventListener('click', () => {
                selectedRating = val;
                highlightStars(val, 'active');
                errorEl.classList.remove('is-visible');
            });
            star.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectedRating = val;
                    highlightStars(val, 'active');
                }
            });
        });

        function highlightStars(upTo, cls) {
            starBtns.forEach(s => s.classList.remove('active', 'hovered'));
            starBtns.forEach(s => {
                if (parseInt(s.dataset.value, 10) <= upTo) s.classList.add(cls);
            });
        }

        // Enviar
        submit.addEventListener('click', async () => {
            if (selectedRating === 0) {
                errorEl.classList.add('is-visible');
                return;
            }

            submit.disabled = true;
            submit.textContent = 'Enviando…';

            try {
                const res = await fetch('/api/rating', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        rating:   selectedRating,
                        comment:  comment.value.trim(),
                        page_url: window.location.pathname
                    })
                });

                if (!res.ok) throw new Error('Error al guardar');

                // Mostrar gracias
                sessionStorage.setItem(SESSION_KEY, '1');
                panel.innerHTML = thanksHTML;
                setTimeout(() => togglePanel(false), 3000);
                // Ocultar el botón después de calificar
                btn.style.display = 'none';

            } catch {
                submit.disabled = false;
                submit.textContent = 'Enviar calificación';
                errorEl.textContent = 'Error al enviar. Intenta de nuevo.';
                errorEl.classList.add('is-visible');
            }
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!panel.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                togglePanel(false);
            }
        });
    }

    // ── Disparar con retraso ─────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(inject, DELAY_MS));
    } else {
        setTimeout(inject, DELAY_MS);
    }

})();
