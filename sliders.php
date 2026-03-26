<?php
// Prevent direct access
if(!defined('INCLUDED_FROM_SLIDERS')) {
    http_response_code(403);
    die('Direct access not permitted');
}

// Database connection
require_once 'config.php';

// Get active sliders from database
$query = "SELECT * FROM sliders WHERE is_active = 1 ORDER BY sort_order ASC";
$result = mysqli_query($conn, $query);

// Store sliders in array
$sliders = array();
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sliders[] = $row;
    }
}
?>

<style>

/* Add keyframes for text animations */
@keyframes slideInLeft {
    0% {
        transform: translateX(-100px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInRight {
    0% {
        transform: translateX(100px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeInUp {
    0% {
        transform: translateY(50px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes scaleIn {
    0% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.slider-pro-banner {
    width: 100%;
    position: relative;
    overflow: hidden;
    background-color: #000;
    aspect-ratio: 16/9;
    max-height: 90vh;
    font-family: 'Poppins', sans-serif;
}

.slider-pro-slide {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 10%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: opacity 0.8s ease-in-out;
}

.slider-pro-slide[style*="background-image"] {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: transform 0.8s ease-in-out;
}

.slider-pro-slide.active {
    opacity: 1;
    z-index: 2;
    position: relative;
}

.slider-pro-slide::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.2) 100%);
    z-index: 2;
    transition: all 0.8s ease;
    backdrop-filter: blur(3px);
}

.slider-pro-slide:hover::after {
    background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.2));
    backdrop-filter: blur(1px);
}

.slider-pro-content {
    position: relative;
    z-index: 3;
    color: #fff;
    text-align: left;
    max-width: 600px;
    opacity: 0;
    transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1);
    transition-delay: 0.3s;
    padding: 2rem;
}

.slider-pro-slide.active .slider-pro-content {
    opacity: 1;
}

.slider-pro-title {
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    letter-spacing: -0.02em;
    line-height: 1.1;
    font-family: 'Playfair Display', serif;
    opacity: 0;
    transition: opacity 0.8s ease;
    transition-delay: 0.5s;
}

.slider-pro-slide.active .slider-pro-title {
    opacity: 1;
    animation: slideInLeft 1s ease forwards;
}

.slider-pro-subtitle {
    font-size: clamp(1rem, 2vw, 1.25rem);
    margin-bottom: 2.5rem;
    font-weight: 300;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    max-width: 500px;
    line-height: 1.8;
    opacity: 0;
    transition: opacity 0.8s ease;
    transition-delay: 0.7s;
}

.slider-pro-slide.active .slider-pro-subtitle {
    opacity: 1;
    animation: slideInRight 1s ease forwards 0.3s;
}

.slider-pro-button {
    display: inline-flex;
    align-items: center;
    padding: 1.2rem 3rem;
    background: transparent;
    color: #fff;
    text-decoration: none;
    border-radius: 2px;
    font-weight: 500;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255,255,255,0.3);
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(5px);
}

.slider-pro-slide.active .slider-pro-button {
    opacity: 1;
    animation: fadeInUp 1s ease forwards 0.6s;
}

.slider-pro-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.1);
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    transform: skewX(-15deg);
}

.slider-pro-button:hover {
    background: rgba(255,255,255,0.9);
    color: #000;
    border-color: transparent;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.slider-pro-button:hover::before {
    transform: skewX(-15deg) translateX(200%);
}

.slider-pro-button i {
    margin-left: 8px;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slider-pro-button:hover i {
    transform: translateX(8px);
}

.slider-pro-nav {
    position: absolute;
    bottom: 5%;
    left: 10%;
    display: flex;
    gap: 12px;
    z-index: 10;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInNav 0.8s ease forwards;
    animation-delay: 1.1s;
}

@keyframes fadeInNav {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slider-pro-dot {
    width: 30px;
    height: 2px;
    border-radius: 0;
    background: rgba(255,255,255,0.3);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.slider-pro-dot::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.5);
    transition: transform 0.4s ease;
}

.slider-pro-dot:hover::before {
    transform: translateX(100%);
}

.slider-pro-dot.active {
    background: #fff;
    width: 50px;
}

.slider-pro-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
    font-size: 1.2rem;
    cursor: pointer;
    z-index: 10;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.slider-pro-arrow:hover {
    background: #fff;
    color: #000;
    border-color: #fff;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.slider-pro-arrow.prev {
    left: 20px;
    opacity: 0;
    transform: translateX(-20px) translateY(-50%);
    animation: fadeInArrowPrev 0.8s ease forwards;
    animation-delay: 1.3s;
}

.slider-pro-arrow.next {
    right: 20px;
    opacity: 0;
    transform: translateX(20px) translateY(-50%);
    animation: fadeInArrowNext 0.8s ease forwards;
    animation-delay: 1.3s;
}

@keyframes fadeInArrowPrev {
    from {
        opacity: 0;
        transform: translateX(-20px) translateY(-50%);
    }
    to {
        opacity: 1;
        transform: translateX(0) translateY(-50%);
    }
}

@keyframes fadeInArrowNext {
    from {
        opacity: 0;
        transform: translateX(20px) translateY(-50%);
    }
    to {
        opacity: 1;
        transform: translateX(0) translateY(-50%);
    }
}

.slider-pro-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: rgba(255,255,255,0.2);
    z-index: 10;
    overflow: hidden;
}

.slider-pro-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #fff, rgba(255,255,255,0.8));
    width: 0;
    transition: width 0.3s linear;
    position: relative;
}

.slider-pro-progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3));
    animation: progressGlow 2s linear infinite;
}

