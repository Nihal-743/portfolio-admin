<?php
// first-run content, same as the old static page.
// after this everything is edited from /admin

function seed_content($pdo)
{
    $settings = [
        'meta_title'       => 'Nihal | Front-End Developer & Web Designer, New Delhi',
        'meta_description' => 'Nihal - front-end developer and web designer in New Delhi. 1.5+ years of experience, 50+ live websites built with HTML, CSS, JavaScript, Tailwind CSS and Bootstrap.',

        'hero_badge'  => 'Open to full-time roles in Delhi NCR',
        'hero_hello'  => "Hi, I'm Nihal.",
        'hero_title'  => 'Front-end developer who builds',
        'typed_words' => "responsive sites.\npixel-perfect UIs.\nclean, fast pages.\nlanding pages.\nonline stores.",
        'hero_lead'   => "For the last 1.5 years I've been designing and coding websites at a web agency in Delhi. 50+ of them are live today. I write clean, mobile-first HTML, CSS and JavaScript, and use Tailwind or Bootstrap when the project needs it.",
        'photo'       => 'img/nihal.jpg',
        'stat_sites'  => '50',
        'stat_years'  => '1.5',
        'float_stack' => 'HTML · CSS · JavaScript',
        'strip_items' => "HTML5\nCSS3\nJavaScript\nTailwind CSS\nBootstrap 5\nResponsive Design\nGit & GitHub\nPage Speed\nOn-page SEO",

        'about_title' => 'I write the code that clients *actually* see.',
        'about_text'  => "I joined WebKey India as an intern in April 2025 and got a full-time front-end role six months later. Since then I've worked on online stores, travel booking sites, clinics, finance blogs and service businesses. Some I built from a blank file, others were WordPress themes I reshaped to fit the brand.\n\nI handle the whole front-end side: layouts, responsive behaviour, forms, cross-browser testing and finally putting the site live on cPanel or Hostinger. Alongside work I'm doing my MCA from IGNOU.",
        'stat3_num'   => 'All',
        'stat3_text'  => 'Industries: retail, travel, health, finance and more',
        'stat4_num'   => 'MCA',
        'stat4_text'  => 'Pursuing, IGNOU',

        'fact_looking'  => 'Front-End Developer / Web Designer role',
        'fact_type'     => 'Full-time',
        'fact_based'    => 'Najafgarh, New Delhi',
        'fact_location' => 'Delhi, Noida, Gurugram',

        'work_sub'  => 'Real client projects, all live right now. Click any card to open the site.',
        'work_more' => "That's 12 out of 50+. Happy to show you the rest on a call.",
        'exp_sub'   => '1.5+ years building websites at an agency. Before that, two years in logistics with Blue Dart.',
        'skills_sub' => "Front-end is what I do every day. Things marked basic I can work with and fix, but I'm still learning them.",

        'contact_title' => "Hiring a front-end developer? *Let's talk.*",
        'contact_lead'  => "I'm looking for a full-time front-end developer or web designer role in Delhi, Noida or Gurugram. I usually reply the same day, by call, WhatsApp or email.",
        'email'     => 'nihalpanchal8002@gmail.com',
        'phone'     => '+91 88821 11743',
        'whatsapp'  => '918882111743',
        'location'  => 'Najafgarh, New Delhi',
        'github'    => 'https://github.com/Nihal-743',
        'linkedin'  => 'https://www.linkedin.com/in/nihal-singh-519897305/',
    ];

    $st = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
    foreach ($settings as $k => $v) {
        $st->execute([$k, $v]);
    }

    // no default login on purpose - the first visit to /admin asks you to create one

    $projects = [
        ['2by2mart', 'https://2by2mart.com', 'shop', 'E-commerce', 1, 'img/work/2by2mart.jpg'],
        ['Jupiter Travels', 'https://jupitertravels.co', 'travel', 'Tour & travel', 1, 'img/work/jupitertravels.jpg'],
        ['SirBus', 'https://sirbus.com', 'travel', 'Tour & travel', 1, 'img/work/sirbus.jpg'],
        ['Be The Police', 'https://bethepolice.in', 'other', 'Services', 1, 'img/work/bethepolice.jpg'],
        ['Agra Tour Bus', 'https://agratourbus.com', 'travel', 'Tour & travel', 0, 'img/work/agratourbus.jpg'],
        ['Chiniya Badam', 'https://chiniyabadam.com', 'shop', 'E-commerce', 0, 'img/work/chiniyabadam.jpg'],
        ['Classic Leathers India', 'https://classicleathersindia.com', 'shop', 'E-commerce', 0, 'img/work/classicleathersindia.jpg'],
        ['IWI Tours & Travels', 'https://iwitoursandtravels.com', 'travel', 'Tour & travel', 0, 'img/work/iwitoursandtravels.jpg'],
        ['Royal Rana Travels', 'https://royalranatravels.com', 'travel', 'Tour & travel', 0, 'img/work/royalranatravels.jpg'],
        ['InstaSafar', 'https://instasafar.in', 'travel', 'Tour & travel', 0, 'img/work/instasafar.jpg'],
        ['Physio Devotion', 'https://physiodevotion.com', 'other', 'Healthcare', 0, 'img/work/physiodevotion.jpg'],
        ['AHK Tips', 'https://ahktips.com', 'other', 'Finance', 0, 'img/work/ahktips.jpg'],
    ];
    $st = $pdo->prepare('INSERT INTO projects (title, url, category, tag, scratch, image, sort) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($projects as $i => $p) {
        $p[] = ($i + 1) * 10;
        $st->execute($p);
    }

    $jobs = [
        ['Oct 2025 - Present', 'Web Designer / Front-end Developer', 'WebKey India, New Delhi',
         "Designed, built and deployed 50+ responsive client websites using HTML, CSS, JavaScript, Bootstrap, Tailwind, React and WordPress.\nBuilt custom UIs from scratch for 2by2mart, Jupiter Travels, SirBus and Be The Police.\nTake care of deployment end to end: domains, hosting, cPanel and Hostinger hPanel, plus maintenance after launch.\nTest on different browsers and phones, and keep the page structure clean for SEO.\nWork with the SEO, ads and content teams so projects go out on the client's deadline.", 1],
        ['Apr 2025 - Sep 2025', 'Web Designer Intern', 'WebKey India, New Delhi',
         "Worked on real client projects under a senior developer.\nCustomised WordPress and HTML themes (layouts, colours, typography, new pages) to match each brand.\nBuilt contact and enquiry forms with JavaScript validation.", 0],
        ['Oct 2022 - Nov 2024', 'Delivery Associate', 'Blue Dart Express Ltd., New Delhi',
         'Managed daily delivery schedules and high-value shipments. This job taught me to be on time, every time.', 0],
    ];
    $st = $pdo->prepare('INSERT INTO jobs (period, title, company, points, is_current, sort) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($jobs as $i => $j) {
        $j[] = ($i + 1) * 10;
        $st->execute($j);
    }

    // skill line format:  "Name *" = highlighted,  "Name (basic)" = basic tag
    $skills = [
        ['Front-end', 'Main stack', "HTML5 *\nCSS3 *\nJavaScript ES6+ *\nTailwind CSS *\nBootstrap 5 *\nResponsive design\nReact.js (basic)\njQuery (basic)"],
        ['CMS & back-end', '', "WordPress (basic)\nWooCommerce (basic)\nPHP (basic)\nMySQL (basic)"],
        ['Strengths', '', "Mobile-first layouts\nPixel-perfect UI\nLanding pages\nE-commerce sites\nCross-browser fixes\nPage speed\nOn-page SEO"],
        ['Tools', '', "VS Code\nChrome DevTools\nGit & GitHub\nGitHub Pages\ncPanel\nHostinger hPanel"],
        ['Soft skills', '', "Team collaboration\nClient communication\nTime management\nProblem solving\nEnglish & Hindi"],
    ];
    $st = $pdo->prepare('INSERT INTO skill_groups (name, label, items, sort) VALUES (?, ?, ?, ?)');
    foreach ($skills as $i => $s) {
        $s[] = ($i + 1) * 10;
        $st->execute($s);
    }

    $steps = [
        ['Understand the brief', 'Sit with the SEO, ads and content team to know who the site is for and what it has to do.'],
        ['Design & build', 'Mobile-first layout in HTML, CSS and JavaScript. Tailwind or Bootstrap if it speeds things up.'],
        ['Test everywhere', 'Chrome, Firefox, Safari, phones and tablets. Forms, speed and on-page SEO get checked too.'],
        ['Go live & maintain', 'Domain, hosting and deployment on cPanel or hPanel, then updates once the site is out.'],
    ];
    $st = $pdo->prepare('INSERT INTO steps (title, text, sort) VALUES (?, ?, ?)');
    foreach ($steps as $i => $s) {
        $s[] = ($i + 1) * 10;
        $st->execute($s);
    }

    $edu = [
        ['2023 - Present', 'Master of Computer Applications', 'IGNOU', 'Pursuing'],
        ['2020 - 2023', 'Bachelor of Computer Applications', 'MDU, Rohtak', ''],
        ['2023 - 2024', 'Certification in Web Development', 'ESS Institute', ''],
        ['2020 / 2018', 'Class XII & Class X', 'CBSE', ''],
    ];
    $st = $pdo->prepare('INSERT INTO education (period, title, place, pill, sort) VALUES (?, ?, ?, ?, ?)');
    foreach ($edu as $i => $e) {
        $e[] = ($i + 1) * 10;
        $st->execute($e);
    }
}
