<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AssessMe — AI-Powered Technical Screening</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #4f8ef7;
            --blue-hover: #6ba4f9;
            --bg: #0a0f1e;
            --card: #0f1729;
            --border: rgba(79, 142, 247, 0.12);
            --border-hover: rgba(79, 142, 247, 0.3);
            --text-muted: #9ca3af;
            --text-dim: #4a5a7a;
        }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            background: var(--bg);
            color: #f3f4f6;
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* ── Background grid ── */
        .bg-grid {
            position: fixed; inset: 0; pointer-events: none; z-index: 0;
            background-image:
                linear-gradient(rgba(79,142,247,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,142,247,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* ── Glowing orbs ── */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(120px); pointer-events: none; z-index: 0;
        }
        .orb-1 {
            top: -200px; left: -100px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, #1a4a9a 0%, transparent 70%);
            opacity: 0.35;
            animation: drift1 12s ease-in-out infinite alternate;
        }
        .orb-2 {
            bottom: 10%; right: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, #0a2a6a 0%, transparent 70%);
            opacity: 0.35;
            animation: drift2 15s ease-in-out infinite alternate;
        }
        @keyframes drift1 { from { transform: translate(0,0); } to { transform: translate(40px, 60px); } }
        @keyframes drift2 { from { transform: translate(0,0); } to { transform: translate(-40px, -30px); } }

        /* ── Nav ── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 40px;
            background: rgba(10, 15, 30, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }
        .logo { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.03em; }
        .logo span { color: var(--blue); }

        .btn-outline {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 24px;
            border: 1px solid var(--blue);
            color: var(--blue);
            border-radius: 8px;
            font-size: 0.875rem; font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .btn-outline:hover { background: var(--blue); color: var(--bg); }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 36px;
            background: var(--blue); color: var(--bg);
            border-radius: 12px; font-weight: 600;
            box-shadow: 0 0 40px rgba(79,142,247,0.3);
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            background: var(--blue-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 50px rgba(79,142,247,0.4);
        }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 32px;
            color: var(--text-muted);
            border: 1px solid #2a3655;
            border-radius: 12px;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-ghost:hover { border-color: var(--blue); color: #f3f4f6; }

        /* ── Hero ── */
        .hero {
            position: relative; z-index: 10;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center;
            padding: 128px 24px 80px;
        }

        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 16px;
            background: rgba(79,142,247,0.15);
            border: 1px solid rgba(79,142,247,0.3);
            border-radius: 9999px;
            font-size: 0.7rem; font-weight: 600;
            color: var(--blue); text-transform: uppercase; letter-spacing: 0.1em;
            margin-bottom: 32px;
            animation: fadeUp 0.6s ease both;
        }
        .badge-dot {
            width: 6px; height: 6px;
            background: var(--blue); border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.3;} }

        h1 {
            font-size: clamp(2.5rem, 7vw, 4.5rem);
            font-weight: 800; line-height: 1.05;
            letter-spacing: -0.03em;
            max-width: 56rem; margin-bottom: 24px;
            animation: fadeUp 0.6s 0.1s ease both;
        }
        .accent {
            color: var(--blue); position: relative; display: inline-block;
        }
        .accent::after {
            content: '';
            position: absolute; bottom: 4px; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(to right, var(--blue), transparent);
            border-radius: 2px;
        }

        .hero-sub {
            font-size: 1.125rem; color: var(--text-muted);
            max-width: 36rem; margin-bottom: 48px;
            font-weight: 300; line-height: 1.7;
            animation: fadeUp 0.6s 0.2s ease both;
        }

        .hero-btns {
            display: flex; flex-wrap: wrap; gap: 16px;
            justify-content: center;
            animation: fadeUp 0.6s 0.3s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Stats bar ── */
        .stats-bar {
            position: relative; z-index: 10;
            display: flex; flex-wrap: wrap; justify-content: center;
            margin: 0 24px 96px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(15, 23, 41, 0.6);
            backdrop-filter: blur(12px);
            overflow: hidden;
        }
        .stat {
            flex: 1; min-width: 160px;
            padding: 28px 32px; text-align: center;
            border-right: 1px solid var(--border);
        }
        .stat:last-child { border-right: none; }
        .stat-value { font-size: 1.875rem; font-weight: 700; color: var(--blue); line-height: 1; margin-bottom: 6px; }
        .stat-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; }

        /* ── Sections ── */
        section { position: relative; z-index: 10; max-width: 72rem; margin: 0 auto; padding: 96px 24px; }
        .section-tag { display: inline-block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: var(--blue); margin-bottom: 16px; }
        .section-title { font-size: clamp(2rem, 5vw, 3rem); font-weight: 700; letter-spacing: -0.02em; line-height: 1.15; margin-bottom: 16px; }
        .section-sub { font-size: 1.1rem; color: var(--text-muted); max-width: 32rem; margin-bottom: 64px; font-weight: 300; line-height: 1.7; }

        /* ── Steps grid ── */
        .steps-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2px;
            background: rgba(79,142,247,0.12);
            border: 1px solid var(--border);
            border-radius: 12px; overflow: hidden;
        }
        .step {
            background: var(--card); padding: 48px;
            transition: background 0.2s;
        }
        .step:hover { background: #1a2540; }
        .step-num { font-size: 3.75rem; font-weight: 800; color: #1a2540; line-height: 1; margin-bottom: 24px; }
        .step:hover .step-num { color: #2a5ab8; }
        .step h3 { font-size: 1.125rem; font-weight: 700; margin-bottom: 12px; }
        .step p { font-size: 0.875rem; color: var(--text-muted); line-height: 1.6; }

        /* ── Feature cards ── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .feature-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 36px;
            position: relative; overflow: hidden;
            transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .feature-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(to right, var(--blue), transparent);
            opacity: 0; transition: opacity 0.2s;
        }
        .feature-card:hover { border-color: var(--border-hover); transform: translateY(-4px); box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
        .feature-card:hover::before { opacity: 1; }
        .feature-icon {
            width: 48px; height: 48px;
            background: rgba(79,142,247,0.15);
            border: 1px solid rgba(79,142,247,0.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 20px;
        }
        .feature-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 10px; }
        .feature-card p { font-size: 0.875rem; color: var(--text-muted); line-height: 1.6; }

        /* ── Audience cards ── */
        .audience-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .audience-card { border-radius: 12px; padding: 48px; overflow: hidden; }
        .audience-card.examiner {
            background: linear-gradient(135deg, #0f1e3d, #1a2f5a);
            border: 1px solid rgba(79,142,247,0.2);
        }
        .audience-card.candidate {
            background: linear-gradient(135deg, #0f1e1a, #1a3330);
            border: 1px solid rgba(79,200,160,0.2);
        }
        .role-tag {
            display: inline-block;
            padding: 4px 12px; border-radius: 9999px;
            font-size: 0.6rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.15em;
            margin-bottom: 20px;
        }
        .role-tag.examiner { background: rgba(79,142,247,0.15); color: #4f8ef7; border: 1px solid rgba(79,142,247,0.3); }
        .role-tag.candidate { background: rgba(79,200,160,0.15); color: #4fc8a0; border: 1px solid rgba(79,200,160,0.3); }
        .audience-card h3 { font-size: 1.375rem; font-weight: 700; margin-bottom: 16px; }
        .audience-list li {
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 0.875rem; color: var(--text-muted);
            line-height: 1.6; margin-bottom: 10px;
        }
        .arrow-examiner { color: #4f8ef7; font-weight: 700; flex-shrink: 0; margin-top: 1px; }
        .arrow-candidate { color: #4fc8a0; font-weight: 700; flex-shrink: 0; margin-top: 1px; }

        /* ── CTA banner ── */
        .cta-banner {
            position: relative; z-index: 10;
            margin: 0 24px 96px;
            padding: 64px 48px;
            background: linear-gradient(135deg, #0d1a36, #112244, #0d1a36);
            border: 1px solid rgba(79,142,247,0.2);
            border-radius: 16px;
            display: flex; flex-wrap: wrap;
            align-items: center; justify-content: space-between;
            gap: 48px; overflow: hidden;
        }
        .cta-orb {
            position: absolute; top: -80px; right: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(79,142,247,0.12) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .cta-text { flex: 1; min-width: 280px; }
        .cta-text h2 { font-size: clamp(1.5rem, 4vw, 2.25rem); font-weight: 700; margin-bottom: 12px; }
        .cta-text p { color: var(--text-muted); font-size: 1rem; font-weight: 300; line-height: 1.7; }

        /* ── Footer ── */
        footer {
            position: relative; z-index: 10;
            border-top: 1px solid var(--border);
            padding: 40px;
            display: flex; flex-wrap: wrap;
            align-items: center; justify-content: space-between; gap: 16px;
        }
        footer p { font-size: 0.75rem; color: var(--text-dim); }
        footer a:hover { color: var(--blue); }
        footer .footer-link { color: var(--text-muted); transition: color 0.2s; }

        /* ── Reveal animation ── */
        .reveal { opacity: 0; transform: translateY(32px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ── SVG icon helper ── */
        .icon { display: inline-block; vertical-align: middle; }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            nav { padding: 16px 20px; }
            .stat { border-right: none; border-bottom: 1px solid var(--border); }
            .stat:last-child { border-bottom: none; }
            .cta-banner { padding: 40px 24px; }
            footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- Background effects -->
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <!-- Nav -->
    <nav>
        <a href="/" class="logo">Assess<span>Me</span></a>
        <a href="/admin/login" class="btn-outline">
            <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            Sign In
        </a>
    </nav>

    <!-- Hero -->
    <div class="hero">
        <div class="badge">
            <span class="badge-dot"></span>
            AI-Powered Technical Screening
        </div>

        <h1>
            Assessments that<br>
            <span class="accent">actually measure</span><br>
            understanding
        </h1>

        <p class="hero-sub">
            AssessMe replaces unreliable manual grading with AI-assisted scoring — while treating AI usage as a metric, not a disqualifier.
        </p>

        <div class="hero-btns">
            <a href="/admin/login" class="btn-primary">
                <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                Sign In
            </a>
            <a href="#how-it-works" class="btn-ghost">
                See how it works
                <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
        </div>
    </div>

    <!-- Stats bar -->
    <div class="stats-bar reveal">
        <div class="stat"><div class="stat-value">GPT-4o</div><div class="stat-label">AI Marking Engine</div></div>
        <div class="stat"><div class="stat-value">3</div><div class="stat-label">Question Types</div></div>
        <div class="stat"><div class="stat-value">100%</div><div class="stat-label">Self-Hosted</div></div>
        <div class="stat"><div class="stat-value">0</div><div class="stat-label">Proxy Config Changes</div></div>
    </div>

    <!-- How it works -->
    <section id="how-it-works">
        <span class="section-tag">Process</span>
        <h2 class="section-title">From question to result<br>in three steps</h2>
        <p class="section-sub">A streamlined workflow designed for engineering teams who value signal over noise.</p>

        <div class="steps-grid reveal">
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

    <!-- Features -->
    <section id="features">
        <span class="section-tag">Features</span>
        <h2 class="section-title">Built for the way<br>engineering teams work</h2>
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

    <!-- Audience -->
    <section id="audience">
        <span class="section-tag">Who it's for</span>
        <h2 class="section-title">Two roles.<br>One platform.</h2>
        <p class="section-sub">Designed for the people on both sides of the screening process.</p>

        <div class="audience-grid reveal">
            <div class="audience-card examiner">
                <span class="role-tag examiner">Examiner</span>
                <h3>Engineering leads &amp; talent teams</h3>
                <ul class="audience-list">
                    <li><span class="arrow-examiner">→</span> Create and manage assessments via UI or JSON import</li>
                    <li><span class="arrow-examiner">→</span> Define marking schemes with extra credit criteria</li>
                    <li><span class="arrow-examiner">→</span> Monitor candidate progress snapshots in real time</li>
                    <li><span class="arrow-examiner">→</span> Review AI scores and override with manual judgement</li>
                    <li><span class="arrow-examiner">→</span> Export results to PDF for stakeholder review</li>
                    <li><span class="arrow-examiner">→</span> Get notified the moment a candidate submits</li>
                </ul>
            </div>

            <div class="audience-card candidate">
                <span class="role-tag candidate">Candidate</span>
                <h3>Engineers being assessed</h3>
                <ul class="audience-list">
                    <li><span class="arrow-candidate">→</span> Access via a unique link — no account required</li>
                    <li><span class="arrow-candidate">→</span> Clean, distraction-free assessment interface</li>
                    <li><span class="arrow-candidate">→</span> Navigate freely between questions, skip and return</li>
                    <li><span class="arrow-candidate">→</span> Answers auto-saved — no lost progress on disconnect</li>
                    <li><span class="arrow-candidate">→</span> Write code in your preferred language</li>
                    <li><span class="arrow-candidate">→</span> Clear submission confirmation and time awareness</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- CTA banner -->
    <div class="cta-banner reveal">
        <div class="cta-orb"></div>
        <div class="cta-text">
            <h2>Ready to run your first assessment?</h2>
            <p>Sign in to the examiner dashboard to create an assessment, register a candidate, and see AssessMe in action.</p>
        </div>
        <a href="/admin/login" class="btn-primary" style="flex-shrink:0;">
            <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            Go to Dashboard
        </a>
    </div>

    <!-- Footer -->
    <footer>
        <a href="/" class="logo">Assess<span>Me</span></a>
        <p>
            &copy; <?php echo date('Y'); ?> AssessMe &nbsp;|&nbsp;
            Built by <a href="https://edward.monatemedia.com" target="_blank" rel="noopener" class="footer-link">Edward Lebogang Baitsewe</a>
            &nbsp;|&nbsp; Powered by <a href="https://www.monatemedia.com" target="_blank" rel="noopener" class="footer-link">Monate Media</a>
        </p>
    </footer>

    <script>
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
