$(document).ready(function(){
    $.nette.init();


    $('.dropdownMenu').hover(function() {
        $(this).find('.dropdown-content').slideDown(200);
    }, function() {
        $(this).find('.dropdown-content').slideUp(200);
    });

    $('.deleteArticle').click(function(){
        $('.popUp-background').css('display', 'block');
        $('.popUp').css('display', 'block');
    });

    $('.ban').click(function(){
        $('.popUp-background').css('display', 'block');
        $('.popUpBan').css('display', 'block');
    });

    $('.role').click(function(){
        $('.popUp-background').css('display', 'block');
        $('.popUp').css('display', 'block');
    });

    $('.deleteArticle-show').click(function(){
        $('.popUp-background').css('display', 'block');
        $('.popUp').css('display', 'block');
    });

    $('.cancel').click(function(){
        $('.popUp-background').css('display', 'none');
        $('.popUp').css('display', 'none');
        $('.popUpBan').css('display', 'none');
    });

    $('.popUp-background').click(function(){
        $(this).css('display', 'none');
        $('.popUp').css('display', 'none');
        $('.popUpBan').css('display', 'none');
    });

    $('.popUp').click(function(e){
        e.stopPropagation();
    });

    $('.popUpBan').click(function(e){
        e.stopPropagation();
    });
});


/**
 * České formuláře, když je lokalizace česká.
 */
tinyMCE.init({
    selector: ".mceEditor_cs",
    plugins: 'autoresize, link, emoticons, codesample',
    toolbar: 'undo redo styleselect bold italic alignleft aligncenter alignright bullist numlist outdent indent code emoticons link codesample',
    autoresize_min_width: '100%',
    autoresize_min_height: 600,
    autoresize_max_height: 1000,
    max_height: 1000,
    language: "cs"
});

/**
 * Anglické formuláře, když je lokalizace anglická.
 */
tinyMCE.init({
    selector: ".mceEditor_en",
    plugins: 'autoresize, link, emoticons, codesample',
    toolbar: 'undo redo styleselect bold italic alignleft aligncenter alignright bullist numlist outdent indent code emoticons link codesample',
    autoresize_min_width: '100%',
    autoresize_min_height: 600,
    autoresize_max_height: 1000,
    max_height: 1000
});

