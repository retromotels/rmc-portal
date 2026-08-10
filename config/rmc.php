<?php
/*
|--------------------------------------------------------------------------
| Retro Motel Collective — portal configuration
|--------------------------------------------------------------------------
| Central source of truth for room bands, tiers, the A–H registration
| sections and the three policy documents. Views and controllers read from
| here so the whole portal stays data-driven.
*/

return [

    'onboard_fee' => 1500,

    // Where new-signup + health-check alerts go, and the from address for outgoing mail.
    'admin_emails' => ['jeremy@retromotels.com', 'luke@retromotels.com'],
    'mail_from'    => ['address' => env('MAIL_FROM_ADDRESS', 'hello@retromotels.com'), 'name' => env('MAIL_FROM_NAME', 'Retro Motel Collective')],
    'mail_reply_to' => ['address' => env('MAIL_REPLY_TO', 'jeremy@retromotels.com'), 'name' => env('MAIL_FROM_NAME', 'Retro Motel Collective')],
    'pending_reminder_days' => 7,

    /*
    | Live sending via SendGrid. Set RMC_MAIL_LIVE=true + SENDGRID_API_KEY in
    | .env to actually send (otherwise messages just queue in the Outbox).
    | Per-type SendGrid dynamic-template IDs make the emails editable in the
    | SendGrid dashboard; when a template id is blank the rendered HTML is sent.
    */
    'mail_live' => env('RMC_MAIL_LIVE', false),
    'sendgrid'  => [
        'key'       => env('SENDGRID_API_KEY'),
        // Live SendGrid dynamic-template IDs (created in the SendGrid dashboard,
        // editable there). Not secret, so kept as defaults; override via .env if needed.
        'templates' => [
            'welcome'          => env('SG_TPL_WELCOME', 'd-4faeea60f20948e58e4368d2eea17c94'),
            'admin_new_signup' => env('SG_TPL_ADMIN_SIGNUP', 'd-960e8e2a8de74d12846f968bb801c7de'),
            'pending_reminder' => env('SG_TPL_PENDING', 'd-d3d6915aca7d47e59ad93221437c365e'),
            'password_reset'   => env('SG_TPL_RESET', 'd-bb124276de0344d0819e1d0919104523'),
            'health_request'   => env('SG_TPL_HEALTH', 'd-a767337c59ef42628406c3eb20682b49'),
        ],
    ],
    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    // Requestable health-check services (the website check is interactive, separate).
    'health_requests' => [
        'ota'     => ['label' => 'OTA health check', 'blurb' => 'A free review of your Booking.com / Expedia listings against best practice.', 'icon' => '🏨'],
        'seo'     => ['label' => 'SEO health check', 'blurb' => 'We review your website’s search visibility and send back plain-English feedback.', 'icon' => '🔍'],
        'gmb'     => ['label' => 'Google My Business audit', 'blurb' => 'A check of your Google Business Profile — accuracy, photos, reviews and posts.', 'icon' => '📍'],
        'reviews' => ['label' => 'Review ranking check', 'blurb' => 'How your guest reviews and ratings compare, and how to lift them.', 'icon' => '⭐'],
        'social'  => ['label' => 'Social / web content package', 'blurb' => 'A tailored plan for social posts and website content to keep bookings coming.', 'icon' => '📣'],
    ],

    // Founding-member discount (toggle from the admin later if you wish)
    'founding' => ['active' => true, 'pct' => 50],

    /*
    | Microsite themes for the admin Site Builder. Each is an "inspired" style
    | (palette / typography / layout feel) named after the reference site.
    */
    'site_themes' => [
        'seasea' => ['label' => 'Sea Sea', 'ref' => 'https://seaseahotel.com/', 'blurb' => 'Coastal · airy · elegant serif', 'accent' => '#2f6f7e', 'sand' => '#f3ede2'],
        'surf'   => ['label' => 'Surf Hotel', 'ref' => 'https://www.surfhotel.com.au/', 'blurb' => 'Bright · retro-surf · warm', 'accent' => '#e2683c', 'sand' => '#fbf4e9'],
        'roy'    => ['label' => 'The Roy', 'ref' => 'https://www.theroy.com.au/', 'blurb' => 'Boutique · moody · refined', 'accent' => '#25332b', 'sand' => '#efe9df'],
        'capon'  => ['label' => 'Capon Cottage', 'ref' => 'https://retromotels.com/capon-cottage-site/', 'blurb' => 'Cosy · heritage · handcrafted', 'accent' => '#8a5a3c', 'sand' => '#f6efe4'],
    ],

    /*
    | Booking.com listing-quality checklist for the admin analyzer. Grouped
    | best-practice items. An 'auto' key means the analyzer can pre-tick it
    | from scraped data when the listing page is readable.
    */
    'listing_checklist' => [
        'Photos & imagery' => [
            ['key' => 'photos_present', 'label' => 'Listing has photos uploaded', 'auto' => 'photos_present'],
            ['key' => 'photos_count', 'label' => 'At least 24 high-quality photos', 'hint' => 'Booking.com rewards 24+ photos with better ranking and conversion.', 'auto' => 'photos_count'],
            ['key' => 'photo_exterior', 'label' => 'Exterior / facade photo included'],
            ['key' => 'photo_rooms', 'label' => 'Every room type is photographed'],
            ['key' => 'photo_bathroom', 'label' => 'Bathroom photos included'],
            ['key' => 'photo_amenities', 'label' => 'Key amenities shown (pool, dining, parking…)'],
            ['key' => 'photo_hires', 'label' => 'Photos are high-resolution & landscape (min 2048px)'],
            ['key' => 'photo_clean', 'label' => 'No watermarks, text overlays or collages'],
        ],
        'Property content' => [
            ['key' => 'name_ok', 'label' => 'Property name is correct & consistent', 'auto' => 'name_present'],
            ['key' => 'desc_present', 'label' => 'Property description is complete', 'auto' => 'description_present'],
            ['key' => 'desc_usp', 'label' => 'Description highlights unique selling points'],
            ['key' => 'desc_area', 'label' => 'Nearby landmarks & transport are described'],
            ['key' => 'property_type', 'label' => 'Property type / category is accurate'],
            ['key' => 'house_rules', 'label' => 'House rules are clear (smoking, parties, quiet hours)'],
        ],
        'Rooms & rates' => [
            ['key' => 'room_types', 'label' => 'All room types are configured'],
            ['key' => 'occupancy', 'label' => 'Occupancy & bed configuration are correct'],
            ['key' => 'rates_loaded', 'label' => 'Rates & availability loaded 12–24 months ahead'],
            ['key' => 'rate_plans', 'label' => 'Multiple rate plans (flexible + non-refundable)'],
            ['key' => 'promotions', 'label' => 'At least one active deal / promotion'],
            ['key' => 'price_parity', 'label' => 'Rates in parity with other channels & direct'],
            ['key' => 'price_visible', 'label' => 'A live price is showing on the listing', 'auto' => 'price_visible'],
        ],
        'Facilities & amenities' => [
            ['key' => 'facilities_complete', 'label' => 'All facilities / amenities ticked accurately'],
            ['key' => 'wifi', 'label' => 'Wi-Fi details set (free/paid, coverage)'],
            ['key' => 'parking', 'label' => 'Parking information set'],
            ['key' => 'breakfast', 'label' => 'Breakfast / meal options configured'],
            ['key' => 'checkinout', 'label' => 'Check-in / check-out times set'],
            ['key' => 'accessibility', 'label' => 'Accessibility features listed (if applicable)'],
        ],
        'Policies & setup' => [
            ['key' => 'cancellation', 'label' => 'Cancellation policy is set'],
            ['key' => 'payment', 'label' => 'Payment / prepayment options configured'],
            ['key' => 'children_beds', 'label' => 'Children & extra-bed policy set'],
            ['key' => 'pets', 'label' => 'Pet policy set'],
            ['key' => 'taxes', 'label' => 'Taxes & fees configured correctly'],
            ['key' => 'licence', 'label' => 'Licence / registration number added (if required)'],
        ],
        'Location' => [
            ['key' => 'map_pin', 'label' => 'Map pin is accurate', 'auto' => 'address_present'],
            ['key' => 'address', 'label' => 'Full address is complete'],
            ['key' => 'landmarks', 'label' => 'Nearby attractions & distances listed'],
        ],
        'Reviews & performance' => [
            ['key' => 'review_present', 'label' => 'A guest review score is showing', 'auto' => 'review_present'],
            ['key' => 'review_good', 'label' => 'Review score is 8.0+ (or trending up)', 'auto' => 'review_good'],
            ['key' => 'review_replies', 'label' => 'Recent guest reviews have been responded to'],
            ['key' => 'response_time', 'label' => 'Message response time is fast (under 1 day)'],
            ['key' => 'content_score', 'label' => 'Booking.com content / quality score is high'],
            ['key' => 'genius', 'label' => 'Enrolled in the Genius programme (if suitable)'],
        ],
    ],

    // Room bands, derived from total rooms
    'bands' => [
        'small' => ['label' => 'Up to 18 rooms', 'rooms' => '≤18', 'rev' => 62278, 'price' => ['standard' => 625, 'growth' => 995, 'full' => 1550]],
        'mid'   => ['label' => '19 – 35 rooms', 'rooms' => '19–35', 'rev' => 124556, 'price' => ['standard' => 1250, 'growth' => 1995, 'full' => 3100]],
        'large' => ['label' => '36+ rooms', 'rooms' => '36+', 'rev' => 186834, 'price' => ['standard' => 1850, 'growth' => 2995, 'full' => 4650]],
    ],

    'tiers' => [
        'standard' => ['name' => 'Standard', 'tag' => '~1.0% of revenue', 'pitch' => 'Collective buying power, benchmarking and community — flat and predictable.'],
        'growth'   => ['name' => 'Growth', 'tag' => '~1.6% of revenue', 'pitch' => 'Everything in Standard plus the take-work-off-my-plate services.'],
        'full'     => ['name' => 'Full Package', 'tag' => '~2.5% of revenue', 'pitch' => 'The managed-service flagship — full reviews, budgets and planning.'],
    ],

    // Auto-select tier from band
    'band_tier' => ['small' => 'standard', 'mid' => 'growth', 'large' => 'full'],

    /*
    | Registration sections. `signup => true` means it is collected during the
    | complete-your-details flow; the rest (C, D, E, G, H) are dashboard tasks.
    | Field types: text, number, textarea, select, yn, file.
    */
    'sections' => [
        'A' => ['title' => 'Property profile', 'icon' => '🏨', 'signup' => true, 'fields' => [
            ['id' => 'propertyName', 'label' => 'Property name', 'type' => 'text', 'req' => true],
            ['id' => 'address', 'label' => 'Street address', 'type' => 'text', 'req' => true],
            ['id' => 'city', 'label' => 'City / town', 'type' => 'text', 'req' => true, 'half' => true],
            ['id' => 'state', 'label' => 'State', 'type' => 'text', 'req' => true, 'half' => true],
            ['id' => 'postcode', 'label' => 'Postcode', 'type' => 'text', 'req' => true, 'half' => true],
            ['id' => 'totalRooms', 'label' => 'Total rooms / units', 'type' => 'number', 'req' => true, 'half' => true],
            ['id' => 'roomTypes', 'label' => 'Room types & bed configs', 'type' => 'textarea', 'req' => true, 'ph' => 'e.g. 4x Queen, 6x Twin, 2x Family'],
            ['id' => 'pool', 'label' => 'Pool on site?', 'type' => 'yn', 'req' => true, 'half' => true],
            ['id' => 'propertyType', 'label' => 'Property type', 'type' => 'select', 'options' => ['Motel', 'Motor inn', 'Apartments', 'B&B', 'Cabins', 'Other'], 'half' => true],
            ['id' => 'checkIn', 'label' => 'Check-in time', 'type' => 'text', 'half' => true],
            ['id' => 'checkOut', 'label' => 'Check-out time', 'type' => 'text', 'half' => true],
            ['id' => 'website', 'label' => 'Website URL', 'type' => 'text'],
            ['id' => 'otaLinks', 'label' => 'Existing OTA listing links', 'type' => 'textarea'],
        ]],
        'B' => ['title' => 'Contacts & ownership', 'icon' => '👤', 'signup' => true, 'fields' => [
            ['id' => 'ownerName', 'label' => 'Owner name', 'type' => 'text', 'req' => true, 'half' => true],
            ['id' => 'legalEntity', 'label' => 'Legal entity', 'type' => 'text', 'half' => true],
            ['id' => 'abn', 'label' => 'ABN', 'type' => 'text', 'half' => true],
            ['id' => 'gstStatus', 'label' => 'GST status', 'type' => 'select', 'options' => ['Registered for GST', 'Not registered'], 'half' => true],
            ['id' => 'managerName', 'label' => 'Day-to-day manager', 'type' => 'text', 'half' => true],
            ['id' => 'managerEmail', 'label' => 'Manager email', 'type' => 'text', 'half' => true],
            ['id' => 'managerMobile', 'label' => 'Manager mobile', 'type' => 'text', 'half' => true],
            ['id' => 'ownershipModel', 'label' => 'Ownership model', 'type' => 'select', 'options' => ['Owner-operated', 'Managed', 'Leased'], 'half' => true],
            ['id' => 'escalationContact', 'label' => 'Escalation contact', 'type' => 'text', 'half' => true],
            ['id' => 'escalationHours', 'label' => 'Escalation hours', 'type' => 'text', 'half' => true],
        ]],
        'C' => ['title' => 'Electricity', 'icon' => '⚡', 'priority' => true, 'note' => 'Procurement — feeds the group electricity tender.', 'fields' => [
            ['id' => 'bill', 'label' => 'Most recent electricity bill (PDF)', 'type' => 'file'],
            ['id' => 'currentRetailer', 'label' => 'Current retailer', 'type' => 'text', 'req' => true, 'half' => true],
            ['id' => 'contractEnd', 'label' => 'Contract end date (if known)', 'type' => 'text', 'half' => true],
            ['id' => 'knowsTariff', 'label' => 'Do you know your tariff?', 'type' => 'select', 'options' => ['Yes', 'No', 'Unsure'], 'half' => true],
            ['id' => 'tariffDetail', 'label' => 'Tariff (if known)', 'type' => 'text', 'half' => true],
            ['id' => 'solar', 'label' => 'Solar on site?', 'type' => 'yn', 'half' => true],
            ['id' => 'interestSolarEV', 'label' => 'Interested in solar / EV charging?', 'type' => 'yn', 'half' => true],
        ]],
        'D' => ['title' => 'Software & technology', 'icon' => '💻', 'priority' => true, 'note' => 'Procurement — real spend data for the tech tender.', 'fields' => [
            ['id' => 'systems', 'label' => 'Software & systems you use', 'type' => 'textarea', 'req' => true, 'ph' => 'PMS, channel manager, booking engine, payments, accounting, Wi-Fi, phone'],
            ['id' => 'invoices', 'label' => 'Recent software invoice per major system', 'type' => 'file', 'multi' => true],
            ['id' => 'contractEnds', 'label' => 'Contract end dates / notice periods', 'type' => 'textarea'],
            ['id' => 'integration', 'label' => 'Preferred integration method', 'type' => 'select', 'options' => ['API', 'iCal', 'Channel manager', 'Manual']],
        ]],
        'E' => ['title' => 'Insurance', 'icon' => '🛡️', 'priority' => true, 'note' => 'Procurement — brokers need claims history for portfolio quotes.', 'fields' => [
            ['id' => 'certificate', 'label' => 'Certificate of currency', 'type' => 'file'],
            ['id' => 'claims5', 'label' => 'Any claims in the last 5 years?', 'type' => 'yn', 'req' => true, 'half' => true],
            ['id' => 'insurer', 'label' => 'Current insurer', 'type' => 'text', 'half' => true],
            ['id' => 'claimsDetail', 'label' => 'Claim details (if any)', 'type' => 'textarea'],
            ['id' => 'premium', 'label' => 'Premium', 'type' => 'text', 'half' => true],
            ['id' => 'renewalDate', 'label' => 'Renewal date', 'type' => 'text', 'half' => true],
        ]],
        'G' => ['title' => 'Photos & content', 'icon' => '📸', 'note' => 'Exterior, reception, each room type, bathroom, amenities. Min 2048px, JPG.', 'fields' => [
            ['id' => 'photos', 'label' => 'Property photos', 'type' => 'file', 'multi' => true],
        ]],
        'H' => ['title' => 'Optional metrics', 'icon' => '📈', 'note' => 'Helps us benchmark and target — all optional.', 'fields' => [
            ['id' => 'occupancy', 'label' => 'Average annual occupancy %', 'type' => 'text', 'half' => true],
            ['id' => 'lengthStay', 'label' => 'Average length of stay', 'type' => 'text', 'half' => true],
            ['id' => 'guestMix', 'label' => 'Typical guest mix', 'type' => 'text'],
        ]],
    ],

    // The three documents accepted at sign-up. Each becomes a stamped PDF.
    'policies' => [
        'privacy' => [
            'title' => 'Privacy & Data Protection Policy',
            'body' => [
                'The Retro Motel Collective ("RMC") collects the information you provide when registering and using this portal in order to deliver member services — collective procurement, benchmarking, marketing support and related activities.',
                'Data protection: all data you provide is stored securely and is never shared in a way that identifies your property. Where the collective uses your data — for benchmarking, group tenders or reporting — it is aggregated and anonymised so individual properties cannot be identified.',
                'We do not sell your data. We only share specific details with a supplier or broker when you ask us to act on your behalf, and only the information needed for that purpose.',
                'You may request a copy of your data or its deletion at any time by contacting RMC head office.',
            ],
        ],
        'terms' => [
            'title' => 'Terms of Membership',
            'body' => [
                'By joining the Retro Motel Collective you agree to these terms. Membership is a monthly subscription to shared services; RMC provides guidance, templates, analysis and group buying power and does not take over on-site management of your property.',
                'Fees are billed monthly per your room band and tier. Founding rates, where offered, are locked for the member while their membership remains active.',
                'Cancellation: you may cancel within 30 days. On cancellation you lose portal access and are removed from member rates and collective deals negotiated on members’ behalf.',
                'RMC provides services on a best-efforts basis. Members remain responsible for their own legal, financial and regulatory obligations.',
            ],
        ],
        'authority' => [
            'title' => 'Member Authority to Act',
            'body' => [
                'You authorise the Retro Motel Collective to act on your behalf in seeking quotes, negotiating group rates and representing your property to suppliers, brokers and online travel agents for the services you have subscribed to.',
                'This authority is limited to obtaining and negotiating offers. RMC will not enter into any binding contract or switch any supplier without your express approval.',
                'This authority can be withdrawn at any time in writing and automatically ends when your membership ends.',
                'No bank details or personal identification documents are collected in the portal; these are supplied securely only at the contracting stage.',
            ],
        ],
    ],
];
