/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import $ from 'jquery';

import { addImgEvent,  addimgEvents, addImgLabel} from './script/imageEvent.js';

import 'bootstrap';
import * as bootstrap from 'bootstrap';

import './styles/app.css';
// import './styles/app.scss';

import { LuhnCheck } from './luhn.js';

import './script/async-search.js';

// Validation du Siret

$('.siret').on('input', function () {
    var valid = LuhnCheck($(this).val());

    let spanClass = '';
    if($(this).val() !== "") {
        if(valid === false) {
            spanClass = ['error', 'text-danger'];
        } else {
            spanClass = ['valid', 'text-success'];
        }
    } else {
        spanClass = ['pending', 'text-muted'];
    }
    

    $(this).next("span").removeClass(['pending', 'valid', 'error', 'text-muted', 'text-danger', 'text-success']).addClass(spanClass);
})

$(document).ready(function() {
    $('.siret').after('<span class="input-group-text pending text-muted"></span>');
})

// adress autocomplete

async function autocompleteStreet(query) {
  if (query.length < 5) {
    $(suggestionsContainer).hide();
    return [];
  }; // Wait for 5 characteres
  
  try {
    // Utilisation de l'API adresse du gouvernement français (gratuite)
    const response = await fetch(
      `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&type=street&limit=5`
    );
    
    const data = await response.json();
    return data.features.map(feature => ({
      label: feature.properties.label,
      street: feature.properties.name,
      city: feature.properties.city,
      postcode: feature.properties.postcode
    }));
  } catch (error) {
    console.error('Erreur autocomplétion:', error);
    return [];
  }
}

function debounce(func, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

const inputStreet = document.querySelector('#registration_step_adress_street, [name="adress[street]"]');
const inputZip = document.querySelector('#registration_step_adress_zipCode, [name="adress[zipCode]"]');
const inputCountry = document.querySelector('#registration_step_adress_country, [name="adress[country]"]');

const suggestionsContainer = document.getElementById('suggestions');

if(inputStreet) {
  inputStreet.addEventListener('input', debounce(async (e) => {
    const query = e.target.value;
    const suggestions = await autocompleteStreet(query);
    
    // Afficher les suggestions
    suggestionsContainer.innerHTML = suggestions
        .map(s => `<div class="suggestion">${s.label}</div>`)
        .join('');

    $(suggestionsContainer).fadeIn();

    // Gérer le clic sur une suggestion
    document.querySelectorAll('.suggestion').forEach((el, index) => {
        el.addEventListener('click', () => {
            inputStreet.value = suggestions[index].street;
            inputZip.value = suggestions[index].postcode;
            inputCountry.value = suggestions[index].city;
            
            suggestionsContainer.innerHTML = '';

            $(suggestionsContainer).hide();
        });
    });
  }));
}

$('.img-miniature').on('click', function() {
    $('#main-img-preview').attr('src', $(this).attr('src'));
})

$('.alert .btn-close').on('click', function() {
    $(this).closest('.alert').slideUp();

    setTimeout(function () {
      $(this).closest('.alert').remove();
    }, 1000);
});

$('input[type="range"]').on('input', function() {
    showRangeValue(this);
})

$(document).ready(function () {
    $('input[type="range"]').each(function() {
        showRangeValue(this);
    })

    $('.spinner').hide();
    
})

function showRangeValue(element) {
    var span = document.createElement('span');
    $(span).addClass(['rounded-pill', 'text-bg-primary', 'px-2', 'py-1', 'position-absolute','text-white', 'z-3']);

    var next = $(element).next('p');

    var min = $(element).attr('min');
    var max = $(element).attr('max');

    if(typeof min === "undefined") {
        min = 0;
    }

    if(typeof max === "undefined") {
        max = 0;
    }

    span.innerText = $(element).val() + $(element).attr('unit');

    span.style.left = percentage($(element).val(), min, max) + '%';
    span.style.transform = 'translateX(-50%)';
    
    next.html(span);
}

function percentage(value, min, max) {
    return Number(((value - min) * 100) / (max - min));
}

// Fetch searching async
$('input#app_async').on('input', function () {
    
})

$('#admin-btn').on('change', function() {
    sendRequest($(this).attr('request-url'), {isChecked: $(this).is(':checked')})
})

async function sendRequest(url, params) {
  try {
    const response = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams(params)
    });
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }

    const result = await response.json();
  } catch (error) {
    console.error(error.message);
  }
}

// popover

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
    const pop = new bootstrap.Popover(el, { trigger: 'manual' });

    el.addEventListener('shown.bs.popover', () => {
      const tipId = el.getAttribute('aria-describedby');
      const tip = document.getElementById(tipId);
      if (!tip) return;

      // Récupère la classe popover-* sur le lien
      const popoverClass = [...el.classList].find(cls => cls.startsWith('popover-'));
      if (!popoverClass) return;

      // On ajoute simplement la classe sur le tip
      tip.classList.add(popoverClass);
    });

    pop.show();
    el.addEventListener('click', e => { e.preventDefault(); pop.toggle(); });
  });
});

// Scroll to top

let mybutton = document.getElementById("top-btn");

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    $(mybutton).fadeIn();
  } else {
    $(mybutton).fadeOut();
  }
}
if(mybutton) {
  // When the user clicks on the button, scroll to the top of the document
  mybutton.addEventListener('click', function() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
  })
}
