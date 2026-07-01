<?php
// Include authentication and database connection
include 'includes/auth_check.php';
include 'includes/db.php';

// Fetch category and event counts safely
$catCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
$eventCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events");

$catCount = 0;
$eventCount = 0;

if ($catCountQuery && $eventCountQuery) {
    $catData = mysqli_fetch_assoc($catCountQuery);
    $eventData = mysqli_fetch_assoc($eventCountQuery);
    $catCount = $catData['total'];
    $eventCount = $eventData['total'];
}

// Get recent events
$recentEventsQuery = mysqli_query($conn, "SELECT * FROM events ORDER BY created_at DESC LIMIT 5");
$recentEvents = [];
if ($recentEventsQuery) {
    while ($row = mysqli_fetch_assoc($recentEventsQuery)) {
        $recentEvents[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Campus Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-width: 250px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            font-weight: 600;
            font-size: 1.4rem;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--success);
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
            width: 25px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }

        /* Header Styles */
        .header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left h1 {
            font-size: 1.5rem;
            color: var(--dark);
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .logout-btn {
            background: var(--warning);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #e1156d;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .welcome-section h2 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .welcome-section p {
            color: #666;
            font-size: 1rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
        }

        .categories-icon {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .events-icon {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
        }

        /* Recent Events Table */
        .recent-events {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h3 {
            color: var(--dark);
            font-size: 1.3rem;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
        }

        .events-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .events-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f5f5f5;
        }

        .events-table tr:last-child td {
            border-bottom: none;
        }

        .event-title {
            font-weight: 500;
        }

        .event-date {
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .status-pending {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }

        .action-btn {
            background: white;
            border: none;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .action-btn h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .action-btn p {
            font-size: 13px;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header h3, .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .header {
                padding: 0 15px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .user-info span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Portal</h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="add_category.php"><i class="fas fa-plus-circle"></i> <span>Add Category</span></a></li>
                <li><a href="add_event.php"><i class="fas fa-calendar-plus"></i> <span>Add Event</span></a></li>
                <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i> <span>Manage Events</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Admin Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <span>Admin User</span>
                </div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>Welcome to Admin Dashboard</h2>
                <p>Manage your campus events and categories efficiently from this admin panel.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon categories-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $catCount; ?></h3>
                        <p>Total Categories</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon events-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $eventCount; ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="recent-events">
                <div class="section-header">
                    <h3>Recent Events</h3>
                    <a href="manage_events.php" class="view-all">View All</a>
                </div>
                <table class="events-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentEvents)): ?>
                            <?php foreach ($recentEvents as $event): ?>
                                <tr>
                                    <td class="event-title"><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td class="event-date"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-active">Active</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #666;">No recent events found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add_category.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h4>Add Category</h4>
                    <p>Create new event categories</p>
                </a>
                <a href="add_event.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h4>Add Event</h4>
                    <p>Create a new campus event</p>
                </a>
                <a href="manage_events.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4>Manage Events</h4>
                    <p>View and edit all events</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Simple animation for stats cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>