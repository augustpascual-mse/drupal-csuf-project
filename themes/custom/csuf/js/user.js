jQuery(function ($){
    var candidateName = $('.field--name-field-first-name .field__item').text() + ' ' + $('.field--name-field-last-name .field__item').text();
    $('.personal-information .title').text(candidateName);
});
