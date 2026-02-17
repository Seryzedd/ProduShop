var buttons = document.querySelectorAll('.btn-remove-val');

buttons.forEach(button => {
    button.addEventListener('click', function () {
        var input = button.previousElementSibling;

        input.value = '';

        input.dispatchEvent(new Event('input'));
    })
})