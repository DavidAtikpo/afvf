const nav = document.querySelector(".nav");
const burgers = document.querySelectorAll(".burger");

function setMenuOpen(open) {
  if (!nav) return;
  nav.classList.toggle("open", open);
  burgers.forEach((btn) => {
    btn.setAttribute("aria-expanded", open ? "true" : "false");
    btn.setAttribute(
      "aria-label",
      open
        ? btn.getAttribute("data-label-close") || "Fermer le menu"
        : btn.getAttribute("data-label-open") || "Ouvrir le menu"
    );
  });
}

burgers.forEach((burger) => {
  if (!nav) return;
  burger.setAttribute("data-label-open", "Ouvrir le menu");
  burger.setAttribute("data-label-close", "Fermer le menu");
  burger.addEventListener("click", () => {
    setMenuOpen(!nav.classList.contains("open"));
  });
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && nav && nav.classList.contains("open")) {
    setMenuOpen(false);
  }
});

/* Hero défilant : .afvf-hero-slider > .afvf-hero-slide (3 s par défaut ; classe afvf-hero-slider--5s = 5 s) */
(function () {
  const sliders = document.querySelectorAll(".afvf-hero-slider");
  sliders.forEach((slider) => {
    const slides = Array.from(slider.querySelectorAll(".afvf-hero-slide"));
    if (slides.length < 2) return;

    const match = /afvf-hero-slider--(\d+)s/.exec(slider.className);
    const delay = (match ? parseInt(match[1], 10) : 3) * 1000;
    const reduceMotion =
      window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const dots = document.createElement("div");
    dots.className = "afvf-hero-slider__dots";
    const dotButtons = slides.map((slide, i) => {
      const dot = document.createElement("button");
      dot.type = "button";
      dot.className = "afvf-hero-slider__dot";
      dot.setAttribute("aria-label", "Diapositive " + (i + 1));
      dot.addEventListener("click", () => {
        show(i);
        restart();
      });
      dots.appendChild(dot);
      return dot;
    });
    slider.appendChild(dots);

    function makeArrow(dir, label) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "afvf-hero-slider__arrow afvf-hero-slider__arrow--" + dir;
      btn.setAttribute("aria-label", label);
      btn.innerHTML =
        '<svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false">' +
        '<path d="' +
        (dir === "prev" ? "M15 5l-7 7 7 7" : "M9 5l7 7-7 7") +
        '" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
      btn.addEventListener("click", () => {
        show(current + (dir === "prev" ? -1 : 1));
        restart();
      });
      slider.appendChild(btn);
    }
    makeArrow("prev", "Diapositive précédente");
    makeArrow("next", "Diapositive suivante");
    slider.classList.add("is-js");

    /* Balayage tactile gauche / droite */
    let touchX = null;
    slider.addEventListener(
      "touchstart",
      (e) => {
        touchX = e.touches.length === 1 ? e.touches[0].clientX : null;
      },
      { passive: true }
    );
    slider.addEventListener(
      "touchend",
      (e) => {
        if (touchX === null) return;
        const dx = e.changedTouches[0].clientX - touchX;
        touchX = null;
        if (Math.abs(dx) > 45) {
          show(current + (dx < 0 ? 1 : -1));
          restart();
        }
      },
      { passive: true }
    );

    /* Flèches du clavier quand le hero a le focus */
    slider.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") {
        show(current - 1);
        restart();
      } else if (e.key === "ArrowRight") {
        show(current + 1);
        restart();
      }
    });

    let current = 0;
    let timer = null;

    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        const active = i === current;
        slide.classList.toggle("is-active", active);
        slide.setAttribute("aria-hidden", active ? "false" : "true");
        dotButtons[i].classList.toggle("is-active", active);
        dotButtons[i].setAttribute("aria-current", active ? "true" : "false");
      });
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      if (reduceMotion || timer) return;
      timer = window.setInterval(() => show(current + 1), delay);
    }

    function restart() {
      stop();
      start();
    }

    slider.addEventListener("mouseenter", stop);
    slider.addEventListener("mouseleave", start);
    slider.addEventListener("focusin", stop);
    slider.addEventListener("focusout", start);
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) stop();
      else start();
    });

    show(0);
    start();
  });
})();

/* Titres « fumée » : trajectoires et sens différents par bloc (pas tout droite → gauche) */
(function () {
  const reduceMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduceMotion) {
    return;
  }
  const variants = ["", "afvf-smoke-variant-b", "afvf-smoke-variant-c"];
  const directions = ["normal", "reverse", "alternate", "alternate-reverse"];
  document.querySelectorAll(".afvf-smoke-title, .afvf-zigzag__title").forEach((el) => {
    const v = variants[Math.floor(Math.random() * variants.length)];
    if (v) {
      el.classList.add(v);
    }
    const dur = 16 + Math.random() * 14;
    el.style.animationDuration = dur + "s";
    el.style.animationDelay = -Math.random() * dur + "s";
    el.style.animationDirection = directions[Math.floor(Math.random() * directions.length)];
    el.style.animationTimingFunction = "ease-in-out";
  });
})();

