<?php 
include '../config.php';

// Get category ID and validate
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id='$id'"));

// Redirect if category doesn't exist
if (!$cat) {
    header("Location: index.php");
    exit;
}

// Get events for this category
$events = mysqli_query($conn, "SELECT * FROM events WHERE category_id='$id' AND event_date >= CURDATE() ORDER BY event_date ASC");

// Get other categories for navigation
$other_cats = mysqli_query($conn, "SELECT * FROM categories WHERE id != '$id' LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cat['name']); ?> - Campus Opportunities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #4cc9f0;
            --success: #2ec4b6;
            --warning: #ff9f1c;
            --danger: #e71d36;
            --light: #f8f9fa;
            --dark: #212529;
            --text: #333333;
            --text-light: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            text-align: center;
            padding: 40px 0;
            color: white;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .breadcrumb a:hover {
            opacity: 1;
        }

        .breadcrumb span {
            color: white;
            opacity: 0.6;
        }

        /* Category Header */
        .category-header {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .category-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
        }

        .category-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .category-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Events Section */
        .events-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--dark);
            font-weight: 600;
        }

        .events-count {
            background: var(--light);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-light);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .event-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            background: white;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .event-content {
            padding: 20px;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .event-description {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .event-date {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-light);
            font-size: 14px;
        }

        .event-date i {
            color: var(--primary);
        }

        .event-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Other Categories */
        .other-categories {
            margin-bottom: 40px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .category-card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 24px;
        }

        .category-card-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 60px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--text-light);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px 0;
            color: white;
            margin-top: 50px;
        }

        .footer p {
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
            }

            .category-title {
                font-size: 2rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .category-stats {
                gap: 20px;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .header {
                padding: 30px 0;
            }

            .header h1 {
                font-size: 1.8rem;
            }

            .category-header {
                padding: 25px;
            }

            .events-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <span><i class="fas fa-chevron-right"></i></span>
            <span><?php echo htmlspecialchars($cat['name']); ?></span>
        </div>

        <!-- Category Header -->
        <div class="category-header">
            <div class="category-icon">
                <i class="fas fa-folder"></i>
            </div>
            <h1 class="category-title"><?php echo htmlspecialchars($cat['name']); ?></h1>
            <p>Explore all upcoming events in this category</p>
            
            <div class="category-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo mysqli_num_rows($events); ?></div>
                    <div class="stat-label">Upcoming Events</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo date('Y'); ?></div>
                    <div class="stat-label">Current Year</div>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="events-section">
            <div class="section-header">
                <h2 class="section-title">Upcoming Events</h2>
                <span class="events-count"><?php echo mysqli_num_rows($events); ?> Events</span>
            </div>

            <?php if (mysqli_num_rows($events) > 0): ?>
                <div class="events-grid">
                    <?php while($e = mysqli_fetch_assoc($events)): ?>
                        <div class="event-card">
                            <?php if (!empty($e['image'])): ?>
                                <img src="../admin/uploads/<?php echo htmlspecialchars($e['image']); ?>" alt="<?php echo htmlspecialchars($e['title']); ?>" class="event-image"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDM1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjhGOUZBIi8+CjxwYXRoIGQ9Ik0xNzUgODBDMTk0LjMzIDgwIDIxMCA2NC4zMyAyMTAgNDVDMjEwIDI1LjY3IDE5NC4zMyAxMCAxNzUgMTBDMTU1LjY3IDEwIDE0MCAyNS42NyAxNDAgNDVDMTQwIDY0LjMzIDE1NS42NyA4MCAxNzUgODBaIiBmaWxsPSIjREREREREIi8+CjxwYXRoIGQ9Ik0xMDUgMTUwVjEyMEMxMTcgMTIwIDEyOSAxMTAgMTQwIDExMEgyMTBDMjIyIDExMCAyMzQgMTIwIDIzNCAxMzBWMTUwSDEwNVoiIGZpbGw9IiNEREREREIiLz4KPC9zdmc+'">
                            <?php else: ?>
                                <div style="width:100%;height:200px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;color:#ddd;">
                                    <i class="fas fa-calendar-alt" style="font-size: 40px;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($e['title']); ?></h3>
                                <p class="event-description"><?php echo htmlspecialchars($e['description']); ?></p>
                                
                                <div class="event-meta">
                                    <div class="event-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('M j, Y', strtotime($e['event_date'])); ?>
                                    </div>
                                    <div class="event-actions">
                                        <?php if (!empty($e['registration_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($e['registration_link']); ?>" target="_blank" class="btn btn-primary">
                                                <i class="fas fa-external-link-alt"></i> Register
                                            </a>
                                        <?php endif; ?>
                                        <a href="event.php?id=<?php echo $e['id']; ?>" class="btn btn-outline">
                                            <i class="fas fa-info-circle"></i> Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Upcoming Events</h3>
                    <p>There are no upcoming events in this category at the moment.</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-arrow-left"></i> Browse All Categories
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Other Categories Section -->
        <?php if (mysqli_num_rows($other_cats) > 0): ?>
        <div class="other-categories">
            <h2 class="section-title" style="color: white; text-align: center; margin-bottom: 25px;">Explore Other Categories</h2>
            <div class="categories-grid">
                <?php 
                $category_icons = ['fas fa-graduation-cap', 'fas fa-briefcase', 'fas fa-users', 'fas fa-flask', 'fas fa-paint-brush', 'fas fa-heart', 'fas fa-code', 'fas fa-globe'];
                $i = 0;
                while($other_cat = mysqli_fetch_assoc($other_cats)): 
                    $icon = $category_icons[$i % count($category_icons)];
                ?>
                    <a href="category.php?id=<?php echo $other_cat['id']; ?>" class="category-card">
                        <div class="category-card-icon">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <h3 class="category-card-name"><?php echo htmlspecialchars($other_cat['name']); ?></h3>
                    </a>
                <?php 
                    $i++;
                endwhile; 
                ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Campus Opportunities Portal. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>