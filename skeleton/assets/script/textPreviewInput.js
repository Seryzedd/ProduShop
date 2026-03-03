document.querySelectorAll('.text-preview').forEach(input => {
    let mainTimer = null;
    let typeTimer = null;

    input.addEventListener('input', function () {
        const value = this.value;
        const counter = document.querySelector('#professional_description_help #count');
        counter.textContent = value.length;

        if(value.length === 0) {
            previewContainer.classList.add('d-none');
        } 

        const previewContainer = document.getElementById('description-preview');

        // Annule toute animation en cours
        clearTimeout(mainTimer);
        clearTimeout(typeTimer);

        // Petite pause avant de relancer l'animation
        mainTimer = setTimeout(function() {
            let textContent = previewContainer.querySelector('.content');
            textContent.textContent = '';
            const text = value.slice(0, 160);
            let i = 0;

            previewContainer.classList.remove('d-none');

            function type() {
                if (i < text.length) {
                    textContent.textContent += text[i];
                    i++;
                    typeTimer = setTimeout(type, 50); // vitesse : 50ms par lettre
                } else if (value.length > 160) {
                    textContent.textContent += '...';
                }
            }

            type();
        }, 500); // attend 500ms après la dernière frappe pour relancer
    });
});