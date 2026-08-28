# NeibrPay — Surveys & Voting Feature Plan

## 1. Overview

A simple polling/voting feature for HOA boards to gauge resident opinion or hold lightweight votes (e.g. "approve the new pool hours," "pick the landscaping vendor," "yes/no on the fence rule change"), inspired by PayHOA's Surveys & Voting module. This is **not** a full election system (no ranked-choice, no legal-quorum certification) — it's a fast way for admins to ask a question and see how the community answers.

**Decisions locked in for this plan:**

- **Vote unit:** one vote per **unit**, not per user account (matches HOA governance norms and avoids co-owner double-voting).
- **Privacy:** admins can see _which units/residents responded_ (for reminders and participation tracking) but **not what they answered** — answers are stored decoupled from identity.
- **Question types:** schema built to be flexible (multiple questions per survey, single-choice, multi-select, yes/no) but the first shippable version limits the _UI_ to one question per survey, single-choice or yes/no, to keep the build small. Multi-question/multi-select UI is a fast-follow using the same tables.
- **Placement:** new top-level **"Surveys"** section in both `admin-web` and `owner-web`, alongside Announcements and Documents rather than bolted onto either.

## 2. Goals / Non-Goals

**Goals**

- Admins create a survey with a question and 2+ options, target it at all units or a subset (reusing the Announcement recipient pattern), and set an optional close date.
- Residents/owners see open surveys they're eligible for, vote once per unit, and can see they've voted.
- Admins see live results (counts + %) and response/participation rate, and can close a survey early or extend it.
- Results are visible to residents after the survey closes (configurable).

**Non-Goals (later phases, not MVP)**

- Ranked-choice voting, weighted votes (e.g. by ownership %), legally-binding board elections, ballot secrecy audits, write-in candidates, ADA-accessible paper ballot fallback.

## 3. Data Model

Mirrors the existing `Announcement` / `AnnouncementRecipient` pattern already in the codebase (`app/Models/Announcement.php`, `announcement_recipients` table) so targeting logic, tenant scoping, and admin/resident permission checks stay consistent.

### `surveys`

| column                    | type                                       | notes                                    |
| ------------------------- | ------------------------------------------ | ---------------------------------------- |
| id                        | bigint                                     |                                          |
| tenant_id                 | fk → tenants                               | cascade delete                           |
| title                     | string                                     |                                          |
| description               | text nullable                              |                                          |
| status                    | enum: `draft`, `open`, `closed`            | draft lets admins prep before publishing |
| opens_at                  | timestamp nullable                         | defaults to publish time                 |
| closes_at                 | timestamp nullable                         | null = stays open until manually closed  |
| results_visibility        | enum: `after_close`, `live`, `admins_only` | default `after_close`                    |
| anonymous_responses       | boolean                                    | default true per decision above          |
| created_by                | fk → users                                 |                                          |
| timestamps + soft deletes |                                            |                                          |

### `survey_questions`

| column     | type                                            | notes                                                                                                    |
| ---------- | ----------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| id         | bigint                                          |                                                                                                          |
| survey_id  | fk → surveys                                    |                                                                                                          |
| prompt     | string                                          | the question text                                                                                        |
| type       | enum: `single_choice`, `multi_select`, `yes_no` | MVP UI only writes `single_choice`/`yes_no`; column exists so `multi_select` is a UI-only addition later |
| sort_order | int                                             | for multi-question surveys later; MVP always has 1 row                                                   |

### `survey_options`

| column             | type   | notes                        |
| ------------------ | ------ | ---------------------------- |
| id                 | bigint |                              |
| survey_question_id | fk     |                              |
| label              | string | e.g. "Yes", "No", "Option A" |
| sort_order         | int    |                              |

### `survey_recipients`

Same shape as `announcement_recipients` — reuse the targeting logic wholesale.
| column | type | notes |
|---|---|---|
| id | bigint | |
| survey_id | fk | |
| recipient_type | enum: `all_units`, `unit` | (no `all_admins`/`resident` — surveys target units, since voting is per-unit) |
| recipient_id | nullable bigint | unit id when `recipient_type = unit` |

### `survey_responses`

The **participation record** — proves a unit voted, without the answer.
| column | type | notes |
|---|---|---|
| id | bigint | |
| survey_id | fk | |
| unit_id | fk → units | unique per (survey_id, unit_id) — enforces one vote per unit |
| responded_by | fk → users nullable | which logged-in user submitted it (for audit/support, not shown to other admins as "how they voted") |
| responded_at | timestamp | |

### `survey_answers`

The actual choice(s), intentionally **not** joined back to `survey_responses.responded_by` in any query used for reporting — joined only to `survey_id`/`survey_question_id`/`survey_option_id` so tallies can be computed without exposing identity. (A `response_id` FK still exists for integrity/undo, but application code never selects `unit_id`/`responded_by` alongside `survey_option_id` in the same result set for the results view.)
| column | type | notes |
|---|---|---|
| id | bigint | |
| survey_response_id | fk → survey_responses | |
| survey_question_id | fk | |
| survey_option_id | fk | one row per selected option (multi-select = multiple rows) |

