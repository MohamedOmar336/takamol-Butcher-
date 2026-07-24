<!DOCTYPE html>
<html lang="ar" dir="rtl" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دكان هب | نقطة البيع الذكية لكل محل</title>
    <meta name="description"
        content="دكان هب - منظومة POS متكاملة للمحلات التجارية. إدارة مبيعات، مخزون، تقارير يومية، فواتير حرارية، ودعم الموازين. ابدأ اليوم!">
    <link rel="shortcut icon" type="image/jpeg"
        href="{{ asset('images/logo.jpg') }}?v={{ filemtime(public_path('images/logo.jpg')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ==================== CSS VARIABLES ==================== */
        :root {
            --red: #e11a22;
            --red-dark: #b50f16;
            --red-glow: rgba(225, 26, 34, 0.35);
            --bg: #0a0a0f;
            --bg2: #111118;
            --bg3: #18181f;
            --card: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 255, 255, 0.08);
            --text: #f0f0f5;
            --text-muted: #8888aa;
            --text-light: #ccccdd;
            --white: #ffffff;
            --gold: #f59e0b;
            --success: #10b981;
        }

        /* ==================== RESET ==================== */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Cairo', sans-serif;
            overflow-x: hidden;
            line-height: 1.7;
        }

        body.en {
            font-family: 'Outfit', sans-serif;
            direction: ltr;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* ==================== PARTICLE CANVAS ==================== */
        #particles-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        /* ==================== NAVBAR ==================== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.4s ease;
        }

        .navbar.scrolled {
            background: rgba(10, 10, 15, 0.92);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--card-border);
        }

        .nav-logo img {
            height: 120px;
            width: auto;
            filter: drop-shadow(0 0 12px var(--red-glow));
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .lang-btn {
            background: var(--card);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            padding: 7px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .lang-btn:hover {
            border-color: var(--red);
            color: var(--white);
        }

        .platform-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--card-border);
            color: var(--text-light);
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .platform-btn:hover {
            border-color: rgba(255, 255, 255, 0.25);
            color: var(--white);
        }

        .nav-cta {
            background: var(--red);
            color: var(--white);
            padding: 9px 22px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px var(--red-glow);
            font-family: inherit;
        }

        .nav-cta:hover {
            background: var(--red-dark);
            transform: translateY(-2px);
            box-shadow: 0 0 35px var(--red-glow);
        }

        /* ==================== SECTIONS WRAPPER ==================== */
        section {
            position: relative;
            z-index: 1;
        }

        /* ==================== HERO ==================== */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 20px 80px;
            position: relative;
        }

        .hero-content {
            max-width: 860px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(225, 26, 34, 0.12);
            border: 1px solid rgba(225, 26, 34, 0.3);
            color: var(--red);
            padding: 7px 18px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 28px;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0.5
            }
        }

        .hero-title {
            font-size: clamp(2.4rem, 6vw, 4.2rem);
            font-weight: 900;
            line-height: 1.25;
            margin-bottom: 22px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero-title .brand-red {
            color: var(--red);
        }

        .typed-cursor {
            display: inline-block;
            border-right: 3px solid var(--red);
            margin-right: 2px;
            animation: blink 0.8s infinite;
        }

        body.en .typed-cursor {
            border-right: none;
            border-left: 3px solid var(--red);
            margin-right: 0;
            margin-left: 2px;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0
            }
        }

        .hero-sub {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 620px;
            margin: 0 auto 40px;
            animation: fadeInUp 0.8s ease 0.35s both;
        }

        .hero-btns {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.5s both;
        }

        .btn-primary-hero {
            background: var(--red);
            color: #fff;
            padding: 15px 34px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.05rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px var(--red-glow);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: inherit;
        }

        .btn-primary-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 40px var(--red-glow);
        }

        .btn-secondary-hero {
            background: transparent;
            color: var(--text);
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            border: 1px solid var(--card-border);
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: inherit;
        }

        .btn-secondary-hero:hover {
            border-color: var(--red);
            color: var(--red);
        }

        .hero-scroll-hint {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 0.78rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateX(-50%) translateY(0)
            }

            50% {
                transform: translateX(-50%) translateY(8px)
            }
        }

        /* ==================== STATS STRIP ==================== */
        .stats-strip {
            background: var(--bg3);
            border-top: 1px solid var(--card-border);
            border-bottom: 1px solid var(--card-border);
            padding: 50px 40px;
        }

        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-num {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--red);
            display: block;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ==================== GENERIC SECTION ==================== */
        .section-pad {
            padding: 100px 20px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 70px;
        }

        .section-tag {
            display: inline-block;
            background: rgba(225, 26, 34, 0.1);
            border: 1px solid rgba(225, 26, 34, 0.25);
            color: var(--red);
            padding: 5px 16px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            margin-bottom: 14px;
            line-height: 1.25;
        }

        .section-desc {
            color: var(--text-muted);
            font-size: 1rem;
            max-width: 580px;
            margin: 0 auto;
        }

        .glow-divider {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--red), transparent);
            margin: 0 auto 20px;
            border-radius: 2px;
        }

        /* ==================== FEATURES ==================== */
        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .feature-card {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 32px 28px;
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--red), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            border-color: rgba(225, 26, 34, 0.3);
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: rgba(225, 26, 34, 0.12);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 20px;
            border: 1px solid rgba(225, 26, 34, 0.2);
        }

        .feature-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .feature-desc {
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.7;
        }

        /* ==================== HOW IT WORKS ==================== */
        .how-section {
            background: var(--bg2);
        }

        .steps-wrapper {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }

        .step-item {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 24px;
            align-items: flex-start;
            padding-bottom: 40px;
            position: relative;
        }

        .step-item:not(:last-child) .step-num::after {
            content: '';
            position: absolute;
            top: 60px;
            right: 28px;
            width: 2px;
            height: calc(100% - 30px);
            background: linear-gradient(to bottom, var(--red), transparent);
        }

        body.en .step-item:not(:last-child) .step-num::after {
            right: auto;
            left: 28px;
        }

        .step-num {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: rgba(225, 26, 34, 0.12);
            border: 2px solid var(--red);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 900;
            color: var(--red);
            flex-shrink: 0;
            position: relative;
        }

        .step-content {
            padding-top: 12px;
        }

        .step-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .step-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* ==================== PRICING ==================== */
        .pricing-grid {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 28px;
        }

        .pricing-card {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px 36px;
            position: relative;
            transition: all 0.35s ease;
        }

        .pricing-card.popular {
            border-color: var(--red);
            background: rgba(225, 26, 34, 0.06);
            box-shadow: 0 0 60px rgba(225, 26, 34, 0.15);
        }

        .popular-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--red);
            color: #fff;
            padding: 5px 20px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .plan-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .plan-price-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 6px;
        }

        .plan-price {
            font-size: 3rem;
            font-weight: 900;
            color: var(--white);
            line-height: 1;
        }

        .plan-currency {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-muted);
            padding-bottom: 8px;
        }

        .plan-period {
            font-size: 0.85rem;
            color: var(--text-muted);
            padding-bottom: 10px;
        }

        .plan-daily {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: var(--success);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 24px;
        }

        .plan-onetime {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: var(--gold);
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: inline-block;
        }

        .plan-divider {
            border: none;
            border-top: 1px solid var(--card-border);
            margin: 22px 0;
        }

        .plan-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .plan-features li::before {
            content: '✓';
            color: var(--success);
            font-weight: 900;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .plan-btn {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .plan-btn-outline {
            background: transparent;
            border: 2px solid var(--card-border);
            color: var(--text);
        }

        .plan-btn-outline:hover {
            border-color: var(--red);
            color: var(--red);
        }

        .plan-btn-solid {
            background: var(--red);
            color: #fff;
            box-shadow: 0 8px 30px var(--red-glow);
        }

        .plan-btn-solid:hover {
            background: var(--red-dark);
            transform: translateY(-2px);
        }

        /* ==================== STORE ACCESS MODAL ==================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .modal-box {
            background: #111118;
            border: 1px solid rgba(225, 26, 34, 0.3);
            border-radius: 24px;
            padding: 40px 36px;
            max-width: 460px;
            width: 90%;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s ease;
            box-shadow: 0 0 60px rgba(225, 26, 34, 0.2);
        }

        .modal-overlay.active .modal-box {
            transform: translateY(0);
        }

        .modal-close {
            position: absolute;
            top: 16px;
            left: 16px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.4rem;
            cursor: pointer;
            line-height: 1;
        }

        body.en .modal-close {
            left: auto;
            right: 16px;
        }

        .modal-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .modal-sub {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 26px;
        }

        .modal-slug-row {
            display: flex;
            align-items: center;
            gap: 0;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.03);
            margin-bottom: 14px;
            transition: border-color 0.2s;
        }

        .modal-slug-row:focus-within {
            border-color: var(--red);
        }

        .modal-slug-input {
            flex: 1;
            background: none;
            border: none;
            padding: 14px 16px;
            color: var(--text);
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            outline: none;
            direction: ltr;
        }

        .modal-slug-suffix {
            padding: 14px 16px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-family: 'Outfit', sans-serif;
            border-right: 1px solid var(--card-border);
        }

        body.en .modal-slug-suffix {
            border-right: none;
            border-left: 1px solid var(--card-border);
        }

        .modal-submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.3s ease;
            box-shadow: 0 6px 24px var(--red-glow);
        }

        .modal-submit-btn:hover {
            background: var(--red-dark);
            transform: translateY(-2px);
        }

        /* ==================== CONTACT ==================== */
        .contact-section {
            background: var(--bg2);
        }

        .contact-card {
            max-width: 700px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 60px 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .contact-card::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--red-glow), transparent 70%);
            pointer-events: none;
        }

        .contact-title {
            font-size: 1.9rem;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .contact-sub {
            color: var(--text-muted);
            margin-bottom: 36px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 36px;
            align-items: center;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-light);
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            padding: 12px 22px;
            border-radius: 12px;
            direction: ltr;
            font-family: 'Outfit', sans-serif;
        }

        .contact-info-item .icon {
            font-size: 1.2rem;
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #25d366;
            color: #fff;
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 1.05rem;
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.3);
            font-family: inherit;
            text-decoration: none;
        }

        .whatsapp-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 40px rgba(37, 211, 102, 0.4);
        }

        /* ==================== FOOTER ==================== */
        footer {
            background: var(--bg);
            border-top: 1px solid var(--card-border);
            padding: 40px;
            text-align: center;
        }

        .footer-logo img {
            height: 30px;
            opacity: 0.7;
            margin-bottom: 14px;
        }

        .footer-copy {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .footer-copy a {
            color: var(--red);
        }

        /* ==================== ANIMATIONS ==================== */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .reveal-delay-3 {
            transition-delay: 0.3s;
        }

        /* ==================== FLOATING WHATSAPP ==================== */
        .floating-wa {
            position: fixed;
            bottom: 28px;
            left: 28px;
            width: 58px;
            height: 58px;
            background: #25d366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.4);
            z-index: 999;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: float-wa 3s ease-in-out infinite;
            text-decoration: none;
        }

        body.en .floating-wa {
            left: auto;
            right: 28px;
        }

        .floating-wa:hover {
            transform: scale(1.12) !important;
        }

        @keyframes float-wa {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-6px)
            }
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .navbar {
                padding: 14px 16px;
                gap: 8px;
            }

            .nav-actions {
                gap: 8px;
            }

            .platform-btn span {
                display: none;
            }

            .hero {
                padding: 100px 16px 70px;
            }

            .section-pad {
                padding: 70px 16px;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .contact-card {
                padding: 40px 24px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Particle Canvas -->
    <canvas id="particles-canvas"></canvas>

    <!-- Floating WhatsApp -->
    <a href="https://wa.me/201111847065" target="_blank" class="floating-wa" title="تواصل عبر واتسآب">💬</a>

    <!-- Store Access Modal -->
    <div class="modal-overlay" id="storeModal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeStoreModal()">✕</button>
            <h2 class="modal-title" id="modalTitle">ادخل على متجرك</h2>
            <p class="modal-sub" id="modalSub">أدخل اسم متجرك للوصول لنظام الكاشير الخاص بك</p>
            <form action="{{ route('central.redirect_store') }}" method="POST">
                @csrf
                @if($errors->any())
                    <p style="color:#ef4444;font-size:0.85rem;margin-bottom:12px;">{{ $errors->first() }}</p>
                @endif
                <div class="modal-slug-row">
                    <input type="text" name="slug" class="modal-slug-input" placeholder="takamul" required
                        value="{{ old('slug') }}" autocomplete="off">
                    @php $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost'; @endphp
                    <span class="modal-slug-suffix">.{{ $centralDomain }}</span>
                </div>
                <button type="submit" class="modal-submit-btn" id="modalSubmit">🚀 انتقل للكاشير</button>
            </form>
        </div>
    </div>

    <!-- ==================== NAVBAR ==================== -->
    <nav class="navbar" id="navbar">
        <div class="nav-logo">
            <a href="#"><img src="{{ asset('images/logo.jpg') }}?v={{ filemtime(public_path('images/logo.jpg')) }}"
                    alt="DokkanHub" onerror="this.src='https://placehold.co/160x38/e11a22/white?text=dokkanhub'"></a>
        </div>
        <div class="nav-actions">
            <button class="lang-btn" id="langToggle" onclick="toggleLang()">EN</button>
            <a href="{{ route('super_admin.login') }}">
                <button class="platform-btn" id="platformBtn">
                    <span>🏢</span>
                    <span id="platformBtnText">منصة الإدارة</span>
                </button>
            </a>
            <button class="nav-cta" id="navCta" onclick="openStoreModal()">ادخل على متجرك 🔑</button>
        </div>
    </nav>

    <!-- ==================== HERO ==================== -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-badge" id="heroBadge">🔴 منظومة POS الأذكى في مصر</div>
            <h1 class="hero-title" id="heroTitle">
                إدارة <span class="brand-red"><span id="heroTyped"></span><span class="typed-cursor"></span></span>
                <br>بدقة وسهولة مع <span class="brand-red">دكان هب</span>
            </h1>
            <p class="hero-sub" id="heroSub">
                نقطة بيع ذكية، تقارير يومية، فواتير حرارية، وإدارة مخزون — كل ده في إيدك بثمن أقل من فنجان قهوة في اليوم
                ☕
            </p>
            <div class="hero-btns">
                <button class="btn-primary-hero" id="heroCta" onclick="openStoreModal()">🔑 ادخل على متجرك</button>
                <a href="#pricing"><button class="btn-secondary-hero" id="heroPlans">📋 شوف الخطط</button></a>
            </div>
        </div>
        <div class="hero-scroll-hint">
            <span id="scrollHint">مرر للأسفل</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </div>
    </section>

    <!-- ==================== STATS ==================== -->
    <div class="stats-strip">
        <div class="stats-grid">
            <div class="stat-item reveal">
                <span class="stat-num" data-target="50" data-suffix="+">0</span>
                <span class="stat-label" id="stat1">محل يستخدم دكان هب</span>
            </div>
            <div class="stat-item reveal reveal-delay-1">
                <span class="stat-num" data-target="1000" data-suffix="+">0</span>
                <span class="stat-label" id="stat2">فاتورة يومياً</span>
            </div>
            <div class="stat-item reveal reveal-delay-2">
                <span class="stat-num" data-target="99.9" data-suffix="%" data-decimal="1">0</span>
                <span class="stat-label" id="stat3">وقت تشغيل مستمر</span>
            </div>
            <div class="stat-item reveal reveal-delay-3">
                <span class="stat-num" data-target="50" data-prefix="من " data-suffix=" جنيه/يوم">0</span>
                <span class="stat-label" id="stat4">تكلفة الاشتراك اليومية</span>
            </div>
        </div>
    </div>

    <!-- ==================== FEATURES ==================== -->
    <section class="section-pad" id="features">
        <div class="section-header">
            <div class="section-tag" id="featuresTag">المميزات</div>
            <div class="glow-divider"></div>
            <h2 class="section-title" id="featuresTitle">كل اللي محلك محتاجه في مكان واحد</h2>
            <p class="section-desc" id="featuresDesc">دكان هب بيديك أدوات احترافية بدون تعقيد — بسيطة، سريعة، وبتشتغل
                معاك من أول يوم</p>
        </div>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon">🖥️</div>
                <h3 class="feature-title" id="f1t">نقطة بيع ذكية (POS)</h3>
                <p class="feature-desc" id="f1d">واجهة كاشير سريعة وسهلة، دعم الباركود والمسح، وبيع بالكيلو أو القطعة
                    بضغطة واحدة</p>
            </div>
            <div class="feature-card reveal reveal-delay-1">
                <div class="feature-icon">⚖️</div>
                <h3 class="feature-title" id="f2t">دعم الموازين الحرارية</h3>
                <p class="feature-desc" id="f2d">تقرأ باركود الموازين تلقائياً وتسجل الوزن والسعر فوراً — مثالي للجزارة
                    والسوبرماركت</p>
            </div>
            <div class="feature-card reveal reveal-delay-2">
                <div class="feature-icon">🧾</div>
                <h3 class="feature-title" id="f3t">فواتير حرارية تلقائية</h3>
                <p class="feature-desc" id="f3d">فاتورة احترافية بشعار محلك بتطلع تلقائياً بعد كل بيعة — مع اسم المتجر
                    والتاريخ والتفاصيل</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">📦</div>
                <h3 class="feature-title" id="f4t">إدارة المخزون</h3>
                <p class="feature-desc" id="f4d">تتابع مخزونك في الوقت الفعلي، تنبيهات الكميات المنخفضة، واستيراد
                    المنتجات بسهولة</p>
            </div>
            <div class="feature-card reveal reveal-delay-1">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title" id="f5t">تقارير يومية على بريدك</h3>
                <p class="feature-desc" id="f5d">في نهاية كل يوم، تقرير شامل بالمبيعات وأكثر المنتجات مبيعاً والمخزون
                    المنخفض — على إيميلك</p>
            </div>
            <div class="feature-card reveal reveal-delay-2">
                <div class="feature-icon">👥</div>
                <h3 class="feature-title" id="f6t">كاشير متعدد وصلاحيات</h3>
                <p class="feature-desc" id="f6d">أضف عدة موظفين، حدد صلاحيات لكل واحد، وتابع أداء كل كاشير بشكل منفصل
                </p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">💰</div>
                <h3 class="feature-title" id="f7t">إدارة الديون والعملاء</h3>
                <p class="feature-desc" id="f7d">سجل عملاءك، تابع ديونهم، وادي لهم إيصال عند السداد — كل ده في مكان واحد
                </p>
            </div>
            <div class="feature-card reveal reveal-delay-1">
                <div class="feature-icon">🎨</div>
                <h3 class="feature-title" id="f8t">شعار محلك في كل حتة</h3>
                <p class="feature-desc" id="f8d">ارفع شعار محلك وهيظهر في الكاشير، الفواتير، وحتى أيقونة الموقع — هوية
                    كاملة لمحلك</p>
            </div>
        </div>
    </section>

    <!-- ==================== HOW IT WORKS ==================== -->
    <section class="section-pad how-section" id="how">
        <div class="section-header">
            <div class="section-tag" id="howTag">كيف يشتغل</div>
            <div class="glow-divider"></div>
            <h2 class="section-title" id="howTitle">ابدأ في 4 خطوات بسيطة</h2>
            <p class="section-desc" id="howDesc">ما فيش تعقيد ولا إعداد طويل — بتشتغل من أول يوم</p>
        </div>
        <div class="steps-wrapper">
            <div class="step-item reveal">
                <div class="step-num">1</div>
                <div class="step-content">
                    <h3 class="step-title" id="s1t">تواصل معنا على واتسآب</h3>
                    <p class="step-desc" id="s1d">بعتلنا رسالة واحدة وفريقنا هيرد عليك خلال دقائق ويشرح لك كل حاجة</p>
                </div>
            </div>
            <div class="step-item reveal reveal-delay-1">
                <div class="step-num">2</div>
                <div class="step-content">
                    <h3 class="step-title" id="s2t">هنجهز متجرك الخاص</h3>
                    <p class="step-desc" id="s2d">بنعمل لك حساب خاص بمحلك مع رابطك الفرعي الخاص — في دقائق معدودة</p>
                </div>
            </div>
            <div class="step-item reveal reveal-delay-2">
                <div class="step-num">3</div>
                <div class="step-content">
                    <h3 class="step-title" id="s3t">أضف منتجاتك</h3>
                    <p class="step-desc" id="s3d">أضف منتجاتك يدوياً أو استوردها من ملف Excel — الكاشير جاهز فوراً</p>
                </div>
            </div>
            <div class="step-item reveal reveal-delay-3">
                <div class="step-num">4</div>
                <div class="step-content">
                    <h3 class="step-title" id="s4t">ابدأ البيع وتابع تقاريرك</h3>
                    <p class="step-desc" id="s4d">الكاشير شغال، التقارير بتيجي على إيميلك كل يوم، وإنت مرتاح البال</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== PRICING ==================== -->
    <section class="section-pad" id="pricing">
        <div class="section-header">
            <div class="section-tag" id="pricingTag">الأسعار</div>
            <div class="glow-divider"></div>
            <h2 class="section-title" id="pricingTitle">خطط واضحة، بدون مفاجآت</h2>
            <p class="section-desc" id="pricingDesc">اختار الخطة اللي تناسبك — كل الخطط فيها نفس المميزات الاحترافية
                الكاملة</p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card reveal">
                <div class="plan-name" id="p1name">الخطة العادية</div>
                <div class="plan-price-row">
                    <span class="plan-price" id="p1price">2,000</span>
                    <span class="plan-currency" id="p1curr">جنيه</span>
                    <span class="plan-period" id="p1period">/ شهر</span>
                </div>
                <div class="plan-daily" id="p1daily">≈ 66.6 جنيه في اليوم 💡</div>
                <hr class="plan-divider">
                <ul class="plan-features">
                    <li id="pf1_1">نقطة بيع كاملة بكاشير متعدد</li>
                    <li id="pf1_2">تقارير يومية على البريد الإلكتروني</li>
                    <li id="pf1_3">فواتير حرارية بشعار محلك</li>
                    <li id="pf1_4">دعم الموازين الحرارية</li>
                    <li id="pf1_5">إدارة المخزون والموظفين</li>
                    <li id="pf1_6">إدارة العملاء والديون</li>
                    <li id="pf1_7">دعم فني متواصل</li>
                </ul>
                <a href="https://wa.me/201111847065?text=أهلاً، عايز أشترك في الخطة العادية 2000 جنيه" target="_blank">
                    <button class="plan-btn plan-btn-outline" id="p1btn">ابدأ بالخطة العادية</button>
                </a>
            </div>
            <div class="pricing-card popular reveal reveal-delay-1">
                <div class="popular-badge" id="popBadge">⭐ الأوفر والأقوى</div>
                <div class="plan-name" id="p2name">خطة التوفير السنوي</div>
                <div class="plan-onetime" id="p2onetime">دفعة أولى 10,000 جنيه عند البدء</div>
                <div class="plan-price-row">
                    <span class="plan-price" id="p2price">2,000</span>
                    <span class="plan-currency" id="p2curr">جنيه</span>
                    <span class="plan-period" id="p2period">/ 3 أشهر</span>
                </div>
                <div class="plan-daily" id="p2daily">وفر 6,000 جنيه سنوياً! 🔥</div>
                <hr class="plan-divider">
                <ul class="plan-features">
                    <li id="pf2_1">كل مميزات الخطة العادية</li>
                    <li id="pf2_2">أولوية الدعم الفني</li>
                    <li id="pf2_3">توفير 6,000 جنيه سنوياً</li>
                    <li id="pf2_4">إعداد مجاني كامل</li>
                    <li id="pf2_5">تدريب مجاني للموظفين</li>
                    <li id="pf2_6">تخصيص شعار وإعدادات المتجر</li>
                    <li id="pf2_7">تقارير متقدمة ومخصصة</li>
                </ul>
                <a href="https://wa.me/201111847065?text=أهلاً، عايز أشترك في خطة التوفير 10000 دفعة أولى + 2000 كل 3 أشهر"
                    target="_blank">
                    <button class="plan-btn plan-btn-solid" id="p2btn">ابدأ بخطة التوفير ⚡</button>
                </a>
            </div>
        </div>
        <p style="text-align:center;color:var(--text-muted);margin-top:30px;font-size:0.9rem;" id="pricingNote">💬 مش
            متأكد؟ تواصل معنا وهنساعدك تختار الأنسب لمحلك</p>
    </section>

    <!-- ==================== CONTACT ==================== -->
    <section class="section-pad contact-section" id="contact">
        <div class="contact-card reveal">
            <div class="section-tag" id="contactTag">تواصل معنا</div>
            <h2 class="contact-title" id="contactTitle">خد الخطوة الأولى النهارده</h2>
            <p class="contact-sub" id="contactSub">فريقنا جاهز يساعدك تبدأ وتشغل محلك بأحدث نظام POS في مصر</p>
            <div class="contact-info">
                <div class="contact-info-item"><span class="icon">📞</span><span>+2 01111847065</span></div>
                <div class="contact-info-item"><span class="icon">✉️</span><span>sales@dokkanhub.com</span></div>
            </div>
            <a href="https://wa.me/201111847065?text=أهلاً دكان هب، عايز أعرف أكتر عن النظام" target="_blank"
                class="whatsapp-btn">
                <span>💬</span><span id="waBtnText">ابعتلنا على واتسآب دلوقتي</span>
            </a>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer>
        <div class="footer-logo"><img
                src="{{ asset('images/logo.jpg') }}?v={{ filemtime(public_path('images/logo.jpg')) }}" alt="DokkanHub"
                onerror="this.src='https://placehold.co/120x30/e11a22/white?text=dokkanhub'"></div>
        <p class="footer-copy" id="footerCopy">© 2026 دكان هب — جميع الحقوق محفوظة | <a
                href="mailto:sales@dokkanhub.com">sales@dokkanhub.com</a></p>
    </footer>

    <script>
        /* ===================== PARTICLES ===================== */
        (function () {
            const canvas = document.getElementById('particles-canvas');
            const ctx = canvas.getContext('2d');
            let W, H, particles = [];
            function resize() { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
            window.addEventListener('resize', resize);
            resize();
            for (let i = 0; i < 80; i++) {
                particles.push({ x: Math.random() * W, y: Math.random() * H, r: Math.random() * 1.8 + 0.4, dx: (Math.random() - 0.5) * 0.4, dy: (Math.random() - 0.5) * 0.4, alpha: Math.random() * 0.5 + 0.1, color: Math.random() > 0.7 ? '#e11a22' : '#ffffff' });
            }
            function draw() {
                ctx.clearRect(0, 0, W, H);
                particles.forEach(p => {
                    ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = p.color; ctx.globalAlpha = p.alpha; ctx.fill();
                    p.x += p.dx; p.y += p.dy;
                    if (p.x < 0) p.x = W; if (p.x > W) p.x = 0; if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;
                });
                ctx.globalAlpha = 1;
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dist = Math.hypot(particles[i].x - particles[j].x, particles[i].y - particles[j].y);
                        if (dist < 100) { ctx.beginPath(); ctx.moveTo(particles[i].x, particles[i].y); ctx.lineTo(particles[j].x, particles[j].y); ctx.strokeStyle = `rgba(225,26,34,${0.08 * (1 - dist / 100)})`; ctx.lineWidth = 0.5; ctx.stroke(); }
                    }
                }
                requestAnimationFrame(draw);
            }
            draw();
        })();

        /* ===================== NAVBAR SCROLL ===================== */
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 60);
        });

        /* ===================== SCROLL REVEAL ===================== */
        const observer = new IntersectionObserver(entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.12 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        /* ===================== COUNTER ANIMATION ===================== */
        function animateCounter(el) {
            const target = parseFloat(el.getAttribute('data-target'));
            const suffix = el.getAttribute('data-suffix') || '';
            const prefix = el.getAttribute('data-prefix') || '';
            const decimals = parseInt(el.getAttribute('data-decimal') || '0');
            const start = performance.now();
            function step(now) {
                const p = Math.min((now - start) / 2000, 1);
                const ease = 1 - Math.pow(1 - p, 3);
                const val = (target * ease).toFixed(decimals);
                el.textContent = prefix + (decimals > 0 ? val : parseInt(val).toLocaleString()) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }
        const counterObs = new IntersectionObserver(entries => entries.forEach(e => { if (e.isIntersecting && !e.target.dataset.counted) { e.target.dataset.counted = '1'; animateCounter(e.target); } }), { threshold: 0.5 });
        document.querySelectorAll('.stat-num').forEach(el => counterObs.observe(el));

        /* ===================== TYPED TEXT ===================== */
        const typedEl = document.getElementById('heroTyped');
        let isAr = true;
        const arWords = ['محلك', 'جزارتك', 'سوبرماركتك', 'متجرك', 'عملك'];
        const enWords = ['Store', 'Butcher Shop', 'Supermarket', 'Business', 'Shop'];
        let wordIdx = 0;
        function typeWord() {
            const words = isAr ? arWords : enWords;
            const word = words[wordIdx % words.length];
            typedEl.textContent = '';
            let i = 0;
            function add() { if (i < word.length) { typedEl.textContent += word[i++]; setTimeout(add, 110); } else { setTimeout(del, 1800); } }
            function del() { if (typedEl.textContent.length > 0) { typedEl.textContent = typedEl.textContent.slice(0, -1); setTimeout(del, 60); } else { wordIdx++; setTimeout(typeWord, 400); } }
            add();
        }
        typeWord();

        /* ===================== STORE MODAL ===================== */
        function openStoreModal() { document.getElementById('storeModal').classList.add('active'); }
        function closeStoreModal() { document.getElementById('storeModal').classList.remove('active'); }
        document.getElementById('storeModal').addEventListener('click', function (e) { if (e.target === this) closeStoreModal(); });
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => openStoreModal());
        @endif

/* ===================== LANG TOGGLE ===================== */
const T = {
            ar: {
                heroBadge: '🔴 منظومة POS الأذكى في مصر', heroSub: 'نقطة بيع ذكية، تقارير يومية، فواتير حرارية، وإدارة مخزون — كل ده في إيدك بثمن أقل من فنجان قهوة في اليوم ☕',
                heroCta: '🔑 ادخل على متجرك', heroPlans: '📋 شوف الخطط', navCta: 'ادخل على متجرك 🔑',
                platformBtnText: 'منصة الإدارة', scrollHint: 'مرر للأسفل',
                stat1: 'محل يستخدم دكان هب', stat2: 'فاتورة يومياً', stat3: 'وقت تشغيل مستمر', stat4: 'تكلفة الاشتراك اليومية',
                featuresTag: 'المميزات', featuresTitle: 'كل اللي محلك محتاجه في مكان واحد', featuresDesc: 'دكان هب بيديك أدوات احترافية بدون تعقيد — بسيطة، سريعة، وبتشتغل معاك من أول يوم',
                f1t: 'نقطة بيع ذكية (POS)', f1d: 'واجهة كاشير سريعة وسهلة، دعم الباركود والمسح، وبيع بالكيلو أو القطعة بضغطة واحدة',
                f2t: 'دعم الموازين الحرارية', f2d: 'تقرأ باركود الموازين تلقائياً وتسجل الوزن والسعر فوراً — مثالي للجزارة والسوبرماركت',
                f3t: 'فواتير حرارية تلقائية', f3d: 'فاتورة احترافية بشعار محلك بتطلع تلقائياً بعد كل بيعة — مع اسم المتجر والتاريخ والتفاصيل',
                f4t: 'إدارة المخزون', f4d: 'تتابع مخزونك في الوقت الفعلي، تنبيهات الكميات المنخفضة، واستيراد المنتجات بسهولة',
                f5t: 'تقارير يومية على بريدك', f5d: 'في نهاية كل يوم، تقرير شامل بالمبيعات وأكثر المنتجات مبيعاً والمخزون المنخفض — على إيميلك',
                f6t: 'كاشير متعدد وصلاحيات', f6d: 'أضف عدة موظفين، حدد صلاحيات لكل واحد، وتابع أداء كل كاشير بشكل منفصل',
                f7t: 'إدارة الديون والعملاء', f7d: 'سجل عملاءك، تابع ديونهم، وادي لهم إيصال عند السداد — كل ده في مكان واحد',
                f8t: 'شعار محلك في كل حتة', f8d: 'ارفع شعار محلك وهيظهر في الكاشير، الفواتير، وحتى أيقونة الموقع — هوية كاملة لمحلك',
                howTag: 'كيف يشتغل', howTitle: 'ابدأ في 4 خطوات بسيطة', howDesc: 'ما فيش تعقيد ولا إعداد طويل — بتشتغل من أول يوم',
                s1t: 'تواصل معنا على واتسآب', s1d: 'بعتلنا رسالة واحدة وفريقنا هيرد عليك خلال دقائق ويشرح لك كل حاجة',
                s2t: 'هنجهز متجرك الخاص', s2d: 'بنعمل لك حساب خاص بمحلك مع رابطك الفرعي الخاص — في دقائق معدودة',
                s3t: 'أضف منتجاتك', s3d: 'أضف منتجاتك يدوياً أو استوردها من ملف Excel — الكاشير جاهز فوراً',
                s4t: 'ابدأ البيع وتابع تقاريرك', s4d: 'الكاشير شغال، التقارير بتيجي على إيميلك كل يوم، وإنت مرتاح البال',
                pricingTag: 'الأسعار', pricingTitle: 'خطط واضحة، بدون مفاجآت', pricingDesc: 'اختار الخطة اللي تناسبك — كل الخطط فيها نفس المميزات الاحترافية الكاملة',
                p1name: 'الخطة العادية', p1price: '2,000', p1curr: 'جنيه', p1period: '/ شهر', p1daily: '≈ 66.6 جنيه في اليوم 💡',
                pf1_1: 'نقطة بيع كاملة بكاشير متعدد', pf1_2: 'تقارير يومية على البريد الإلكتروني', pf1_3: 'فواتير حرارية بشعار محلك', pf1_4: 'دعم الموازين الحرارية', pf1_5: 'إدارة المخزون والموظفين', pf1_6: 'إدارة العملاء والديون', pf1_7: 'دعم فني متواصل',
                p1btn: 'ابدأ بالخطة العادية', popBadge: '⭐ الأوفر والأقوى',
                p2name: 'خطة التوفير السنوي', p2onetime: 'دفعة أولى 10,000 جنيه عند البدء', p2price: '2,000', p2curr: 'جنيه', p2period: '/ 3 أشهر', p2daily: 'وفر 6,000 جنيه سنوياً! 🔥',
                pf2_1: 'كل مميزات الخطة العادية', pf2_2: 'أولوية الدعم الفني', pf2_3: 'توفير 6,000 جنيه سنوياً', pf2_4: 'إعداد مجاني كامل', pf2_5: 'تدريب مجاني للموظفين', pf2_6: 'تخصيص شعار وإعدادات المتجر', pf2_7: 'تقارير متقدمة ومخصصة',
                p2btn: 'ابدأ بخطة التوفير ⚡', pricingNote: '💬 مش متأكد؟ تواصل معنا وهنساعدك تختار الأنسب لمحلك',
                contactTag: 'تواصل معنا', contactTitle: 'خد الخطوة الأولى النهارده', contactSub: 'فريقنا جاهز يساعدك تبدأ وتشغل محلك بأحدث نظام POS في مصر',
                waBtnText: 'ابعتلنا على واتسآب دلوقتي',
                modalTitle: 'ادخل على متجرك', modalSub: 'أدخل اسم متجرك للوصول لنظام الكاشير الخاص بك', modalSubmit: '🚀 انتقل للكاشير',
                footerCopy: '© 2026 دكان هب — جميع الحقوق محفوظة | '
            },
            en: {
                heroBadge: "🔴 Egypt's Smartest POS System", heroSub: "Smart POS, daily email reports, thermal invoices & inventory management — all in your hands for less than the cost of a coffee per day ☕",
                heroCta: '🔑 Access Your Store', heroPlans: '📋 View Plans', navCta: 'Access Your Store 🔑',
                platformBtnText: 'Admin Platform', scrollHint: 'Scroll down',
                stat1: 'Stores using DokkanHub', stat2: 'Daily invoices', stat3: 'Uptime', stat4: 'Starting cost per day',
                featuresTag: 'Features', featuresTitle: 'Everything Your Business Needs, In One Place', featuresDesc: 'DokkanHub gives you professional tools without complexity — simple, fast, and ready from day one',
                f1t: 'Smart POS Terminal', f1d: 'Fast & intuitive cashier interface, barcode scanning, sell by weight or unit with a single tap',
                f2t: 'Thermal Scale Support', f2d: 'Automatically reads scale barcodes, records weight & price instantly — perfect for butcher shops & supermarkets',
                f3t: 'Automatic Thermal Invoices', f3d: 'Professional invoice with your store logo printed automatically after every sale',
                f4t: 'Inventory Management', f4d: 'Track your stock in real-time, low-stock alerts, and easy product import from Excel',
                f5t: 'Daily Reports to Your Email', f5d: 'Every evening, a full sales report with top products and low-stock warnings delivered to your inbox',
                f6t: 'Multi-Cashier & Permissions', f6d: 'Add multiple employees, set specific permissions for each, and track every cashier\'s performance',
                f7t: 'Customer Debt Management', f7d: 'Register customers, track debts, and issue receipts on payment — all in one place',
                f8t: 'Your Logo Everywhere', f8d: 'Upload your store logo and it appears on the POS, invoices, and even the browser favicon',
                howTag: 'How It Works', howTitle: 'Start in 4 Simple Steps', howDesc: "No complex setup — you're operational from day one",
                s1t: 'Contact Us on WhatsApp', s1d: 'Send us one message and our team will respond within minutes',
                s2t: 'We Set Up Your Store', s2d: 'We create your own account with a unique subdomain — done in minutes',
                s3t: 'Add Your Products', s3d: 'Add products manually or import from Excel — your POS is ready immediately',
                s4t: 'Start Selling & Track Reports', s4d: 'The cashier is running, reports come to your email daily, and you have total peace of mind',
                pricingTag: 'Pricing', pricingTitle: 'Clear Plans, No Surprises', pricingDesc: 'Choose the plan that suits you — all plans include the full professional feature set',
                p1name: 'Standard Plan', p1price: '2,000', p1curr: 'EGP', p1period: '/ month', p1daily: '≈ 66.6 EGP per day 💡',
                pf1_1: 'Full POS with multi-cashier', pf1_2: 'Daily email sales reports', pf1_3: 'Thermal invoices with your logo', pf1_4: 'Thermal scale support', pf1_5: 'Inventory & staff management', pf1_6: 'Customer & debt management', pf1_7: 'Continuous technical support',
                p1btn: 'Start Standard Plan', popBadge: '⭐ Best Value',
                p2name: 'Annual Savings Plan', p2onetime: 'One-time setup fee: 10,000 EGP', p2price: '2,000', p2curr: 'EGP', p2period: '/ 3 months', p2daily: 'Save 6,000 EGP per year! 🔥',
                pf2_1: 'All Standard Plan features', pf2_2: 'Priority technical support', pf2_3: 'Save 6,000 EGP per year', pf2_4: 'Free full setup', pf2_5: 'Free staff training', pf2_6: 'Custom logo & store settings', pf2_7: 'Advanced custom reports',
                p2btn: 'Start Savings Plan ⚡', pricingNote: "💬 Not sure? Contact us and we'll help you choose what's best for your business",
                contactTag: 'Contact Us', contactTitle: 'Take the First Step Today', contactSub: "Our team is ready to help you launch your business with Egypt's most advanced POS system",
                waBtnText: 'Message Us on WhatsApp Now',
                modalTitle: 'Access Your Store', modalSub: 'Enter your store name to access your dedicated POS system', modalSubmit: '🚀 Go to POS',
                footerCopy: '© 2026 DokkanHub — All Rights Reserved | '
            }
        };

        let currentLang = 'ar';
        function toggleLang() { currentLang = currentLang === 'ar' ? 'en' : 'ar'; isAr = currentLang === 'ar'; applyLang(currentLang); }
        function applyLang(lang) {
            const t = T[lang];
            const isArabic = lang === 'ar';
            document.getElementById('html-root').setAttribute('lang', lang);
            document.getElementById('html-root').setAttribute('dir', isArabic ? 'rtl' : 'ltr');
            document.body.classList.toggle('en', !isArabic);
            document.getElementById('langToggle').textContent = isArabic ? 'EN' : 'ع';
            document.title = isArabic ? 'دكان هب | نقطة البيع الذكية لكل محل' : 'DokkanHub | Smart POS for Every Business';
            const ids = Object.keys(t);
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el && t[id] !== undefined && id !== 'footerCopy') el.textContent = t[id];
            });
            const fc = document.getElementById('footerCopy');
            if (fc) fc.innerHTML = t.footerCopy + '<a href="mailto:sales@dokkanhub.com">sales@dokkanhub.com</a>';
        }
    </script>
</body>

</html>