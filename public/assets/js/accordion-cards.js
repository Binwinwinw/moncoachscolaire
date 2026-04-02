(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.accordion-card .accordion-toggle').forEach(btn => {
      btn.setAttribute('tabindex', '0');
      btn.setAttribute('role', 'button');
      btn.setAttribute('aria-expanded', 'false');
      const content = btn.nextElementSibling;
      if (content) {
        content.setAttribute('aria-hidden', 'true');
        content.style.transition = 'max-height 0.4s cubic-bezier(0.4,0,0.2,1)';
        content.style.overflow = 'hidden';
        content.style.maxHeight = '0px';
      }
      btn.addEventListener('click', function() {
        if (!content) return;
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', (!expanded).toString());
        if (!expanded) {
          content.classList.remove('hidden');
          content.setAttribute('aria-hidden', 'false');
          content.style.maxHeight = content.scrollHeight + 'px';
        } else {
          content.setAttribute('aria-hidden', 'true');
          content.style.maxHeight = '0px';
          setTimeout(()=>content.classList.add('hidden'), 400);
        }
      });
      btn.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          btn.click();
        }
      });
    });
  });
})();
