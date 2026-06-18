# TODO — ConnectBridge Modern UI/UX

## Step 1 — Design system
- [x] Update `assets/css/styles.css` to match required palette, typography (Poppins), spacing grid, components (cards/buttons/glassmorphism), animations, and dark-mode support.
- [x] Update `assets/css/auth.css` to match design system (buttons/cards/inputs), add WCAG-friendly focus/hover, and align to required layout.



## Step 2 — Shared layout
- [ ] Update `views/partials/navbar.php` markup hooks/classes for sticky transparent-on-top → white-on-scroll behavior.
- [ ] Update `assets/css/styles.css` navbar/footer styles to match spec.

## Step 3 — Landing page
- [ ] Refactor `index.php` into required landing page sections: hero + search, categories grid, how it works, featured workers carousel, testimonials, newsletter, footer.
- [ ] Reuse existing verified-workers logic as “Featured Workers” data source.
- [ ] Add required components: search bar, dropdowns, hover animations.

## Step 4 — Auth pages
- [ ] Refactor `login.php` to add required fields/UX: remember me, forgot password, social login icons, inline accessible errors.
- [ ] Refactor `register.php` to match required UX: role selection cards, worker-specific fields (skills/experience/hourly rate/upload ID), improved form styling.

## Step 5 — Missing pages (UI templates)
- [ ] Create `services.php` (category grid) and `contact.php` (contact form + layout).
- [ ] Create `user_dashboard.php`, `service_listing.php`, `worker_profile.php`, and `payment.php`.
- [ ] Redesign existing pages if required for consistency: `worker_dashboard.php`, `admin_dashboard.php`, `booking.php`, `booking_history.php`, `worker_details.php`, `notifications.php`.

## Step 6 — UI components
- [ ] Implement stepper UI for `booking.php` (date → time → address → payment → confirmation).
- [ ] Implement charts placeholders for dashboards (CSS/SVG/Canvas without external libs).
- [ ] Add skeleton loaders, toast notifications, modals, dropdown/search/pagination styles.
- [ ] Implement smooth page animations using CSS + IntersectionObserver.

## Step 7 — QA
- [ ] Responsive check: desktop/tablet/mobile breakpoints.
- [ ] Accessibility pass: labels, focus states, aria attributes, contrast.
- [ ] Smoke test: forms submit + page navigation works on XAMPP.

