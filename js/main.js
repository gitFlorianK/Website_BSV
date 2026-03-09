/* ============================================
   Bogensportverein 1960 Plauen e.V.
   Navigation, Lightbox & Scroll-Animationen
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initScrollAnimations();
  initLightbox();
  loadPageTitle();
});

/* --- Mobile Navigation --- */
function initNavigation() {
  const hamburger = document.querySelector('.hamburger');
  const navList = document.querySelector('.nav-list');

  if (!hamburger || !navList) return;

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navList.classList.toggle('open');
    document.body.style.overflow = navList.classList.contains('open') ? 'hidden' : '';
  });

  // Close menu on link click
  navList.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      hamburger.classList.remove('active');
      navList.classList.remove('open');
      document.body.style.overflow = '';
    });
  });

  // Close on escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navList.classList.contains('open')) {
      hamburger.classList.remove('active');
      navList.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
}

/* --- Scroll Animations (IntersectionObserver) --- */
function initScrollAnimations() {
  const elements = document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right');

  if (!elements.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  });

  elements.forEach(el => observer.observe(el));
}

/* --- Page Titles from CMS --- */
function loadPageTitle() {
  const hero = document.querySelector('.hero[data-page]');
  if (!hero) return;

  const pageKey = hero.dataset.page;

  fetch('admin/api.php?action=page_titles')
    .then(res => res.json())
    .then(data => {
      const page = data[pageKey];
      if (!page) return;

      const h1 = hero.querySelector('.hero-content h1');
      const p = hero.querySelector('.hero-content p');

      if (h1) h1.textContent = page.title;

      if (page.subtitle) {
        if (p) {
          p.textContent = page.subtitle;
        } else {
          const newP = document.createElement('p');
          newP.textContent = page.subtitle;
          h1.parentNode.appendChild(newP);
        }
      } else if (p) {
        p.remove();
      }
    })
    .catch(() => {}); // Keep static fallback on error
}

/* --- Lightbox --- */
function initLightbox() {
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightbox = document.getElementById('lightbox');

  if (!galleryItems.length || !lightbox) return;

  const lightboxImg = lightbox.querySelector('img');
  const closeBtn = lightbox.querySelector('.lightbox-close');
  const prevBtn = lightbox.querySelector('.lightbox-prev');
  const nextBtn = lightbox.querySelector('.lightbox-next');

  let currentIndex = 0;
  const images = Array.from(galleryItems).map(item => item.querySelector('img').src);

  function openLightbox(index) {
    currentIndex = index;
    lightboxImg.src = images[currentIndex];
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }

  function navigate(direction) {
    currentIndex = (currentIndex + direction + images.length) % images.length;
    lightboxImg.src = images[currentIndex];
  }

  galleryItems.forEach((item, index) => {
    item.addEventListener('click', () => openLightbox(index));
  });

  closeBtn.addEventListener('click', closeLightbox);
  prevBtn.addEventListener('click', () => navigate(-1));
  nextBtn.addEventListener('click', () => navigate(1));

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('active')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigate(-1);
    if (e.key === 'ArrowRight') navigate(1);
  });
}

