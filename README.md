# FeatureBoard

FeatureBoard is a multi-project product management application designed to organize, prioritize, and track feature roadmaps across multiple software products. Features are tracked using a Kanban workflow with four phases: **Backlog**, **Planned**, **In Progress**, and **Released**.

---

## Relational Architecture

This application models a **One-to-Many relationship** between Projects and Features:

```text
                  +--------------------------------+
                  |            FRONTEND            |
                  |     (HTML, CSS, Vanilla JS)    |
                  +---------------+----------------+
                                  |
                                  | Native fetch()
                                  v
                  +---------------+----------------+
                  |          MIDDLE LAYER          |
                  |             (PHP)              |
                  +---------------+----------------+
                                  |
                                  | Prepared queries (PDO)
                                  v
                  +---------------+----------------+
                  |            BACKEND             |
                  |        (SQLite + PDO)          |
                  |  [projects] < 1---* > [features] |
                  +--------------------------------+
```

1. **Frontend**: Single-Page Application (SPA) offering alphabetical project switcher tabs, dynamic stats badges, real-time query searching, status/priority filtering, dynamic project creation, and a slide-in modal drawer.
2. **Middle Layer**: Serves frontend assets, exposes REST API routes, validates incoming feature/project details, and handles database connection pooling.
3. **Backend**: SQLite database file (`database.db`) enforcing foreign key integrity and cascading deletes.

---

## Database Schema

```sql
-- 1. Projects Table
CREATE TABLE projects (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  category TEXT,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Features Table
CREATE TABLE features (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  project_id INTEGER NOT NULL,
  title TEXT NOT NULL,
  description TEXT,
  priority TEXT NOT NULL,          -- Allowed: 'low', 'medium', 'high'
  status TEXT NOT NULL,            -- Allowed: 'backlog', 'planned', 'in_progress', 'released'
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

---

## REST API Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/projects` | Returns all available projects sorted alphabetically. |
| `GET` | `/api/projects/:id` | Returns details for a single project. |
| `POST` | `/api/projects` | Creates a new project workspace. |
| `GET` | `/api/projects/:id/features` | Returns all features belonging to a project. |
| `GET` | `/api/projects/:id/stats` | Returns statistics for a specific project. |
| `GET` | `/api/features` | Returns all features (supports `?project_id=` filter). |
| `GET` | `/api/features/:id` | Returns details for a single feature. |
| `POST` | `/api/features` | Creates a feature (requires valid `project_id` in body). |
| `PUT` | `/api/features/:id` | Updates a feature's parameters. |
| `DELETE`| `/api/features/:id` | Deletes a feature. |
| `GET` | `/api/stats` | Returns global aggregates across all projects. |

---

## Seeding Dataset
When launched with an empty database, the system initializes **8 products** (each with 15 highly realistic features matching their category, making a total of 120 features):
1. **PathByte** (EdTech / Career): Career growth and roadmap platform for students.
2. **ConvoHub** (Education / Communication): Student cohort communication and collaboration platform.
3. **Inventura** (AI / Inventory): Smart inventory and demand forecasting platform.
4. **Constraint System** (Productivity): Constraint-based planning and productivity platform.
5. **LaunchPad AI** (AI / Product Management): AI product execution and meeting-to-action platform.
6. **Nakshatraa** (Consultation): Astrology, counselling and consultation platform.
7. **StudyFlow** (EdTech): Student study planning and academic organization platform.
8. **CampusPulse** (Campus Management): Student feedback and campus issue management platform.

---

## Installation & Setup

Ensure you have **PHP 8+** installed on your system.

1. Navigate to the project directory:
   ```bash
   cd FeatureBoard
   ```

2. Start the PHP development server:
   ```bash
   npm start
   ```
   *(Or run directly: `php -S localhost:3000 router.php`)*

3. Open the application:
   [http://localhost:3000](http://localhost:3000)

---

## Walkthrough Demo Steps

1. **Open FeatureBoard**: Visit `http://localhost:3000`.
2. **Switch Active Project**: Click on the project tabs at the top (e.g. **PathByte**, **ConvoHub**, or **LaunchPad AI**). Verify that the statistics counts and board roadmaps update instantly.
3. **Inspect the Layout**: Notice the horizontal swimlane display. The development phases stack vertically as rows, and features scroll horizontally. Any lane that has zero cards matching your active search, priority, or status filters is automatically hidden to keep the board clean.
4. **Click Add Feature**: Click the **+ Add Feature Idea** button in the header. A drawer will slide in from the right.
5. **Create a Feature under a New Project**:
   - In the **Target Project** dropdown, select **`+ Create New Project...`**.
   - Under **New Project Details**, enter:
     - Project Name: `TaskSync`
     - Category: `Productivity`
     - Description: `Calendar scheduling manager.`
   - Under **Feature Details**, enter:
     - Title: `Google Calendar Integration`
     - Priority: `High`
     - Status: `Backlog`
   - Click **Save Changes**.
6. **Observe Dynamic Creation**: Notice that a new project tab named **TaskSync** is created, highlighted as active, and the new card appears in its Backlog lane.
7. **Edit Feature Status**: Click the "Google Calendar Integration" card to open the drawer. Change Status to **Released** and click **Save Changes**. Verify the card moves successfully to the Released lane.
8. **Verify Persistence**: Refresh the browser (Cmd+R or F5) and verify "Google Calendar Integration" is still in the Released lane of the **TaskSync** project.
9. **Delete Feature**: Click the card again to open the drawer, click the red **Delete Feature** button on the bottom left, and confirm the deletion.
10. **Verify Deletion**: Verify the statistics counts decrement, the card is removed, and the drawer closes automatically.
