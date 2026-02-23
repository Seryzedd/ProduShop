var addimgEvents = function() {
    var inputs = document.querySelectorAll('input[type="file"]');
    console.log(inputs);
    inputs.forEach(input => {
        addImgEvent(input);
    });
};

addimgEvents();

function addImgEvent(input) {
    // Évite les doublons d'écouteurs
    if (input.dataset.imgEventBound) return;
    input.dataset.imgEventBound = true;

    input.addEventListener('change', e => {
        const file = e.currentTarget.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {
            const img = createImg(e.target.result);

            const previewContainer = input.id
                ? document.querySelector('label[for="' + input.id + '"]')
                : null;

            if (!previewContainer) {
                console.warn('Aucun label trouvé pour l\'input :', input.id);
                return;
            }

            previewContainer.classList.add('d-flex', 'flex-wrap', 'align-items-center', 'gap-2');

            const existingImg = previewContainer.querySelector('img');
            if (existingImg) {
                existingImg.replaceWith(img);
            } else {
                previewContainer.prepend(img);
            }
        };

        reader.readAsDataURL(file);
    });
}

var addImgLabel = function() {
    
    document.querySelectorAll('label[for$="_file"][data-current-image]').forEach(label => {
        var img = createImg(label.dataset.currentImage);
        label.classList.add('d-flex', 'flex-wrap', 'align-items-center', 'gap-2');
        label.prepend(img);
    });
};
function createImg(src) {
    const img = document.createElement('img');
    img.src = src;
    img.classList.add('img-thumbnail', 'mt-2', 'me-2');
    img.style.maxWidth = '100px';

    return img;
}

addImgLabel();

export { addImgEvent, addimgEvents, addImgLabel};