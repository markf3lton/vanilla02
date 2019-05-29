$= jQuery;

/*** BEGIN FILE EXPLORER CODE ***/
//Show and Hide the image preloader so expanded content is not seen by the user.
$('.view-landing-page-management .views-exposed-form.bef-exposed-form-preloader').hide();
$('.view-landing-page-management .views-exposed-form.bef-exposed-form').show();

// Execute this after the site is loaded.
$(function() {
    // Find list items representing folders and
    // style them accordingly.  Also, turn them
    // into links that can expand/collapse the
    // tree leaf.
    $('.view-landing-page-management .views-exposed-form.bef-exposed-form li > ul').each(function(i) {
        // Find this list's parent list item.
        var parent_li = $(this).parent('li');

        // Temporarily remove the list from the
        // parent list item, wrap the remaining
        // text in an anchor, then reattach it.
        var sub_ul = $(this).remove();
        parent_li.wrapInner('<a/>').find('a').click(function() {
            // Make the anchor toggle the leaf display.
            sub_ul.slideToggle(300);
          
          //Add class to change folder image when clicked on
          $(this).toggleClass('expanded');
          
        });
        parent_li.append(sub_ul);
    });

    // Hide all lists except the outermost.
    $('.view-landing-page-management .views-exposed-form.bef-exposed-form ul ul').hide();
    $(".view-landing-page-management .bef-link-active").parents().show();
    $(".view-landing-page-management div.bef-link-active").parent().addClass('active-folder')
  });

/*** END FILE EXPLORER CODE ***/