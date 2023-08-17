jQuery(document).ready(function($) {
    $('.settings-tab').hide(); // Hide all tab content initially
    $('.nav-tab-wrapper a:first').addClass('nav-tab-active'); // Add active class to the first tab link
    $('.settings-tab:first').show(); // Show the content of the first tab

    // Handle tab click event
    $('.nav-tab-wrapper a').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab-wrapper a').removeClass('nav-tab-active'); // Remove active class from all tab links
        $('.settings-tab').hide(); // Hide all tab content

        var tabId = $(this).attr('href');
        $(this).addClass('nav-tab-active'); // Add active class to the clicked tab link
        $(tabId).show(); // Show the content of the clicked tab
    });
});