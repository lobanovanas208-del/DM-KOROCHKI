document.addEventListener('DOMContentLoaded', function () {
    const track = document.querySelector('.slider-track');
    const dots  = document.querySelectorAll('.slider-dots .dot');

    if (!track || dots.length === 0) return;

    let currentIndex = 0;
    const totalSlides = track.children.length;

    function goToSlide(index) {
        currentIndex = (index + totalSlides) % totalSlides;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentIndex);
        });
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => goToSlide(i));
    });

    setInterval(() => goToSlide(currentIndex + 1), 5000);
});