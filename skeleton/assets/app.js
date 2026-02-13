
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import $ from 'jquery';

import './styles/app.css';
import './styles/app.scss';

import { LuhnCheck } from './luhn.js';

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

const inputStreet = document.getElementById('registration_step_adress_street');
const inputZip = document.getElementById('registration_step_adress_zipCode');
const inputCountry = document.getElementById('registration_step_adress_country');

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

