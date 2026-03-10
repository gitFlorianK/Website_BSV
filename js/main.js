/* ============================================
   Bogensportverein 1960 Plauen e.V.
   Navigation, Lightbox, Scroll-Animationen
   & gemeinsame Hilfsfunktionen
   ============================================ */

/* --- Shared HTML Escape --- */
function esc(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}

/* --- Sanitize HTML (allow safe tags, strip scripts) --- */
function sanitizeHtml(html) {
  const tmp = document.createElement('div');
  tmp.innerHTML = html || '';
  // Remove all script/style/event handlers
  tmp.querySelectorAll('script, style, iframe, object, embed, link').forEach(el => el.remove());
  // Remove event handler attributes from all elements
  tmp.querySelectorAll('*').forEach(el => {
    for (const attr of Array.from(el.attributes)) {
      if (attr.name.startsWith('on') || attr.name === 'srcdoc' ||
          (attr.name === 'href' && attr.value.trim().toLowerCase().startsWith('javascript:'))) {
        el.removeAttribute(attr.name);
      }
    }
  });
  return tmp.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initScrollAnimations();
  initLightbox();
  loadCustomNavItems();
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
function initScrollAnimations(root) {
  const container = root || document;
  const elements = container.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right');

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

/* --- Custom Nav Items from CMS --- */
function loadCustomNavItems() {
  const navList = document.querySelector('.nav-list');
  if (!navList) return;

  fetch('admin/api.php?action=custom_pages_nav')
    .then(res => res.json())
    .then(pages => {
      if (!Array.isArray(pages) || !pages.length) return;
      pages.forEach(p => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = 'page.php?slug=' + encodeURIComponent(p.slug);
        a.textContent = p.nav_label || p.title;
        li.appendChild(a);
        navList.appendChild(li);
      });
    })
    .catch(err => console.warn('Custom nav load failed:', err));
}

/* --- Lightbox --- */
function initLightbox(container) {
  const scope = container || document;
  const galleryItems = scope.querySelectorAll('.gallery-item');
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
