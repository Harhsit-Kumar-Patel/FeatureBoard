<?php
// api.php - Database initialization, data seeding, and REST API controller

// Helper function to return JSON response and exit
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    // Connect to SQLite database
    $db = new PDO('sqlite:' . __DIR__ . '/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Enable foreign key support in SQLite
    $db->exec('PRAGMA foreign_keys = ON;');

    // Initialize Database Tables
    $db->exec("
      CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );

      CREATE TABLE IF NOT EXISTS features (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        project_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        description TEXT,
        priority TEXT NOT NULL,
        status TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
      );
    ");

    // Seed Sample Projects and Features if database is empty
    $projectCount = $db->query('SELECT COUNT(*) as count FROM projects')->fetch();
    if ($projectCount['count'] == 0) {
        $insertProject = $db->prepare('INSERT INTO projects (name, category, description) VALUES (?, ?, ?)');
        $insertFeature = $db->prepare('INSERT INTO features (project_id, title, description, priority, status) VALUES (?, ?, ?, ?, ?)');

        $db->beginTransaction();
        try {
            // 1. PathByte
            $insertProject->execute(['PathByte', 'EdTech / Career', 'Career growth and roadmap platform for students.']);
            $p1Id = $db->lastInsertId();
            $p1Features = [
                ['Personalized Career Roadmaps', 'Custom step-by-step learning paths generated based on student career goals.', 'high', 'released'],
                ['Skill Gap Analysis', 'Analyze user skills against target job profiles to identify training needs.', 'high', 'in_progress'],
                ['Career Goal Setting', 'Set short-term and long-term milestones for academic and career advancement.', 'high', 'released'],
                ['Learning Resource Library', 'Curated list of articles, tutorials, and courses for hot tech roles.', 'high', 'released'],
                ['Progress Tracking', 'Visual charts mapping student progress through their personalized roadmap.', 'medium', 'in_progress'],
                ['Roadmap Customization', 'Allow students to drag, drop, and edit milestones on their roadmap.', 'high', 'planned'],
                ['Resume Builder', 'Interactive template for building resumes centered around student projects.', 'high', 'planned'],
                ['Internship Recommendations', 'Automatically recommend open internship spots matching student roadmap skills.', 'high', 'backlog'],
                ['Job Role Explorer', 'Detailed guide on daily duties, salaries, and skills for 50+ tech jobs.', 'medium', 'released'],
                ['Skill Assessment Tests', 'Short quizzes to verify skill competency and add badges to student profiles.', 'medium', 'planned'],
                ['Weekly Learning Goals', 'Push notifications and widgets to help students stay on track with study goals.', 'medium', 'backlog'],
                ['Course Recommendations', 'Smart suggestions linking skill gaps to specific online courses.', 'medium', 'planned'],
                ['Portfolio Builder', 'One-click tool to host projects and showcase progress to recruiters.', 'high', 'backlog'],
                ['Career Readiness Score', 'An aggregate rating measuring general profile completeness and skill verified statuses.', 'medium', 'backlog'],
                ['Mentor Recommendations', 'Connect students with professionals in their target job sector.', 'low', 'backlog']
            ];
            foreach ($p1Features as $f) {
                $insertFeature->execute([$p1Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 2. ConvoHub
            $insertProject->execute(['ConvoHub', 'Education / Communication', 'Student cohort communication and collaboration platform.']);
            $p2Id = $db->lastInsertId();
            $p2Features = [
                ['Student Profiles', 'Individual pages for students showing bios, cohorts, social links, and skills.', 'high', 'released'],
                ['Notice Board', 'Static bulletin board for official university or cohort announcements.', 'high', 'released'],
                ['Cohort Announcements', 'Targeted broadcast updates sent to specific class groupings.', 'high', 'released'],
                ['Group Chat', 'Active multi-user text channels grouped by cohorts and subjects.', 'high', 'in_progress'],
                ['Direct Messaging', '1-on-1 private messaging between students or students and professors.', 'high', 'planned'],
                ['File Sharing', 'Ability to upload and share PDFs, images, and documents in chat streams.', 'medium', 'planned'],
                ['Event Announcements', 'Shared calendar cards detailing upcoming group study sessions and university events.', 'medium', 'released'],
                ['Student Search', 'Query utility to lookup classmates based on names, cohorts, or skills.', 'medium', 'released'],
                ['Verified Profiles', 'System validation checks ensuring accounts represent officially enrolled students.', 'high', 'released'],
                ['Notification Center', 'In-app feed and email notifications for cohort announcements and mentions.', 'high', 'planned'],
                ['Message Search', 'Look up keywords in historical direct messages and group chats.', 'medium', 'backlog'],
                ['Pinned Announcements', 'Keep important messages pinned to the top of group chat feeds.', 'medium', 'planned'],
                ['Read Receipts', 'Visual indicators showing when messages have been viewed.', 'low', 'backlog'],
                ['Online Status', 'Show green active dots when students are currently logged in.', 'low', 'backlog'],
                ['Polls & Surveys', 'Allow teachers or study leaders to publish quick, simple group votes.', 'medium', 'backlog']
            ];
            foreach ($p2Features as $f) {
                $insertFeature->execute([$p2Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 3. Inventura
            $insertProject->execute(['Inventura', 'AI / Inventory', 'Smart inventory and demand forecasting platform.']);
            $p3Id = $db->lastInsertId();
            $p3Features = [
                ['Inventory Dashboard', 'Main analytics panel tracking stock value, SKU counts, and active low-stock flags.', 'high', 'released'],
                ['Product Management', 'Catalog to create, edit, delete, and group products by category and pricing.', 'high', 'released'],
                ['Stock Tracking', 'Live quantity counters with incoming and outgoing shipment calculations.', 'high', 'released'],
                ['Low Stock Alerts', 'System warnings when items fall below pre-defined threshold counts.', 'high', 'in_progress'],
                ['Demand Forecasting', 'Statistical projections estimating future inventory needs based on sales records.', 'high', 'in_progress'],
                ['Sales History', 'Database log tracking sold items, dates, profit metrics, and customers.', 'medium', 'released'],
                ['Supplier Management', 'Directory containing supplier names, contact details, and price sheets.', 'medium', 'planned'],
                ['Purchase Orders', 'Draft and dispatch procurement sheets directly to wholesalers.', 'high', 'planned'],
                ['Inventory Search', 'Filter products by barcode, SKU, name, or wholesale supplier.', 'medium', 'released'],
                ['Stock Adjustment', 'Manual overwrite capabilities for inventory audits, damages, or returns.', 'medium', 'released'],
                ['Inventory Reports', 'Exportable spreadsheets summarizing quarterly stock turnover and values.', 'high', 'planned'],
                ['Automated Reordering', 'Initiate purchase orders automatically once items hit low stock.', 'high', 'backlog'],
                ['Multi-Location Inventory', 'Track stock across different physical warehouses and shelves.', 'medium', 'backlog'],
                ['Barcode Scanning', 'Quick scan utility using device cameras for rapid stock check-ins.', 'medium', 'backlog'],
                ['Inventory Audit Log', 'Detailed trail recording user, action, and timestamp for all stock edits.', 'low', 'backlog']
            ];
            foreach ($p3Features as $f) {
                $insertFeature->execute([$p3Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 4. Constraint System
            $insertProject->execute(['Constraint System', 'Productivity', 'Constraint-based planning and productivity platform.']);
            $p4Id = $db->lastInsertId();
            $p4Features = [
                ['Constraint Creation', 'Establish dynamic rules restricting activity timing, location, or energy bounds.', 'high', 'released'],
                ['Daily Planning', 'Organize schedule blocks mapped strictly around user constraints.', 'high', 'released'],
                ['Time Constraints', 'Specific time bounds restricting task scheduling to specific blocks.', 'high', 'released'],
                ['Task Prioritization', 'Weighted lists arranging tasks to fit optimal constraint slots.', 'high', 'in_progress'],
                ['Availability Tracking', 'Calendar integration to specify exact hours when user is free.', 'medium', 'planned'],
                ['Focus Sessions', 'Pomodoro-style timers running only within established focus constraints.', 'medium', 'planned'],
                ['Daily Capacity Planning', 'Gauge hours available against task weight limits to prevent burnout.', 'high', 'backlog'],
                ['Constraint Conflict Detection', 'Warnings when schedule settings create impossible time overrides.', 'high', 'backlog'],
                ['Weekly Planning', 'Broader review mapping general targets and constraints for the upcoming week.', 'medium', 'planned'],
                ['Productivity Dashboard', 'Graphs highlighting target completion rates against constraint accuracy.', 'medium', 'in_progress'],
                ['Task Dependencies', 'Sequencing tasks so prerequisite constraints are verified before work blocks start.', 'medium', 'backlog'],
                ['Calendar Integration', 'Sync constraints directly with Google Calendar or Apple iCal feeds.', 'high', 'backlog'],
                ['Recurring Constraints', 'Establish daily or weekly constraint rules, like study times or lunch breaks.', 'medium', 'planned'],
                ['Productivity Insights', 'Tips mapping peak energy levels to specific task completions.', 'low', 'backlog'],
                ['Schedule Optimization', 'Algorithm calculating the most efficient day schedule matching user constraints.', 'high', 'backlog']
            ];
            foreach ($p4Features as $f) {
                $insertFeature->execute([$p4Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 5. LaunchPad AI
            $insertProject->execute(['LaunchPad AI', 'AI / Product Management', 'AI product execution and meeting-to-action platform.']);
            $p5Id = $db->lastInsertId();
            $p5Features = [
                ['Meeting Transcript Input', 'Input raw speech-to-text text sheets from product sync sessions.', 'high', 'released'],
                ['Meeting Summary', 'Automatically summarize meeting goals, discussion details, and outcomes.', 'high', 'released'],
                ['Decision Extraction', 'Parse meeting logs to isolate key architectural and roadmap agreements.', 'high', 'in_progress'],
                ['Action Item Extraction', 'Convert dialog mentions into formatted tasks with owners and dates.', 'high', 'in_progress'],
                ['PRD Generation', 'Draft Product Requirement Documents directly from recorded audio ideas.', 'high', 'planned'],
                ['Jira Ticket Generation', 'API calls to push action items straight into a Jira task board.', 'high', 'planned'],
                ['Product Context Storage', 'Host document libraries so the AI remembers project history and scope rules.', 'high', 'planned'],
                ['Decision History', 'Chronological timeline of all approved product updates and adjustments.', 'medium', 'planned'],
                ['Meeting History', 'Directory storing transcripts, summaries, and action sets from old syncs.', 'medium', 'released'],
                ['Action Item Tracking', 'Track task updates from extraction down to final developer merge.', 'high', 'planned'],
                ['Product Requirement Templates', 'Standard PRD layouts for writing clean roadmap specs.', 'medium', 'backlog'],
                ['Stakeholder Identification', 'Isolate team members mentioned as owners of specific modules.', 'medium', 'backlog'],
                ['Requirement Prioritization', 'Algorithmic scoring sorting PRD tasks by effort and value.', 'high', 'backlog'],
                ['Execution Dashboard', 'Interactive dashboard checking PRD task progress in real time.', 'high', 'backlog'],
                ['Meeting-to-Execution Workflow', 'Automated chain moving audio records to ready Jira boards in one click.', 'high', 'backlog']
            ];
            foreach ($p5Features as $f) {
                $insertFeature->execute([$p5Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 6. Nakshatraa
            $insertProject->execute(['Nakshatraa', 'Consultation', 'Astrology, counselling and consultation platform.']);
            $p6Id = $db->lastInsertId();
            $p6Features = [
                ['Astrologer Profile', 'Detailed bios, areas of expertise, languages, and pricing details for consultants.', 'high', 'released'],
                ['Consultation Booking', 'Calendar picker to choose time slots and secure video consultation appointments.', 'high', 'planned'],
                ['Service Catalogue', 'List of standard astrology and counselling offerings with pricing cards.', 'high', 'released'],
                ['Astrology Consultation', 'Core video call room optimized for consulting sessions and screensharing charts.', 'high', 'released'],
                ['Marriage Consultation', 'Specific compatibility chart calculations and counseling paths.', 'high', 'planned'],
                ['Customer Enquiry Form', 'Contact and basic detail forms for prospects looking for customized advice.', 'high', 'released'],
                ['Appointment Management', 'Staff dashboard to approve, reschedule, or cancel booking requests.', 'high', 'planned'],
                ['Consultation Packages', 'Bundle multiple sessions together with bulk-buying discounts.', 'medium', 'backlog'],
                ['Customer Reviews', 'Allow verified clients to post ratings and feedback on profiles.', 'medium', 'backlog'],
                ['Testimonials', 'Publish curated customer success cards directly to the homepage.', 'medium', 'released'],
                ['WhatsApp Consultation Request', 'Direct chat integration to request fast appointments via WhatsApp messaging.', 'high', 'planned'],
                ['Payment Integration', 'Payment gateway connection to secure bookings via credit cards and digital wallets.', 'high', 'backlog'],
                ['Consultation History', 'Log storing summaries and recorded audio details from previous consultations.', 'medium', 'backlog'],
                ['Follow-up Requests', 'Automatically queue automated reminders for follow-up review bookings.', 'medium', 'backlog'],
                ['Blog / Astrology Articles', 'Publishing system for posting articles, horoscopes, and advice columns.', 'low', 'backlog']
            ];
            foreach ($p6Features as $f) {
                $insertFeature->execute([$p6Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 7. StudyFlow
            $insertProject->execute(['StudyFlow', 'EdTech', 'Student study planning and academic organization platform.']);
            $p7Id = $db->lastInsertId();
            $p7Features = [
                ['Subject Management', 'Organize coursework, syllabi, notes, and professor details by subject folders.', 'high', 'released'],
                ['Study Task Creation', 'Create academic tasks linked directly to courses and syllabus units.', 'high', 'released'],
                ['Exam Schedule', 'Calendar log recording upcoming midterms, quizzes, and finals.', 'high', 'released'],
                ['Assignment Tracking', 'Monitor homework status from drafting to submission and final grading.', 'high', 'in_progress'],
                ['Study Calendar', 'Unified visual calendar tracking task deadlines and scheduled study slots.', 'high', 'planned'],
                ['Study Sessions', 'Focus modules with ambient music, timers, and note pads for active studying.', 'medium', 'released'],
                ['Revision Planner', 'Calculate revision schedules based on exam dates and difficulty level.', 'high', 'planned'],
                ['Deadline Reminders', 'Custom push notifications and warnings for upcoming assignment submissions.', 'high', 'planned'],
                ['Subject Progress', 'Progress meters charting completed tasks and units for each course.', 'medium', 'in_progress'],
                ['Study Streaks', 'Gamified calendar trackers motivating daily task completions.', 'low', 'backlog'],
                ['Notes Organization', 'Rich text markdown editor to compile course notes within subject folders.', 'medium', 'planned'],
                ['Resource Bookmarks', 'Directory to store study URLs, online tutorials, and PDF document links.', 'medium', 'backlog'],
                ['Weekly Study Goals', 'Target hour setting widgets for help in structuring weekend routines.', 'medium', 'planned'],
                ['Performance Dashboard', 'Graphs analyzing study trends and task completions over semesters.', 'high', 'backlog'],
                ['Exam Preparation Mode', 'Specific mode muting distractions and focusing calendar on exam topics.', 'high', 'backlog']
            ];
            foreach ($p7Features as $f) {
                $insertFeature->execute([$p7Id, $f[0], $f[1], $f[2], $f[3]]);
            }

            // 8. CampusPulse
            $insertProject->execute(['CampusPulse', 'Campus Management', 'Student feedback and campus issue management platform.']);
            $p8Id = $db->lastInsertId();
            $p8Features = [
                ['Issue Reporting', 'Form to submit campus complaints, upload photographs, and tag location details.', 'high', 'released'],
                ['Issue Categories', 'Pre-defined taxonomy options (dorm issues, safety, IT setup, cafeteria, facilities).', 'high', 'released'],
                ['Issue Status Tracking', 'Progress tracker showing if reports are Open, Under Review, or Resolved.', 'high', 'released'],
                ['Campus Dashboard', 'Visual map and metrics outlining active complaints and resolved issues.', 'high', 'released'],
                ['Issue Search', 'Search keywords, locations, or tags in the open issue database.', 'medium', 'released'],
                ['Issue Voting', 'Upvoting mechanism letting students highlight critical shared complaints.', 'high', 'planned'],
                ['Priority Assignment', 'Administrative utility sorting issues by student vote count and safety level.', 'high', 'in_progress'],
                ['Admin Resolution Workflow', 'Back-office dashboard for campus staff to log actions and mark updates.', 'high', 'planned'],
                ['Location-Based Issues', 'Geo-tagging tools showing issues on an interactive campus map.', 'medium', 'backlog'],
                ['Anonymous Reporting', 'Privacy settings to conceal reporter identities on sensitive issues.', 'high', 'planned'],
                ['Resolution Comments', 'Allow admins to write explanation notes explaining resolved statuses.', 'medium', 'backlog'],
                ['Issue History', 'Archived directory of all complaints resolved in past semesters.', 'medium', 'planned'],
                ['Department Assignment', 'Route issues to relevant departments (e.g. Facilities, Student Life, IT).', 'high', 'backlog'],
                ['Resolution Time Tracking', 'Analytics to log average resolving times by category.', 'medium', 'backlog'],
                ['Student Satisfaction Rating', 'A poll letting reporters rate the quality of administrative resolutions.', 'medium', 'backlog']
            ];
            foreach ($p8Features as $f) {
                $insertFeature->execute([$p8Id, $f[0], $f[1], $f[2], $f[3]]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    jsonResponse(["error" => "Database Connection/Initialization Error: " . $e->getMessage()], 500);
}

// Helper validation routine
function validateFeature($title, $priority, $status, $projectId, $db) {
    if (!$title || !is_string($title) || trim($title) === '') {
        return 'Title cannot be empty.';
    }
    $allowedPriorities = ['low', 'medium', 'high'];
    if (!$priority || !in_array(strtolower($priority), $allowedPriorities)) {
        return 'Priority must be one of: low, medium, high.';
    }
    $allowedStatuses = ['backlog', 'planned', 'in_progress', 'released'];
    if (!$status || !in_array(strtolower($status), $allowedStatuses)) {
        return 'Status must be one of: backlog, planned, in_progress, released.';
    }
    if (!$projectId || !is_numeric($projectId)) {
        return 'A valid Project ID is required.';
    }
    
    // Verify project exists
    $stmt = $db->prepare('SELECT 1 FROM projects WHERE id = ?');
    $stmt->execute([$projectId]);
    if (!$stmt->fetch()) {
        return 'Project not found.';
    }
    
    return null;
}

// --- Request Parsing & Routing ---

$uri = $_SERVER['REQUEST_URI'];
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}
$method = $_SERVER['REQUEST_METHOD'];

// Parse JSON request body if present
$body = [];
$inputJSON = file_get_contents('php://input');
if ($inputJSON) {
    $body = json_decode($inputJSON, true) ?: [];
}

try {
    // 1. GET /api/projects
    if ($method === 'GET' && $uri === '/api/projects') {
        $projects = $db->query('SELECT * FROM projects ORDER BY name ASC')->fetchAll();
        jsonResponse($projects);
    }
    
    // 2. GET /api/projects/:id
    elseif ($method === 'GET' && preg_match('#^/api/projects/(\d+)$#', $uri, $matches)) {
        $id = intval($matches[1]);
        $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        
        if (!$project) {
            jsonResponse(["error" => "Project not found"], 404);
        }
        jsonResponse($project);
    }
    
    // 3. POST /api/projects
    elseif ($method === 'POST' && $uri === '/api/projects') {
        $name = isset($body['name']) ? $body['name'] : '';
        $category = isset($body['category']) ? $body['category'] : 'General';
        $description = isset($body['description']) ? $body['description'] : '';
        
        if (!$name || !is_string($name) || trim($name) === '') {
            jsonResponse(["error" => "Project name is required."], 400);
        }
        
        $stmt = $db->prepare('INSERT INTO projects (name, category, description) VALUES (?, ?, ?)');
        $stmt->execute([trim($name), trim($category), trim($description)]);
        
        $newId = $db->lastInsertId();
        $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$newId]);
        jsonResponse($stmt->fetch(), 201);
    }
    
    // 4. GET /api/projects/:id/features
    elseif ($method === 'GET' && preg_match('#^/api/projects/(\d+)/features$#', $uri, $matches)) {
        $id = intval($matches[1]);
        $stmt = $db->prepare('SELECT * FROM features WHERE project_id = ? ORDER BY created_at DESC');
        $stmt->execute([$id]);
        jsonResponse($stmt->fetchAll());
    }
    
    // 5. GET /api/projects/:id/stats
    elseif ($method === 'GET' && preg_match('#^/api/projects/(\d+)/stats$#', $uri, $matches)) {
        $id = intval($matches[1]);
        
        // Verify project exists
        $stmt = $db->prepare('SELECT 1 FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonResponse(["error" => "Project not found"], 404);
        }
        
        $stmt = $db->prepare('SELECT status, COUNT(*) as count FROM features WHERE project_id = ? GROUP BY status');
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll();
        
        $stats = [
            'total' => 0,
            'backlog' => 0,
            'planned' => 0,
            'in_progress' => 0,
            'released' => 0
        ];
        
        $total = 0;
        foreach ($rows as $row) {
            $key = strtolower($row['status']);
            if ($key === 'in_progress') {
                $stats['in_progress'] = intval($row['count']);
            } else {
                $stats[$key] = intval($row['count']);
            }
            $total += intval($row['count']);
        }
        $stats['total'] = $total;
        
        jsonResponse($stats);
    }
    
    // 6. GET /api/features
    elseif ($method === 'GET' && $uri === '/api/features') {
        $projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;
        if ($projectId) {
            $stmt = $db->prepare('SELECT * FROM features WHERE project_id = ? ORDER BY created_at DESC');
            $stmt->execute([$projectId]);
            jsonResponse($stmt->fetchAll());
        } else {
            $features = $db->query('SELECT * FROM features ORDER BY created_at DESC')->fetchAll();
            jsonResponse($features);
        }
    }
    
    // 7. GET /api/features/:id
    elseif ($method === 'GET' && preg_match('#^/api/features/(\d+)$#', $uri, $matches)) {
        $id = intval($matches[1]);
        $stmt = $db->prepare('SELECT * FROM features WHERE id = ?');
        $stmt->execute([$id]);
        $feature = $stmt->fetch();
        
        if (!$feature) {
            jsonResponse(["error" => "Feature not found"], 404);
        }
        jsonResponse($feature);
    }
    
    // 8. POST /api/features
    elseif ($method === 'POST' && $uri === '/api/features') {
        $projectId = isset($body['project_id']) ? intval($body['project_id']) : null;
        $title = isset($body['title']) ? $body['title'] : '';
        $description = isset($body['description']) ? $body['description'] : '';
        $priority = isset($body['priority']) ? $body['priority'] : 'medium';
        $status = isset($body['status']) ? $body['status'] : 'backlog';
        
        $valError = validateFeature($title, $priority, $status, $projectId, $db);
        if ($valError) {
            jsonResponse(["error" => $valError], 400);
        }
        
        $stmt = $db->prepare('INSERT INTO features (project_id, title, description, priority, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $projectId,
            trim($title),
            trim($description),
            strtolower(trim($priority)),
            strtolower(trim($status))
        ]);
        
        $newId = $db->lastInsertId();
        $stmt = $db->prepare('SELECT * FROM features WHERE id = ?');
        $stmt->execute([$newId]);
        jsonResponse($stmt->fetch(), 201);
    }
    
    // 9. PUT /api/features/:id
    elseif ($method === 'PUT' && preg_match('#^/api/features/(\d+)$#', $uri, $matches)) {
        $id = intval($matches[1]);
        
        $stmt = $db->prepare('SELECT * FROM features WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            jsonResponse(["error" => "Feature not found"], 404);
        }
        
        $projectId = isset($body['project_id']) ? intval($body['project_id']) : intval($existing['project_id']);
        $title = isset($body['title']) ? $body['title'] : $existing['title'];
        $description = isset($body['description']) ? $body['description'] : $existing['description'];
        $priority = isset($body['priority']) ? $body['priority'] : $existing['priority'];
        $status = isset($body['status']) ? $body['status'] : $existing['status'];
        
        $valError = validateFeature($title, $priority, $status, $projectId, $db);
        if ($valError) {
            jsonResponse(["error" => $valError], 400);
        }
        
        $stmt = $db->prepare('UPDATE features SET project_id = ?, title = ?, description = ?, priority = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([
            $projectId,
            trim($title),
            trim($description),
            strtolower(trim($priority)),
            strtolower(trim($status)),
            $id
        ]);
        
        $stmt = $db->prepare('SELECT * FROM features WHERE id = ?');
        $stmt->execute([$id]);
        jsonResponse($stmt->fetch());
    }
    
    // 10. DELETE /api/features/:id
    elseif ($method === 'DELETE' && preg_match('#^/api/features/(\d+)$#', $uri, $matches)) {
        $id = intval($matches[1]);
        
        $stmt = $db->prepare('SELECT 1 FROM features WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonResponse(["error" => "Feature not found"], 404);
        }
        
        $stmt = $db->prepare('DELETE FROM features WHERE id = ?');
        $stmt->execute([$id]);
        jsonResponse(["success" => true, "message" => "Feature deleted successfully."]);
    }
    
    // 11. GET /api/stats
    elseif ($method === 'GET' && $uri === '/api/stats') {
        $rows = $db->query('SELECT status, COUNT(*) as count FROM features GROUP BY status')->fetchAll();
        
        $stats = [
            'total' => 0,
            'backlog' => 0,
            'planned' => 0,
            'in_progress' => 0,
            'released' => 0
        ];
        
        $total = 0;
        foreach ($rows as $row) {
            $key = strtolower($row['status']);
            if ($key === 'in_progress') {
                $stats['in_progress'] = intval($row['count']);
            } else {
                $stats[$key] = intval($row['count']);
            }
            $total += intval($row['count']);
        }
        $stats['total'] = $total;
        
        jsonResponse($stats);
    }
    
    // Catch-all 404 for unhandled API endpoints
    else {
        jsonResponse(["error" => "Endpoint not found"], 404);
    }
    
} catch (Exception $e) {
    jsonResponse(["error" => "Internal Server Error: " . $e->getMessage()], 500);
}
