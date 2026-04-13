# Phase 1 Contract: FinTrack (A) - FinLyzer (B) - FinGoals (C)

Last Updated: 2026-04-14  
Status: Baseline Locked

## Scope

This contract locks cross-service behavior for:

- Real-time flow: A -> B -> C -> A.
- Periodic monthly flow: recap, trend analysis, investment recommendation.
- User-facing authentication boundary: Keycloak OIDC.
- Internal service-to-service boundary: API key with phased hardening.

## Service Roles

### Service A (FinTrack)

- User entry gateway.
- Transaction source of truth.
- Orchestrator for summary pipeline and callback consumer from C.

### Service B (FinLyzer)

- Financial analysis engine.
- Consumes transaction feed from A.
- Exposes latest analysis payload for C.

### Service C (FinGoals)

- Planning and goal engine.
- Produces plan result from B analysis.
- User-facing planner/goals with strict own-data access.

## Canonical Endpoints

### Internal Endpoints (service-to-service)

- A -> B:
  - POST /api/internal/analyze
  - POST /api/internal/analyze/auto
  - POST /api/internal/analyze/auto/run
  - GET /api/internal/analyze/auto/latest
- A -> C:
  - POST /api/internal/plan
- C -> A callback:
  - POST /api/service3/plans/callback

### User Canonical Endpoints

- B user-facing:
  - GET /api/user/analyze/auto/latest
- C user-facing:
  - POST /api/user/plan
  - GET|POST|PUT|DELETE /api/user/goals

## Auth Boundary

### User-facing

- OIDC Authorization Code flow via Keycloak.
- Callback validation must include:
  - state
  - nonce
  - issuer
  - audience
  - expiry
  - replay prevention
- Identity mapping:
  - users.keycloak_sub is global identity key.
  - local users.id remains domain FK.

### Internal service-to-service

- API key is required on internal endpoints.
- During hybrid phase, legacy aliases may remain, but canonical paths stay internal.

## Ownership Rules

- User endpoints in B and C must reject principal and user_id mismatch with 403.
- User endpoints must scope responses to authenticated principal only.
- Service C user role cannot access other users' goals or plans.

## Real-time Flow Contract

- A collects current user transactions.
- A sends normalized payload to B internal analyze endpoint.
- A maps B output to C planner payload:
  - user_id
  - total_income
  - total_expense
  - top_category
  - insight
  - optional saving_percentage
- A receives C planning output and returns combined summary.

## Periodic Flow Contract

- A exposes incremental feed for B pull sync.
- B stores sync cursor per user (next_since).
- B computes trend and summary and exposes latest payload for C.
- C updates plan and goal recommendation outputs.
- A stores callback records for visibility and audit.

## Non-functional Requirements

- No breaking changes on local user_id relations.
- keycloak_sub uniqueness is enforced per service.
- Upstream failures map to 502 at orchestrator boundaries.
- Backward compatibility endpoints can exist during hybrid rollout only.

## Exit Criteria for Phase 1

- This contract file is approved and referenced by implementation PRs.
- Canonical endpoint list and auth boundary are unchanged unless the contract is revised.
- Every Phase 2+ task traces to one section in this file.
