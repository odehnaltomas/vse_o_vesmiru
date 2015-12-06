$(function(){

});


/**
 * České formuláře, když je lokalizace česká.
 */
tinyMCE.init({
    selector: ".mceEditor_cs",
    theme: 'advanced',
    plugins: 'autoresize',
    width: '100%',
    height: 500,
    language: "cs"
});

/**
 * Anglické formuláře, když je lokalizace anglická.
 */
tinyMCE.init({
    selector: ".mceEditor_en"
});
