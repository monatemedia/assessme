# Product Requirements Document

## AssessMe — AI-Assisted Technical Screening Tool

**Author:** Edward Lebogang Baitsewe
**Version:** 1.0 — MVP
**Date:** March 2026
**Format:** Markdown
**Status:** Draft

---

## Table of Contents

1. [Overview](#1-overview)
2. [Problem Statement](#2-problem-statement)
3. [Goals and Non-Goals](#3-goals-and-non-goals)
4. [User Roles](#4-user-roles)
5. [Technology Stack](#5-technology-stack)
6. [Architecture Overview](#6-architecture-overview)
7. [Feature Specifications](#7-feature-specifications)
   - 7.1 [Authentication and Access](#71-authentication-and-access)
   - 7.2 [Examiner — Test Management](#72-examiner--test-management)
   - 7.3 [Examiner — Candidate Management](#73-examiner--candidate-management)
   - 7.4 [Examiner — Results and Grading](#74-examiner--results-and-grading)
   - 7.5 [Candidate — Assessment Experience](#75-candidate--assessment-experience)
   - 7.6 [AI Marking and Plagiarism Detection](#76-ai-marking-and-plagiarism-detection)
   - 7.7 [Notifications](#77-notifications)
   - 7.8 [PDF Export](#78-pdf-export)
8. [Question Types](#8-question-types)
9. [Time Limiting](#9-time-limiting)
10. [Unique Candidate Links](#10-unique-candidate-links)
11. [AI Marking — Scoring Model](#11-ai-marking--scoring-model)
12. [Deliberate Design Decisions](#12-deliberate-design-decisions)
13. [Phase 2 Roadmap](#13-phase-2-roadmap)
14. [Deployment](#14-deployment)
15. [Database Schema — High Level](#15-database-schema--high-level)
16. [JSON Import Schema](#16-json-import-schema)

---

## 1. Overview

AssessMe is a self-hosted, AI-assisted technical screening tool designed for engineering teams to create, administer, and grade candidate assessments. It is built to be lightweight, fast to deploy, and opinionated — favouring convention over configuration.

The platform solves a real problem that surfaced during the author's own job application process: existing AI plagiarism detection tools produce binary pass/fail outcomes that penalise candidates who use AI responsibly as a tool, rather than measuring the depth of understanding behind their answers. AssessMe treats AI usage as a metric to inform examiner judgement, not as grounds for automatic disqualification.

---

## 2. Problem Statement

Technical assessments administered over Google Docs or similar unstructured tools suffer from several problems:

- No structured marking scheme — grading is inconsistent and time-consuming
- No AI usage visibility — examiners cannot distinguish responsible AI-assisted work from AI slop
- No time management — candidates have no guardrails or structure around submission
- No candidate tracking — examiners cannot see assessment progress in real time
- No audit trail — there is no record of when a candidate started, paused, or submitted

AssessMe addresses all of the above within a self-hosted, Dockerised environment that can be deployed behind an existing Nginx reverse proxy without any changes to the proxy configuration.

---

## 3. Goals and Non-Goals

### Goals — MVP

- Examiners can create and manage assessments with multiple question types
- Candidates receive a unique access link and complete assessments in a structured interface
- AI marks free-text and code answers against an examiner-defined answer key, with numeric scores and qualitative feedback
- AI plagiarism detection runs on submission and produces a per-answer and overall usage percentage
- Examiners can override AI scores and view candidate progress snapshots
- The entire platform runs in Docker and deploys behind an existing Nginx reverse proxy with no proxy changes required

### Non-Goals — MVP

- Email delivery via external services (Mailgun, Amazon SES, Postmark) — deferred to Phase 2
- Real-time candidate monitoring via WebSockets — deferred to Phase 2
- Multi-tenancy (department-level isolation) — deferred to Phase 2
- Candidate-initiated resit requests — not supported; examiner controls attempt limits

---

## 4. User Roles

| Role | Description |
|---|---|
| **Superuser** | Platform administrator. Manages examiner accounts. Phase 2: manages tenants. |
| **Examiner** | Creates assessments, manages candidates, reviews and grades results. |
| **Candidate** | Accesses assessment via unique link, completes and submits answers. |

---

## 5. Technology Stack

| Layer | Technology | Rationale |
|---|---|---|
| Backend framework | Laravel 13 | Mature, well-documented, excellent ecosystem |
| Admin panel | Filament 5 | Rapid UI development, native code editor, multitenancy support |
| Frontend (candidate view) | Blade + Livewire | Lightweight, server-rendered, no separate SPA needed |
| Database | PostgreSQL 18.3 (Alpine) | Future-fit for AI extensions (pgvector for vector search), excellent JSON support, superior query planner for complex analytics |
| Cache and queues | Redis | Fast, reliable queue driver for background AI jobs |
| AI marking and plagiarism | OpenAI GPT-4o | Powerful instruction-following, structured output support |
| PDF export | Spatie Laravel PDF | Docker-friendly, no external services required |
| Containerisation | Docker + Docker Compose | Consistent deployment, resource-limited containers |
| Reverse proxy | Nginx (existing, unmodified) | App sits behind existing proxy; no proxy changes required |
| Code editor (candidate) | Filament 5 native CodeEditor | Built-in syntax highlighting for PHP, Python, JavaScript, Ruby, Go, and more — CodeMirror 6 deferred to Phase 2 |

---

## 6. Architecture Overview

```
Internet
    |
Nginx Reverse Proxy (existing, unmodified)
    |
Docker Network
    |
    +--- app (Laravel 13 + Filament 5)
    |         Port: 8000 (internal)
    |
    +--- postgres (PostgreSQL 18.3 Alpine)
    |         Port: 5432 (internal)
    |
    +--- redis (Redis)
              Port: 6379 (internal)
```

All services communicate over an internal Docker network. No ports are exposed to the host except through the reverse proxy. All environment variables, credentials, and configuration are defined in the application's `docker-compose.yml`. No changes are required to the Nginx reverse proxy configuration.

---

## 7. Feature Specifications

### 7.1 Authentication and Access

**Examiner authentication:**
- Standard email and password login via Filament's built-in auth
- Examiner accounts created by Superuser

**Candidate authentication:**
- Candidates do not register themselves
- Access is granted via a unique UUID token link generated by the examiner
- Candidates land on a simple confirmation page (name + optional PIN) before beginning
- No username/password complexity required for candidates

---

### 7.2 Examiner — Test Management

**Create assessment:**
- Assessment title, description, and instructions
- Add questions of three types: Multiple Choice, Free Text, Code (see Section 8)
- Set marks per question
- Define the answer key per question (used by AI for marking)
- Define extra credit criteria — additional insights beyond the answer key that earn bonus marks (e.g. mentioning OWASP API Security Top 10)
- Drag and drop question ordering via Filament's native reorder functionality
- Preview assessment as candidate would see it (read-only view)
- Reuse assessment across multiple candidates by generating new unique links

**JSON import:**
- As an alternative to creating assessments through the UI, examiners may upload a structured JSON file to populate a complete assessment including all questions, answer keys, marks, and extra credit criteria
- This enables rapid assessment creation, version control of assessment content, and programmatic generation of assessments
- Three JSON templates are provided with the application:
  - `assessment-filled.json` — a fully populated example assessment (the xneelo Backend Systems Engineer screening, with Edward's answers used as the answer key) for app testing and onboarding reference
  - `assessment-blank.json` — an empty template with inline comments explaining every available field and option, for examiners creating new assessments from scratch
  - See Section 16 for the JSON schema specification

**Time limit configuration (per assessment):**

| Mode | Behaviour |
|---|---|
| No time limit | Candidate may take as long as needed |
| Closing date and time | Examiner sets a specific deadline (e.g. Friday 27 March at 17:00). Candidate may log in and out freely until the deadline, at which point the configured expiry behaviour fires. |
| Time limit from first login | Clock starts when the candidate first accesses the assessment. Candidate may log in and out; the clock keeps running. |

**On time expiry — examiner selects one of three behaviours:**

| Behaviour | Description |
|---|---|
| Warning only | Candidate is notified they are over time; submission remains open |
| Grace period | Candidate receives a configurable grace period (e.g. 5 minutes) then auto-submit |
| Auto-submit | Assessment is submitted immediately on expiry |

**Attempt limits:**
- Examiner sets the maximum number of attempts per candidate (default: 1)
- Candidate cannot request a resit; examiner controls all attempt grants

---

### 7.3 Examiner — Candidate Management

**Register candidate:**
- Examiner enters candidate name and email
- System generates a unique UUID token link
- Link is displayed to the examiner to share manually (no email sending in MVP)
- Link format: `https://yourapp.com/assessment/{uuid-token}`

**Candidate status dashboard:**
- List view of all candidates for a given assessment
- Status per candidate: Not Started / In Progress / Submitted
- Snapshot of progress for in-progress candidates (questions answered vs total)
- Examiner cannot fill in or modify candidate answers

---

### 7.4 Examiner — Results and Grading

**Results dashboard:**
- Per-candidate results view showing all questions, candidate answers, AI scores, and AI feedback
- AI plagiarism percentage per answer and rolled up to overall assessment percentage
- Examiner can override any AI-assigned score with a manual score and optional note
- Final score calculated as sum of all marks (AI or manually overridden)
- Export assessment results to PDF via Spatie Laravel PDF

**AI uncertainty handling:**
- If GPT-4o returns low confidence on a score, the answer is flagged for mandatory examiner review
- Flagged answers are visually highlighted in the results dashboard

**Bell notification:**
- In-app bell notification when a candidate submits their assessment
- Email notification is sent to the Laravel log (not delivered externally in MVP)
- Note: Email delivery infrastructure is in place and ready for Phase 2 integration with an external provider (Mailgun, Postmark, SES, etc.)

---

### 7.5 Candidate — Assessment Experience

**Assessment interface:**
- Clean, distraction-free Blade + Livewire interface (not Filament admin panel)
- Progress indicator showing questions answered vs total
- List view toggle: candidate can see all questions at a glance, with answered/skipped status per question
- Free navigation — candidate can move forwards and backwards between questions at any time
- Auto-save on every answer change — no data loss on browser close or network interruption
- Submit button with confirmation modal

**Code questions:**
- Filament 5 native CodeEditor rendered in the candidate view
- Examiner selects a default/preferred language when creating the question
- Examiner may provide the code snippet in multiple languages (e.g. Ruby, PHP, Python, JavaScript)
- Candidate selects their preferred language from a dropdown
- Note: The language preference of the candidate vs the examiner's preferred language is captured and visible to the examiner in results. The examiner makes the final judgement call on language choice — when a candidate is genuinely skilled, syntax is semantics.

---

### 7.6 AI Marking and Plagiarism Detection

**When it runs:** On final submission. Examiner may also trigger on demand from the results dashboard.

**All AI calls are dispatched as background jobs** via Laravel's queue system, backed by Redis. AI marking and plagiarism detection are never run synchronously during an HTTP request — doing so would block the response, risk connection timeouts, and degrade the candidate and examiner experience. On submission, the candidate receives an immediate confirmation and the AI marking jobs are queued for background processing. The examiner's results dashboard reflects live job status (Pending / Processing / Complete).

**AI marking (free text and code questions):**
- Each answer is sent to GPT-4o with the question, the examiner's answer key, the marks available, and the extra credit criteria
- GPT-4o returns: numeric score, confidence level, qualitative feedback, and a list of matched criteria from the answer key
- If confidence is below threshold, the answer is flagged for examiner review
- Multiple choice questions are marked by the system — no AI call required

**AI plagiarism detection:**
- Each free-text and code answer is assessed for AI-generated content probability
- Result stored as a percentage (0–100%) per answer
- Overall assessment AI usage percentage = weighted average across all applicable answers
- Percentage is displayed to the examiner as an informational metric only — it does not affect the score
- Philosophy: AI usage percentage informs examiner judgement; it does not trigger automatic disqualification. A candidate who used AI responsibly to structure well-reasoned answers should not be penalised. A candidate who submitted AI slop with no understanding will be revealed by the examiner's own review of the answers.

---

### 7.7 Notifications

**In-app (MVP primary):**
- Bell icon in Filament dashboard
- Database-backed notifications (Filament native)
- Examiner notified when candidate submits

**Email (MVP — log only):**
- Email notifications are sent to the Laravel log (`storage/logs/laravel.log`)
- Email infrastructure (Mailable classes, templates) is fully built and ready
- Phase 2: plug in Mailgun, Postmark, or Amazon SES by updating `MAIL_MAILER` in `.env`

---

### 7.8 PDF Export

- Examiner can export a candidate's full results to PDF
- Includes: candidate name, assessment title, all questions, candidate answers, AI scores, examiner override scores, AI plagiarism percentage, and final score
- Powered by Spatie Laravel PDF — no external services required, fully Docker-compatible

---

## 8. Question Types

### MVP Question Types

| Type | Description | Auto-Gradable |
|---|---|---|
| Multiple Choice — Single Answer | One correct answer from a list of options. Rendered as radio buttons. | Yes — system marked |
| Multiple Choice — Multiple Answer | More than one correct answer from a list of options. Rendered as checkboxes. Negative marking applies. | Yes — system marked |
| Free Text | Open-ended written answer | Yes — GPT-4o |
| Code | Candidate writes or edits code in a syntax-highlighted editor with language selection | Yes — GPT-4o |

**Multiple Choice — Multiple Answer: Negative Marking Rules**

To prevent candidates from selecting all options to guarantee full marks, incorrect selections are penalised. The examiner configures negative marking per question with two scope options:

| Scope | Behaviour |
|---|---|
| Question-scoped (default) | One mark deducted per incorrect selection, applied to that question's score only. Minimum score for the question is zero — the candidate cannot go below zero on a single question. |
| Assessment-scoped | One mark deducted per incorrect selection, applied to the candidate's total assessment score. The candidate's overall score can go negative. Use with caution — best suited for assessments where guessing must be strongly discouraged. |

The examiner selects the negative marking scope when creating or editing a multiple-answer question. The candidate is informed at the start of the assessment whether negative marking is in effect, and at question level whether the scope is question or assessment-wide.

### Phase 2 Question Types

The following question types are deferred to Phase 2. Types marked as straightforward are candidates for early Phase 2 inclusion:

| Type | Description | Auto-Gradable | Complexity |
|---|---|---|---|
| Matching Pairs | Connect Column A to Column B — terminology, definitions, function-to-output | Yes | Low — straightforward Phase 2 candidate |
| Ordering / Drag and Drop | Place items in the correct sequence (e.g. steps of an algorithm) | Yes | Medium |
| Categorisation | Drag items into labelled buckets (e.g. Compiled vs Interpreted languages) | Yes | Medium |
| Fill in the Blanks — Dropdown | Sentence with embedded dropdowns; tests precision without typo frustration | Yes | Medium |
| Fill in the Blanks — Drag and Drop | Word bank at the bottom; drag terms into correct gaps in a paragraph | Yes | Medium |
| Hotspot | Upload a diagram; candidate clicks the correct region (e.g. "click the syntax error") | Yes | High |
| Mermaid Diagram | Candidate constructs or completes a Mermaid diagram as their answer | Partial — examiner review | High |
| File Upload | Candidate uploads a PDF, ZIP, or design file for complex project submissions | No — manual rubric | Low |
| Mathematical / Formula Input | LaTeX or visual formula editor for STEM assessments | Yes | High |
| Likert Scale | 1–5 or Strongly Disagree–Strongly Agree — for candidate self-assessment or course feedback | N/A — not graded | Low |

**Post-assessment candidate feedback (Phase 2):**
A Likert scale and free-text box are appended after submission, inviting the candidate to rate the assessment experience and provide optional comments. This data is visible to the examiner in the results dashboard.

---

## 9. Time Limiting

Three modes selectable per assessment:

| Mode | Clock Start | Candidate Can Pause |
|---|---|---|
| No time limit | N/A | Yes |
| Closing date and time | Examiner sets a specific deadline (e.g. Friday 27 March at 17:00) | Yes — deadline is a fixed point in time |
| From first login | When candidate first accesses the assessment | Yes — clock keeps running in the background |

On expiry, the examiner's pre-configured behaviour fires (warning, grace period, or auto-submit).

---

## 10. Unique Candidate Links

- Each candidate is assigned a UUID v4 token on registration
- Link format: `https://yourapp.com/assessment/{uuid-token}`
- Laravel catches the token via a standard route parameter — no Nginx changes required:

```php
Route::get('/assessment/{token}', [AssessmentController::class, 'show']);
```

- Token is validated against the database on every request
- Expired or invalid tokens return a friendly error page

---

## 11. AI Marking — Scoring Model

The following example illustrates how the marking scheme is structured for a free-text question:

**Question:**
> Given a multitude of services that need to access a resource via a REST API, describe how you would go about securing these interactions to ensure that only the right consumers are allowed access.

**Answer key (used by GPT-4o for marking):**

```
- Authentication
  - Internal services (API keys, mTLS)
  - External services (OAuth 2.0, JWT)
- Authorisation (RBAC, per-endpoint validation)
- Network restrictions (IP whitelisting)
- Encryption (HTTPS/TLS 1.2+, no credentials in URLs)
- Rate limiting (HTTP 429)
- Input validation (schema validation, injection prevention)
- Error handling (generic responses to client, detailed internal logs)
```

**Extra credit criteria (bonus marks):**
- Mentions OWASP API Security Top 10
- Distinguishes between internal and external consumers without being prompted
- Mentions specific tools (e.g. Kong, AWS API Gateway, Nginx)

**GPT-4o prompt structure (simplified):**

```
You are a technical assessment marker. Given the question, answer key, 
and candidate answer below, return a JSON object with:
- score: integer (0 to max_marks)
- confidence: float (0.0 to 1.0)
- feedback: string (qualitative feedback for the examiner)
- matched_criteria: array of strings from the answer key that were addressed
- bonus_criteria_matched: array of extra credit criteria that were addressed

Question: {question}
Answer key: {answer_key}
Extra credit criteria: {extra_credit}
Max marks: {max_marks}
Candidate answer: {candidate_answer}

Return only valid JSON. No preamble.
```

---

## 12. Deliberate Design Decisions

The following decisions were made intentionally and are documented here for transparency:

| Decision | Rationale |
|---|---|
| No external email service in MVP | Keeps deployment simple. Email infrastructure is built and ready — plug in a provider in Phase 2 by changing one `.env` variable. |
| Filament 5 native CodeEditor instead of CodeMirror 6 | Filament 5 ships with a native code editor supporting all required languages. CodeMirror 6 deferred to Phase 2 if more advanced editor features are needed. |
| AI plagiarism as informational metric only | Binary AI pass/fail systems penalise responsible AI use. A percentage gives the examiner context to make an informed judgement rather than an automated one. |
| Candidate language choice for code questions | When a candidate is genuinely skilled, syntax is semantics. Forcing a specific language penalises capable engineers who happen to work in a different stack. The examiner sees the language choice and makes the final call. |
| No Nginx changes required | The app is self-contained in Docker. All routing, SSL termination, and configuration live inside the app's `docker-compose.yml`. This makes it deployable on any existing server without touching shared infrastructure. |
| Candidates cannot request resits | Simplicity. The examiner controls all attempt grants. |
| PostgreSQL over MySQL | PostgreSQL is future-fit for AI-powered features. The `pgvector` extension enables vector similarity search, making it possible to build an AI chat assistant or semantic search over assessment data in Phase 2 without a database migration. PostgreSQL also offers a superior query planner for complex analytics and better native JSON support. |
| Closing date and time instead of clock-from-link | A fixed deadline (e.g. Friday at 17:00) is more intuitive and fairer for candidates across time zones. It allows candidates to manage their own time rather than being penalised for when the examiner happened to generate the link. |

---

## 13. Phase 2 Roadmap

| Feature | Notes |
|---|---|
| Multi-tenancy | Filament 5 has native multitenancy support via `HasTenants` interface. Different departments can run independent assessments with full data isolation. Superuser manages tenant onboarding. |
| External email delivery | Infrastructure is already built. Connect Mailgun, Postmark, or Amazon SES by setting `MAIL_MAILER` and related credentials in `.env`. |
| Real-time candidate monitoring | Laravel Echo + Pusher or Soketi for live progress updates without page refresh. |
| CodeMirror 6 integration | Replace Filament native editor with CodeMirror 6 for advanced features: vim keybindings, diff view, collaborative editing. |
| Candidate resit requests | Allow candidates to request a resit; examiner approves or denies. |
| Additional AI model support | Abstract the AI marking layer behind an interface. Swap GPT-4o for Claude, Gemini, or a self-hosted model without changing business logic. |
| Assessment analytics | Aggregate statistics across candidates: average score per question, most commonly missed criteria, AI usage trends. |
| AI answer key review | Before publishing an assessment, the examiner can run a pre-flight check where GPT-4o reviews the answer key and scoring criteria, flags ambiguities, suggests missing criteria, and confirms that the marking instructions will produce consistent results. This guards against poorly specified answer keys that lead to inconsistent AI marking. Not needed for the initial AssessMe assessment — the answer key was co-developed with AI and is already well specified. |
| AI-powered chat assistant | A conversational assistant embedded in the examiner dashboard to help with navigation, assessment setup, and results interpretation. Powered by GPT-4o with pgvector (PostgreSQL) for semantic search over assessment data — made possible by the deliberate choice of PostgreSQL as the database from day one. |
| Post-assessment candidate feedback | Likert scale and free-text feedback form appended after submission. Examiner can view aggregate feedback per assessment. |
| Advanced question types | Matching Pairs, Ordering, Categorisation, Fill in the Blanks, Hotspot, Mermaid Diagram, File Upload, Mathematical Formula Input (see Section 8 for complexity ratings). |

---

## 14. Deployment

The application deploys as a Docker Compose stack. All configuration lives in `docker-compose.yml` and `.env`. No changes are required to the host Nginx reverse proxy.

**`docker-compose.yml` resource limits (example):**

```yaml
services:
  app:
    image: assessme-app
    deploy:
      resources:
        limits:
          cpus: "0.75"
          memory: 512M
    restart: unless-stopped
    env_file: .env

  postgres:
    image: postgres:18.3-alpine
    deploy:
      resources:
        limits:
          cpus: "0.50"
          memory: 256M
    restart: unless-stopped

  redis:
    image: redis:alpine
    deploy:
      resources:
        limits:
          cpus: "0.25"
          memory: 128M
    restart: unless-stopped
```

**Nginx reverse proxy configuration (no changes required):**

The existing Nginx proxy passes requests to the app container on its internal Docker network port. The app handles all routing internally via Laravel.

---

## 15. Database Schema — High Level

```
users
  id, name, email, password, role (superuser|examiner), timestamps

assessments
  id, examiner_id, title, description, instructions,
  time_limit_mode (none|from_link|from_first_login),
  time_limit_seconds, on_expiry (warn|grace|autosubmit),
  grace_period_seconds, max_attempts, timestamps

questions
  id, assessment_id, type (multiple_choice_single|multiple_choice_multiple|free_text|code),
  body, marks, order, preferred_language,
  answer_key (JSON), extra_credit_criteria (JSON),
  negative_marking_enabled (boolean), negative_marking_scope (question|assessment),
  timestamps

question_options         (multiple choice only)
  id, question_id, body, is_correct

candidates
  id, examiner_id, assessment_id, name, email,
  token (UUID), attempt_count, timestamps

candidate_sessions
  id, candidate_id, assessment_id,
  started_at, submitted_at, closing_at,
  first_login_at, status (not_started|in_progress|submitted)

answers
  id, candidate_id, question_id, body (text|JSON),
  selected_language, autosaved_at, timestamps

scores
  id, answer_id, ai_score, ai_confidence, ai_feedback,
  ai_plagiarism_percentage, matched_criteria (JSON),
  examiner_override_score, examiner_note,
  flagged_for_review (boolean),
  ai_job_status (pending|processing|complete|failed),
  timestamps

notifications
  id, user_id, type, data (JSON), read_at, timestamps
```

---

## 16. JSON Import Schema

The following is the canonical JSON schema for importing an assessment. Two files are shipped with the application:

- `assessment-filled.json` — the xneelo Backend Systems Engineer screening fully populated with Edward's answers as the answer key, for use in app testing
- `assessment-blank.json` — an empty template with inline `_comment` fields explaining every available option

```json
{
  "_comment": "AssessMe JSON Import Schema v1.0",

  "title": "Assessment title here",
  "description": "A brief description of the assessment shown to the candidate.",
  "instructions": "Instructions shown to the candidate at the top of the assessment.",

  "time_limit": {
    "_comment": "mode options: 'none' | 'closing_datetime' | 'from_first_login'",
    "mode": "closing_datetime",
    "closing_at": "2026-03-27T17:00:00",
    "_comment_closing_at": "ISO 8601 format. Required when mode is 'closing_datetime'. Ignored otherwise.",
    "duration_seconds": null,
    "_comment_duration": "Required when mode is 'from_first_login'. Set to null otherwise. Example: 7200 = 2 hours."
  },

  "on_expiry": {
    "_comment": "behaviour options: 'warn' | 'grace' | 'autosubmit'",
    "behaviour": "grace",
    "grace_period_seconds": 300,
    "_comment_grace": "Only used when behaviour is 'grace'. Example: 300 = 5 minutes."
  },

  "max_attempts": 1,
  "_comment_attempts": "Maximum number of times a candidate may attempt this assessment. Default: 1.",

  "questions": [
    {
      "order": 1,
      "_comment": "type options: 'multiple_choice_single' | 'multiple_choice_multiple' | 'free_text' | 'code'",
      "type": "free_text",
      "body": "The full question text goes here.",
      "marks": 5,

      "answer_key": [
        "First key concept the candidate should address",
        "Second key concept",
        "Third key concept"
      ],
      "_comment_answer_key": "Used by GPT-4o to mark free_text and code questions. Each item is a gradable criterion. Leave as empty array [] for multiple_choice.",

      "extra_credit_criteria": [
        "Bonus insight 1 — e.g. mentions OWASP API Security Top 10",
        "Bonus insight 2"
      ],
      "_comment_extra_credit": "Optional. Bonus marks awarded for insights beyond the answer key. Leave as empty array [] if not applicable.",

      "options": [],
      "_comment_options": "Required for multiple_choice_single and multiple_choice_multiple only. Leave as empty array [] for free_text and code. Example: [{ 'body': 'Option A', 'is_correct': false }, { 'body': 'Option B', 'is_correct': true }]",

      "negative_marking": {
        "_comment": "Only applies to multiple_choice_multiple. scope options: 'question' | 'assessment'. 'question' = minimum score for this question is zero. 'assessment' = deduction applied to total assessment score, can go negative.",
        "enabled": false,
        "scope": "question"
      },

      "preferred_language": null,
      "_comment_preferred_language": "For code questions only. Options: 'ruby' | 'php' | 'python' | 'javascript' | 'go' | 'java' | 'sql' | null. Candidate may still select a different language.",

      "code_snippets": [],
      "_comment_code_snippets": "For code questions only. Provide the starter snippet in one or more languages. Example: [{ 'language': 'ruby', 'body': '#!/usr/bin/env ruby\n...' }, { 'language': 'python', 'body': '...' }]. Leave as empty array [] for free_text and multiple_choice."
    },

    {
      "order": 2,
      "type": "multiple_choice",
      "body": "Which of the following is the correct answer?",
      "marks": 1,
      "answer_key": [],
      "extra_credit_criteria": [],
      "options": [
        { "body": "Option A", "is_correct": false },
        { "body": "Option B", "is_correct": true },
        { "body": "Option C", "is_correct": false },
        { "body": "Option D", "is_correct": false }
      ],
      "preferred_language": null,
      "code_snippets": []
    },

    {
      "order": 3,
      "type": "code",
      "body": "Rewrite the following script in your preferred language, correcting all identified issues.",
      "marks": 10,
      "answer_key": [
        "Uses prepared statements to prevent SQL injection",
        "Replaces hardcoded credentials with environment variables",
        "Connects as a least-privilege user instead of root",
        "Validates input before use",
        "Selects specific columns instead of SELECT *",
        "Uses meaningful class and method names"
      ],
      "extra_credit_criteria": [
        "Adds inline comments explaining each security improvement"
      ],
      "options": [],
      "preferred_language": "ruby",
      "code_snippets": [
        {
          "language": "ruby",
          "body": "#!/usr/bin/env ruby\nrequire 'mysql2'\n\nrequested_group = ARGV[0]\n\nclass A\n  def self.get_all_table_data_for_group(group)\n    client = Mysql2::Client.new(:host => \"localhost\", :username => \"root\")\n    results = client.query(\"SELECT * FROM my_table WHERE group='#{group}'\")\n    return results\n  end\nend\n\nputs A.get_all_table_data_for_group(requested_group)"
        },
        {
          "language": "php",
          "body": "<?php\n// PHP equivalent starter snippet here"
        },
        {
          "language": "python",
          "body": "# Python equivalent starter snippet here"
        }
      ]
    }
  ]
}
```

---

*This document was authored by Edward Lebogang Baitsewe. It represents original thinking developed through research, personal production experience with Docker, Laravel, and VPS deployment, and iterative refinement. External tools including Claude AI and Google AI Mode were used in the research and drafting process.*