@keyframes progressGlow {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(100%);
    }
}

@media (max-width: 1200px) {
    .slider-pro-banner {
        aspect-ratio: 16/10;
    }
}

@media (max-width: 768px) {
    .slider-pro-banner {
        aspect-ratio: 4/3;
    }
    
    .slider-pro-slide {
        padding: 0 5%;
        background-position: center 20%;
    }
    
    .slider-pro-content {
        max-width: 100%;
    }
    
    .slider-pro-nav {
        left: 5%;
    }
    
    .slider-pro-title {
        font-size: clamp(2rem, 4vw, 3rem);
    }
    
    .slider-pro-subtitle {
        font-size: 1rem;
    }
    
    .slider-pro-button {
        padding: 0.8rem 2rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .slider-pro-banner {
        aspect-ratio: 3/4;
    }
    
    .slider-pro-slide {
        background-position: center 30%;
    }
    
    .slider-pro-nav {
        left: 20px;
    }
    
    .slider-pro-title {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .slider-pro-subtitle {
        font-size: 0.9rem;
        margin-bottom: 2rem;
    }
    
    .slider-pro-button {
        padding: 0.7rem 1.5rem;
        font-size: 0.8rem;
    }
    
    .slider-pro-arrow {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

<div class="slider-pro-banner">
    <?php if (empty($sliders)): ?>
    <!-- Default slider if no sliders in database -->
    <div class="slider-pro-slide active" style="background-image: url('./banner/default.jpg');">
        <div class="slider-pro-content">
            <h1 class="slider-pro-title">Welcome to PoolPal</h1>
            <p class="slider-pro-subtitle">Your trusted carpooling platform for safe and comfortable journeys.</p>
            <a href="signup.php" class="slider-pro-button">Join Now <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($sliders as $index => $slider): 
            // Skip if media file is not set or empty
            if (empty($slider['media_file'])) continue;
            
            // Set default values if not provided
            $title = !empty($slider['title']) ? $slider['title'] : 'Welcome to PoolPal';
            $description = !empty($slider['description']) ? $slider['description'] : 'Your trusted carpooling platform';
            $link = !empty($slider['link_url']) ? $slider['link_url'] : 'signup.php';
            $alt_text = !empty($slider['alt_text']) ? $slider['alt_text'] : $title;
        ?>
        <div class="slider-pro-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
             style="background-image: url('<?php echo htmlspecialchars($slider['media_file']); ?>');"
             aria-label="<?php echo htmlspecialchars($alt_text); ?>">
            <div class="slider-pro-content">
                <h1 class="slider-pro-title"><?php echo htmlspecialchars($title); ?></h1>
                <p class="slider-pro-subtitle"><?php echo htmlspecialchars($description); ?></p>
                <a href="<?php echo htmlspecialchars($link); ?>" 
                   class="slider-pro-button">
                   Browse Nearby Rides <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Navigation arrows -->
    <button class="slider-pro-arrow prev" aria-label="Previous slide">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="slider-pro-arrow next" aria-label="Next slide">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Navigation dots -->
    <div class="slider-pro-nav">
        <?php 
        $total_slides = empty($sliders) ? 1 : count($sliders);
        for ($i = 0; $i < $total_slides; $i++):
        ?>
        <span class="slider-pro-dot <?php echo $i === 0 ? 'active' : ''; ?>"></span>
        <?php endfor; ?>
    </div>

    <!-- Progress bar -->
    <div class="slider-pro-progress">
        <div class="slider-pro-progress-bar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slider-pro-slide');
    const dots = document.querySelectorAll('.slider-pro-dot');
    const prevBtn = document.querySelector('.slider-pro-arrow.prev');
    const nextBtn = document.querySelector('.slider-pro-arrow.next');
    const progressBar = document.querySelector('.slider-pro-progress-bar');
    
    let currentSlide = 0;
    let slideInterval;
    const intervalTime = 4000; // Reduced to 4 seconds for more dynamic transitions
    let isAnimating = false;
    
    function resetProgressBar() {
        progressBar.style.width = '0%';
    }
    
    function startProgressBar() {
        resetProgressBar();
        requestAnimationFrame(() => {
            progressBar.style.width = '100%';
            progressBar.style.transition = `width ${intervalTime}ms linear`;
        });
    }
    
    function goToSlide(index, direction = 'next') {
        if (isAnimating) return;
        isAnimating = true;
        
        const currentSlideElement = slides[currentSlide];
        const nextIndex = (index + slides.length) % slides.length;
        const nextSlideElement = slides[nextIndex];
        
        // Prepare slides for transition
        currentSlideElement.style.transition = 'opacity 0.8s ease-in-out';
        nextSlideElement.style.transition = 'opacity 0.8s ease-in-out';
        
        // Set initial positions
        if (direction === 'next') {
            nextSlideElement.style.transform = 'translateX(100%)';
            currentSlideElement.style.transform = 'translateX(0)';
        } else {
            nextSlideElement.style.transform = 'translateX(-100%)';
            currentSlideElement.style.transform = 'translateX(0)';
        }
        
        // Update dots
        dots[currentSlide].classList.remove('active');
        dots[nextIndex].classList.add('active');
        
        // Start transition
        requestAnimationFrame(() => {
            // Animate slides
            nextSlideElement.style.opacity = '1';
            currentSlideElement.style.opacity = '0';
            
            if (direction === 'next') {
                currentSlideElement.style.transform = 'translateX(-100%)';
                nextSlideElement.style.transform = 'translateX(0)';
            } else {
                currentSlideElement.style.transform = 'translateX(100%)';
                nextSlideElement.style.transform = 'translateX(0)';
            }
        });
        
        // Update current slide
        currentSlide = nextIndex;
        
        // Reset animation state after transition
        setTimeout(() => {
            currentSlideElement.classList.remove('active');
            nextSlideElement.classList.add('active');
            isAnimating = false;
            
            // Reset transforms
            slides.forEach(slide => {
                slide.style.transform = '';
                slide.style.transition = '';
            });
        }, 800);
        
        resetProgressBar();
        startProgressBar();
    }
    
    function nextSlide() {
        goToSlide(currentSlide + 1, 'next');
    }
    
    function prevSlide() {
        goToSlide(currentSlide - 1, 'prev');
    }
    
    // Initialize slider with smoother auto-play
    function startSlider() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
        slideInterval = setInterval(() => {
            nextSlide();
        }, intervalTime);
        startProgressBar();
    }
    
    function pauseSlider() {
        if (slideInterval) {
            clearInterval(slideInterval);
            slideInterval = null;
        }
        progressBar.style.transition = 'none';
    }
    
    // Event Listeners
    prevBtn.addEventListener('click', () => {
        prevSlide();
        pauseSlider();
        // Slight delay before restarting to prevent immediate transition
        setTimeout(startSlider, 50);
    });
    
    nextBtn.addEventListener('click', () => {
        nextSlide();
        pauseSlider();
        // Slight delay before restarting to prevent immediate transition
        setTimeout(startSlider, 50);
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            if (index === currentSlide) return;
            
            if (index > currentSlide) {
                goToSlide(index, 'next');
            } else {
                goToSlide(index, 'prev');
            }
            pauseSlider();
            // Slight delay before restarting to prevent immediate transition
            setTimeout(startSlider, 50);
        });
    });
    
    // Enhanced touch events for mobile swipe
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    let minSwipeDistance = 50;
    
    const slider = document.querySelector('.slider-pro-banner');
    
    slider.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
        pauseSlider();
    }, { passive: true });
    
    slider.addEventListener('touchmove', e => {
        e.preventDefault();
    }, { passive: false });
    
    slider.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
        // Restart slider after swipe
        setTimeout(startSlider, 50);
    }, { passive: true });
    
    function handleSwipe() {
        const deltaX = touchStartX - touchEndX;
        const deltaY = touchStartY - touchEndY;
        
        // Check if horizontal swipe
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            if (deltaX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
    }
    
    // Enhanced hover effects
    slider.addEventListener('mouseenter', () => {
        pauseSlider();
        prevBtn.style.opacity = '1';
        nextBtn.style.opacity = '1';
    });
    
    slider.addEventListener('mouseleave', () => {
        startSlider();
        prevBtn.style.opacity = '0.7';
        nextBtn.style.opacity = '0.7';
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            pauseSlider();
            setTimeout(startSlider, 50);
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            pauseSlider();
            setTimeout(startSlider, 50);
        }
    });
    
    // Visibility change handling
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            pauseSlider();
        } else {
            startSlider();
        }
    });
    
    // Start the slider
    startSlider();
});
</script> 