/* « A la UNE » : flèches du carrousel photos + lecture auto de la vidéo */
(function () {
  const chevron = function (dir) {
    return dir === "prev"
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  };

  document.querySelectorAll(".afvf-une-carousel").forEach(function (box) {
    const track = box.querySelector(".afvf-une-carousel__track");
    if (!track || box.querySelector(".afvf-une-carousel__arrow")) {
      return;
    }
    const step = function () {
      const slide = track.querySelector(".afvf-une-carousel__slide");
      if (!slide) {
        return track.clientWidth * 0.6;
      }
      const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
      return slide.getBoundingClientRect().width + gap;
    };
    const move = function (dir) {
      const max = track.scrollWidth - track.clientWidth;
      if (dir > 0 && track.scrollLeft >= max - 4) {
        track.scrollTo({ left: 0, behavior: "smooth" });
      } else if (dir < 0 && track.scrollLeft <= 4) {
        track.scrollTo({ left: max, behavior: "smooth" });
      } else {
        track.scrollBy({ left: dir * step(), behavior: "smooth" });
      }
    };
    ["prev", "next"].forEach(function (dir) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "afvf-une-carousel__arrow afvf-une-carousel__arrow--" + dir;
      btn.setAttribute("aria-label", dir === "prev" ? "Photo précédente" : "Photo suivante");
      btn.innerHTML = chevron(dir);
      btn.addEventListener("click", function () {
        move(dir === "prev" ? -1 : 1);
      });
      box.appendChild(btn);
    });
  });

  document.querySelectorAll(".afvf-une__video video").forEach(function (video) {
    video.muted = true;
    video.loop = true;
    video.setAttribute("playsinline", "");
    const p = video.play();
    if (p && typeof p.catch === "function") {
      p.catch(function () {});
    }
  });
})();

/* Podcasts : lecteur audio personnalisé (play, barre, temps) sur les cartes .afvf-podcast-card */
(function () {
  const fmt = function (t) {
    if (!isFinite(t) || t < 0) {
      return "00:00";
    }
    const m = Math.floor(t / 60);
    const s = Math.floor(t % 60);
    return (m < 10 ? "0" : "") + m + ":" + (s < 10 ? "0" : "") + s;
  };
  const ICON_PLAY = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4l13 8-13 8z" fill="currentColor"/></svg>';
  const ICON_PAUSE = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z" fill="currentColor"/></svg>';
  const all = [];

  document.querySelectorAll(".afvf-podcast-card").forEach(function (card) {
    const audio = card.querySelector("audio");
    const body = card.querySelector(".afvf-podcast-card__body");
    if (!audio || !body || card.classList.contains("is-enhanced")) {
      return;
    }
    audio.preload = "metadata";
    audio.controls = false;

    const player = document.createElement("div");
    player.className = "afvf-podcast-card__player";

    const play = document.createElement("button");
    play.type = "button";
    play.className = "afvf-podcast-card__play";
    play.setAttribute("aria-label", "Lecture");
    play.innerHTML = ICON_PLAY;

    const seek = document.createElement("div");
    seek.className = "afvf-podcast-card__seek";
    const bar = document.createElement("div");
    bar.className = "afvf-podcast-card__bar";
    const fill = document.createElement("div");
    fill.className = "afvf-podcast-card__fill";
    bar.appendChild(fill);
    seek.appendChild(bar);

    const time = document.createElement("span");
    time.className = "afvf-podcast-card__time";
    time.textContent = "00:00 / 00:00";

    player.appendChild(play);
    player.appendChild(seek);
    player.appendChild(time);
    body.appendChild(player);
    card.classList.add("is-enhanced");

    const update = function () {
      const d = audio.duration;
      time.textContent = fmt(audio.currentTime) + " / " + fmt(d);
      fill.style.width = isFinite(d) && d > 0 ? (audio.currentTime / d) * 100 + "%" : "0%";
    };
    audio.addEventListener("loadedmetadata", update);
    audio.addEventListener("timeupdate", update);
    audio.addEventListener("play", function () {
      play.innerHTML = ICON_PAUSE;
      play.setAttribute("aria-label", "Pause");
    });
    const reset = function () {
      play.innerHTML = ICON_PLAY;
      play.setAttribute("aria-label", "Lecture");
    };
    audio.addEventListener("pause", reset);
    audio.addEventListener("ended", reset);

    play.addEventListener("click", function () {
      if (audio.paused) {
        all.forEach(function (other) {
          if (other !== audio) {
            other.pause();
          }
        });
        const p = audio.play();
        if (p && typeof p.catch === "function") {
          p.catch(function () {});
        }
      } else {
        audio.pause();
      }
    });
    seek.addEventListener("click", function (e) {
      const rect = seek.getBoundingClientRect();
      if (rect.width > 0 && isFinite(audio.duration)) {
        audio.currentTime = Math.min(Math.max((e.clientX - rect.left) / rect.width, 0), 1) * audio.duration;
      }
    });
    all.push(audio);
    update();
  });
})();

