const passwordInputFirst = document.querySelector('.passwordValidator');
const passwordInputsecond = document.getElementById('registration_step_security_password_second');

// Règles de validation
const validationRules = {
    email: {
        format: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    },
    password: {
        length: /.{10,}/,
        uppercase: /[A-Z]/,
        lowercase: /[a-z]/,
        special: /[!;?,:%*$@&éàç]/
    }
};

// État de validation
let validation = {
    email: false,
    password: {
        length: false,
        uppercase: false,
        lowercase: false,
        special: false
    }
};

passwordInputFirst.addEventListener('input', validatePassword);
passwordInputFirst.addEventListener('blur', validatePassword);

// Password validation
function validatePassword() {
    const password = passwordInputFirst.value;
    
    // Vérifier chaque règle
    validation.password.length = validationRules.password.length.test(password);
    validation.password.uppercase = validationRules.password.uppercase.test(password);
    validation.password.lowercase = validationRules.password.lowercase.test(password);
    validation.password.special = validationRules.password.special.test(password);

    console.log(validation.password.length);
    
    // Mettre à jour l'affichage de chaque règle
    updateRule('length-rule', validation.password.length, password);
    updateRule('uppercase-rule', validation.password.uppercase, password);
    updateRule('lowercase-rule', validation.password.lowercase, password);
    updateRule('special-rule', validation.password.special, password);
    
    // Mettre à jour la bordure de l'input
    const allValid = Object.values(validation.password).every(v => v === true);
    if (password === '') {
        passwordInputFirst.classList.remove('valid', 'invalid');
    } else if (allValid) {
        passwordInputFirst.classList.add('valid');
        passwordInputFirst.classList.remove('invalid');
    } else {
        passwordInputFirst.classList.add('invalid');
        passwordInputFirst.classList.remove('valid');
    }
}

// Mettre à jour l'affichage d'une règle
function updateRule(ruleId, isValid, value) {
    const rule = document.querySelector('#' + ruleId + ' > .rule-icon');
    if (value === '') {
        rule.classList.remove('is-error', 'is-valid');
        rule.classList.add('is-question');
    } else if (isValid) {
        rule.classList.add('is-valid');
        rule.classList.remove('is-question', 'is-error');
    } else {
        rule.classList.add('is-error');
        rule.classList.remove('is-valid', 'is-question');
    }
}