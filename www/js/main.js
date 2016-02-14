$(function(){
    $.nette.init();

    $('.ratings_stars').hover(
        function() {
            $(this).prevAll().andSelf().addClass('ratings_over');
            $(this).nextAll().removeClass('ratings_vote');
        },

        function() {
            $(this).prevAll().andSelf().removeClass('ratings_over');
        }
    );
});


/**
 * České formuláře, když je lokalizace česká.
 */
tinyMCE.init({
    selector: ".mceEditor_cs",
    plugins: 'autoresize',
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
    selector: ".mceEditor_en"
});

