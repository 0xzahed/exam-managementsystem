// Welcome page slider functionality
let slideIndex = 1;

function nextSlide() {
    slideIndex++;
    if (slideIndex > 3) {
        slideIndex = 1;
    }
    showSlide(slideIndex);
}

function currentSlide(index) {
    slideIndex = index;
    showSlide(slideIndex);
}

function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    
    // Hide all slides
    slides.forEach(slide => {
        slide.classList.remove('active');
    });
    
    // Remove active class from all dots
    dots.forEach(dot => {
        dot.classList.remove('bg-indigo-600');
        dot.classList.add('bg-gray-300');
    });
    
    // Show current slide
    if (slides[index - 1]) {
        slides[index - 1].classList.add('active');
    }
    
    // Highlight current dot
    if (dots[index - 1]) {
        dots[index - 1].classList.remove('bg-gray-300');
        dots[index - 1].classList.add('bg-indigo-600');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize first slide
    showSlide(1);
    
    // Auto-slide every 4 seconds
    setInterval(nextSlide, 4000);
});

// Make functions global so they can be called from HTML onclick events
window.currentSlide = currentSlide;
