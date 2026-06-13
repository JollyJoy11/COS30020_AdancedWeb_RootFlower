document.addEventListener('DOMContentLoaded', function() {
    // ========================
    // Scroll Animated Item
    // ========================
    const items = document.querySelectorAll('.animated-item');
    if (items.length > 0) {
        const itemsObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, Array.from(items).indexOf(entry.target) * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        items.forEach(item => itemsObserver.observe(item));
    }

    // ========================
    // Homepage Workshop Review Animation
    // ========================
    const reviews = document.querySelectorAll('.review');
    if (reviews.length > 0) {
        const reviewsObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        reviews.forEach((review, index) => {
            review.classList.add(index % 2 === 0 ? 'from-left' : 'from-right');
            reviewsObserver.observe(review);
        });
    }

    // ========================
    // Tooltip
    // ========================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (tooltipTriggerList.length > 0) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // ========================
    // Auto Alert Dismiss
    // ========================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show'); 
            alert.addEventListener('transitionend', () => alert.remove());
        }, 3000); 
    });

    // ========================
    // Workshop Countdown
    // ========================
    const daysEl = document.getElementById("days");
    const hoursEl = document.getElementById("hours");
    const minutesEl = document.getElementById("minutes");
    const secondsEl = document.getElementById("seconds");

    if (daysEl && hoursEl && minutesEl && secondsEl && typeof window.workshopDate !== 'undefined') {
        function updateCountdown() {
            const now = new Date().getTime();
            
            const workshopTimestamp = Number(window.workshopDate);
            if (isNaN(workshopTimestamp)) return; 
            
            const distance = workshopTimestamp - now;

            if (distance <= 0) {
                daysEl.innerText = "00";
                hoursEl.innerText = "00";
                minutesEl.innerText = "00";
                secondsEl.innerText = "00";
                
                clearInterval(updateCountdown.intervalId); 
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            daysEl.innerText = days.toString().padStart(2, '0');
            hoursEl.innerText = hours.toString().padStart(2, '0');
            minutesEl.innerText = minutes.toString().padStart(2, '0');
            secondsEl.innerText = seconds.toString().padStart(2, '0');
        }
        
        updateCountdown.intervalId = setInterval(updateCountdown, 1000); 
        
        updateCountdown(); 
    }

    // ========================
    // Update Profile Image Crop Preview
    // ========================
    const profileInput = document.getElementById('profileInput');

    if (profileInput && typeof Cropper !== 'undefined') { 
        const cropPreview = document.getElementById('cropPreview');
        const hiddenCroppedInput = document.getElementById('cropped_profile'); 
        
        const profileImageForm = profileInput.closest('form'); 
        
        let cropper;
        
        profileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = () => {
                cropPreview.src = reader.result;
                cropPreview.classList.remove('d-none');

                if (cropper) cropper.destroy();

                setTimeout(() => {
                    cropper = new Cropper(cropPreview, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 1,
                        responsive: true,
                        background: true,
                        ready() {
                            const modalDialog = document.querySelector('#uploadProfileModal .modal-dialog');
                            const img = this.element;
                            const containerWidth = modalDialog.offsetWidth - 50; 
                            
                            if (img.naturalWidth > containerWidth) {
                                 cropper.setCanvasData({ width: containerWidth });
                            }
                        }
                    });
                }, 50); 
            };
            reader.readAsDataURL(file);
        });

        if (profileImageForm) {
            profileImageForm.addEventListener('submit', function(e) {
                if (e.submitter && e.submitter.name === 'update_profile') {
                    if (cropper) { 
                        const canvas = cropper.getCroppedCanvas({ 
                            width: 300, 
                            height: 300 
                        });
                        
                        hiddenCroppedInput.value = canvas.toDataURL('image/png'); 
                    } 
                }
            });
        }
    }

    // ========================
    // Student Work Detail Carousel Indicator
    // ========================
    const indicator = document.getElementById('carousel-indicator');
    const carouselEl = document.getElementById('mediaCarousel');

    if (indicator && carouselEl && typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
        const mediaItems = carouselEl.querySelectorAll('.carousel-item');
        const total = mediaItems.length;

        const carousel = new bootstrap.Carousel(carouselEl, { interval: false });
        
        const updateIndicator = () => {
            const activeIndex = [...mediaItems].findIndex(item => item.classList.contains('active')) + 1;
            indicator.textContent = `${activeIndex} / ${total}`;
        };

        carouselEl.addEventListener('slid.bs.carousel', updateIndicator);
        
        updateIndicator();
    }

    // ========================
    // Masonry Grid Initialization
    // ========================
    const grid = document.querySelector('.row[data-masonry]');

    if (grid) {
        const videos = grid.querySelectorAll('video');
        let loadedVideos = 0;

        if (videos.length === 0) {
            imagesLoaded(grid, function () {
                new Masonry(grid, { percentPosition: true });
            });
            return;
        }

        videos.forEach(video => {
            video.addEventListener('loadedmetadata', () => {
                loadedVideos++;
                if (loadedVideos === videos.length) {
                    imagesLoaded(grid, function () {
                        new Masonry(grid, { percentPosition: true });
                    });
                }
            });
        });
    }

    // ========================
    // Admin Clock Display
    // ========================
    const clockElement = document.getElementById("liveClock");

    if (clockElement) {
        function updateClock() {
            const now = new Date();

            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };

            const formattedTime = now.toLocaleString('en-MY', options).replace('am','AM').replace('pm','PM');
                                    
            clockElement.textContent = formattedTime; 
        }

        updateClock();
        setInterval(updateClock, 1000);
    }

    // ========================
    // Prevent Overhead Media
    // ========================
    const form = document.forms['studentwork_submission'];
    const MAX_TOTAL_SIZE = 40960000; // ~39 MB

    if (form){
        const mediaInputs = [
            document.querySelector('input[name="studentwork_media1"]'),
            document.querySelector('input[name="studentwork_media2"]'),
            document.querySelector('input[name="studentwork_media3"]'),
            document.querySelector('input[name="studentwork_media4"]')
        ];

        form.addEventListener('submit', function(event) {
            let totalSize = 0;
            let filesPresent = false;
            
            mediaInputs.forEach(input => {
                if (input && input.files.length > 0) {
                    filesPresent = true;
                    totalSize += input.files[0].size;
                }
            });

            if (filesPresent && totalSize > MAX_TOTAL_SIZE) {
                event.preventDefault(); 
                alert("Upload failed! The total size of all media files exceeds the allowed limit (40MB). Please remove some files or use smaller files.");
                return false;
            }

            return true; // Proceed with submission
        });
    }

    // ========================
    // Select Multiple Checkbox
    // ========================
    const STORAGE_KEY = 'selectedStudentWorks';

    function getSelectedIds() {
        const storedIds = localStorage.getItem(STORAGE_KEY) || '[]';
        return JSON.parse(storedIds).map(String);
    }

    function saveSelectedIds(ids) {
        const uniqueIds = Array.from(new Set(ids.map(String)));
        localStorage.setItem(STORAGE_KEY, JSON.stringify(uniqueIds));
    }

    function updateIconAndState(checkboxes, selectAllCheckbox, selectAllIcon) {
        if (!selectAllIcon || checkboxes.length === 0) {
            selectAllIcon.className = 'bi bi-square select-all-icon';
            return;
        }

        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const totalCount = checkboxes.length;

        // 1. All Checked (Full Square)
        if (checkedCount === totalCount && totalCount > 0) {
            selectAllIcon.className = 'bi bi-check-square select-all-icon';
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        // 2. Some Checked (Dash Square / Indeterminate)
        } else if (checkedCount > 0) {
            selectAllIcon.className = 'bi bi-dash-square select-all-icon';
            selectAllCheckbox.checked = false; 
            selectAllCheckbox.indeterminate = true;
        // 3. None Checked (Empty Square)
        } else {
            selectAllIcon.className = 'bi bi-square select-all-icon';
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    // Main logic for individual checkbox change
    function handleIndividualChange(checkboxes, selectAllCheckbox, selectAllIcon, event) {
        let currentIds = getSelectedIds();
        const id = event.target.value;

        if (event.target.checked) {
            if (!currentIds.includes(id)) currentIds.push(id);
        } else {
            currentIds = currentIds.filter(item => item !== id);
        }
        
        saveSelectedIds(currentIds);
        updateIconAndState(checkboxes, selectAllCheckbox, selectAllIcon);
    }
    
    const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllIcon = document.getElementById('selectAllIcon');
    const postForm = document.querySelector('form[method="POST"]');
    const allSelectedIdsInput = document.getElementById('allSelectedIds');

    let currentIds = getSelectedIds();

    checkboxes.forEach(checkbox => {
        const id = checkbox.value;
        // Pre-check if the ID is in the persistent list
        if (currentIds.includes(id)) {
            checkbox.checked = true;
        }

        // Attach listener
        checkbox.addEventListener('change', (event) => {
            handleIndividualChange(checkboxes, selectAllCheckbox, selectAllIcon, event);
        });
    });

    updateIconAndState(checkboxes, selectAllCheckbox, selectAllIcon);

    if (selectAllIcon) {
        selectAllIcon.addEventListener('click', () => {
            const shouldSelect = !selectAllCheckbox.checked; 
            
            const allFilteredIds = window.allSelectableIds || [];
            
            if (shouldSelect) {
                saveSelectedIds(allFilteredIds.map(String)); 
            } else {
                saveSelectedIds([]); 
            }

            checkboxes.forEach(checkbox => {
                checkbox.checked = shouldSelect;
            });
            
            updateIconAndState(checkboxes, selectAllCheckbox, selectAllIcon);
        });
    }

    // Clear selection
    function clearSelectionOnFilterChange() {
        setTimeout(() => {
            localStorage.removeItem(STORAGE_KEY);
        }, 10);
        checkboxes.forEach(cb => cb.checked = false);
        updateIconAndState(checkboxes, selectAllCheckbox, selectAllIcon);
    }

    document.querySelectorAll('#status-tabs a').forEach(el => {
        el.addEventListener('click', clearSelectionOnFilterChange);
    });

    const refreshBtn = document.getElementById('refresh');
    if (refreshBtn) { 
        refreshBtn.addEventListener('click', clearSelectionOnFilterChange);
    }
    
    document.querySelectorAll('form[method="GET"]').forEach(form => {
        form.addEventListener('submit', clearSelectionOnFilterChange);
    });

    document.querySelectorAll('thead a').forEach(link => {
        link.addEventListener('click', clearSelectionOnFilterChange);
    });
    
    // Put Selection into HTML forms
    if (postForm && allSelectedIdsInput) {
        postForm.addEventListener('submit', function(e) {
            allSelectedIdsInput.value = getSelectedIds().join(',');
            
            if (e.submitter && e.submitter.name === 'action_bulk_delete' || e.submitter.name === 'action_bulk_approve' || e.submitter.name === 'action_bulk_reject' || submitterName === 'action_bulk_restore' || submitterName === 'action_bulk_delete_perm' || submitterName === 'action_bulk_reject_edit' || submitterName === 'action_bulk_approve_edit') {
                setTimeout(() => {
                    localStorage.removeItem(STORAGE_KEY);
                }, 500); 
            }
        });
    }
});
