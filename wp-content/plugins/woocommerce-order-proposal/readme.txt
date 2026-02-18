= 2.0.5 (2024-06-26) =
- New: filter My Account Proposal actions `wpo_wcpdf_myaccount_proposal_actions`
- Tweak: improvements in `load_updater` method
- Fix: proposal document Bulk Action and Cloud Upload (using Professional extension)
- Translations: updated POT
- Tested up to WooCommerce 9.0 & WordPress 6.6

= 2.0.4 (2024-04-24) =
- Fix: call to a member function `wcml_get_translated_email_string()` on `null`
- Fix: bug on `wc_order_proposal_format_date()` type hint
- Fix: Proposal valid from field is incorrect depending on selected language
- Translations: updated POT
- Tested up to WooCommerce 8.8

= 2.0.3 (2024-03-20) =
- New: bumps PDF plugin min version to 3.8.0
- New: bumps WC min version to 3.3
- New: dependency checker class
- Fix: warnings with PHP 8.2 and added type hinting for properties
- Fix: display number in templates when using Order Number
- Translations: updated POT
- Tested up to WooCommerce 8.7 & WordPress 6.5

= 2.0.2 (2024-02-05) =
- New: bumps PHP minimum version to `7.2`
- Tweak: removes legacy imports
- Tweak: template markup improvements
- Fix: numbering issue and improved number setting
- Fix: mPDF footer issues
- Fix: issue with email translations when using WPML
- Translations: updated POT
- Tested up to WooCommerce 8.5

= 2.0.1 (2023-12-06) =
- New: bumps PDF base plugin minimum version to `3.7.3`
- New: filter hook: `wpo_wcop_payment_options_text`
- New: adds notice if WooCommerce is not activated
- Tweak: updates the number/date/title document functions in the PDF templates
- Tweak: move settings from WooCommerce general settings to a new tab
- Fix: bug with call to undefined function `wcpdf_get_document()` when the PDF Invoices plugin is not activated
- Translations: updated Spanish
- Translations: updated POT
- Updated bundled license manager/updater
- Tested up to WooCommerce 8.4 & WordPress 6.4

= 2.0.0 (2023-07-10) =
- New: WooCommerce HPOS compatibility (beta)
- Tweak: adds order and email objects as arguments in `woocommerce_order_proposal_send_payment_link` hook 
- Fix: bug on stock increasing for cancelled orders
- Tested up to WooCommerce 7.8

= 1.9.1 (2023-04-06) =
- Add `bacs` & `cheque` instructions to email templates
- Tested up to WooCommerce 7.6 & WordPress 6.2

= 1.9.0 (2022-12-07) =
- New: WC Order Email: Order confirmation
- Tweak: removes legacy WC versions compatibility classes
- Tweak: improved templates following main free PDF plugin current standards
- Fix: loads PDF template type from WCPDF plugin or fallback to Simple
- Updated bundled license manager/updater
- Tested up to WooCommerce 7.1 & WordPress 6.1

= 1.8.1 (2022-06-16) =
- New: before document label hook: wpo_wcpdf_before_document_label
- Tweak: fill in the document icon sheet with white
- Fix: labels for order confirmation metabox
- Fix: suppress PHP8.1 incompatible return type notice
- Updated bundled license manager/updater
- Tested up to WooCommerce 6.6

= 1.8.0 =
- New: Allow license activation directly via bundled updater (optional)
- New: Display the Proposal notes in the templates
- New: Email subject tag {proposal_number}
- New: Update PDF action icons to match PDF Invoice styling
- New: Show a confirmation dialog to the user when clicking the decline proposal link in their account
- Fix: Added missing template action hooks for PDF documents
- Fix: Shipping address setting for PDF document (Simple Premium template)
- Fix: Settings hints for order confirmation and order proposal numbers
- Fix: Plugin header information
- Fix: Plugin instantiation
- Fix: Prevent plugin crashing when PDF Invoices is not installed/activated
- Translations: Improved string internationalization
- Translations: Added translation template and update Spanish, French, Dutch, German & Portuguese
- Tested up to WooCommerce 5.9

= 1.7.19 =
- PDF Custom Order Confirmation Number
- Option to always show the Proposal PDF

= 1.7.18 =
- PDF Custom Proposal Number

= 1.7.17 =
- Gateway filter add more arguments

= 1.7.16 =
- Filter to change gateway status and message

= 1.7.15 =
- Fix do not reduce stock on Woocommerce 4.7

= 1.7.14 =
- Fix for guests with auto login enabled
- Do not enable auto login by default

