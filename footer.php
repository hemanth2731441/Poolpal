<!-- Footer -->
<footer class="pp-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand footer-col">
        <a href="fpage.php" class="pp-logo-link">
          <img src="images/logo/logo-new.png" alt="PoolPal" class="pp-logo-img" />
        </a>
        <p>Connecting commuters for a greener tomorrow. Share rides, save costs, and reduce your carbon footprint — all from our mobile app.</p>
        <div class="footer-social">
          <a href="https://www.facebook.com/poolpal" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://x.com/poolpal" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
          <a href="https://www.instagram.com/poolpal" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://www.linkedin.com/company/poolpal" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://www.youtube.com/@poolpal" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-col">
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="fpage.php">Home</a></li>
          <li><a href="aboutus.php">About Us</a></li>
          <li><a href="#download">Get the App</a></li>
        </ul>
      </div>

      <!-- Legal -->
      <div class="footer-col">
        <h4 class="footer-heading">Legal</h4>
        <ul class="footer-links">
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Refund Policy</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div class="footer-col">
        <h4 class="footer-heading">Contact</h4>
        <div class="footer-contact-item">
          <i class="fas fa-envelope"></i>
          <span>support@poolpal.com</span>
        </div>
        <div class="footer-contact-item">
          <i class="fas fa-phone-alt"></i>
          <span>+91 9948434347</span>
        </div>
        <div class="footer-contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>Ramaraonagar, Motinagar,<br>Balanagar Mandal, Medchal District,<br>Telangana, India — 500018</span>
        </div>
      </div>
    </div>

    <div class="footer-divider"></div>

    <div class="footer-bottom">
      <span>&copy; 2025 MacGInfotech. All rights reserved.</span>
      <span>Made with <i class="fas fa-heart" style="color: var(--primary); margin: 0 4px;"></i> in India</span>
    </div>
  </div>
</footer>

<!-- Back to top -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <i class="fas fa-chevron-up"></i>
</button>

<script>
// Back to top
const btt = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
  btt.classList.toggle('visible', window.scrollY > 400);
});
btt.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
</body>
</html>
