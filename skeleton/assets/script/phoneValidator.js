const FRENCH_PHONE_REGEX = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\-]?\d{2}){4}$/;

const getMessages = () => window.phoneValidation?.messages ?? {
    required: 'The phone number is required.',
    invalid:  'Invalid format. Ex: 06 12 34 56 78 or +33 6 12 34 56 78',
};

/**
 * Strip spaces, dots and dashes from a phone string.
 * @param {string} value
 * @returns {string}
 */
const stripFormatting = (value) => value.replace(/[\s.\-]/g, '');

/**
 * Check whether a phone number matches the French format.
 * @param {string} value
 * @returns {boolean}
 */
const isValidPhone = (value) => FRENCH_PHONE_REGEX.test(value.trim());

/**
 * Display an error message below the input.
 * @param {HTMLInputElement} input
 * @param {string} message
 */
const showError = (input, message) => {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');

    let feedback = input.nextElementSibling;
    if (!feedback?.classList.contains('phone-feedback')) {
        feedback = document.createElement('div');
        feedback.classList.add('phone-feedback', 'invalid-feedback');
        input.after(feedback);
    }
    feedback.textContent = message;
};

/**
 * Mark the input as valid and remove any existing error message.
 * @param {HTMLInputElement} input
 */
const showSuccess = (input) => {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');

    const feedback = input.nextElementSibling;
    if (feedback?.classList.contains('phone-feedback')) {
        feedback.remove();
    }
};

/**
 * Clear validation state when the field is empty and untouched.
 * @param {HTMLInputElement} input
 */
const clearState = (input) => {
    input.classList.remove('is-invalid', 'is-valid');

    const feedback = input.nextElementSibling;
    if (feedback?.classList.contains('phone-feedback')) {
        feedback.remove();
    }
};

/**
 * Run validation logic and update the UI.
 * @param {HTMLInputElement} input
 */
const validate = (input) => {
    const { required, invalid } = getMessages();
    const val = input.value.trim();

    if (!val) {
        showError(input, required);
    } else if (!isValidPhone(val)) {
        showError(input, invalid);
    } else {
        showSuccess(input);
    }
};

/**
 * Auto-format a French phone number as the user types (e.g. "06 12 34 56 78").
 * Only applies to numbers starting with 0 and up to 10 digits.
 * @param {HTMLInputElement} input
 */
const handleInput = (input) => {
    const digits = stripFormatting(input.value);
    if (digits.startsWith('0') && digits.length <= 10) {
        input.value = digits.replace(/(\d{2})(?=\d)/g, '$1 ').trim();
    }
};

/**
 * Return a debounced version of a function.
 * Delays execution until after {delay}ms have passed since the last call.
 * @param {Function} fn
 * @param {number} delay - milliseconds to wait (default: 500)
 * @returns {Function}
 */
const debounce = (fn, delay = 500) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
};

/**
 * Bind all validation and formatting listeners to a single phone input.
 * @param {HTMLInputElement} input
 */
const bindPhoneInput = (input) => {
    // Track whether the user has already interacted with the field
    let touched = false;

    input.addEventListener('input', () => {
        handleInput(input);

        // Start live validation only once the user has left the field once,
        // or if the field already has a visible error — avoids showing errors
        // while the user is still typing for the first time
        if (touched) {
            debounceValidate(input);
        } else if (input.value.trim() === '') {
            clearState(input);
        }
    });

    // Debounced validator reused across input events
    const debounceValidate = debounce(validate);

    input.addEventListener('input', () => {
        touched = true;
        validate(input);
    });

    input.closest('form')?.addEventListener('submit', (e) => {
        touched = true;
        const val = input.value.trim();
        if (!val || !isValidPhone(val)) {
            e.preventDefault();
            validate(input);
        }
    });
};

// Entry point — initialise all inputs with the phoneFormat class
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input.phoneFormat').forEach(bindPhoneInput);
});