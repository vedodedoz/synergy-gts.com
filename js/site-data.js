/**
 * site-data.js — Synergy GTS
 * Loads public site data from the SQLite-backed API
 * and applies content/branch overrides to rendered pages.
 */
(function () {
  'use strict';

  function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function applyContentKeys(content) {
    document.querySelectorAll('[data-content-key]').forEach(function (el) {
      var key = el.getAttribute('data-content-key');
      if (content[key] !== undefined) {
        el.textContent = content[key];
      }
    });
  }

  function applyEmailLinks(content) {
    var email = content['company.email'];
    if (!email) return;
    document.querySelectorAll('[data-content-email]').forEach(function (el) {
      el.textContent = email;
      if (el.tagName === 'A') el.href = 'mailto:' + email;
    });
  }

  function applyBranches(branches) {
    var container = document.getElementById('branchesInfoContainer');
    if (!container) return;

    var html = '';
    branches.forEach(function (b) {
      html += '<div class="branch-info-item">' +
        '<strong>' + esc(b.name) + (b.primary ? ' <span class="badge-primary">HQ</span>' : '') + '</strong>' +
        '<span>' + esc(b.city + ', ' + b.province + ', ' + b.country) + '</span>' +
        '<a href="mailto:' + esc(b.email) + '">' + esc(b.email) + '</a>' +
        (b.hours ? '<span>' + esc(b.hours) + '</span>' : '') +
        '</div>';
    });
    container.innerHTML = html;
  }

  async function run() {
    try {
      var response = await fetch('api/public_data.php', { cache: 'no-store' });
      var data = await response.json();
      if (!data || !data.success) return;

      var content = data.content || {};
      var branches = Array.isArray(data.branches) ? data.branches : [];

      applyContentKeys(content);
      applyEmailLinks(content);
      applyBranches(branches);
    } catch (err) {
      // Fail silently to avoid blocking page rendering.
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }

}());
