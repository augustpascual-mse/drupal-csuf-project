jQuery(function ($){
    $('ul.mdl-js-menu li.menu-benefits').on( "click", function() {
        $('.mdl-layout__content .company-about-us').hide();
        $('.mdl-layout__content .company-interview').hide();
        $('.mdl-layout__content .company-benefits').css('display', 'flex');
    });
    $('ul.mdl-js-menu li.menu-about-us').on( "click", function() {
        $('.mdl-layout__content .company-interview').hide();
        $('.mdl-layout__content .company-benefits').hide();
        $('.mdl-layout__content .company-about-us').css('display', 'flex');
    });
    $('ul.mdl-js-menu li.menu-interview').on( "click", function() {
        $('.mdl-layout__content .company-about-us').hide();
        $('.mdl-layout__content .company-benefits').hide();
        $('.mdl-layout__content .company-interview').css('display', 'flex');
    });
});
