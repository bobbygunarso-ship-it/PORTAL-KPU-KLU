    </main>
    <script>
        const adminMobileMenuBtn = document.getElementById('adminMobileMenuBtn');
        const adminSidebarNav = document.getElementById('adminSidebarNav');
        if (adminMobileMenuBtn && adminSidebarNav) {
            adminMobileMenuBtn.addEventListener('click', () => {
                adminSidebarNav.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
