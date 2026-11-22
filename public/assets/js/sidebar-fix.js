// Sidebar Submenu Fix
// Prevents submenu from auto-collapsing when clicking on submenu items

$(document).ready(function() {
    // Fix: Only apply submenu toggle to parent menu items, not submenu links
    $('.sidebar-menu li.submenu > a').on('click', function(e) {
        e.preventDefault();

        var $parentLi = $(this).parent('li.submenu');

        // Toggle subdrop on the <li> parent (CSS selector: li.subdrop > ul)
        if ($parentLi.hasClass('subdrop')) {
            $parentLi.removeClass('subdrop');
        } else {
            $parentLi.addClass('subdrop');
        }
    });

    // Keep stopPropagation for submenu item links so clicking them
    // doesn't trigger the parent menu toggle
    $('.sidebar-menu li.submenu ul li a').on('click', function(e) {
        e.stopPropagation();
    });

    // Initialize: open submenu if active link is inside it
    $('.sidebar-menu li.submenu a.active').each(function () {
        var $parentLi = $(this).closest('li.submenu');
        if ($parentLi.length) {
            $parentLi.addClass('subdrop');
            // Also add subdrop to parent submenus (for nested submenus)
            var $ancestor = $parentLi.closest('li.submenu');
            while ($ancestor.length) {
                $ancestor.addClass('subdrop');
                $ancestor = $ancestor.parent().closest('li.submenu');
            }
        }
    });
});
