window.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
window.isSearchBot = /Bot/i.test(navigator.userAgent);
window.addEventListener("DOMContentLoaded", function () {
    if (window.isMobile) {
        document.body.classList.add("is-mobile");
    }
});
