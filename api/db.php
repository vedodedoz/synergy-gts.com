<?php

declare(strict_types=1);

function db_root_path(string $relative = ''): string
{
    $base = dirname(__DIR__);
    return $relative === '' ? $base : $base . DIRECTORY_SEPARATOR . $relative;
}

function db_file_path(): string
{
    return db_root_path('app_data.sqlite');
}

function db_connect(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO('sqlite:' . db_file_path());
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    db_initialize($pdo);
    return $pdo;
}

function db_initialize(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS jobs (
        id TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        department TEXT NOT NULL,
        location TEXT NOT NULL,
        type TEXT NOT NULL,
        description TEXT NOT NULL,
        responsibilities_json TEXT NOT NULL,
        requirements_json TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS branches (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        city TEXT NOT NULL,
        province TEXT NOT NULL,
        country TEXT NOT NULL,
        address TEXT,
        email TEXT NOT NULL,
        hours TEXT,
        is_primary INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS content (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS applications (
        id TEXT PRIMARY KEY,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        linkedin TEXT,
        job_title TEXT NOT NULL,
        experience TEXT NOT NULL,
        cover TEXT NOT NULL,
        submitted_at TEXT NOT NULL,
        cv_file_name TEXT NOT NULL,
        cv_mime TEXT NOT NULL,
        cv_blob BLOB NOT NULL,
        details_pdf_name TEXT NOT NULL,
        details_pdf_blob BLOB NOT NULL
    )');

    seed_defaults($pdo);
}

function default_jobs(): array
{
    return [
        [
            'id' => 'job_1',
            'title' => 'SAP S/4HANA Consultant',
            'department' => 'SAP Practice',
            'location' => 'Toronto, ON / Remote',
            'type' => 'Full-time',
            'description' => 'Join our growing SAP S/4HANA practice and lead transformation engagements for Canadian enterprises. You will be responsible for end-to-end implementation delivery — from blueprinting through go-live and hypercare.',
            'responsibilities' => ['Lead SAP S/4HANA implementation and migration projects','Conduct business requirement workshops with client stakeholders','Configure SAP modules (FICO, MM, SD) to meet business needs','Develop functional specifications and test documentation','Support UAT and hypercare phases through go-live','Mentor junior consultants and support pre-sales activities'],
            'requirements' => ['5+ years SAP S/4HANA implementation experience','Strong knowledge of FICO, MM, or SD modules','Experience with SAP Activate methodology','Excellent communication and client-facing skills','SAP Certification preferred','Bachelor\'s degree in Business, IT, or related field']
        ],
        [
            'id' => 'job_2',
            'title' => 'SAP FICO Functional Lead',
            'department' => 'SAP Practice',
            'location' => 'Vancouver, BC / Hybrid',
            'type' => 'Full-time',
            'description' => 'Lead the FICO consulting practice from our Vancouver office. Drive SAP finance transformation projects for enterprise clients across Canada, mentor junior consultants, and establish best practices for our growing SAP Finance team.',
            'responsibilities' => ['Lead end-to-end SAP FICO implementations in S/4HANA environments','Design financial process blueprints and solution architecture','Manage client relationships and executive stakeholder communication','Mentor and develop junior FICO consultants','Establish FICO best practices and internal delivery standards','Contribute to pre-sales proposals and solution design'],
            'requirements' => ['7+ years SAP FICO consulting experience','Proven track record in finance transformation projects','Deep expertise in S/4HANA Finance and Central Finance','Experience in public sector or manufacturing preferred','SAP Certified Application Associate – Financial Accounting','Strong leadership and project management skills']
        ],
        [
            'id' => 'job_3',
            'title' => 'SAP BTP Developer',
            'department' => 'Technology Practice',
            'location' => 'Toronto, ON / Remote',
            'type' => 'Full-time',
            'description' => 'Design and build integrations and extensions on the SAP Business Technology Platform (BTP). Collaborate with functional consultants and client architects to deliver cloud-native SAP solutions.',
            'responsibilities' => ['Develop CAP applications on SAP BTP','Build integrations using SAP Integration Suite and BTP Integration','Design and implement SAP Fiori / UI5 custom apps','Work with REST/OData APIs to extend S/4HANA functionality','Support CI/CD pipelines and DevOps practices on BTP','Document technical designs and support knowledge transfer'],
            'requirements' => ['3+ years SAP BTP development experience','Hands-on with CAP, SAP Integration Suite, and BTP services','Proficiency in JavaScript/TypeScript and Node.js or Java','Experience with SAP Fiori/UI5 development','Familiarity with SAP S/4HANA APIs and OData services','BTP Certification is a plus']
        ],
        [
            'id' => 'job_4',
            'title' => 'SAP Project Manager',
            'department' => 'Delivery',
            'location' => 'Toronto, ON / Hybrid',
            'type' => 'Full-time',
            'description' => 'Manage end-to-end delivery of SAP implementation and migration projects. Act as the primary interface between client stakeholders and our delivery team, ensuring projects are delivered on time, on budget, and to scope.',
            'responsibilities' => ['Own project planning, scheduling, risk management, and status reporting','Manage project budgets and resource allocation','Facilitate steering committee and executive stakeholder meetings','Coordinate functional and technical workstreams across the delivery team','Manage change control and scope throughout project lifecycle','Drive issue resolution and escalation management'],
            'requirements' => ['5+ years SAP project management experience','Proven delivery of full-cycle SAP S/4HANA implementations','PMP or Prince2 certification strongly preferred','Experience with SAP Activate methodology','Strong stakeholder management and communication skills','Proficiency in MS Project, Jira, or equivalent PM tools']
        ],
        [
            'id' => 'job_5',
            'title' => 'SAP MM/WM Consultant',
            'department' => 'SAP Practice',
            'location' => 'Calgary, AB / Remote',
            'type' => 'Full-time',
            'description' => 'Join our supply chain practice to deliver SAP Materials Management and Warehouse Management solutions. Work with manufacturing and retail clients to optimise procurement, inventory, and warehouse operations on S/4HANA.',
            'responsibilities' => ['Configure and implement SAP MM and EWM/WM modules in S/4HANA','Gather and analyse business requirements for supply chain processes','Develop functional specifications for custom enhancements','Conduct unit testing, SIT, and support UAT','Provide post go-live hypercare and end-user training','Collaborate with SD and FICO consultants for end-to-end process design'],
            'requirements' => ['4+ years SAP MM/WM consulting experience','Strong knowledge of procurement-to-pay and inventory management processes','Experience with SAP S/4HANA embedded analytics for supply chain','Familiarity with SAP EWM is an advantage','Good understanding of cross-module integration (SD, FICO)','Bachelor\'s degree in Supply Chain, Business, or related field']
        ],
        [
            'id' => 'job_6',
            'title' => 'SAP SD Consultant',
            'department' => 'SAP Practice',
            'location' => 'Montreal, QC / Hybrid',
            'type' => 'Full-time',
            'description' => 'Drive SAP Sales & Distribution implementations for clients in retail, manufacturing, and professional services. Configure end-to-end order-to-cash processes in SAP S/4HANA and help clients transform their sales operations.',
            'responsibilities' => ['Configure and implement SAP SD module in S/4HANA','Map and optimise order-to-cash business processes','Prepare functional specifications and conduct testing cycles','Integrate SD with FICO and MM for end-to-end process flows','Deliver end-user training and post-go-live support','Support pre-sales activities and solution scoping'],
            'requirements' => ['3+ years SAP SD consulting experience','Strong understanding of order-to-cash business processes','Experience with SAP S/4HANA pricing, shipping, and billing','Cross-module integration knowledge (FICO, MM)','Excellent client communication and workshop facilitation skills','SAP SD certification is an advantage']
        ]
    ];
}

function default_branches(): array
{
    return [
        ['id' => 'branch_1', 'name' => 'Toronto Office', 'city' => 'Toronto', 'province' => 'Ontario', 'country' => 'Canada', 'address' => 'Toronto, Ontario, Canada', 'email' => 'inquiries@synergy-gts.com', 'hours' => 'Mon–Fri 9am–6pm EST', 'is_primary' => 1],
        ['id' => 'branch_2', 'name' => 'Vancouver Office', 'city' => 'Vancouver', 'province' => 'British Columbia', 'country' => 'Canada', 'address' => 'Vancouver, British Columbia, Canada', 'email' => 'inquiries@synergy-gts.com', 'hours' => 'Mon–Fri 9am–6pm PST', 'is_primary' => 0]
    ];
}

function default_content(): array
{
    return [
        'company.name' => 'Synergy-GTS',
        'company.tagline' => 'Empowering Canadian & North American Enterprises Through Intelligent Digital Transformation since 2010.',
        'company.email' => 'inquiries@synergy-gts.com',
        'company.phone' => 'Available Mon-Fri 9am-6pm EST',
        'company.founded' => '2010',
        'home.hero.title' => 'Transform Your Enterprise with Intelligent SAP Solutions',
        'home.hero.subtitle' => 'Synergy GTS delivers end-to-end SAP consulting, implementation, and managed services for Canadian enterprises.',
        'about.hero.title' => 'About Synergy GTS',
        'about.hero.subtitle' => 'Driving Enterprise Innovation Across Canada — Trusted SAP consulting partner since 2010.',
        'services.hero.title' => 'Our Services',
        'services.hero.subtitle' => 'End-to-end SAP consulting and managed services tailored for Canadian and North American enterprises.',
        'industries.hero.title' => 'Industries We Serve',
        'industries.hero.subtitle' => 'Deep domain expertise across key Canadian industry sectors.',
        'case-studies.hero.title' => 'Case Studies',
        'case-studies.hero.subtitle' => 'Real-world enterprise transformation success stories.',
        'careers.hero.title' => 'Careers at Synergy GTS',
        'careers.hero.subtitle' => 'Build your career with a leading SAP consulting firm. Join our team of experts and make a real impact.',
        'contact.hero.title' => 'Contact Us',
        'contact.hero.subtitle' => 'Ready to transform your enterprise? Let\'s discuss how Synergy-GTS can help you achieve your digital transformation goals.'
    ];
}

function seed_defaults(PDO $pdo): void
{
    $now = gmdate('c');

    $jobCount = (int)$pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn();
    if ($jobCount === 0) {
        $stmt = $pdo->prepare('INSERT INTO jobs (id,title,department,location,type,description,responsibilities_json,requirements_json,created_at,updated_at) VALUES (:id,:title,:department,:location,:type,:description,:responsibilities_json,:requirements_json,:created_at,:updated_at)');
        foreach (default_jobs() as $job) {
            $stmt->execute([
                ':id' => $job['id'],
                ':title' => $job['title'],
                ':department' => $job['department'],
                ':location' => $job['location'],
                ':type' => $job['type'],
                ':description' => $job['description'],
                ':responsibilities_json' => json_encode($job['responsibilities'], JSON_UNESCAPED_UNICODE),
                ':requirements_json' => json_encode($job['requirements'], JSON_UNESCAPED_UNICODE),
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        }
    }

    $branchCount = (int)$pdo->query('SELECT COUNT(*) FROM branches')->fetchColumn();
    if ($branchCount === 0) {
        $stmt = $pdo->prepare('INSERT INTO branches (id,name,city,province,country,address,email,hours,is_primary,created_at,updated_at) VALUES (:id,:name,:city,:province,:country,:address,:email,:hours,:is_primary,:created_at,:updated_at)');
        foreach (default_branches() as $branch) {
            $stmt->execute([
                ':id' => $branch['id'],
                ':name' => $branch['name'],
                ':city' => $branch['city'],
                ':province' => $branch['province'],
                ':country' => $branch['country'],
                ':address' => $branch['address'],
                ':email' => $branch['email'],
                ':hours' => $branch['hours'],
                ':is_primary' => $branch['is_primary'],
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        }
    }

    $contentCount = (int)$pdo->query('SELECT COUNT(*) FROM content')->fetchColumn();
    if ($contentCount === 0) {
        $stmt = $pdo->prepare('INSERT INTO content (key, value, updated_at) VALUES (:key, :value, :updated_at)');
        foreach (default_content() as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value, ':updated_at' => $now]);
        }
    }

    $settingsCount = (int)$pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
    if ($settingsCount === 0) {
        $stmt = $pdo->prepare('INSERT INTO settings (key, value, updated_at) VALUES (:key, :value, :updated_at)');
        $stmt->execute([':key' => 'admin_password_b64', ':value' => base64_encode('Admin@2026'), ':updated_at' => $now]);
    }
}

function json_out(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function parse_json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function map_job_row(array $row): array
{
    return [
        'id' => $row['id'],
        'title' => $row['title'],
        'department' => $row['department'],
        'location' => $row['location'],
        'type' => $row['type'],
        'description' => $row['description'],
        'responsibilities' => json_decode($row['responsibilities_json'], true) ?: [],
        'requirements' => json_decode($row['requirements_json'], true) ?: [],
    ];
}

function map_branch_row(array $row): array
{
    return [
        'id' => $row['id'],
        'name' => $row['name'],
        'city' => $row['city'],
        'province' => $row['province'],
        'country' => $row['country'],
        'address' => $row['address'] ?? '',
        'email' => $row['email'],
        'hours' => $row['hours'] ?? '',
        'primary' => ((int)$row['is_primary']) === 1,
    ];
}

function fetch_jobs(PDO $pdo): array
{
    $rows = $pdo->query('SELECT * FROM jobs ORDER BY created_at ASC')->fetchAll();
    return array_map('map_job_row', $rows ?: []);
}

function fetch_branches(PDO $pdo): array
{
    $rows = $pdo->query('SELECT * FROM branches ORDER BY is_primary DESC, created_at ASC')->fetchAll();
    return array_map('map_branch_row', $rows ?: []);
}

function fetch_content(PDO $pdo): array
{
    $rows = $pdo->query('SELECT key, value FROM content')->fetchAll();
    $content = [];
    foreach ($rows ?: [] as $row) {
        $content[$row['key']] = $row['value'];
    }
    return $content;
}
