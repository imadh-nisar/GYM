// Handle login/register messages
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  if (params.has("msg")) {
    alert(params.get("msg"));
  }
});

// ========================================
// Enhanced Animations & Interactions
// ========================================

// Reveal on scroll
const revealElements = document.querySelectorAll('[data-reveal]');
const revealOnScroll = () => {
  const windowHeight = window.innerHeight;
  revealElements.forEach(el => {
    const elementTop = el.getBoundingClientRect().top;
    const elementVisible = 150;
    if (elementTop < windowHeight - elementVisible) {
      el.classList.add('visible');
    }
  });
};

// Add scroll listener with throttle
let scrollTimeout;
window.addEventListener('scroll', () => {
  if (scrollTimeout) clearTimeout(scrollTimeout);
  scrollTimeout = setTimeout(revealOnScroll, 10);
});

// Initial check
revealOnScroll();

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// Add parallax effect to cards
const parallaxCards = document.querySelectorAll('.card');
parallaxCards.forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = (y - centerY) / 20;
    const rotateY = (centerX - x) / 20;
    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
  });
  
  card.addEventListener('mouseleave', () => {
    card.style.transform = '';
  });
});

// Add ripple effect to buttons
document.querySelectorAll('.btn').forEach(btn => {
  btn.classList.add('ripple');
  btn.addEventListener('click', function(e) {
    const ripple = document.createElement('span');
    ripple.classList.add('ripple-effect');
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = e.clientX - rect.left - size / 2 + 'px';
    ripple.style.top = e.clientY - rect.top - size / 2 + 'px';
    this.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  });
});

// Add typing effect to headings
const typedElements = document.querySelectorAll('.typing-effect');
typedElements.forEach(el => {
  const text = el.textContent;
  el.textContent = '';
  let i = 0;
  const type = () => {
    if (i < text.length) {
      el.textContent += text.charAt(i);
      i++;
      setTimeout(type, 50);
    }
  };
  type();
});

// Counter animation for numbers
const counterElements = document.querySelectorAll('.counter');
const animateCounter = (el) => {
  const target = parseInt(el.dataset.target);
  const duration = 2000;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      el.textContent = target;
      clearInterval(timer);
    } else {
      el.textContent = Math.floor(current);
    }
  }, 16);
};

counterElements.forEach(el => {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(el);
        observer.unobserve(el);
      }
    });
  });
  observer.observe(el);
});

// Add floating animation to specific elements
const floatingElements = document.querySelectorAll('.floating');
floatingElements.forEach((el, index) => {
  el.style.animationDelay = `${index * 0.5}s`;
});

// Toast notification system
const gymgeeks = window.gymgeeks || {};

gymgeeks.showToast = function(message, type = 'info') {
  const toast = document.createElement('div');
  toast.className = `toast-notification toast-${type}`;
  toast.innerHTML = `
    <div class="toast-content">
      <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : type === 'error' ? 'bi-x-circle-fill' : 'bi-info-circle-fill'}"></i>
      <span>${message}</span>
    </div>
  `;
  
  // Add styles dynamically
  if (!document.getElementById('toast-styles')) {
    const styles = document.createElement('style');
    styles.id = 'toast-styles';
    styles.textContent = `
      .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideInRight 0.3s ease;
      }
      .toast-success { border-left: 4px solid #10b981; }
      .toast-error { border-left: 4px solid #dc3545; }
      .toast-info { border-left: 4px solid #667eea; }
      .toast-content { display: flex; align-items: center; gap: 10px; }
      @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
    `;
    document.head.appendChild(styles);
  }
  
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slideOutRight 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
};

gymgeeks.confetti = function() {
  const colors = ['#667eea', '#764ba2', '#10b981', '#f59e0b', '#dc3545'];
  for (let i = 0; i < 50; i++) {
    const confetti = document.createElement('div');
    confetti.className = 'confetti';
    confetti.style.cssText = `
      position: fixed;
      left: ${Math.random() * 100}vw;
      top: -10px;
      width: ${Math.random() * 10 + 5}px;
      height: ${Math.random() * 10 + 5}px;
      background: ${colors[Math.floor(Math.random() * colors.length)]};
      border-radius: ${Math.random() > 0.5 ? '50%' : '0'};
      animation: fall ${Math.random() * 3 + 2}s linear forwards;
      z-index: 9999;
    `;
    document.body.appendChild(confetti);
    setTimeout(() => confetti.remove(), 5000);
  }
  
  // Add confetti animation styles
  if (!document.getElementById('confetti-styles')) {
    const styles = document.createElement('style');
    styles.id = 'confetti-styles';
    styles.textContent = `
      @keyframes fall {
        to {
          transform: translateY(100vh) rotate(720deg);
        }
      }
    `;
    document.head.appendChild(styles);
  }
};

window.gymgeeks = gymgeeks;

// Add hover sound effect (optional - commented out)
// document.querySelectorAll('.btn, .card').forEach(el => {
//   el.addEventListener('mouseenter', () => {
//     // Uncomment to enable sound
//     // new Audio('/assets/sounds/hover.mp3').play();
//   });
// });