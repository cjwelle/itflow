# JustTechs domain-based onboarding

## Goal

Allow a person authenticated by an external identity provider to enter the
client portal without an administrator manually creating a contact first.

1. A verified corporate email domain that has already been claimed by a client
   maps the person to that client.
2. An unclaimed verified corporate domain starts a new client onboarding flow.
3. The first person supplies the company name and becomes the initial client
   contact for that client.

## Identity and tenancy policy

* Provider identity is identified by immutable issuer + subject, never email
  alone. The provider's verified email is a secondary attribute.
* Google is eligible for domain-based onboarding only when its ID token has
  `email_verified=true` and supplies a non-personal email domain.
* Apple Sign in with Apple may provide a private relay address and only sends
  the email on first authorization. It may sign in an existing, explicitly
  linked account, but it must not create or choose a tenant from the returned
  email domain.
* A domain claim requires a DNS TXT challenge before it becomes authoritative
  for automatic client matching. Existing ITFlow `domains` rows are inventory,
  not proof of control, and must not silently grant tenant access.
* New clients created from self-service onboarding are marked as leads and are
  scoped to their own client portal data. They receive no staff/agent role,
  no client-to-client access, and no automatic billing or privileged service.
* `justtechs.com` is a reserved staff domain. Its identities are routed to the
  tech backend and are never eligible for customer self-service onboarding or
  customer tenant assignment.
* Email matching is case-insensitive and each identity maps to exactly one
  ITFlow user/contact. The flow never changes an existing contact's client.

## Proposed data model

`identity_provider_accounts`

* `identity_provider_account_id`
* `provider` (`google` or `apple`)
* `issuer`
* `subject`
* `user_id`
* `verified_email` (nullable for Apple relay/private-email cases)
* `created_at`, `last_login_at`, `revoked_at`

Unique index: `(issuer, subject)`.

`client_domain_claims`

* `client_domain_claim_id`
* `domain_name` (normalized registrable domain)
* `client_id`
* `verification_token`
* `verified_at`, `created_at`, `revoked_at`

Unique active claim: `domain_name`.

## Onboarding flow

1. The user selects Google or Apple on the client portal login page.
2. The callback validates OIDC state, nonce, PKCE, issuer, audience, signature,
   expiry, and verified email where applicable.
3. A known issuer/subject signs into its linked client contact.
4. A Google user with an existing verified domain claim is offered a contact
   creation/association flow for that claim's client; the operation is
   rate-limited and logged.
5. A Google user with no claim enters company name. The application creates a
   lead client and its initial contact, then requires DNS TXT verification
   before additional members of that domain can join automatically.
6. Apple users without an existing linked account must verify a corporate email
   through the Google path or a separate one-time email challenge before they
   can link to a client.

## Non-goals

* Passwordless staff/agent SSO. Staff-specific encryption keys are currently
  password-derived, so this requires a separate approved key-management design.
* Trusting a domain merely because a user typed it, or because it is present in
  the ITFlow domain inventory.
