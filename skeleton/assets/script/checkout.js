document.addEventListener('DOMContentLoaded', () => {

    const form            = document.getElementById('payment-form');
    const newCardSection  = document.getElementById('new-card-section');
    const cardErrors      = document.getElementById('card-errors');
    const submitBtn       = document.getElementById('submit-btn');
    const newMethodInput  = document.getElementById('stripe_card_newPaymentMethodId');
    const spinner         = document.getElementById('payment-spinner');

    if (!form) return;

    const publicKey        = form.dataset.publicKey        ?? null;
    const labelProcessing  = form.dataset.labelProcessing  ?? 'Processing...';
    const labelPay         = form.dataset.labelPay         ?? 'Pay';
    const labelAuthRequired= form.dataset.labelAuthRequired ?? 'Authentication required, please wait...';

    if (!publicKey) {
        console.error('[Stripe] Missing data-public-key on form.');
        return;
    }

    setLoading(false);

    // -------------------------------------------------------------------------
    // ----------------- Variable from :root -------------------
    // -------------------------------------------------------------------------

    const rootStyles = getComputedStyle(document.documentElement);

    function cssVar(varName, fallback) {
        const value = rootStyles.getPropertyValue(varName).trim();
        return value !== '' ? value : fallback;
    }

    const colorBody    = cssVar('--bs-body-color',           '#212529');
    const colorDanger  = cssVar('--bs-danger-text-emphasis', cssVar('--bs-danger', '#dc3545'));
    const colorSuccess = cssVar('--bs-success',              '#198754');
    const colorMuted   = cssVar('--bs-secondary-color',      '#6c757d');
    const colorPrimary = cssVar('--bs-primary',              '#0d6efd');
    const fontFamily   = cssVar('--bs-body-font-family',     'system-ui, sans-serif');
    const fontSize     = cssVar('--bs-body-font-size',       '24px');

    // -------------------------------------------------------------------------
    // Style Stripe
    // -------------------------------------------------------------------------

    const stripeStyle = {
        base: {
            fontFamily,
            fontSize,
            fontSmoothing  : 'antialiased',
            color          : colorBody,
            iconColor      : colorPrimary,
            '::placeholder': { color: colorMuted },
        },
        invalid: {
            color          : colorDanger,
            iconColor      : colorDanger,
            backgroundColor: 'transparent',
        },
        complete: {
            color          : colorBody,
            iconColor      : colorSuccess,
            backgroundColor: 'transparent',
        },
    };

    // -------------------------------------------------------------------------
    // Initialisation des 3 éléments Stripe
    // -------------------------------------------------------------------------

    const stripe   = Stripe(publicKey);
    const elements = stripe.elements({
        appearance: { theme: 'none' },
    });

    const cardNumber = elements.create('cardNumber', { style: stripeStyle, showIcon: true });
    const cardExpiry = elements.create('cardExpiry', { style: stripeStyle });
    const cardCvc    = elements.create('cardCvc',    { style: stripeStyle });

    cardNumber.mount('#card-number-element');
    cardExpiry.mount('#card-expiry-element');
    cardCvc.mount('#card-cvc-element');

    // -------------------------------------------------------------------------
    // Gestion des erreurs par champ
    // -------------------------------------------------------------------------

    [
        { el: cardNumber, id: 'card-number-element' },
        { el: cardExpiry, id: 'card-expiry-element' },
        { el: cardCvc,    id: 'card-cvc-element'    },
    ].forEach(({ el, id }) => {
        el.on('change', ({ error, complete }) => {
            const node = document.getElementById(id);
            if (node) {
                node.classList.toggle('is-invalid', !!error);
                node.classList.toggle('is-valid',   complete && !error);
            }
            if (cardErrors) {
                cardErrors.textContent = error ? error.message : '';
            }
        });
    });

    // -------------------------------------------------------------------------
    // Affichage conditionnel du Card Element
    // -------------------------------------------------------------------------

    function syncCardVisibility() {
        const selected = form.querySelector('input[type="radio"]:checked');
        const isNew    = !selected || selected.value === 'new';
        if (newCardSection) newCardSection.style.display = isNew ? 'block' : 'none';
    }

    syncCardVisibility();

    form.addEventListener('change', (e) => {
        if (e.target.type === 'radio') syncCardVisibility();
    });

    // -------------------------------------------------------------------------
    // Soumission
    // -------------------------------------------------------------------------

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const selected = form.querySelector('input[type="radio"]:checked');
        const isNew    = !selected || selected.value === 'new';

        // Carte existante → soumission classique
        if (!isNew) {
            setLoading(true, labelProcessing);
            form.submit();
            return;
        }

        setLoading(true, labelProcessing);

        // Nouvelle carte → tokenisation Stripe.js
        const { paymentMethod, error: pmError } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumber,
        });

        if (pmError) {
            showError(pmError.message);
            setLoading(false);
            return;
        }

        if (newMethodInput) newMethodInput.value = paymentMethod.id;

        form.submit();
    });

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    function showError(message) {
        if (cardErrors) cardErrors.textContent = message;
    }

    function setLoading(isLoading, label = labelPay) {
        if (!submitBtn) return;

        submitBtn.disabled = isLoading;

        if (spinner) spinner.classList.toggle('d-none', !isLoading);

        const p   = submitBtn.querySelector('p');
        const txt = submitBtn.querySelector('.txt');

        if (isLoading === false) {
            if (p)   p.classList.add('d-none');
            if (txt) { txt.textContent = label; txt.classList.remove('d-none'); }
        } else {
            if (p)   p.classList.remove('d-none');
            if (txt) txt.classList.add('d-none');
        }
    }
});