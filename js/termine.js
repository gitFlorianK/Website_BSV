/* ============================================
   Termine - CMS Event Loader (Startseite)
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  const timeline = document.getElementById('termine-timeline');
  if (!timeline) return;

  loadEvents();

  async function loadEvents() {
    try {
      const res = await fetch('admin/api.php?action=events');
      if (!res.ok) throw new Error('API nicht erreichbar');
      const events = await res.json();
      renderEvents(events);
    } catch (err) {
      // Fallback: static content
      timeline.innerHTML = `
        <div class="timeline-item">
          <div class="timeline-card">
            <span class="timeline-date">Laufend</span>
            <h4>Reguläres Training</h4>
            <p>Siehe Trainingszeiten für aktuelle Termine.</p>
          </div>
        </div>
      `;
    }
  }

  function renderEvents(events) {
    if (events.length === 0) {
      timeline.innerHTML = '<p class="timeline-empty">Aktuell keine Termine geplant.</p>';
      return;
    }

    timeline.innerHTML = events.map(event => `
      <div class="timeline-item">
        <div class="timeline-card">
          <span class="timeline-date">${escapeHtml(event.date_text)}</span>
          <h4>${escapeHtml(event.title)}</h4>
          ${event.description ? `<p>${escapeHtml(event.description)}</p>` : ''}
        </div>
      </div>
    `).join('');
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
});
