<?php 
include '../config.php';

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query for events
$events_query = "SELECT e.*, c.name as category_name FROM events e 
                 JOIN categories c ON e.category_id = c.id 
                 WHERE e.event_date >= CURDATE()";

if (!empty($search)) {
    $events_query .= " AND (e.title LIKE '%$search%' OR e.description LIKE '%$search%')";
}

if ($category_filter > 0) {
    $events_query .= " AND e.category_id = $category_filter";
}

$events_query .= " ORDER BY e.event_date ASC";
$events = mysqli_query($conn, $events_query);

// Get categories for filter
$cats = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Opportunities Portal</title>
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

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .search-input {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-btn {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(102, 126, 234, 0.3);
        }

        .reset-btn {
            background: var(--light);
            color: var(--text);
            border: 2px solid #e9ecef;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reset-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        /* Categories Section */
        .categories-section {
            margin-bottom: 40px;
        }

        .section-title {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

        .category-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 28px;
        }

        .category-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .category-description {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Events Section */
        .events-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .events-title {
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

        .event-category {
            display: inline-block;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
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
            -webkit-line-clamp: 2;
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

            .search-form {
                grid-template-columns: 1fr;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .events-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .categories-grid {
                grid-template-columns: 1fr;
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

            .search-section, .events-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Campus Opportunities Portal</h1>
            <p>Discover exciting events, workshops, and opportunities across campus</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label class="form-label">Search Events</label>
                    <input type="text" name="search" class="search-input" placeholder="Search by event title or description..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Filter by Category</label>
                    <select name="category" class="search-input">
                        <option value="0">All Categories</option>
                        <?php 
                        mysqli_data_seek($cats, 0);
                        while($c = mysqli_fetch_assoc($cats)): 
                        ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($category_filter == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
            
            <?php if (!empty($search) || $category_filter > 0): ?>
                <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="color: var(--text-light);">
                            <?php 
                            $results_count = mysqli_num_rows($events);
                            echo "Found $results_count event" . ($results_count != 1 ? 's' : '');
                            if (!empty($search)) echo " for \"$search\"";
                            if ($category_filter > 0) {
                                mysqli_data_seek($cats, 0);
                                while($c = mysqli_fetch_assoc($cats)) {
                                    if ($c['id'] == $category_filter) {
                                        echo " in " . htmlspecialchars($c['name']);
                                        break;
                                    }
                                }
                            }
                            ?>
                        </span>
                    </div>
                    <a href="?" class="reset-btn">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Categories Section -->
        <div class="categories-section">
            <h2 class="section-title">Browse by Category</h2>
            <div class="categories-grid">
                <?php 
                mysqli_data_seek($cats, 0);
                $category_icons = ['fas fa-graduation-cap', 'fas fa-briefcase', 'fas fa-users', 'fas fa-flask', 'fas fa-paint-brush', 'fas fa-heart', 'fas fa-code', 'fas fa-globe'];
                $i = 0;
                while($c = mysqli_fetch_assoc($cats)): 
                    $icon = $category_icons[$i % count($category_icons)];
                ?>
                    <a href="?category=<?php echo $c['id']; ?>" class="category-card">
                        <div class="category-icon">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <h3 class="category-name"><?php echo htmlspecialchars($c['name']); ?></h3>
                        <p class="category-description">Explore all events in this category</p>
                    </a>
                <?php 
                    $i++;
                endwhile; 
                ?>
            </div>
        </div>

        <!-- Events Section -->
        <div class="events-section">
            <div class="events-header">
                <h2 class="events-title">Upcoming Events</h2>
                <span class="events-count"><?php echo mysqli_num_rows($events); ?> Events</span>
            </div>

            <?php if (mysqli_num_rows($events) > 0): ?>
                <div class="events-grid">
                    <?php while($event = mysqli_fetch_assoc($events)): ?>
                        <div class="event-card">
                            <?php if (!empty($event['image'])): ?>
                                <img src="../admin/uploads/<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDM1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzNTAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjhGOUZBIi8+CjxwYXRoIGQ9Ik0xNzUgODBDMTk0LjMzIDgwIDIxMCA2NC4zMyAyMTAgNDVDMjEwIDI1LjY3IDE5NC4zMyAxMCAxNzUgMTBDMTU1LjY3IDEwIDE0MCAyNS42NyAxNDAgNDVDMTQwIDY0LjMzIDE1NS42NyA4MCAxNzUgODBaIiBmaWxsPSIjREREREREIi8+CjxwYXRoIGQ9Ik0xMDUgMTUwVjEyMEMxMTcgMTIwIDEyOSAxMTAgMTQwIDExMEgyMTBDMjIyIDExMCAyMzQgMTIwIDIzNCAxMzBWMTUwSDEwNVoiIGZpbGw9IiNEREREREIiLz4KPC9zdmc+'">
                            <?php else: ?>
                                <div style="width:100%;height:200px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;color:#ddd;">
                                    <i class="fas fa-calendar-alt" style="font-size: 40px;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-content">
                                <span class="event-category"><?php echo htmlspecialchars($event['category_name']); ?></span>
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                
                                <div class="event-meta">
                                    <div class="event-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                    </div>
                                    <div class="event-actions">
                                        <?php if (!empty($event['registration_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($event['registration_link']); ?>" target="_blank" class="btn btn-primary">
                                                <i class="fas fa-external-link-alt"></i> Register
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-outline" onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Events Found</h3>
                    <p>Try adjusting your search criteria or browse all categories</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Campus Opportunities Portal. All rights reserved.</p>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:12px; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; position:relative;">
            <button onclick="closeEventModal()" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:20px; cursor:pointer; color:#666;">&times;</button>
            <div id="modalContent" style="padding:30px;">
                <!-- Content will be loaded here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function showEventDetails(event) {
            const modal = document.getElementById('eventModal');
            const content = document.getElementById('modalContent');
            
            let imageHtml = '';
            if (event.image) {
                imageHtml = `<img src="../admin/uploads/${event.image}" alt="${event.title}" style="width:100%; height:250px; object-fit:cover; border-radius:8px; margin-bottom:20px;" 
                                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjI1MCIgdmlld0JveD0iMCAwIDYwMCAyNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iMjUwIiBmaWxsPSIjRjhGOUZBIi8+CjxwYXRoIGQ9Ik0zMDAgMTAwQzMzMy4xMzcgMTAwIDM2MCA3My4xMzcyIDM2MCA0MEMzNjAgNi44NjI3NSAzMzMuMTM3IC0yMCAzMDAgLTIwQzI2Ni44NjMgLTIwIDI0MCA2Ljg2Mjc1IDI0MCA0MEMyNDAgNzMuMTM3MiAyNjYuODYzIDEwMCAzMDAgMTAwWiIgZmlsbD0iI0RERERERCIvPgo8cGF0aCBkPSJNMTgwIDIwMFYxNjBDMjAwIDE2MCAyMjAgMTUwIDI0MCAxNTBIMzYwQzM4MCAxNTAgNDAwIDE2MCA0MDAgMTcwVjIwMEgxODBaIiBmaWxsPSIjREREREREIi8+Cjwvc3ZnPg=='">`;
            }
            
            content.innerHTML = `
                ${imageHtml}
                <span style="display:inline-block; background:rgba(67, 97, 238, 0.1); color:#4361ee; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:15px;">${event.category_name}</span>
                <h2 style="color:#212529; margin-bottom:15px; font-size:1.5rem;">${event.title}</h2>
                <p style="color:#666; margin-bottom:20px; line-height:1.6;">${event.description}</p>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:25px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-calendar" style="color:#4361ee;"></i>
                        <div>
                            <div style="font-size:12px; color:#6c757d;">Event Date</div>
                            <div style="font-weight:600;">${new Date(event.event_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</div>
                        </div>
                    </div>
                </div>
                
                ${event.registration_link ? `
                <div style="text-align:center; margin-top:25px;">
                    <a href="${event.registration_link}" target="_blank" style="background:linear-gradient(to right, #4361ee, #3a0ca3); color:white; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-external-link-alt"></i> Register Now
                    </a>
                </div>
                ` : ''}
            `;
            
            modal.style.display = 'flex';
        }

        function closeEventModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('eventModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEventModal();
            }
        });
    </script>
</body>
</html>