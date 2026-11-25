<?php
session_start();
require_once '../admin/db_con.php';

if (isset($_SESSION['client_login'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Lebawi International Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #5f2985;
            --primary-light: #7b3aa8;
            --secondary: #ffffff;
            --accent: #212529;
            --highlight: #f1c40f;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--secondary);
            color: var(--accent);
            overflow-x: hidden;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .nav-link {
            color: var(--secondary);
            font-weight: 500;
            transition: color 0.3s;
        }
        .navbar .nav-link:hover {
            color: var(--highlight);
        }
        .hero {
            position: relative;
            height: 100vh;
            background: url('https://lebawi.com/wp-content/uploads/2023/05/slidenew2.jpg') no-repeat center center/cover;
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
            animation: slideInDown 1s ease-out;
        }
        .hero p {
            font-size: 1.5rem;
            margin: 20px 0;
        }
        .btn-custom {
            background: var(--highlight);
            color: var(--accent);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: background 0.3s, transform 0.3s;
        }
        .btn-custom:hover {
            background: #d4ac0d;
            transform: scale(1.1);
        }
        .features {
            padding: 80px 0;
        }
        .feature-card {
            background: var(--secondary);
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        .feature-card i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        .testimonials {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--secondary);
        }
        .swiper-slide {
            text-align: center;
            padding: 20px;
        }
        .testimonial-card {
            background: var(--secondary);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .footer {
            background: var(--accent);
            color: var(--secondary);
            padding: 40px 0;
            text-align: center;
        }
        .footer a {
            color: var(--highlight);
            text-decoration: none;
            margin: 0 10px;
        }
        .footer a:hover {
            color: #d4ac0d;
        }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate {
            animation: fadeIn 1s ease-out;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-white" href="#">Lebawi International Academy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Why Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Locally Robust Globally Connected</h1>
            <p>የሰው ልጅ ክቡር ነው ፡፡</p>
            <div class="mt-4">
                <a href="login.php" class="btn btn-custom me-2">Login</a>
                <a href="signup.php" class="btn btn-custom">Sign Up</a>
            </div>
        </div>
    </div>

    <div class="features" id="features">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Why Choose Lebawi?</h2>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <i class="fas fa-graduation-cap"></i>
                        <h4>Academic Excellence</h4>
                        <p>Our rigorous curriculum and certified educators foster critical thinking and personal growth.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="fas fa-users"></i>
                        <h4>Vibrant Community</h4>
                        <p>Join a supportive network of dedicated teachers, staff, and engaged parents.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <i class="fas fa-trophy"></i>
                        <h4>Holistic Development</h4>
                        <p>We celebrate achievements and nurture well-rounded students through diverse programs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="testimonials">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">What Parents Say</h2>
            <div class="swiper mySwiper" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p style="color:purple;">Lebawi has transformed my child's education. The teachers are exceptional!"</p>
                            <h5 style="color:purple;">- Sarah K.</h5>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p style="color:purple;">"The community at Lebawi is unmatched. My kids love it here!"</p>
                            <h5 style="color:purple;">- Michael T.</h5>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <p style="color:purple;">"A truly world-class institution with a focus on every student's success."</p>
                            <h5 style="color:purple;">- Aisha M.</h5>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; 2025 Lebawi International Academy. All rights reserved.</p>
            <div class="mt-2">
                <a href="mailto:info@lebawi.academy"><i class="fas fa-envelope"></i> Contact Us</a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });
        new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            breakpoints: {
                768: { slidesPerView: 2 },
                992: { slidesPerView: 3 }
            }
        });
    </script>
</body>
</html>