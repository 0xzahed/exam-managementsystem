// Exam Index Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    window.filterExams = function(filterType, event) {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        // Add active class to clicked button
        event.target.classList.remove('bg-gray-100', 'text-gray-700');
        event.target.classList.add('active', 'bg-blue-600', 'text-white');
        
            // Filter logic
        const examCards = document.querySelectorAll('.exam-card');
        
        examCards.forEach(card => {
            let shouldShow = true;
            
            switch(filterType) {
                case 'draft':
                    shouldShow = card.dataset.status === 'draft';
                    break;
                case 'available':
                    shouldShow = card.dataset.availability === 'available';
                    break;
                case 'completed':
                    shouldShow = card.dataset.completion === 'completed';
                    break;
                case 'pending':
                    shouldShow = card.dataset.completion === 'pending';
                    break;
                case 'all':
                default:
                    shouldShow = true;
                    break;
            }
            
            if (shouldShow) {
                card.style.display = 'block';
                card.classList.add('fade-in');
            } else {
                card.style.display = 'none';
                card.classList.remove('fade-in');
            }
        });
    };
    
    // Add fade-in animation to exam cards
    const examCards = document.querySelectorAll('.exam-card');
    examCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
});