/* Actualités : « j’aime » (cœur) + copier le lien */
(function () {
  "use strict";

  function key(id) {
    return "afvf_like_" + id;
  }

  function mark(btn) {
    try {
      if (localStorage.getItem(key(btn.getAttribute("data-post")))) {
        btn.setAttribute("aria-pressed", "true");
      }
    } catch (e) {}
  }

  document.querySelectorAll(".afvf-news-like").forEach(mark);

  document.addEventListener("click", function (e) {
    const like = e.target.closest ? e.target.closest(".afvf-news-like") : null;
    if (like) {
      e.preventDefault();
      const id = like.getAttribute("data-post");
      const liked = like.getAttribute("aria-pressed") === "true";
      const body = new URLSearchParams();
      body.set("action", "afvf_like");
      body.set("post", id);
      body.set("dir", liked ? "down" : "up");
      like.setAttribute("aria-pressed", liked ? "false" : "true");
      try {
        if (liked) {
          localStorage.removeItem(key(id));
        } else {
          localStorage.setItem(key(id), "1");
        }
      } catch (err) {}
      if (window.afvfAjax && window.afvfAjax.url) {
        fetch(window.afvfAjax.url, { method: "POST", body: body, credentials: "same-origin" })
          .then(function (r) {
            return r.json();
          })
          .then(function (res) {
            const n = like.querySelector(".afvf-news-like__n");
            if (res && res.success && n) {
              n.textContent = res.data.likes;
            }
          })
          .catch(function () {});
      }
      return;
    }
    const copy = e.target.closest ? e.target.closest(".afvf-post-share__copy") : null;
    if (copy) {
      e.preventDefault();
      const url = copy.getAttribute("data-url") || window.location.href;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          copy.classList.add("is-copied");
          setTimeout(function () {
            copy.classList.remove("is-copied");
          }, 1500);
        });
      }
    }
  });
})();

/* Actualités : menu « ⋮ » (partager) + signet */
(function () {
  "use strict";

  function closeMenus(except) {
    document.querySelectorAll(".afvf-news-menu").forEach(function (m) {
      if (m === except) {
        return;
      }
      const l = m.querySelector(".afvf-news-menu__list");
      const b = m.querySelector(".afvf-news-menu__btn");
      if (l) {
        l.hidden = true;
      }
      if (b) {
        b.setAttribute("aria-expanded", "false");
      }
    });
  }

  document.querySelectorAll(".afvf-news-bookmark").forEach(function (btn) {
    try {
      if (localStorage.getItem("afvf_bm_" + btn.getAttribute("data-post"))) {
        btn.setAttribute("aria-pressed", "true");
      }
    } catch (e) {}
  });

  document.addEventListener("click", function (e) {
    const t = e.target;
    const toggle = t.closest ? t.closest(".afvf-news-menu__btn") : null;
    if (toggle) {
      e.preventDefault();
      const menu = toggle.closest(".afvf-news-menu");
      const list = menu.querySelector(".afvf-news-menu__list");
      const open = list.hidden;
      closeMenus(open ? menu : null);
      list.hidden = !open;
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      return;
    }
    const bm = t.closest ? t.closest(".afvf-news-bookmark") : null;
    if (bm) {
      e.preventDefault();
      const on = bm.getAttribute("aria-pressed") !== "true";
      bm.setAttribute("aria-pressed", on ? "true" : "false");
      try {
        const k = "afvf_bm_" + bm.getAttribute("data-post");
        if (on) {
          localStorage.setItem(k, "1");
        } else {
          localStorage.removeItem(k);
        }
      } catch (err) {}
      return;
    }
    if (!(t.closest && t.closest(".afvf-news-menu"))) {
      closeMenus(null);
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeMenus(null);
    }
  });
})();

/* Événement : total de l’inscription */
(function () {
  "use strict";
  document.querySelectorAll(".afvf-ev-form").forEach(function (form) {
    const out = form.querySelector(".afvf-ev-total__n");
    function update() {
      let total = 0;
      form.querySelectorAll(".afvf-ev-qty").forEach(function (sel) {
        total += (parseFloat(sel.getAttribute("data-price")) || 0) * (parseInt(sel.value, 10) || 0);
      });
      if (out) {
        out.textContent = total.toFixed(2).replace(".", ",");
      }
    }
    form.addEventListener("change", update);
    update();
  });
})();

/* Commande événement : compte à rebours « Temps restant » */
(function () {
  "use strict";
  const box = document.querySelector(".afvf-co");
  if (!box) {
    return;
  }
  const out = box.querySelector(".afvf-co__time");
  let left = parseInt(box.getAttribute("data-left"), 10) || 0;
  function pad(n) {
    return (n < 10 ? "0" : "") + n;
  }
  function tick() {
    if (out) {
      out.textContent = pad(Math.floor(left / 60)) + ":" + pad(left % 60);
    }
    if (left <= 0) {
      window.location.href = box.getAttribute("data-back") || "/";
      return;
    }
    left -= 1;
    setTimeout(tick, 1000);
  }
  tick();
})();
