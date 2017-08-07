

// Set default heights (IE sucks)
if ( jQuery.browser.msie ) {
    var DDDialogDefaultHeight = 254;
    var DDDialogDefaultExtraHeight = 114;
} else {
    var DDDialogDefaultHeight = 246;
    var DDDialogDefaultExtraHeight = 106;
}

// This function is run when a button is clicked. It creates a dialog box for the user to input the data.
function DDButtonClick() {

    // Close any existing copies of the dialog
    DDDialogClose();

    // Calculate the height/maxHeight (i.e. add some height for Blip.tv)
    DDDialogHeight = DDDialogDefaultHeight;
    DDDialogMaxHeight = DDDialogDefaultHeight + DDDialogDefaultExtraHeight;

    // Open the dialog while setting the width, height, title, buttons, etc. of it
    var buttons = { "Okay": DDButtonOkay, "Cancel": DDDialogClose };
    var title = '<img src="/wp-content/plugins/dipdive/resource/gfx/dipdive.png" alt="Dipdive" width="20" height="20" /> Dipdive';
    jQuery("#dd-dialog").dialog({ autoOpen: false, width: 750, minWidth: 750, height: DDDialogHeight, minHeight: DDDialogHeight, maxHeight: DDDialogMaxHeight, title: title, buttons: buttons });

    // Reset the dialog box incase it's been used before
    jQuery("#dd-dialog-input").val("");
    jQuery("#dd-dialog-tag").val('dipdive');

    // Style the jQuery-generated buttons by adding CSS classes and add second CSS class to the "Okay" button
    jQuery(".ui-dialog button").addClass("button").each(function(){
        if ( "Okay" == jQuery(this).html() ) jQuery(this).addClass("button-highlighted");
    });

    // Do some hackery on any links in the message -- jQuery(this).click() works weird with the dialogs, so we can't use it
    jQuery("#dd-dialog-message a").each(function(){
        jQuery(this).attr("onclick", 'window.open( "' + jQuery(this).attr("href") + '", "_blank" );return false;' );
    });

    // Show the dialog now that it's done being manipulated
    jQuery("#dd-dialog").dialog("open");

    // Focus the input field
    jQuery("#dd-dialog-input").focus();
}

// Close + reset
function DDDialogClose() {
    jQuery(".ui-dialog").height(DDDialogDefaultHeight);
    jQuery("#dd-dialog").dialog("close");
}

// Callback function for the "Okay" button
function DDButtonOkay() {

    var tag = jQuery("#dd-dialog-tag").val();
    var text = jQuery("#dd-dialog-input").val();
    var width = jQuery("#dd-dialog-width").val();
    var height = jQuery("#dd-dialog-height").val();

    if ( !tag || !text ) return DDDialogClose();

    
    var text = "[" + tag + "]" + text + "[/" + tag + "]";


    if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
        ed.focus();
        if (tinymce.isIE)
            ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);

        ed.execCommand('mceInsertContent', false, text);
    } else
        edInsertContent(edCanvas, text);

    DDDialogClose();
}


// On page load...
jQuery(document).ready(function(){
    // Add the buttons to the HTML view
    jQuery("#ed_toolbar").append('<input type="button" value="Dipdive" onclick="DDButtonClick()" title="Dipdive Embed" />');

    // If the Enter key is pressed inside an input in the dialog, do the "Okay" button event
    jQuery("#dd-dialog :input").keyup(function(event){
        if ( 13 == event.keyCode ) // 13 == Enter
            DDButtonOkay();
    });
});
