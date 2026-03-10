/* ============================================
   Aktuelles - CMS Content Loader
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('aktuelles-content');
  const tabBtns = document.querySelectorAll('.tab-btn');

  if (!container) return;

  let allPosts = [];
  let currentFilter = 'all';

  // Tab switching
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.dataset.tab;
      renderPosts();
    });
  });

  // Load posts from CMS API
  loadPosts();

  async function loadPosts() {
    try {
      const res = await fetch('admin/api.php?action=posts');
      if (!res.ok) throw new Error('API nicht erreichbar');
      allPosts = await res.json();
      renderPosts();
    } catch (err) {
      // Fallback: show static content hint
      container.innerHTML = `
        <div class="empty-hint">
          <p>Inhalte werden über das CMS gepflegt.</p>
          <p><a href="admin/">Zum CMS-Login</a></p>
        </div>
      `;
    }
  }

  function renderPosts() {
    const filtered = currentFilter === 'all'
      ? allPosts
      : allPosts.filter(p => p.type === currentFilter);

    if (filtered.length === 0) {
      container.innerHTML = '<p class="empty-hint">Noch keine Beiträge vorhanden.</p>';
      return;
    }

    // Group by category
    const grouped = {};
    filtered.forEach(post => {
      const cat = post.category || 'Allgemein';
      if (!grouped[cat]) grouped[cat] = [];
      grouped[cat].push(post);
    });

    let html = '';
    for (const [category, posts] of Object.entries(grouped)) {
      html += `<div class="aktuelles-category fade-in">`;
      html += `<h3 class="category-title">${esc(category)}</h3>`;

      posts.forEach(post => {
        if (post.type === 'blog') {
          html += renderBlogPost(post);
        } else {
          html += renderGalleryPost(post);
        }
      });

      html += `</div>`;
    }

    container.innerHTML = html;

    // Re-init scroll animations and lightbox for new content
    initScrollAnimations(container);
    initLightbox(container);
  }

  function renderBlogPost(post) {
    const date = formatDate(post.created_at);
    let html = `
      <article class="aktuelles-post aktuelles-blog">
        <div class="post-meta">
          <span class="post-date">${date}</span>
          <span class="post-badge post-badge-blog">Bericht</span>
        </div>
        <h4>${esc(post.title)}</h4>
        <div class="post-content">${sanitizeHtml(post.content || '')}</div>
    `;

    if (post.images && post.images.length > 0) {
      html += `<div class="post-images">`;
      post.images.forEach(img => {
        html += `<div class="gallery-item"><img src="uploads/${esc(img.filename)}" alt="${esc(img.alt_text || post.title)}" loading="lazy"></div>`;
      });
      html += `</div>`;
    }

    html += `<div class="post-author">von ${esc(post.author)}</div>`;
    html += `</article>`;
    return html;
  }

  function renderGalleryPost(post) {
    const date = formatDate(post.created_at);
    let html = `
      <article class="aktuelles-post aktuelles-gallery">
        <div class="post-meta">
          <span class="post-date">${date}</span>
          <span class="post-badge post-badge-gallery">Galerie</span>
        </div>
        <h4>${esc(post.title)}</h4>
    `;

    if (post.content) {
      html += `<div class="post-content">${sanitizeHtml(post.content)}</div>`;
    }

    if (post.images && post.images.length > 0) {
      html += `<div class="gallery-grid">`;
      post.images.forEach(img => {
        html += `<div class="gallery-item"><img src="uploads/${esc(img.filename)}" alt="${esc(img.alt_text || post.title)}" loading="lazy"></div>`;
      });
      html += `</div>`;
    }

    html += `</article>`;
    return html;
  }

  function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
  }
});
