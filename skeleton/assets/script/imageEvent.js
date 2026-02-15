var addimgEvents = function() {
    document.querySelectorAll('input[type="file"]').forEach(input => {
        addImgEvent(input);
    });
};

function addImgEvent(input) {
    input.addEventListener('change', e => {
        const file = e.currentTarget.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {

            const img = createImg(e.target.result);

            const previewContainer = document.querySelector('label[for="' + input.id + '"]');
            previewContainer.classList.add('d-flex', 'flex-wrap', 'align-items-center', 'gap-2');
            const existingImg = previewContainer.querySelector('img');
            
            if (existingImg) {
                existingImg.replaceWith(img);
            } else {
                previewContainer.prepend(img);
            }
        };
        
        if (file) {
            reader.readAsDataURL(file);
        }
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

export { addImgEvent, addimgEvents, addImgLabel};