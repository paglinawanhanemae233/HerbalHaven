/**
 * Mobile Enhancements
 * Adds swipe gestures, pull-to-refresh, and improved mobile interactions
 */

(function() {
    'use strict';

    // Swipe gesture detection for catalogue cards
    function initSwipeGestures() {
        const cards = document.querySelectorAll('.herb-card');
        if (cards.length === 0 || window.innerWidth > 768) return;

        cards.forEach(card => {
            let startX = 0;
            let startY = 0;
            let isDown = false;

            card.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isDown = true;
            }, { passive: true });

            card.addEventListener('touchmove', (e) => {
                if (!isDown) return;
                
                const currentX = e.touches[0].clientX;
                const currentY = e.touches[0].clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;

                // If horizontal swipe is greater than vertical, prevent default
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    e.preventDefault();
                }
            }, { passive: false });

            card.addEventListener('touchend', (e) => {
                if (!isDown) return;
                
                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const diffX = endX - startX;
                const diffY = endY - startY;

                // Detect swipe left (to view details) or right
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    const link = card.querySelector('.herb-card-link, a');
                    if (link && diffX < 0) {
                        // Swipe left - navigate to details
                        link.click();
                    }
                }

                isDown = false;
            }, { passive: true });
        });
    }

    // Pull-to-refresh functionality
    function initPullToRefresh() {
        if (window.innerWidth > 768) return; // Only on mobile

        let startY = 0;
        let isPulling = false;
        const container = document.querySelector('.container, .card-grid');
        if (!container) return;

        const indicator = document.createElement('div');
        indicator.className = 'pull-refresh-indicator';
        indicator.innerHTML = '↓ Pull to refresh';
        container.parentNode.insertBefore(indicator, container);

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
                isPulling = true;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!isPulling || window.scrollY > 0) {
                isPulling = false;
                return;
            }

            const currentY = e.touches[0].clientY;
            const diffY = currentY - startY;

            if (diffY > 50) {
                indicator.classList.add('active');
                indicator.innerHTML = '↓ Release to refresh';
            } else {
                indicator.classList.remove('active');
                indicator.innerHTML = '↓ Pull to refresh';
            }
        }, { passive: true });

        document.addEventListener('touchend', () => {
            if (isPulling && indicator.classList.contains('active')) {
                indicator.innerHTML = '⟳ Refreshing...';
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } else {
                indicator.classList.remove('active');
            }
            isPulling = false;
        }, { passive: true });
    }

    // Improve form inputs on mobile
    function improveMobileInputs() {
        if (window.innerWidth > 768) return;

        const inputs = document.querySelectorAll('input[type="text"], input[type="search"], textarea');
        inputs.forEach(input => {
            // Prevent zoom on focus (iOS)
            if (input.style.fontSize === '' || parseInt(input.style.fontSize) < 16) {
                input.style.fontSize = '16px';
            }

            // Add clear button for search inputs
            if (input.type === 'search' || input.name === 'search') {
                input.addEventListener('input', function() {
                    if (this.value && !this.nextElementSibling?.classList.contains('clear-search')) {
                        const clearBtn = document.createElement('button');
                        clearBtn.type = 'button';
                        clearBtn.className = 'clear-search';
                        clearBtn.innerHTML = '×';
                        clearBtn.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 1.5rem; color: var(--secondary-text); cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;';
                        clearBtn.addEventListener('click', () => {
                            this.value = '';
                            this.focus();
                            clearBtn.remove();
                            if (this.form) this.form.submit();
                        });
                        this.parentNode.style.position = 'relative';
                        this.parentNode.appendChild(clearBtn);
                    } else if (!this.value && this.nextElementSibling?.classList.contains('clear-search')) {
                        this.nextElementSibling.remove();
                    }
                });
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initSwipeGestures();
            initPullToRefresh();
            improveMobileInputs();
        });
    } else {
        initSwipeGestures();
        initPullToRefresh();
        improveMobileInputs();
    }

    // Re-initialize on resize (if switching between mobile/desktop)
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            initSwipeGestures();
        }, 250);
    });
})();