This gives real "track who responded, hide what they answered" behavior: the participation list (`survey_responses`) and the tally (`survey_answers` grouped by option) are queried separately and never joined in the same response payload.

## 4. Backend (Laravel)

New models: `Survey`, `SurveyQuestion`, `SurveyOption`, `SurveyRecipient`, `SurveyResponse`, `SurveyAnswer` — same conventions as `Announcement*` (tenant scoping via `scopeForTenant`, `HasFactory`, soft deletes on `Survey`).

New controller: `App\Http\Controllers\Api\SurveyController`, following `AnnouncementController`'s split between admin and resident endpoints:

- `GET /surveys` — admin list (all surveys for tenant, any status)
- `POST /surveys` — admin create (title, description, question, options, recipients, close date) — admin-only, `403` via `$user->isAdmin()` check like Announcements
- `GET /surveys/{survey}` — admin detail incl. live tallies + participation count
- `PUT /surveys/{survey}` — edit while in `draft`; limited edits once `open` (e.g. extend `closes_at`, can't change options after any vote is cast)
- `POST /surveys/{survey}/close` — admin manual close
- `DELETE /surveys/{survey}` — soft delete
- `GET /surveys/for-user` — resident: open + past surveys the user's unit(s) are targeted by, with `has_voted` flag per survey
- `POST /surveys/{survey}/vote` — resident: submit answer(s) for their unit; server rejects if already voted (unique constraint on `survey_responses`), if survey closed, or if unit not in recipient set
- `GET /surveys/{survey}/results` — resident: only returns data if `results_visibility` allows it for a non-admin caller

A scheduled command (Laravel scheduler, similar to existing invoice reminder job) auto-transitions `open` → `closed` when `closes_at` passes.

## 5. Frontend

### `admin-web` (board/manager)

- New **Surveys** nav item.
- List view: title, status badge, response rate ("14 of 22 units voted"), closes date.
- Create/Edit form: title, description, question text, question type toggle (Single choice / Yes-No to start), option list (add/remove rows), audience picker (reuse the Announcement audience component — All Units vs specific units), open/close dates, results visibility.
- Results view: bar/percentage breakdown per option, total participation, list of _which units have responded_ (not what they chose) so the board can send reminders to non-responders.

### `owner-web` (resident portal)

- New **Surveys** nav item (or a card on the dashboard for currently-open surveys, matching how Announcements surface today).
- Open surveys show the question + options as radio/checkbox, a Submit button; once voted, show "You voted — results available after close" (or live results if `results_visibility` allows).
- Closed surveys move to a "Past Surveys" list with results if visible to residents.

### Shared

- Add `Survey` types to `packages/api-client` alongside existing `Announcement` types, following the same axios wrapper pattern.

## 6. Notifications

Reuse the existing notification pipeline used for Announcements (email + Firebase push):

- New survey published → notify targeted units' residents.
- Optional reminder job ~2 days before `closes_at` to units that haven't voted (only possible because participation, not answers, is tracked).
- Survey closed → optional "results are in" notification if `results_visibility` includes residents.

## 7. Permissions

- Only `user->isAdmin()` can create/edit/close/delete surveys — identical gate to `AnnouncementController`.
- Any authenticated resident/owner tied to a targeted unit can vote once per survey per unit.
- Multi-owner units: first owner to submit records the unit's vote; subsequent owners on the same unit see "already voted by your unit" rather than being able to vote again — consistent with the one-vote-per-unit decision.

## 8. Phased Rollout

**Phase 1 (MVP — ship first)**

- Single-question surveys, single-choice or yes/no only.
- All-units or specific-units targeting.
- Manual or scheduled close.
- Admin results view + resident post-close results.
- Email/push notification on publish.

**Phase 2**

- Multi-select questions.
- Multiple questions per survey (schema already supports it — just unlocks the UI for `sort_order` > 1 and a stepper/wizard form).
- Non-voter reminder job.
- Live (pre-close) results toggle.

**Phase 3 (only if there's demand for real elections)**

- Ranked-choice, candidate/nominee style ballots, quorum thresholds, exportable vote certification report — the heavier PayHOA-style "election" tooling.

## 9. Open Questions for You

1. Should a **draft** state exist (admin builds a survey, previews it, publishes later), or should every created survey go live immediately? (Plan assumes draft exists.)
2. For multi-owner units, is "first vote wins" the right rule, or should co-owners need to coordinate a single answer some other way (e.g. only the primary/first-listed owner on the unit can vote)?
3. Do you want survey results ever exportable (CSV/PDF), or is in-app viewing enough for MVP?
