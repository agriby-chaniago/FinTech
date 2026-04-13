# Verification Gates Checklist Before Full Cutover

Last Updated: 2026-04-14

## Gate 1 - SSO Continuity A/B/C

Target:

- Login once and access A, B, C without re-login.

Evidence:

- Browser test recording from local/staging.
- Session and token traces for all three services.

## Gate 2 - Single Logout A/B/C

Target:

- Logout from one service invalidates access to all three.

Evidence:

- Logout flow recording.
- Follow-up access attempt returns unauthorized or redirect to OIDC login.

## Gate 3 - Callback Validation

Target:

- Every OIDC callback validates:
  - state
  - nonce
  - issuer
  - audience
  - expiry
  - replay prevention

Evidence:

- Automated tests and manual negative tests for tampered id_token claims.

## Gate 4 - Real-time and Periodic Flow Stability

Target:

- A -> B -> C -> A real-time flow remains successful.
- Monthly periodic flow remains successful.

Evidence:

- Integration smoke tests.
- Error budget dashboards for orchestrator endpoints.

## Gate 5 - Principal Ownership Enforcement

Target:

- User-facing endpoints in B and C reject mismatched user_id with 403.

Evidence:

- Feature tests for mismatch rejection.

## Gate 6 - keycloak_sub Integrity

Target:

- No duplicate keycloak_sub.
- No orphan keycloak_sub mappings in active user accounts.

Evidence:

- DB uniqueness constraints.
- Regular audit query outputs.

## Gate 7 - Test and Validation Suite

Target:

- FinTrack, FinLyzer, FinGoals test suites pass (allowing known non-blocking deprecations).
- Cross-app auth and orchestration integration tests pass.

Evidence:

- CI artifacts and local run logs.
