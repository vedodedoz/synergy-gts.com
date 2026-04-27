/* ==========================================
   Synergy-GTS — Main JavaScript
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {

  // ==========================================
  // NAVBAR SCROLL EFFECT
  // ==========================================
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }, { passive: true });
  }

  // ==========================================
  // MOBILE MENU TOGGLE
  // ==========================================
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileMenu = document.querySelector('.mobile-menu');
  const mobileMenuIcon = mobileToggle?.querySelector('svg');

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = mobileMenu.classList.contains('open');
      mobileMenu.classList.toggle('open');

      // Swap hamburger / X icon
      if (mobileMenuIcon) {
        mobileMenuIcon.innerHTML = isOpen
          ? '<line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
          : '<line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
      }
    });

    // Close mobile menu on link click
    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        if (mobileMenuIcon) {
          mobileMenuIcon.innerHTML = '<line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
        }
      });
    });
  }

  // ==========================================
  // DESKTOP DROPDOWN MENUS
  // ==========================================
  document.querySelectorAll('.nav-item').forEach(item => {
    const dropdown = item.querySelector('.nav-dropdown');
    if (!dropdown) return;

    item.addEventListener('mouseenter', () => dropdown.classList.add('show'));
    item.addEventListener('mouseleave', () => dropdown.classList.remove('show'));
  });

  // ==========================================
  // SCROLL ANIMATIONS (Intersection Observer)
  // ==========================================
  const animatedElements = document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right');

  if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '-30px 0px'
    });

    animatedElements.forEach(el => observer.observe(el));
  } else {
    // Fallback: show all elements
    animatedElements.forEach(el => el.classList.add('visible'));
  }

  // ==========================================
  // STATS COUNTER ANIMATION
  // ==========================================
  const counters = document.querySelectorAll('.stat-number');

  if (counters.length > 0 && 'IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));
  }

  function animateCounter(element) {
    const text = element.textContent.trim();
    // Try data attributes first
    let target = parseInt(element.getAttribute('data-target'), 10);
    let suffix = element.getAttribute('data-suffix') || '';

    // Fallback: parse from text content
    if (isNaN(target)) {
      const match = text.match(/^(\d+)/);
      if (match) {
        target = parseInt(match[1], 10);
        suffix = text.replace(match[1], '');
      } else {
        return; // Can't parse, skip
      }
    }

    const duration = 2000;
    const start = performance.now();

    function update(currentTime) {
      const elapsed = currentTime - start;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = Math.round(eased * target);
      element.textContent = current + suffix;

      if (progress < 1) {
        requestAnimationFrame(update);
      }
    }

    requestAnimationFrame(update);
  }

  // ==========================================
  // TESTIMONIALS CAROUSEL
  // ==========================================
  const slides = document.querySelectorAll('.testimonial-slide');
  const dots = document.querySelectorAll('.testimonial-dot');

  if (slides.length > 0) {
    let currentSlide = 0;
    let autoPlayInterval;

    // Initialize first slide
    slides[0].classList.add('active');
    if (dots[0]) dots[0].classList.add('active');

    function goToSlide(index) {
      slides.forEach(s => s.classList.remove('active'));
      dots.forEach(d => d.classList.remove('active'));

      currentSlide = index;
      if (currentSlide >= slides.length) currentSlide = 0;
      if (currentSlide < 0) currentSlide = slides.length - 1;

      slides[currentSlide].classList.add('active');
      if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    // Dot click
    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        goToSlide(i);
        resetAutoPlay();
      });
    });

    // Auto play
    function startAutoPlay() {
      autoPlayInterval = setInterval(() => {
        goToSlide(currentSlide + 1);
      }, 5000);
    }

    function resetAutoPlay() {
      clearInterval(autoPlayInterval);
      startAutoPlay();
    }

    startAutoPlay();
  }

  // ==========================================
  // CONTACT FORM — Web3Forms real email delivery
  // ==========================================
  const contactForm = document.getElementById('contactForm');

  if (contactForm) {
    // Keep replyto in sync with email field
    const emailInput = document.getElementById('email');
    const replytoInput = document.getElementById('replyto');
    if (emailInput && replytoInput) {
      emailInput.addEventListener('input', () => {
        replytoInput.value = emailInput.value;
      });
    }

    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const submitBtn = document.getElementById('submitBtn');
      const successBox = document.getElementById('formSuccess');
      const errorBox   = document.getElementById('formError');
      const errorMsg   = document.getElementById('formErrorMsg');

      // Hide any previous feedback
      successBox.style.display = 'none';
      errorBox.style.display   = 'none';

      // Loading state
      const originalHTML = submitBtn.innerHTML;
      submitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;animation:spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg> Sending...';
      submitBtn.disabled = true;

      try {
        const formData = new FormData(contactForm);
        const response = await fetch('https://api.web3forms.com/submit', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          successBox.style.display = 'flex';
          contactForm.reset();
          if (replytoInput) replytoInput.value = '';
          submitBtn.innerHTML = originalHTML;
          submitBtn.disabled = false;
          // Scroll success message into view
          successBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
          throw new Error(data.message || 'Submission failed');
        }
      } catch (err) {
        errorMsg.textContent = err.message && err.message !== 'Failed to fetch'
          ? err.message
          : 'Network error — please check your connection and try again.';
        errorBox.style.display = 'flex';
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
      }
    });
  }

  // ==========================================
  // SMOOTH SCROLL FOR ANCHOR LINKS
  // ==========================================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;

      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

});
