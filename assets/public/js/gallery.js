document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.post-gallery').forEach(function (g) {
        var track = g.querySelector('.post-gallery__track');
        var slides = g.querySelectorAll('.post-gallery__slide');
        var dots = g.querySelectorAll('.post-gallery__dot');
        var total = slides.length;
        if (!track || total < 2) return;
        var cur = 0;
        function go(n) {
            cur = (n + total) % total;
            track.style.transform = 'translateX(-' + cur * 100 + '%)';
            dots.forEach(function (d, i) { d.classList.toggle('active', i === cur); });
        }
        var bp = g.querySelector('.post-gallery__btn--prev');
        var bn = g.querySelector('.post-gallery__btn--next');
        if (bp) bp.addEventListener('click', function () { go(cur - 1); });
        if (bn) bn.addEventListener('click', function () { go(cur + 1); });
        dots.forEach(function (d, i) { d.addEventListener('click', function () { go(i); }); });
        var sx = 0;
        track.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
        track.addEventListener('touchend', function (e) {
            var dx = e.changedTouches[0].clientX - sx;
            if (Math.abs(dx) > 40) go(dx < 0 ? cur + 1 : cur - 1);
        }, { passive: true });
    });
});
