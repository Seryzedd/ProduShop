/**
 * checkout.js
 */

document.addEventListener('DOMContentLoaded', () => {

    const form            = document.getElementById('payment-form');
    const newCardSection  = document.getElementById('new-card-section');
    const cardErrors      = document.getElementById('card-errors');
    const submitBtn       = document.getElementById('submit-btn');
    const newMethodInput  = document.getElementById('new-payment-method-id');
    const spinner         = document.getElementById('payment-spinner');

    if (!form) return;

    const publicKey       = form.dataset.publicKey       ?? null;
    const labelProcessing = form.dataset.labelProcessing ?? 'Processing...';
    const labelPay        = form.dataset.labelPay        ?? 'Pay';

    if (!publicKey) {
        console.error('[Stripe] Missing data-public-key on form.');
        return;
    }

    setLoading(false);

    // -------------------------------------------------------------------------
    // Lecture des variables CSS Bootstrap directement sur :root
    // Plus fiable que de passer par un élément intermédiaire
    // -------------------------------------------------------------------------

    const rootStyles = getComputedStyle(document.documentElement);

    function cssVar(varName, fallback) {
        const value = rootStyles.getPropertyValue(varName).trim();
        return value !== '' ? value : fallback;
    }

    const colorBody    = cssVar('--bs-body-color',        '#212529');
    const colorDanger  = cssVar('--bs-danger-text-emphasis', cssVar('--bs-danger', '#dc3545'));
    const colorSuccess = cssVar('--bs-success',            '#198754');
    const colorMuted   = cssVar('--bs-secondary-color',    '#6c757d');
    const colorPrimary = cssVar('--bs-primary',            '#0d6efd');
    const fontFamily   = cssVar('--bs-body-font-family',   'system-ui, sans-serif');
    const fontSize     = cssVar('--bs-body-font-size',     '24px');

    // -------------------------------------------------------------------------
    // Style Stripe — pas de backgroundColor pour laisser le .form-control gérer
    // le fond, ce qui évite tout conflit de rendu dans l'iframe
    // -------------------------------------------------------------------------

    const stripeStyle = {
        base: {
            fontFamily,
            fontSize,
            fontSmoothing  : 'antialiased',
            color          : colorBody,
            iconColor      : colorPrimary,
            '::placeholder': {
                color: colorMuted,
            },
        },
        invalid: {
            color    : colorDanger,
            iconColor: colorDanger,
            backgroundColor: 'transparent'
        },
        complete: {
            color    : colorBody,
            iconColor: colorSuccess,
            backgroundColor: 'transparent'
        },
    };

    // -------------------------------------------------------------------------
    // Initialisation des 3 éléments Stripe
    // -------------------------------------------------------------------------

    const stripe   = Stripe(publicKey);
    const elements = stripe.elements({
        // Passe la couleur de fond globalement à tous les éléments
        // Stripe l'applique correctement à l'iframe sans conflit
        appearance: {
            theme: 'none', // désactive le thème Stripe par défaut
        },
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
    // Affichage conditionnel
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

        if (!isNew) {
            setLoading(true);
            form.submit();
            return;
        }

        setLoading(true);

        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumber,
        });

        if (error) {
            if (cardErrors) cardErrors.textContent = error.message;
            setLoading(false);
            return;
        }

        if (newMethodInput) newMethodInput.value = paymentMethod.id;
        form.submit();
    });

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    function setLoading(isLoading) {
        if (!submitBtn) return;

        submitBtn.disabled = isLoading;

        if (isLoading) {
            if (spinner) {
                spinner.classList.remove('d-none');
                submitBtn.querySelector('p').classList.add('d-none');
                submitBtn.querySelector('.txt').classList.remove('d-none');
            }
        } else {
            if (spinner) {
                spinner.classList.add('d-none');
                submitBtn.querySelector('p').classList.add('d-none');
                submitBtn.querySelector('.txt').classList.remove('d-none');
            };
        }
    }
});