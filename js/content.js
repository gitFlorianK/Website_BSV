/* ============================================
   CMS Content Loader
   Lädt Seiteninhalte dynamisch aus der API
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  const hero = document.querySelector('.hero[data-page]');
  if (!hero) return;

  const page = hero.dataset.page;
  if (page === 'aktuelles') return; // handled by aktuelles.js

  fetch('admin/api.php?action=page_data&page=' + page)
    .then(res => res.json())
    .then(data => {
      if (data.title) renderTitle(hero, data.title);
      if (data.sections) renderSections(page, data.sections);
      if (data.board_members) renderBoardMembers(data.board_members);
      if (data.seasons) renderTraining(data.seasons);
      if (data.courses) renderCourses(data.courses, data.sections);
      if (data.sponsors) renderSponsors(data.sponsors);
      if (data.links) renderInfoLinks(data.links);
    })
    .catch(() => {}); // Keep static fallback
});

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}

/* --- Hero Title --- */
function renderTitle(hero, t) {
  const h1 = hero.querySelector('.hero-content h1');
  const p = hero.querySelector('.hero-content p');
  if (h1) h1.textContent = t.title;
  if (t.subtitle) {
    if (p) p.textContent = t.subtitle;
    else {
      const el = document.createElement('p');
      el.textContent = t.subtitle;
      h1.parentNode.appendChild(el);
    }
  } else if (p) {
    p.remove();
  }
}

/* --- Generic Sections --- */
function renderSections(page, sections) {
  switch (page) {
    case 'index':
      setHTML('[data-cms="willkommen"]', sections.willkommen);
      setText('[data-cms="cta-title"]', sections.cta_title);
      setText('[data-cms="cta-text"]', sections.cta_text);
      break;
    case 'contact':
      setHTML('[data-cms="intro"]', sections.intro);
      setText('[data-cms="org-name"]', sections.org_name);
      setText('[data-cms="contact-person"]', sections.contact_person);
      setText('[data-cms="street"]', sections.street);
      setText('[data-cms="city"]', sections.city);
      if (sections.phone) {
        const phoneEl = document.querySelector('[data-cms="phone"]');
        if (phoneEl) phoneEl.innerHTML = '<span class="contact-icon">&#9742;</span> <strong>Telefon:</strong> ' + esc(sections.phone) + (sections.phone_note ? ' (' + esc(sections.phone_note) + ')' : '');
      }
      if (sections.email) {
        const emailEl = document.querySelector('[data-cms="email"]');
        if (emailEl) emailEl.innerHTML = '<span class="contact-icon">&#9993;</span> <strong>E-Mail:</strong> <a href="mailto:' + esc(sections.email) + '">' + esc(sections.email) + '</a>';
      }
      break;
    case 'imprint':
    case 'datenschutz':
      setHTML('[data-cms="content"]', sections.content);
      break;
    case 'anfaengerkurs':
      setHTML('[data-cms="intro"]', sections.intro);
      setText('[data-cms="anmeldung-text"]', sections.anmeldung_text);
      if (sections.anmeldung_email) {
        const btn = document.querySelector('[data-cms="anmeldung-email"]');
        if (btn) {
          btn.href = 'mailto:' + sections.anmeldung_email;
          btn.textContent = sections.anmeldung_email;
        }
      }
      break;
  }
}

function setHTML(sel, html) {
  const el = document.querySelector(sel);
  if (el && html !== undefined) el.innerHTML = html;
}

function setText(sel, text) {
  const el = document.querySelector(sel);
  if (el && text !== undefined) el.textContent = text;
}

/* --- Board Members (index) --- */
function renderBoardMembers(members) {
  const grid = document.querySelector('[data-cms="board"]');
  if (!grid || !members.length) return;

  grid.innerHTML = members.map(m => `
    <div class="card vorstand-card fade-in visible">
      ${m.image ? `<img src="${esc(m.image)}" alt="${esc(m.name)}" class="card-img">` : ''}
      <div class="role">${esc(m.role)}</div>
      <h3>${esc(m.name)}</h3>
      ${m.email ? `<div class="email">${esc(m.email)}</div>` : ''}
    </div>
  `).join('');
}