= 1.7.13 =
- Pay Page allow for payment with auto Login

= 1.7.12 =
- PDF Fix for Simple and Business Template

= 1.7.11 =
- Fix PDF Download button string in my account
- WPML translation fixes for emails and PDFs

= 1.7.10 =
- Use wc_date_format

= 1.7.9 =
- French Translation Fix

= 1.7.8 =
- French Translation

= 1.7.7 =
- Fix increase order stock function

= 1.7.6 =
- Fix preventing stock reduction on WC 3.8

= 1.7.5 =
- Fix preventing stock reduction

= 1.7.4 =
- Trigger Admin Email on Proposal Accept

= 1.7.3 =
- Only include files when Woocommerce is available

= 1.7.2 =
- Fix stock reduction on the payment gateway

= 1.7.1 =
- PDF Template fix

= 1.7.0 =
- Workaround so proposals stock items are not reduced in WC 3.6
- Send order proposal in order even when disabled

= 1.6.11 =
- Also timeout order proposal requests

= 1.6.10 =
- Fix stock not beeing added back on cancellation
- Add compatibility warning for WC Quotation Quote
- Code cleanups
- Email Template Fix if the template is overwritten
- Compatibility Check

= 1.6.9 =
- PDF Template align with PDF package

= 1.6.8 =
- Hide Order Proposal Payment Gateway on Account Pay page

= 1.6.7 =
- PDF compatibility fix for new version

= 1.6.6 =
- Fix email action hook
- Fix PDF template title

= 1.6.5 =
- Payment Gateway only reserver stock if enabled in the global options
- Make Order Proposal a valid complete payment status

= 1.6.4 =
- PDF Template fixes
- Rename Proposal data save button
- Fix for adding order statuses

= 1.6.3 =
- Fix wrong order proposal end time on payment gateway
- Fix email template admin can not be found for preview
- Fix error when PDF invoice template does not match order proposal template

= 1.6.2 =
- Dutch translation

= 1.6.1 =
- Fix possible error on thank you page
- Hardening of the Order Proposal Requested Status check

= 1.6.0 =
- Keep Payment Method title on Gateway
- Fix WPML Email Bugfix
- Add Order Proposal Requested Status for the Payment Gateway

= 1.5.1 =
- Email fixes

= 1.5.0 =
- Fix Order Confirmation PDF screen
- Fix PDF Missing text for proposal payment gateway
- Add seperate Email for Proposal Payment Gateway
- PDF Footer override
- Make Payment Gateways in PDF optional
- Email class fixes
- Email translation fixes
- Fix query prefix being fixed

= 1.4.7 =
- Order screen WC 3.3 fix (VSH-306)

= 1.4.6 =
- Date Format fix for WC Version >= 3.0.0 (VSH-298)
- Added Order Proposal On Hold Email Action (VSH-302)
- Add Spanish

= 1.4.5 =
- Order Proposal Email in Order Action WC 3.2 (VSH-292)
- Order Proposal Email on Order Payment (VSH-293)

= 1.4.4 =
- Add proposal end date to proposal pdf (VSH-290)
- Remove Closing php tags (VSH-291)

= 1.4.3 =
- Add formal German
- Remove language string variable

= 1.4.2 =
- Fix Language File Name

= 1.4.1 =
- Template fix (VSH-283)

= 1.4 =
- Text Domain DE (VSH-48)
- Template Fixes (VSH-250)
- PDF Defaults (VSH-271)
- Reload Text Domain on PDF Creation (VSH-277)
- Integration into PDF Customizer (VSH-278)
- Compatibility with WPML Country Limiter (VSH-279)
- Make Default Option for require prepayment PDF text (VSH-280)

= 1.3 =
- Add PDF 2.0 Template Support

= 1.2.1 =
- Bugfixes for Payment Gateway
- Bugfix for Stock Reservation

= 1.2 =
- Hide Login Redirect for payment order for WC >= 3.1 (VSH-214)
- Global Option to hide cancelled proposals (VSH-128)
- Add Order Proposal as payment method (VSH-190)
- Add Premium PDF Templates (VSH-219)
- Stock in normal orders is not reduced when Proposal Stock Reservation is on (VSH-223)

= 1.1.1 =
- Don't deduct stock when reduce order stock was used (VSH-183)
- small bugfixes

= 1.1 =
- version number fix
- Reserve Stock Option (VSH-155)
- Email Order Payment Link (VSH-154)
- Order Time not changed correctly after Proposal acceptance (VSH-178)

= 1.0  =
- Inital Release
