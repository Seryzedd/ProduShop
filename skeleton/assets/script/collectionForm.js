import { addImgEvent,  addimgEvents, addImgLabel} from './imageEvent.js';

function addFormToCollection(e) {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

    const item = document.createElement('li');
    item.classList.add('list-group-item');

    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.classList.add('btn', 'btn-ouline-danger', 'remove_collection_item_btn', 'btn-trash', 'position-absolute', 'top-0', 'end-0', 'p-2', 'rounded-start-0');

    btn.addEventListener("click", removeFormFromCollection);

    item.appendChild(btn);

    collectionHolder.appendChild(item);

    addimgEvents();

    collectionHolder.dataset.index++;
};

addImgLabel();

document
  .querySelectorAll('.add_collection_item_btn')
  .forEach(btn => {
      btn.addEventListener("click", addFormToCollection)
  });

function removeFormFromCollection(e) {
    e.currentTarget.closest('li').remove();
}

document
  .querySelectorAll('.remove_collection_item_btn')
  .forEach(btn => {
      btn.addEventListener("click", removeFormFromCollection)
  });