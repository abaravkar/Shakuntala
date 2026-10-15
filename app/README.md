# Shakuntala Rabdi Mastani

Laravel 12 + MySQL + Node/Vite website, built from the supplied 9-page ZIP and the shared project conversation on 11 October 2026.

## Run locally

Requirements: PHP 8.2+, Composer, MySQL 8+, Node 22.12+ (Node 24 works). PHP needs PDO MySQL, mbstring, OpenSSL, fileinfo, XML and the normal Laravel extensions. SQLite is used only for isolated automated tests.

```powershell
cd F:\laravel\Shakuntala\app
composer install
npm ci
Copy-Item .env.example .env # only on a new checkout; keep the existing local .env
php artisan key:generate
```

Create an empty MySQL database named `shakuntala` with `utf8mb4_unicode_ci`, and set its host, port, username and password in `.env`. The current local installation has already been migrated and seeded on MySQL 8.4.7. The example root/no-password credentials are for this local environment; use a dedicated database account for deployment.

```powershell
php artisan migrate --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://127.0.0.1:8000. For frontend development, run `npm run dev` in a second terminal. Node provides the Vite asset build and development server; Laravel serves the application and connects to MySQL.

## Administrator

```powershell
php artisan shakuntala:admin your-email@example.com
```

Enter a password of at least 12 characters at the hidden prompt, then open `/admin/login`. No default admin account or shared password is installed. The administrator can manage multilingual product categories, branches, blog articles, gallery assets and social links; review applications; and update inquiry statuses. Images use filenames in `public/assets` (uploads are not implemented). Use draft/closed status to hide content. Seeding is idempotent and does not overwrite content edits.

## Public pages

Home, About, Menu, Locations, Franchise, Distribution, Moments, Blog, Contact, Social channels, Instagram gallery, detailed Franchise application, Agreement stage preview, Terms, Privacy and Refund: 16 pages.

Marathi is the default. The language toggle supports Hindi and English, remembers the choice between pages and preserves form answers when switched. Existing brand assets, homepage celebration effects and reduced-motion controls are retained. Some original prototype explanatory text and server validation messages remain in English and should receive final editorial review.

## Database-backed flows

- Contact, franchise inquiry and distribution inquiry store validated details, contact consent time, locale and a UUID receipt.
- Detailed applications capture contact details, company/trade name, GST registration, shop ownership, area, address, Google Maps URL, investment range and experience.
- Applications are stored as `awaiting_payment`, with `payment_status=pending` and server-controlled fee of 500000 paise (₹5,000). The client cannot supply a payment status, approval or signed state.
- The review step contains Terms, Privacy and Refund links. These links are absent from the main navigation and footer, as requested.
- CSRF protection, request throttling, a honeypot, server validation, escaped output and administrator authorization are included.

## Pending integrations and content decisions

Offline payment claims and administrator verification are implemented. Razorpay application-specific payment links and signed webhooks are implemented, with provider credentials still required. Refund execution, OTP delivery and agreement signing are not connected. Legal terms remain drafts for business review.

The Instagram page uses supplied images; no automatic API feed is claimed. Official social account URLs can be entered and published in the administrator. No unrelated company's demo links are used. Outbound email/SMS notifications are not configured; enquiries are reviewed in the admin area.

Eight outlets are marked reported, three planned, Dhanori unverified, and Phaltan/Indapur closed. Closed outlets are hidden. Prices are indicative starting prices from the supplied prototype. Legal pages are multilingual business-review drafts, not approved policies. Blog copy starts in draft. Confirm exact menu items, prices, active branches, rights to photography, legal identity, official contact channels, refund conditions and final translations before launch.

## Verify

```powershell
php artisan test
npm run build
php artisan view:cache
```

Tests cover all pages, legal-link placement, branch visibility, validated storage, consent, spam protection, rate limits, GST/maps validation, server-owned payment state, admin authentication/authorization and content publishing. Tests use an in-memory SQLite database and never reset the local MySQL database.

Design and system polish completed on 11 October 2026: dedicated-page CSS restored, duplicated styles removed, hero clipping fixed, flavour cards wrapped into a responsive grid, contact section rebuilt with a live form, shared footer improved, product image framing adjusted, and gallery/branch visibility connected consistently to the CMS. Published products/gallery entries require images, products require prices, and social entries require links.

Verification: 12 automated tests / 103 assertions passed; production Vite build and Blade compilation passed. All 16 public pages were checked in the browser at 1440px, 768px and 390px for horizontal overflow and broken images. Mobile wizard validation, retained language-switch values, conditional GST fields and pending-payment/legal links were checked. A real browser contact submission was verified in MySQL 8.4.7 and the exact temporary record was removed afterward.

## Deploy later

Point the web server at `public`, use HTTPS, set `APP_ENV=production`, `APP_DEBUG=false`, the correct `APP_URL`, secure session cookies and dedicated MySQL credentials. Generate a fresh app key, run migrations, build Vite assets and create your admin account. Restrict database access and back up application records. Complete the pending provider integrations and business approvals before accepting payments or signing agreements.

The original ZIP is kept unchanged in the parent folder and extracted reference files are in `../reference`. No hosting or Google Drive changes were made.

Partner flow follow-up: prominent application links on Franchise, Distribution, Contact and the shared footer; distribution contact/territory/review wizard at /distribution/application; onboarding stages at /partners/onboarding. Distribution submissions use the validated inquiry storage and UUID receipt. Payments and OTP signing remain pending provider configuration. Verification: 13 tests / 120 assertions passed, production assets built, Blade compiled, and six partner routes returned HTTP 200. Browser automation was blocked by its URL policy; the latest changes have server-side and HTTP verification only.

## Application payment workflow

Google Maps is optional. The franchise fee remains server-controlled at ₹5,000. Bank transfer, UPI, QR and shared Razorpay-link claims collect transaction ID, amount, date, payer, optional bank/app, notes and a private receipt (PDF/PNG/JPEG, up to 5 MB). Claims wait for an administrator; screenshots never verify payment automatically. The admin verifies receipt of funds or rejects with a note. A verified payment unlocks application review. Application approval moves to agreement_pending, not a signed or active franchise. Audit entries and payment history are retained. Distribution retains its no-fee inquiry workflow pending confirmation of its fee policy.

Payment instructions: /admin/payment-settings stores bank details, UPI ID, QR upload and an optional shared Razorpay payment URL. No real receiving details were supplied, so the UI displays setup placeholders until configured. Shared-link payments require manual verification using their payment ID.

Automatic online payment: set RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET and RAZORPAY_WEBHOOK_SECRET in .env. The applicant saves the application then requests a unique Razorpay Payment Link. No applicant contact details are sent while creating the link; its reference is the application UUID. In Razorpay configure payment_link.paid to POST to the public HTTPS /payments/razorpay/webhook URL. Signature, application reference/link, captured status, INR and exact amount are checked before unlocking review. Retries are idempotent. Returning from a payment page or submitting a client status never marks payment verified. Localhost cannot receive public webhooks; use Razorpay test mode and a configured accessible environment for live provider testing. Tests use mocked provider responses; no actual payment was made.

After saving, the applicant receives a signed private status link valid for 30 days. Keep it private. The page allows submitting missing/corrected transaction details, reports pending/verified status, and shows the next review stage. Transaction proof downloads are administrator-only. Never include storage/app/private or real .env data in distributed ZIPs.

Payment workflow verification: 20 tests / 192 assertions passed before final copy refinements, including optional Maps, claim validation, exact fees, duplicate transactions, payment and application review gates, private proof authorization, signed status access, rejection/resubmission, unique online links and signed/idempotent webhook confirmation. MySQL migration applied and assets compiled. Bank, UPI, QR, Razorpay credentials and approved agreement/OTP provider configuration remain to be supplied.
Final verification: 20 tests / 192 assertions passed after copy refinements. The browser successfully reached the payment step with Maps blank, displayed the conditional offline fields, and hid/disabled them for online payment. Browser preview values were not submitted.

Application draft continuity: the current wizard step and non-file inputs are saved in tab-session storage for up to 24 hours. Returning from legal pages via Back or their application link, and refreshing the wizard, restore the draft. The draft clears after successful submission. CSRF tokens and file data are not stored. A receipt must be reattached after a full reload; the original page retains the file when a legal page opens separately. Browser navigation, reload, and privacy-return checks passed using unsubmitted preview data.

### Franchise model CMS
`/admin/franchise-models` manages the four formats imported from the handwritten notes: Cart A, Cart B, Kiosk and Ice Cream Parlour. Admins can add/edit models, change space, estimated investment, product lines, cost lines (`Item | Amount`), notes, order and published/draft visibility. The public `/franchise` page reads published database models directly. Migration `2026_10_11_000003` installs the starting content once without overwriting future CMS edits. Costs are setup estimates separate from the application fee; unresolved dimensions and totals are explicitly noted. Small distributor terms were not supplied and are not invented.
Verified: full suite 22 tests / 214 assertions, production Vite build, Blade compilation, 1421px desktop and 390px mobile (no horizontal overflow), and expandable cost breakdown. CMS model details currently use English text with multilingual section labels.

Official identity images supplied by the owner: assets/favicon.png (browser icon), assets/website-logo.png (header/footer), assets/brand-logo.png (home story/About artwork). Original PNG files are preserved.

### Instagram API feed
Official account: @shakuntala_rabadi_mastani. `/admin/instagram` reports connection status and supports protected manual sync. Configure Instagram API with Instagram Login for a professional account; set a long-lived `INSTAGRAM_ACCESS_TOKEN` privately on the server, with `instagram_business_basic` permission. No password, token or app secret belongs in chat or the ZIP. `php artisan instagram:sync` validates the account username before caching up to 12 posts for 24 hours. The hourly Laravel scheduled command requires the host to run `php artisan schedule:run` each minute. Tokens require renewal when expired. Live connectivity is pending owner authorization; only mocked provider tests have been run. Missing credentials/API failure leave the CMS gallery available; a recent successful cache is retained on transient failure. Video cards use thumbnails linking to Instagram. No posting/comment/messaging permissions are requested.

Flavour carousel: custom SVG ingredient artwork replaces emoji on Menu and Home; continuous 18px/s loop, hover/focus pause, manual controls and touch swiping. Reduced-motion preference disables autoplay. Verified desktop/mobile overflow, arrow/pause behavior and Marathi labels.
