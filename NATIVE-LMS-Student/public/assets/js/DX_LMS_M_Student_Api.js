(function () {
  'use strict';
  const endpoint = '/api/index.php?resource=';

  async function get(resource) {
    const response = await fetch(endpoint + encodeURIComponent(resource), {
      method: 'GET', credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store'
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload?.error?.message || 'Data tidak dapat dimuat.');
    return payload.data;
  }

  function text(selector, value) {
    const node = document.querySelector(selector);
    if (node) node.textContent = String(value ?? 0);
  }

  function renderCourses(target, courses) {
    if (!target) return;
    target.replaceChildren();
    if (!courses.length) {
      const empty = document.createElement('p');
      empty.className = 'col-span-full rounded-xl border border-outline-variant bg-surface-container-lowest p-6 text-center text-on-surface-variant';
      empty.textContent = 'Belum ada course yang tersedia.';
      target.append(empty);
      return;
    }
    courses.forEach(course => {
      const card = document.createElement('article');
      card.className = 'course-card flex flex-col rounded-xl border border-surface-variant bg-surface-container-lowest p-5 shadow-sm';
      card.dataset.filters = JSON.stringify([course.category || '']);
      const badge = document.createElement('span');
      badge.className = 'w-fit rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container';
      badge.textContent = course.category;
      const title = document.createElement('h3');
      title.className = 'course-title mt-4 font-headline-sm text-headline-sm font-bold text-on-surface';
      title.textContent = course.title;
      const code = document.createElement('p');
      code.className = 'mt-1 text-xs text-on-surface-variant';
      code.textContent = course.course_code;
      const description = document.createElement('p');
      description.className = 'mt-3 line-clamp-3 text-sm text-on-surface-variant';
      description.textContent = course.description || 'Materi pembelajaran tersedia di dalam course.';
      const progress = document.createElement('p');
      progress.className = 'mt-4 text-sm font-semibold text-tertiary';
      const percentage = Math.max(0, Math.min(100, Number(course.progress) || 0));
      progress.textContent = `${percentage}% selesai`;
      const progressTrack = document.createElement('div');
      progressTrack.className = 'dls-progress';
      const progressBar = document.createElement('div');
      progressBar.className = 'dls-progress-bar dls-progress-bar-primary';
      progressBar.style.width = `${percentage}%`;
      progressTrack.append(progressBar);
      const action = document.createElement('a');
      action.className = 'dls-course-link mt-4 inline-flex items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-on-primary';
      action.href = `/?route=/student/course/detail&id=${encodeURIComponent(course.course_code)}`;
      action.textContent = 'Buka course';
      card.append(badge, title, code, description, progress, progressTrack, action);
      target.append(card);
    });
  }

  document.addEventListener('DOMContentLoaded', async function () {
    const dashboard = document.querySelector('[data-student-dashboard]');
    const coursePage = document.querySelector('[data-student-courses]');
    if (!dashboard && !coursePage) return;
    try {
      const [stats, courses] = await Promise.all([get('dashboard'), get('courses')]);
      text('[data-api-active-courses]', `${stats.active_courses} Course Aktif`);
      text('[data-api-completed-courses]', stats.completed_courses);
      text('[data-api-total-courses]', stats.total_courses);
      const courseList = document.querySelector('[data-api-course-list]');
      // Keep the complete PHP-rendered cards (filters, status, links and progress).
      // The API card renderer is only a fallback for pages without server markup.
      if (courseList && courseList.childElementCount === 0) renderCourses(courseList, courses);
    } catch (error) {
      const status = document.querySelector('[data-api-status]');
      if (status) { status.textContent = error.message; status.hidden = false; }
    }
  });
})();
