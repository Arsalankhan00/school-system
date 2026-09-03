        </div> <!-- End of page-content -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> School Management System. All Rights Reserved.</p>
        </footer>
    </div> <!-- End of main-content -->
    
    <!-- Bootstrap JS Bundle: Required for dropdowns, modals, and responsive features to work -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // DOMContentLoaded means "wait until the HTML is fully loaded before running this Javascript"
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- Feature 1: Dynamic Page Title ---
            // Find the link in the sidebar that is currently 'active' (the page we are on)
            const activeLink = document.querySelector('.sidebar-menu li a.active');
            if (activeLink) {
                // Take the text from that link and put it into the top header's title
                const titleText = activeLink.innerText.trim();
                document.querySelector('.page-title').innerText = titleText;
            }

            // --- Feature 2: Sidebar Toggle ---
            // Grab the button and the sections we want to move
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            // If the toggle button exists on this page...
            if(sidebarToggle) {
                // When someone clicks the button...
                sidebarToggle.addEventListener('click', function() {
                    // .toggle() means: if the class is there, remove it. If it's not there, add it!
                    // This creates the sliding animation effect via CSS.
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }
        });
    </script>
</body>
</html>
