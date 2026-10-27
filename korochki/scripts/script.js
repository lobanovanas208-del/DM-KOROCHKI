/**
 * script.js — бургер-меню, слайдер, маска телефона, модалка отзыва
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('script.js загружен');

    // ===== БУРГЕР-МЕНЮ =====
    var burgerBtn = document.getElementById('burgerBtn');
    var headerNav = document.getElementById('headerNav');

    if (burgerBtn && headerNav) {
        console.log('Бургер найден');

        burgerBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Клик по бургеру');
            headerNav.classList.toggle('open');
            burgerBtn.classList.toggle('active');
        });

        // Закрытие при клике на ссылку
        var navLinks = headerNav.querySelectorAll('.nav-btn');
        for (var i = 0; i < navLinks.length; i++) {
            navLinks[i].addEventListener('click', function () {
                headerNav.classList.remove('open');
                burgerBtn.classList.remove('active');
            });
        }

        // Закрытие при клике вне меню
        document.addEventListener('click', function (e) {
            if (!headerNav.contains(e.target) && !burgerBtn.contains(e.target)) {
                headerNav.classList.remove('open');
                burgerBtn.classList.remove('active');
            }
        });
    } else {
        console.log('Бургер не найден на этой странице');
    }

    // ===== СЛАЙДЕР =====
    var slider = document.getElementById('mainSlider');
    if (slider) {
        var track = document.getElementById('sliderTrack');
        var dots  = document.querySelectorAll('#sliderDots .dot');

        if (track && dots.length > 0) {
            var currentIndex = 0;
            var totalSlides  = 4;
            var timer        = null;

            function goToSlide(index) {
                if (index < 0) index = totalSlides - 1;
                if (index >= totalSlides) index = 0;

                currentIndex = index;
                track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';

                for (var i = 0; i < dots.length; i++) {
                    if (i === currentIndex) dots[i].classList.add('active');
                    else dots[i].classList.remove('active');
                }
            }

            function startTimer() {
                timer = setInterval(function () {
                    goToSlide(currentIndex + 1);
                }, 3000);
            }

            function resetTimer() {
                clearInterval(timer);
                startTimer();
            }

            for (var i = 0; i < dots.length; i++) {
                (function (idx) {
                    dots[idx].addEventListener('click', function () {
                        goToSlide(idx);
                        resetTimer();
                    });
                })(i);
            }

            startTimer();
        }
    }

    // ===== МАСКА ТЕЛЕФОНА =====
    var phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '');
            if (x.length > 0 && x[0] === '8') x = x.substring(1);

            var formatted = '';
            if (x.length > 0) formatted = '8(' + x.substring(0, 3);
            if (x.length >= 4) formatted += ')' + x.substring(3, 6);
            if (x.length >= 7) formatted += '-' + x.substring(6, 8);
            if (x.length >= 9) formatted += '-' + x.substring(8, 10);

            e.target.value = formatted;
        });

        phoneInput.addEventListener('keydown', function (e) {
            if ([46, 8, 9, 27, 13, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
            if ((e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88) && (e.ctrlKey || e.metaKey)) return;
            if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

    // ===== МОДАЛКА ОТЗЫВА =====
    window.openReviewModal = function (requestId, reviewId, comment, stars) {
        var rReq = document.getElementById('reviewRequestId');
        var rId  = document.getElementById('reviewId');
        var rCom = document.getElementById('reviewComment');
        var rSt  = document.getElementById('reviewStars');
        var modal = document.getElementById('reviewModal');

        if (rReq) rReq.value = requestId;
        if (rId)  rId.value  = reviewId;
        if (rCom) rCom.value = comment;
        if (rSt)  rSt.value  = stars || 5;
        if (modal) modal.style.display = 'flex';
    };

    window.closeReviewModal = function () {
        var modal = document.getElementById('reviewModal');
        if (modal) modal.style.display = 'none';
    };

    var modal = document.getElementById('reviewModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeReviewModal();
        });
    }
});