
        // Add navigation loading logic
        function loadNavigation() {
            fetch('../nav-icons.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(icons => {
                const navBar = document.querySelector('.nav-bar');
                navBar.innerHTML = '';
                
                icons.forEach((icon, index) => {
                    const navItem = document.createElement('div');
                    navItem.className = `nav-item${icon.active ? ' active' : ''}`;
                    navItem.id = icon.id;
                    navItem.textContent = icon.emoji;
                    
                    // Add staggered animation
                    setTimeout(() => {
                        navBar.appendChild(navItem);
                        // Trigger animation after a brief delay
                        setTimeout(() => navItem.classList.add('loaded'), 50);
                    }, index * 100);
                    
                    // Add click handler
                    navItem.addEventListener('click', () => {
                        // Remove active class from all items
                        document.querySelectorAll('.nav-item').forEach(item => {
                            item.classList.remove('active');
                        });
                        // Add active class to clicked item
                        navItem.classList.add('active');
                        
                        // Here you can add navigation logic
                        console.log(`Navigating to: ${icon.id}`);
                    });
                });
            })
            .catch(error => {
                alert('Error loading navigation:', error);
                // Show error state in navigation bar
                const navBar = document.querySelector('.nav-bar');
                navBar.innerHTML = '<div class="nav-item">⚠️</div>';
            });
        }

        // Load navigation when the page loads
        document.addEventListener('DOMContentLoaded', loadNavigation);
        
        
        