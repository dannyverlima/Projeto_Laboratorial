const SLIDE_VISIBLE_MS = 10000;
const FADE_DURATION_MS = 650;

function setConsumoBars() {
  const bars = document.querySelectorAll(".consumo-bar");
  bars.forEach((bar) => {
    const rawValue = Number(bar.dataset.value || 0);
    const safeValue = Math.max(8, Math.min(100, rawValue));

    bar.style.height = "8%";
    requestAnimationFrame(() => {
      bar.style.height = `${safeValue}%`;
    });
  });
}

function initInfoRotator() {
  const root = document.querySelector("[data-rotator]");
  if (!root) {
    return;
  }

  const slides = [...root.querySelectorAll("[data-slide]")];
  const indicators = [...root.querySelectorAll("[data-slide-to]")];

  if (slides.length === 0) {
    return;
  }

  let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains("is-active")));
  let timerId = null;
  let isTransitioning = false;

  const setIndicators = (index) => {
    indicators.forEach((indicator, i) => {
      const isActive = i === index;
      indicator.classList.toggle("is-active", isActive);
      indicator.setAttribute("aria-current", isActive ? "true" : "false");
    });
  };

  const showSlide = (index) => {
    slides.forEach((slide, i) => {
      const isActive = i === index;
      slide.classList.toggle("is-active", isActive);
      slide.setAttribute("aria-hidden", isActive ? "false" : "true");
    });
    setIndicators(index);
  };

  const clearCycleTimer = () => {
    if (timerId !== null) {
      window.clearTimeout(timerId);
      timerId = null;
    }
  };

  const queueNextSlide = () => {
    clearCycleTimer();
    timerId = window.setTimeout(() => {
      transitionTo(activeIndex + 1);
    }, SLIDE_VISIBLE_MS);
  };

  const transitionTo = (targetIndex) => {
    if (slides.length < 2 || isTransitioning) {
      return;
    }

    const nextIndex = (targetIndex + slides.length) % slides.length;
    if (nextIndex === activeIndex) {
      queueNextSlide();
      return;
    }

    isTransitioning = true;
    clearCycleTimer();

    const currentSlide = slides[activeIndex];
    currentSlide.classList.remove("is-active");
    currentSlide.setAttribute("aria-hidden", "true");

    window.setTimeout(() => {
      activeIndex = nextIndex;

      const nextSlide = slides[activeIndex];
      nextSlide.classList.add("is-active");
      nextSlide.setAttribute("aria-hidden", "false");
      setIndicators(activeIndex);

      window.setTimeout(() => {
        isTransitioning = false;
        queueNextSlide();
      }, FADE_DURATION_MS);
    }, FADE_DURATION_MS);
  };

  indicators.forEach((indicator) => {
    indicator.addEventListener("click", () => {
      const target = Number(indicator.dataset.slideTo || "0");
      if (Number.isNaN(target) || target === activeIndex) {
        return;
      }

      transitionTo(target);
    });
  });

  root.addEventListener("mouseenter", clearCycleTimer);
  root.addEventListener("focusin", clearCycleTimer);

  root.addEventListener("mouseleave", () => {
    if (!isTransitioning) {
      queueNextSlide();
    }
  });

  root.addEventListener("focusout", () => {
    if (!isTransitioning) {
      queueNextSlide();
    }
  });

  showSlide(activeIndex);
  queueNextSlide();
}

document.addEventListener("DOMContentLoaded", () => {
  setConsumoBars();
  initInfoRotator();
});
