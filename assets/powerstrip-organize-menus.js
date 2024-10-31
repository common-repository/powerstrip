document.addEventListener('DOMContentLoaded', function () {
    var menus = document.querySelectorAll('li.ps-topmenu.has-ps-submenu');

    menus.forEach(function(menu) {
        // Create a new UL element to contain all following .ps-submenu items
        var submenuContainer = document.createElement('ul');
        submenuContainer.className = 'ps-submenu-container wp-submenu wp-submenu-wrap'; // Add a class for styling

        // Find the content of the original menu item's .wp-menu-name span
        var wpMenuNameElement = menu.querySelector('.wp-menu-name');
        var wpMenuName = '';

        // Iterate over child nodes and extract only text nodes
        wpMenuNameElement.childNodes.forEach(function(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                wpMenuName += node.textContent.trim();
            }
        });

        // Create the new li element with the specified properties
        var submenuHead = document.createElement('li');
        submenuHead.className = 'wp-submenu-head';
        submenuHead.setAttribute('aria-hidden', 'true');
        submenuHead.appendChild(document.createTextNode(wpMenuName));

        // Append the new li element to the submenu container
        submenuContainer.appendChild(submenuHead);
        
        // Insert the new submenu container directly into the menu item
        menu.appendChild(submenuContainer);

        // Start with the next sibling of the current menu
        var nextElement = menu.nextElementSibling;

        // Loop through following siblings until another .ps-topmenu or the end of the list
        while (nextElement) {
            if (nextElement.classList.contains('ps-topmenu')) {
                break; // Stop if we hit another .ps-topmenu
            }
            if (nextElement.classList.contains('ps-submenu')) {
                submenuContainer.appendChild(nextElement.cloneNode(true)); // Clone and append
                var toRemove = nextElement; // Store current element to remove
                nextElement = nextElement.nextElementSibling; // Move to the next element
                toRemove.remove(); // Remove the original element from the DOM
                continue; // Continue with the next iteration
            }
            nextElement = nextElement.nextElementSibling; // Move to the next sibling
        }
    });


    // Make sure top level IDs are intact
    var menuItems = document.querySelectorAll('li.ps-topmenu.has-ps-submenu');
    menuItems.forEach(function(item) {
        // Find the span within the item that has the 'has-ps-sub' class
        var span = item.querySelector('span.has-ps-sub');
        if (span) {
            // Get the data-id attribute from the span
            var dataId = span.getAttribute('data-id');

            // Add the data-id as a data attribute to the li element
            item.setAttribute('id', dataId);
        }
    });

    // HANDLE THIRDLEVEL MENUS
    // Function to add event listeners
    function addSubMenuListeners() {
        var subMenus = document.querySelectorAll('.has-ps-submenu');

        subMenus.forEach(function(menu) {
            menu.addEventListener('mouseenter', onMouseEnter);
        });
    }

    // Function to remove event listeners
    function removeSubMenuListeners() {
        var subMenus = document.querySelectorAll('.has-ps-submenu');

        subMenus.forEach(function(menu) {
            menu.removeEventListener('mouseenter', onMouseEnter);
        });
    }


    
    // Mouse enter event handler
    function onMouseEnter() {
        var subMenu = this.querySelector('.ps-submenu-container');
        
        if (subMenu) {
            // Get the bounding rectangle of the submenu
            var subMenuRect = subMenu.getBoundingClientRect();
            
            // Calculate adjustments if the submenu goes beyond the viewport
            if (subMenuRect.bottom + 30 > window.innerHeight) {
                // Push up if the submenu extends past the bottom of the viewport
                var overflowBottom = subMenuRect.bottom - window.innerHeight + 30; // Added 30px buffer
                subMenu.style.top = (parseInt(window.getComputedStyle(subMenu).top) - overflowBottom) + 'px';
            }

            if (subMenuRect.top < 60) { // Ensuring there's a 60px space from the top
                // Pull down if the submenu is above the viewport or within 60px of the viewport top
                subMenu.style.top = (parseInt(window.getComputedStyle(subMenu).top) + (60 - subMenuRect.top)) + 'px';
            }

            // Check if horizontal adjustment is also needed
            var subMenuRightEdge = subMenuRect.right;
            var viewportWidth = window.innerWidth;

            if (subMenuRightEdge > viewportWidth) {
                // Adjust the submenu position to the left side of the parent menu item
                subMenu.style.left = 'auto';
                subMenu.style.right = '100%'; // Positions it to the left of the parent
            } else {
                // Reset positions if within viewport to handle dynamic changes
                subMenu.style.left = '100%';
                subMenu.style.right = 'auto';
            }
        }
    }

    // Initial check and add listeners if viewport width is greater than 782px
    if (window.innerWidth > 782) {
        addSubMenuListeners();
    }

    // Add a resize event listener to handle changes in viewport width
    window.addEventListener('resize', function() {
        if (window.innerWidth > 782) {
            addSubMenuListeners();
        } else {
            removeSubMenuListeners();
        }
    });


});
