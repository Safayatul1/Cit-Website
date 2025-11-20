// script.js
(function () {
    // ===== Typewriter with multiple lines =====
    async function typeLines(el, lines, speed = 45, startDelay = 200, lineDelay = 250) {
      if (!el || !Array.isArray(lines)) return;
      el.textContent = ""; el.classList.add("typing");
      await wait(startDelay);
      for (let i = 0; i < lines.length; i++) {
        await typeText(el, lines[i], speed);
        if (i !== lines.length - 1) { el.textContent += "\n"; await wait(lineDelay); }
      }
      el.classList.remove("typing");
      el.classList.add("typed");
    }
    function typeText(el, text, speed) {
      return new Promise((resolve) => {
        let i = 0;
        const id = setInterval(() => {
          el.textContent += text.charAt(i++);
          if (i >= text.length) { clearInterval(id); resolve(); }
        }, speed);
      });
    }
    const wait = (ms) => new Promise(r => setTimeout(r, ms));
  
    // Start typing
    const titleEl = document.getElementById("hero-title");
    const subEl   = document.getElementById("hero-subtitle");
    const titleLines = titleEl?.dataset.lines ? JSON.parse(titleEl.dataset.lines) : [];
    const subLines   = subEl?.dataset.lines   ? JSON.parse(subEl.dataset.lines)   : [];
    (async () => {
      await typeLines(titleEl, titleLines, 45, 200, 220);
      await typeLines(subEl,   subLines,   32, 120, 150);
    })();
  
    // ===== Scroll-reveal (appear when entering viewport, hide when leaving) =====
    const revealEls = Array.from(document.querySelectorAll(".reveal"));
    revealEls.forEach(el => {
      const delay = Number(el.dataset.reveal || 0);
      el.style.setProperty("--d", `${delay}ms`);
    });
  
    if ("IntersectionObserver" in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) entry.target.classList.add("show");
          else entry.target.classList.remove("show");  // hides again when you scroll away
        });
      }, { threshold: 0.2 });
      revealEls.forEach(el => io.observe(el));
    } else {
      // Fallback: show everything if IO not supported
      revealEls.forEach(el => el.classList.add("show"));
    }
  
    // ===== Animate skill bars when visible =====
    const fills = Array.from(document.querySelectorAll(".fill"));
    if ("IntersectionObserver" in window && fills.length) {
      const barObs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          const span = e.target;
          if (e.isIntersecting) {
            const pct = Math.max(0, Math.min(100, Number(span.dataset.target || 0)));
            span.style.width = pct + "%";
          } else {
            // Reset when leaving so it can animate again when re-entering
            span.style.width = "0%";
          }
        });
      }, { threshold: 0.6 });
      fills.forEach(f => barObs.observe(f));
    } else {
      fills.forEach(f => f.style.width = (f.dataset.target || 0) + "%");
    }
  })();
  