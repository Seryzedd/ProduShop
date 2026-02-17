var inputs = document.querySelectorAll('.filter-input');

console.log(inputs)

inputs.forEach(input => {
    input.addEventListener('input', function() {
        var container = document.querySelector(this.getAttribute("data-container"));
            var containerTarget = this.getAttribute("container-target");

            // Masque tous les éléments cibles d'abord
            var allTargets = container.querySelectorAll(containerTarget);
            allTargets.forEach(element => {
                hideElement(element);
            });

            // Affiche ceux dont un input correspond à la valeur saisie
            var matchingInputs = filterByInputValues(input.value, container);
            matchingInputs.forEach(matchingInput => {
                var targetElement = matchingInput.closest(containerTarget);
                if (targetElement) {
                    showElement(targetElement);
                }
            });
    })
})

function filterByInputValues(value, container) {
    if (!value) {
        return container.querySelectorAll('input');
    }
    return container.querySelectorAll('input[value*="' + value + '"]');
}

function hideElement(element) {
    element.classList.add('d-none');
}

function showElement(element) {
    element.classList.remove('d-none');
}