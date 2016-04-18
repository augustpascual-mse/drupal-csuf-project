jQuery(function ($){
    var candidateName = $('.field--name-field-first-name .field__item').text() + ' ' + $('.field--name-field-last-name .field__item').text();
    $('.personal-information .title').text(candidateName);

    $('#edit-field-school-start-date-0-value-day').val('1');
    $('#edit-field-school-end-date-0-value-day').val('1');
    $('#edit-field-job-start-date-0-value-day').val('1');
    $('#edit-field-job-end-date-0-value-day').val('1');
});
