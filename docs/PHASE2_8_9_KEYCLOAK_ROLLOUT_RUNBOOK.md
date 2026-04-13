# Keycloak Setup, Hybrid Rollout, and Cutover Runbook

Last Updated: 2026-04-14  
Status: Operational Runbook

## Phase 2 - Keycloak Foundation

### Realm and Clients

Create realm: fintech.

Create clients:

- fintrack-web
  - Access type: confidential/public as required by environment.
  - Standard flow enabled (Authorization Code).
  - PKCE required.
  - Redirect URI: <http://127.0.0.1:8001/auth/oidc/callback\>
  - Post logout URI: <http://127.0.0.1:8001\>
- finlyzer-web
  - Redirect URI: <http://127.0.0.1:8002/auth/oidc/callback\>
  - Post logout URI: <http://127.0.0.1:8002\>
- fingoals-web
  - Redirect URI: <http://127.0.0.1:8003/auth/oidc/callback\>
  - Post logout URI: <http://127.0.0.1:8003\>

### Security Controls

- Email verification enabled.
- Brute force protection enabled.
- Refresh token rotation enabled.
- Realm audit events enabled.

### Env Checklist per Service

Set these variables:

- AUTH_MODE
- KEYCLOAK_ENABLED
- KEYCLOAK_BASE_URL
- KEYCLOAK_REALM
- KEYCLOAK_ISSUER
- KEYCLOAK_CLIENT_ID
- KEYCLOAK_CLIENT_SECRET
- KEYCLOAK_REDIRECT_URI
- KEYCLOAK_POST_LOGOUT_REDIRECT_URI

## Phase 8 - Hybrid Rollout (2 Weeks)

### Cohort Progression

- Day 1-2: internal staff only.
- Day 3-4: 25% users.
- Day 5-7: 50% users.
- Day 8-10: 75% users.
- Day 11-14: 100% users.

Order of migration:

- Service A first.
- Service B second.
- Service C third.

### Rollback Rule (Locked)

Rollback trigger:

- login or token error rate > 2%.
- sustained for 15 minutes.

Action:

- stop cohort escalation immediately.
- revert to previous stable cohort.
- keep internal API key flow active while stabilizing.

### Observability Metrics

Track at least:

- OIDC callback error count (state/nonce/issuer/audience/exp/replay failures).
- Token exchange error rate.
- Userinfo fetch error rate.
- Success login rate by service A/B/C.
- SLO for finance summary orchestration A->B->C.

## Phase 9 - Cutover and Hardening

### Cutover Preconditions

- All Verification Gates pass.
- Hybrid cohort at 100% stable for at least 72 hours.
- No unresolved high-severity auth incidents.

### Cutover Actions

- Disable local user-facing auth routes in A/B/C.
- Keep internal service API key contract for pipeline internals.
- Remove legacy fallback endpoints only after stability window.
- Freeze endpoint contract changes for one release cycle.

### Post-cutover Hardening

- Rotate Keycloak client secrets.
- Rotate inter-service API keys.
- Tighten session lifetime and refresh token policy.
- Monitor replay cache hit anomalies.
- Run monthly keycloak_sub data integrity checks.
