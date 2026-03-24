<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AssessMe — AI-assisted technical screening. Create assessments, administer candidates, and grade responses with intelligent automation.">
    <title>AssessMe — AI-Assisted Technical Screening</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:       #0a0f1e;
            --navy-mid:   #0f1729;
            --navy-light: #1a2540;
            --slate:      #2a3655;
            --accent:     #4f8ef7;
            --accent-dim: #2a5ab8;
            --accent-glow:rgba(79,142,247,0.15);
            --gold:       #f0c060;
            --text:       #e8edf8;
            --text-muted: #8899bb;
            --text-dim:   #4a5a7a;
            --border:     rgba(79,142,247,0.12);
            --radius:     12px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--navy);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ─── GRID BACKGROUND ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(79,142,247,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,142,247,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
        }

        /* ─── GLOW ORBS ─── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.35;
        }
        .orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, #1a4a9a 0%, transparent 70%);
            top: -200px; left: -100px;
            animation: drift1 20s ease-in-out infinite;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #0a2a6a 0%, transparent 70%);
            bottom: 10%; right: -100px;
            animation: drift2 25s ease-in-out infinite;
        }

        @keyframes drift1 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(60px, 80px); }
        }
        @keyframes drift2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-40px, -60px); }
        }

        /* ─── NAV ─── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(10,15,30,0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }

        .nav-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--text);
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .nav-logo span {
            color: var(--accent);
        }

        .nav-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-login:hover {
            background: var(--accent);
            color: var(--navy);
        }

        /* ─── HERO ─── */
        .hero {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 24px 80px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: var(--accent-glow);
            border: 1px solid rgba(79,142,247,0.3);
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--accent);
            margin-bottom: 32px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            animation: fadeUp 0.6s ease both;
        }

        .hero-badge::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 2s ease infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.8rem, 7vw, 5.5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.03em;
            max-width: 900px;
            margin-bottom: 24px;
            animation: fadeUp 0.6s 0.1s ease both;
        }

        h1 .highlight {
            color: var(--accent);
            position: relative;
        }

        h1 .highlight::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), transparent);
            border-radius: 2px;
        }

        .hero-sub {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: var(--text-muted);
            max-width: 560px;
            margin-bottom: 48px;
            font-weight: 300;
            line-height: 1.7;
            animation: fadeUp 0.6s 0.2s ease both;
        }

        .hero-cta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeUp 0.6s 0.3s ease both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: var(--accent);
            color: var(--navy);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 0 40px rgba(79,142,247,0.3);
        }

        .btn-primary:hover {
            background: #6ba4f9;
            transform: translateY(-2px);
            box-shadow: 0 8px 50px rgba(79,142,247,0.4);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--slate);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 400;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--text);
        }

        /* ─── STATS BAR ─── */
        .stats-bar {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            gap: 0;
            flex-wrap: wrap;
            margin: 0 24px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: rgba(15,23,41,0.6);
            backdrop-filter: blur(10px);
            overflow: hidden;
            animation: fadeUp 0.6s 0.4s ease both;
        }

        .stat {
            flex: 1;
            min-width: 160px;
            padding: 28px 32px;
            text-align: center;
            border-right: 1px solid var(--border);
        }

        .stat:last-child { border-right: none; }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* ─── SECTIONS ─── */
        section {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 100px 24px;
        }

        .section-tag {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--accent);
            margin-bottom: 16px;
        }

        h2 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .section-sub {
            font-size: 1.05rem;
            color: var(--text-muted);
            max-width: 520px;
            line-height: 1.7;
            margin-bottom: 64px;
            font-weight: 300;
        }

        /* ─── HOW IT WORKS ─── */
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .step {
            background: var(--navy-mid);
            padding: 48px 36px;
            position: relative;
            transition: background 0.2s;
        }

        .step:hover { background: var(--navy-light); }

        .step-num {
            font-family: 'Syne', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            color: var(--navy-light);
            line-height: 1;
            margin-bottom: 24px;
            transition: color 0.2s;
        }

        .step:hover .step-num { color: var(--accent-dim); }

        .step h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }

        .step p {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ─── FEATURES ─── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
        }

        .feature-card {
            background: var(--navy-mid);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--accent), transparent);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .feature-card:hover {
            border-color: rgba(79,142,247,0.3);
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }

        .feature-card:hover::before { opacity: 1; }

        .feature-icon {
            width: 48px; height: 48px;
            background: var(--accent-glow);
            border: 1px solid rgba(79,142,247,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text);
        }

        .feature-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* ─── WHO IT'S FOR ─── */
        .audience {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 640px) { .audience { grid-template-columns: 1fr; } }

        .audience-card {
            border-radius: var(--radius);
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        .audience-card.examiner {
            background: linear-gradient(135deg, #0f1e3d 0%, #1a2f5a 100%);
            border: 1px solid rgba(79,142,247,0.2);
        }

        .audience-card.candidate {
            background: linear-gradient(135deg, #0f1e1a 0%, #1a3330 100%);
            border: 1px solid rgba(79,200,160,0.2);
        }

        .audience-card .role-tag {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 20px;
        }

        .examiner .role-tag {
            background: rgba(79,142,247,0.15);
            color: var(--accent);
            border: 1px solid rgba(79,142,247,0.3);
        }

        .candidate .role-tag {
            background: rgba(79,200,160,0.15);
            color: #4fc8a0;
            border: 1px solid rgba(79,200,160,0.3);
        }

        .audience-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .audience-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .audience-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .audience-list li::before {
            content: '→';
            color: var(--accent);
            font-weight: 600;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .candidate .audience-list li::before { color: #4fc8a0; }

        /* ─── AI CALLOUT ─── */
        .ai-callout {
            position: relative;
            z-index: 1;
            margin: 0 24px;
            padding: 64px 48px;
            background: linear-gradient(135deg, #0d1a36 0%, #112244 50%, #0d1a36 100%);
            border: 1px solid rgba(79,142,247,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 48px;
            flex-wrap: wrap;
            overflow: hidden;
        }

        .ai-callout::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(79,142,247,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .ai-callout-text { flex: 1; min-width: 280px; }

        .ai-callout-text h2 {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            margin-bottom: 12px;
        }

        .ai-callout-text p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            font-weight: 300;
        }

        .ai-callout .btn-primary { flex-shrink: 0; }

        /* ─── FOOTER ─── */
        footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--border);
            padding: 40px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text);
            text-decoration: none;
        }

        .footer-logo span { color: var(--accent); }

        .footer-copy {
            font-size: 0.82rem;
            color: var(--text-dim);
        }

        .footer-copy a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-copy a:hover { color: var(--accent); }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            nav { padding: 16px 20px; }
            .stats-bar { margin: 0 16px; }
            .stat { min-width: 140px; padding: 20px; }
            .ai-callout { padding: 40px 28px; margin: 0 16px; }
            footer { padding: 32px 20px; flex-direction: column; text-align: center; }
        }

        @media (max-width: 480px) {
            .hero-cta { flex-direction: column; align-items: center; }
            .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <!-- NAV -->
    <nav>
        <a href="/" class="nav-logo">Assess<span>Me</span></a>
        <a href="/admin/login" class="nav-login">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Sign In
        </a>
    </nav>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-badge">AI-Powered Technical Screening</div>

        <h1>
            Assessments that<br>
            <span class="highlight">actually measure</span><br>
            understanding
        </h1>

        <p class="hero-sub">
            AssessMe replaces unreliable manual grading with AI-assisted scoring — while treating AI usage as a metric, not a disqualifier.
        </p>

        <div class="hero-cta">
            <a href="/admin/login" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Sign In
            </a>
            <a href="#how-it-works" class="btn-secondary">
                See how it works
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
            </a>
        </div>
    </div>

    <!-- STATS BAR -->
    <div class="stats-bar reveal">
        <div class="stat">
            <div class="stat-value">GPT-4o</div>
            <div class="stat-label">AI Marking Engine</div>
        </div>
        <div class="stat">
            <div class="stat-value">3</div>
            <div class="stat-label">Question Types</div>
        </div>
        <div class="stat">
            <div class="stat-value">100%</div>
            <div class="stat-label">Self-Hosted</div>
        </div>
        <div class="stat">
            <div class="stat-value">0</div>
            <div class="stat-label">Proxy Config Changes</div>
        </div>
    </div>

    <!-- HOW IT WORKS -->
    <section id="how-it-works">
        <span class="section-tag">Process</span>
        <h2>From question to result<br>in three steps</h2>
        <p class="section-sub">A streamlined workflow designed for engineering teams who value signal over noise.</p>

        <div class="steps reveal">
            <div class="step">
                <div class="step-num">01</div>
                <h3>Build your assessment</h3>
                <p>Create questions via the UI or import a JSON file. Define your answer key, allocate marks, and set time limits. Multiple choice, free text, and code questions supported.</p>
            </div>
            <div class="step">
                <div class="step-num">02</div>
                <h3>Send a unique link</h3>
                <p>Register a candidate and generate a UUID access link. No account creation required. Candidates access the assessment directly — clean, distraction-free, auto-saving.</p>
            </div>
            <div class="step">
                <div class="step-num">03</div>
                <h3>Review AI-graded results</h3>
                <p>On submission, GPT-4o marks free-text and code answers against your key. You get a numeric score, qualitative feedback, and an AI usage percentage — then override anything you disagree with.</p>
            </div>
        </div>
    </section>

    <!-- KEY FEATURES -->
    <section id="features">
        <span class="section-tag">Features</span>
        <h2>Built for the way<br>engineering teams work</h2>
        <p class="section-sub">Every decision made to reduce noise and surface genuine signal.</p>

        <div class="features-grid reveal">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>AI Marking via GPT-4o</h3>
                <p>Free-text and code answers are marked against your answer key. Each response gets a score, confidence level, and qualitative feedback — queued as background jobs so nothing blocks.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>AI Usage as a Metric</h3>
                <p>Plagiarism detection produces a percentage per answer, rolled up across the assessment. It informs your judgement — it never auto-disqualifies. A candidate who used AI responsibly shouldn't be penalised.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💻</div>
                <h3>Code Questions</h3>
                <p>Syntax-highlighted code editor with language selection. Examiner sets a preferred language and can provide starter snippets in multiple languages. When a candidate is skilled, syntax is semantics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔗</div>
                <h3>UUID Candidate Links</h3>
                <p>No account registration for candidates. Generate a unique link, share it manually. The candidate confirms their name and begins. Auto-save ensures no progress is ever lost.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏱️</div>
                <h3>Flexible Time Limits</h3>
                <p>Set a closing deadline, a timer from first login, or no limit at all. Configure expiry behaviour per assessment — warning, grace period, or auto-submit.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🐳</div>
                <h3>Self-Hosted in Docker</h3>
                <p>Deploys behind your existing Nginx reverse proxy with zero configuration changes. All settings live in docker-compose.yml. PostgreSQL with pgvector-ready for Phase 2 AI features.</p>
            </div>
        </div>
    </section>

    <!-- WHO IT'S FOR -->
    <section id="audience">
        <span class="section-tag">Who it's for</span>
        <h2>Two roles.<br>One platform.</h2>
        <p class="section-sub">Designed for the people on both sides of the screening process.</p>

        <div class="audience reveal">
            <div class="audience-card examiner">
                <span class="role-tag">Examiner</span>
                <h3>Engineering leads &amp; talent teams</h3>
                <ul class="audience-list">
                    <li>Create and manage assessments via UI or JSON import</li>
                    <li>Define marking schemes with extra credit criteria</li>
                    <li>Monitor candidate progress snapshots in real time</li>
                    <li>Review AI scores and override with manual judgement</li>
                    <li>Export results to PDF for stakeholder review</li>
                    <li>Get notified the moment a candidate submits</li>
                </ul>
            </div>
            <div class="audience-card candidate">
                <span class="role-tag">Candidate</span>
                <h3>Engineers being assessed</h3>
                <ul class="audience-list">
                    <li>Access via a unique link — no account required</li>
                    <li>Clean, distraction-free assessment interface</li>
                    <li>Navigate freely between questions, skip and return</li>
                    <li>Answers auto-saved — no lost progress on disconnect</li>
                    <li>Write code in your preferred language</li>
                    <li>Clear submission confirmation and time awareness</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- AI CALLOUT -->
    <div class="ai-callout reveal">
        <div class="ai-callout-text">
            <h2>Ready to run your first assessment?</h2>
            <p>Sign in to the examiner dashboard to create an assessment, register a candidate, and see AssessMe in action.</p>
        </div>
        <a href="/admin/login" class="btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Go to Dashboard
        </a>
    </div>

    <!-- FOOTER -->
    <footer>
        <a href="/" class="footer-logo">Assess<span>Me</span></a>
        <p class="footer-copy">
            &copy; <span id="year"></span> AssessMe &nbsp;|&nbsp;
            Built by <a href="https://edward.monatemedia.com" target="_blank" rel="noopener">Edward Lebogang Baitsewe</a>
            &nbsp;|&nbsp; Powered by <a href="https://www.monatemedia.com" target="_blank" rel="noopener">Monate Media</a>
        </p>
    </footer>

    <script>
        // Dynamic year
        document.getElementById('year').textContent = new Date().getFullYear();

        // Scroll reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(el => {
                if (el.isIntersecting) {
                    el.target.classList.add('visible');
                    observer.unobserve(el.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    </script>

</body>
</html>
