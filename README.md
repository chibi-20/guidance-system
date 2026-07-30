# Guidance Record System

A CodeIgniter 4 web application for the Guidance Office of **Jacobo Z. Gonzales
Memorial National High School** to manage student records and discipline
cases — replacing paper-based Student Discipline Action Forms with a searchable,
role-based digital system.

## Roles & Permissions

| Role | Students | Cases | Reports | Offense Types | Sections | Users | Promote Students |
|---|---|---|---|---|---|---|---|
| **admin** | full | full | view/export | manage | manage | manage | run |
| **guidance** | full | full | view/export | manage | manage | — | — |
| **discipline_officer** | view/create case | full | view/export | — | — | — | — |
| **principal** | view | view/resolve | view/export | — | — | — | — |
| **adviser** | view | — | — | — | — | — | — |

Access is enforced both by route filters (`app/Config/Routes.php` +
`app/Filters/RoleFilter.php`) and hidden in the sidebar for roles that can't
use a given section.

## Features

- **Authentication** — session-based login/logout, role stored in session,
  `AuthFilter` guards every route except `/login`.
- **Dashboard** — active student count, open case count, flagged repeat
  offenders, cases filed this month, plus Chart.js breakdowns of cases by
  category and top offense types.
- **Students** — full CRUD with soft delete, LRN/name search and sorting,
  demographic profile, current + historical **Enrollment History**, and a
  **Grade Level & Section** assignment on every add/edit that creates or
  updates the student's `enrollments` row for the current school year.
- **Bulk CSV Import** — upload a CSV to insert/update many students at once;
  validates every row up front (all-or-nothing), auto-creates any section
  named in the file that doesn't exist yet, and reports rows
  processed/inserted/updated/errored.
- **Cases** — file a case against a student (offense type, incident report,
  narrative, prior actions), track open/ongoing/resolved/escalated status,
  resolve with disciplinary action details, and see live offense-count badges
  (this offense type vs. overall) per student.
- **Case PDF export** — generates the official Student Discipline Action Form
  as a PDF (via mPDF) from a resolved or in-progress case, with blank
  signature lines for the student, parent/guardian, and School Head/Discipline
  Designate.
- **Offense Types** — manage the grave/minor offense catalog, including a
  quick-add popup available directly from the case-filing form.
- **Sections** — manage sections per grade level (7–10), with a live count of
  currently-enrolled active students per section and safeguards against
  deleting a section that has any enrollment history.
- **Promote Students** (admin only) — advance every active student to the
  next grade level for a new school year in one transaction: matches
  Grade N sections to same-named Grade N+1 sections, leaves students
  "Unassigned" (never guessed) when no match exists, graduates Grade 10
  students instead of promoting them, and never modifies past enrollment or
  case rows — only adds new ones. Logs each run to `audit_logs`.
- **Reports** — filterable case list (date range, category, offense type,
  grade/section, status) with summary stats, cases-by-offense-type and
  cases-by-section breakdowns, a repeat-offenders list, and CSV export.
- **User management** (admin only) — create staff accounts with a
  system-generated temporary password, edit/deactivate/reactivate, and reset
  passwords.
- **DepEd-branded UI** — a shared `theme.css` design system (blue/gold
  palette, Inter typeface) applied consistently across every page via the
  single `app/Views/layouts/main.php` layout, with a responsive off-canvas
  sidebar on small screens.

## Tech Stack

- **Framework:** CodeIgniter 4.7 (PHP 8.2+)
- **Database:** MySQL/MariaDB
- **PDF generation:** [mPDF](https://mpdf.github.io/)
- **Front end:** Bootstrap 5.3, Bootstrap Icons, Chart.js — all via CDN, no
  build step required
- **Dev server:** XAMPP (Apache + MySQL) or `php spark serve`

## Setup

1. **Database** — create a MySQL database (default expected name:
   `guidance_system`).
2. **Environment** — copy `env` to `.env` and set:
   ```
   CI_ENVIRONMENT = development
   app.baseURL = 'http://localhost:8080'
   database.default.hostname = localhost
   database.default.database = guidance_system
   database.default.username = root
   database.default.password =
   ```
3. **Install dependencies:**
   ```
   composer install
   ```
4. **Run migrations and seeders:**
   ```
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```
   This creates the schema and seeds a current school year (2026-2027), a
   handful of Grade 10 sections, the offense type catalog, and three staff
   accounts (see below).
5. **Serve the app:**
   ```
   php spark serve --host localhost --port 8080
   ```
   or point Apache/XAMPP's virtual host at the `public/` folder.

### Default seeded accounts

| Username | Password | Role |
|---|---|---|
| `admin` | `Admin@12345` | admin |
| `discipline_officer` | `Officer@12345` | discipline_officer |
| `guidance_counselor` | `Guidance@12345` | guidance |

Change these before using the system with real student data.

## Project Structure Notes

- Every authenticated page extends the single shared layout
  `app/Views/layouts/main.php` (sidebar, top bar, theme) — new pages inherit
  the design system automatically rather than styling themselves ad hoc.
- `enrollments` is the source of truth for "what grade/section is this
  student in, and when" — it's append-only by design (promotion, section
  edits, and CSV import all add or update rows for the *current* school year
  only; historical rows are never touched), which is what keeps case history
  accurate to the section a student was in when a case was filed.
- `audit_logs` currently only receives entries from the Promote Students
  action.

## Known Gaps

A few tables/fields exist in the schema without a UI built on top of them
yet: `parent_communications`, `attachments` (and the related student
`photo_path` field), and `others_involved` (read-only, shown on the case PDF
but with no form to add entries). These were left for a future iteration
rather than built out speculatively.
