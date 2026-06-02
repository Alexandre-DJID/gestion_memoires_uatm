document.addEventListener('DOMContentLoaded', () => {
  // Menu Actif dans la sidebar
  const currentPath = window.location.pathname;
  const sidebarLinks = document.querySelectorAll('.sidebar a');
  sidebarLinks.forEach(link => {
    if (currentPath.includes(link.getAttribute('href'))) {
      link.classList.add('active');
    }
  });

  // Mobile Menu Toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      const spans = menuToggle.querySelectorAll('span');
      if (sidebar.classList.contains('open')) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 6px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -6px)';
      } else {
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
      }
    });

    // Fermer le menu au clic sur le contenu principal 
    document.querySelector('.main-content')?.addEventListener('click', () => {
      if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        const spans = menuToggle.querySelectorAll('span');
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
      }
    });
  }

  window.togglePassword = (btn) => {
    const input = btn.parentElement.querySelector('input');
    if (input) {
      input.type = input.type === 'password' ? 'text' : 'password';
      btn.textContent = input.type === 'password' ? '👁️' : '🙈';
    }
  };

  // Like Style  
  window.toggleLike = (chip) => {
    chip.classList.toggle('active');
    const countSpan = chip.querySelector('.like-count');
    if (countSpan) {
      let count = parseInt(countSpan.textContent);
      count = chip.classList.contains('active') ? count + 1 : count - 1;
      countSpan.textContent = count;
    }
  };

  const revealElements = document.querySelectorAll('.card, .hero-content, .feature-card');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  revealElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s ease-out';
    observer.observe(el);
  });

  // Validation des formulaires statiques
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      const requiredInputs = form.querySelectorAll('[required]');
      let isValid = true;

      requiredInputs.forEach(input => {
        if (!input.value.trim()) {
          isValid = false;
          input.style.borderColor = 'var(--rouge)';
          input.classList.add('shake');
          setTimeout(() => input.classList.remove('shake'), 500);
        } else {
          input.style.borderColor = 'var(--gris-moyen)';
        }
      });

      if (!isValid) {
        e.preventDefault();
        // afficher une alerte visuelle simple
        console.log('Formulaire invalide : champs requis manquants.');
      }
    });
  });
});