/* --- Training (training) --- */
function renderTraining(seasons) {
  const container = document.querySelector('[data-cms="training"]');
  if (!container || !seasons.length) return;

  container.innerHTML = seasons.map((s, i) => `
    <section class="section${i % 2 ? ' section-alt' : ''}">
      <div class="container">
        <div class="training-block fade-in visible">
          <h2 class="section-title">${esc(s.title)}</h2>
          <p class="section-subtitle text-center mb-3">${esc(s.date_range)}</p>

          <div class="card mb-3">
            <h3>Standort</h3>
            <p class="training-location">
              ${esc(s.location_name)}<br>
              ${esc(s.location_address)}
            </p>
            ${s.location_image ? `<img src="${esc(s.location_image)}" alt="${esc(s.location_name)}" style="border-radius: var(--radius); margin-top: 1rem;">` : ''}
          </div>

          <table class="training-table">
            <thead><tr><th>Tag</th><th>Zeit</th><th>Gruppe</th></tr></thead>
            <tbody>
              ${s.times.map(t => `<tr><td>${esc(t.day)}</td><td>${esc(t.time_text)}</td><td>${esc(t.group_name)}</td></tr>`).join('')}
            </tbody>
          </table>
          ${s.note ? `<p class="training-note">${esc(s.note)}</p>` : ''}
        </div>
      </div>
    </section>
  `).join('');
}

/* --- Courses (anfaengerkurs) --- */
function renderCourses(courses, sections) {
  const grid = document.querySelector('[data-cms="courses"]');
  if (!grid || !courses.length) return;

  grid.innerHTML = courses.map(c => `
    <div class="card kurs-card fade-in visible">
      <h3>${esc(c.title)}</h3>
      ${c.status_text ? `<span class="kurs-badge warteliste">${esc(c.status_text)}</span>` : ''}
      ${c.deadline ? `<p class="mt-1" style="color: var(--text-dim);">${esc(c.deadline)}</p>` : ''}

      <h4 class="mt-2">Termine</h4>
      ${c.schedule_text ? `<p style="color: var(--text-dim);">${esc(c.schedule_text)}</p>` : ''}
      <ul class="kurs-dates">
        ${c.dates.map(d => `<li>${esc(d.date_text)}${d.note ? ' – ' + esc(d.note) : ''}</li>`).join('')}
      </ul>

      ${c.cost ? `<div class="kurs-price">Unkostenbeitrag: ${esc(c.cost)}</div>` : ''}
      ${c.equipment_note ? `<p class="mt-1" style="color: var(--text-dim);">${esc(c.equipment_note)}</p>` : ''}
    </div>
  `).join('');
}

/* --- Sponsors --- */
function renderSponsors(sponsors) {
  const grid = document.querySelector('[data-cms="sponsors"]');
  if (!grid || !sponsors.length) return;

  grid.innerHTML = sponsors.map(s => `
    <div class="card sponsor-card fade-in visible">
      <h3>${esc(s.name)}</h3>
      <div class="sponsor-info">
        ${esc(s.address)}${s.phone ? '<br>Tel: ' + esc(s.phone) : ''}
      </div>
      ${s.website ? `<a href="${esc(s.website)}" target="_blank" rel="noopener" class="sponsor-link btn btn-outline">Website besuchen</a>` : ''}
    </div>
  `).join('');
}

/* --- Info Links (information) --- */
function renderInfoLinks(links) {
  const vGrid = document.querySelector('[data-cms="links-verbaende"]');
  const cGrid = document.querySelector('[data-cms="links-vereine"]');

  if (vGrid && links.verbaende) {
    vGrid.innerHTML = links.verbaende.map(l => linkCard(l, '&#127993;')).join('');
  }
  if (cGrid && links.vereine) {
    cGrid.innerHTML = links.vereine.map(l => linkCard(l, '&#127919;')).join('');
  }
}

function linkCard(l, icon) {
  return `
    <a href="${esc(l.url)}" target="_blank" rel="noopener" class="card info-link-card fade-in visible">
      <div class="link-icon">${icon}</div>
      <div>
        <h4>${esc(l.title)}</h4>
        <p>${esc(l.subtitle)}</p>
      </div>
    </a>
  `;
}
