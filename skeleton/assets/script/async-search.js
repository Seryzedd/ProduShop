// assets/js/async-search.js

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('async-search');
    const dropdown = document.getElementById('search-dropdown');

    if (!input || !dropdown) return;

    const typeLabels = {
        product:  { label: 'Produit',  css: 'bg-primary' },
        shelf:    { label: 'Rayon',    css: 'bg-success' },
        merchant: { label: 'Marchand', css: 'bg-warning text-dark' },
    };

    let debounceTimer;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const value = input.value.trim();

        if (value.length < 2) {
            dropdown.innerHTML = '';
            dropdown.classList.add('d-none');
            input.classList.remove('rounded-bottom-0');
            input.nextElementSibling.classList.remove('rounded-bottom-0');
            return;
        }

        debounceTimer = setTimeout(async () => {
            const url = input.getAttribute('async-url');
            
            const formData = new FormData();
            formData.append('search', value);
            //input.next('label').remove('rounded-bottom-0');
            //input.nextElementSibling.classList.remove('rounded-bottom-0');

            try {
                const response = await fetch(url, { method: 'POST', body: formData });
                const results = await response.json();

                dropdown.innerHTML = '';

                if (results.length === 0) {
                    // dropdown.innerHTML = '<li class="list-group-item text-muted">Aucun résultat</li>';
                    dropdown.classList.remove('d-none');
                    input.classList.add('rounded-bottom-0');
                    input.nextElementSibling.classList.add('rounded-bottom-0');
                    return;
                }

                // Grouper les résultats par type
                const grouped = results.reduce((acc, item) => {
                    if (!acc[item.type]) acc[item.type] = [];
                    acc[item.type].push(item);
                    return acc;
                }, {});

                // Ordre d'affichage souhaité
                const typeOrder = ['product', 'shelf', 'merchant'];

                typeOrder.forEach(type => {
                    if (!grouped[type] || grouped[type].length === 0) return;

                    const typeInfo = typeLabels[type] ?? { label: type, css: 'bg-secondary' };

                    // Titre du groupe — non cliquable
                    const header = document.createElement('li');
                    header.className = 'list-group-item fst-italic text-muted small py-1 px-3';
                    //header.style.pointerEvents = 'none';
                    header.textContent = typeInfo.label;
                    dropdown.appendChild(header);

                    // Éléments du groupe
                    grouped[type].forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action fw-bold fs-6 ps-3';

                        const a = document.createElement('a');
                        a.className = 'text-decoration-none text-body';
                        a.href = item.url ?? '#';
                        a.textContent = item.label;

                        li.appendChild(a);
                        dropdown.appendChild(li);
                    });
                });

                dropdown.classList.remove('d-none');
                input.classList.add('rounded-bottom-0');
                input.nextElementSibling.classList.add('rounded-bottom-0');
            } catch (e) {
                console.error('Search error', e);
            }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('d-none');
            input.classList.remove('rounded-bottom-0');
            input.nextElementSibling.classList.remove('rounded-bottom-0');
        }
    });
});