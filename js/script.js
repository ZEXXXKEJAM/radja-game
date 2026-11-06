$(document).ready(function () {

    // =========================
    // VARIABEL
    // =========================

    let currentIndex = 0;

    let autoSlide;

    let resizeTimer;

    const slider = $(".product-slider");

    const products = $(".product-card");

    const totalProducts = products.length;

    const cardGap = 20;

    if (!slider.length || !totalProducts) {
        return;
    }


    // =========================
    // MENENTUKAN JUMLAH PRODUK
    // =========================

    function getVisibleProducts() {

        if ($(window).width() <= 600) {

            return 1;

        } else if ($(window).width() <= 900) {

            return 2;

        } else {

            return 3;

        }

    }


    // =========================
    // BATAS INDEX TERAKHIR
    // =========================

    function getMaxIndex() {

        return Math.max(0, totalProducts - getVisibleProducts());

    }


    // =========================
    // UKURAN CARD
    // Satu langkah geser = lebar card + jarak antar card
    // =========================

    function setCardSize() {

        let visible = getVisibleProducts();

        let wrapperWidth = $(".slider-wrapper").innerWidth();

        let cardWidth = (wrapperWidth / visible) - cardGap;

        products.css({
            "flex": "0 0 " + cardWidth + "px",
            "width": cardWidth + "px",
            "max-width": cardWidth + "px",
            "margin": "0 10px"
        });

    }


    function getSlideStep() {

        return products.first().outerWidth(true);

    }


    // =========================
    // MEMBUAT DOT
    // =========================

    function createDots() {

        $(".slider-dots").empty();

        let totalSlide = getMaxIndex() + 1;

        for (let i = 0; i < totalSlide; i++) {

            $(".slider-dots").append(
                '<span class="dot" data-index="' +
                i +
                '"></span>'
            );

        }

        $(".dot").eq(currentIndex).addClass("active");

    }


    // =========================
    // UPDATE SLIDER
    // Geser track dalam pixel agar card tidak terpotong
    // =========================

    function updateSlider() {

        let maxIndex = getMaxIndex();

        if (currentIndex > maxIndex) {

            currentIndex = maxIndex;

        }

        if (currentIndex < 0) {

            currentIndex = 0;

        }

        slider.css(
            "transform",
            "translateX(-" +
            (currentIndex * getSlideStep()) +
            "px)"
        );

        $(".dot").removeClass("active");

        $(".dot")
            .eq(currentIndex)
            .addClass("active");

    }


    // =========================
    // NEXT
    // =========================

    function nextSlide() {

        currentIndex++;

        if (currentIndex > getMaxIndex()) {

            currentIndex = 0;

        }

        updateSlider();

    }


    // =========================
    // PREVIOUS
    // =========================

    function previousSlide() {

        currentIndex--;

        if (currentIndex < 0) {

            currentIndex = getMaxIndex();

        }

        updateSlider();

    }


    // =========================
    // TOMBOL NEXT / PREVIOUS
    // =========================

    $(".slider-btn.next").click(function () {

        nextSlide();

        restartAutoSlide();

    });

    $(".slider-btn.prev").click(function () {

        previousSlide();

        restartAutoSlide();

    });


    // =========================
    // DOT CLICK
    // =========================

    $(document).on("click", ".dot", function () {

        currentIndex =
            parseInt($(this).attr("data-index"), 10);

        updateSlider();

        restartAutoSlide();

    });


    // =========================
    // AUTO SLIDE
    // =========================

    function startAutoSlide() {

        stopAutoSlide();

        autoSlide = setInterval(function () {

            nextSlide();

        }, 1500);

    }


    // =========================
    // STOP AUTO SLIDE
    // =========================

    function stopAutoSlide() {

        clearInterval(autoSlide);

        autoSlide = null;

    }


    // =========================
    // RESTART AUTO SLIDE
    // =========================

    function restartAutoSlide() {

        stopAutoSlide();

        startAutoSlide();

    }


    // =========================
    // MOUSE MASUK / KELUAR
    // =========================

    $(".slider-container").mouseenter(function () {

        stopAutoSlide();

    });

    $(".slider-container").mouseleave(function () {

        startAutoSlide();

    });


    // =========================
    // RESPONSIVE
    // =========================

    $(window).on("resize", function () {

        clearTimeout(resizeTimer);

        resizeTimer = setTimeout(function () {

            setCardSize();

            currentIndex = Math.min(currentIndex, getMaxIndex());

            createDots();

            updateSlider();

        }, 150);

    });


    // =========================
    // JALANKAN PERTAMA KALI
    // =========================

    setCardSize();

    createDots();

    updateSlider();

    startAutoSlide();

});
