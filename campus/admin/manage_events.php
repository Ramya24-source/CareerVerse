<?php 
include 'includes/auth_check.php'; 
include 'includes/db.php'; 


$events = mysqli_query($conn, "SELECT e.*, c.name AS catname FROM events e 
JOIN categories c ON e.category_id = c.id ORDER BY e.created_at DESC");

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Panel</title>
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #e6ffe6;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }

        .alert-error {
            background-color: #ffe6e6;
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h3 {
            color: var(--dark);
            font-size: 1.3rem;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            width: 250px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        /* Events Table */
        .events-table {
            width: 100%;
            border-collapse: collapse;
        }

        .events-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-weight: 600;
            font-size: 14px;
            background: #f8f9fa;
        }

        .events-table td {
            padding: 15px;
            border-bottom: 1px solid #f5f5f5;
        }

        .events-table tr:last-child td {
            border-bottom: none;
        }

        .events-table tr:hover {
            background-color: #f8f9fa;
        }

        .event-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .event-title {
            font-weight: 500;
            color: var(--dark);
            max-width: 250px;
        }

        .event-description {
            color: #666;
            font-size: 14px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .event-category {
            display: inline-block;
            padding: 5px 12px;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .event-date {
            color: #666;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .btn-edit:hover {
            background: var(--success);
            color: white;
        }

        .btn-delete {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning);
        }

        .btn-delete:hover {
            background: var(--warning);
            color: white;
        }

        .btn-view {
            background: rgba(72, 149, 239, 0.1);
            color: var(--info);
        }

        .btn-view:hover {
            background: var(--info);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #888;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--dark);
            font-size: 1.3rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #888;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-cancel:hover {
            background: #e9ecef;
        }

        .btn-confirm {
            background: var(--warning);
            color: white;
        }

        .btn-confirm:hover {
            background: #e1156d;
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
            .header {
                padding: 0 15px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .user-info span {
                display: none;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .events-table {
                display: block;
                overflow-x: auto;
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
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="add_category.php"><i class="fas fa-plus-circle"></i> <span>Add Category</span></a></li>
                <li><a href="add_event.php"><i class="fas fa-calendar-plus"></i> <span>Add Event</span></a></li>
                <li><a href="manage_events.php" class="active"><i class="fas fa-calendar-alt"></i> <span>Manage Events</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Admin Panel</h1>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Manage Events</h1>
                <div class="breadcrumb">
                    <a href="index.php">Dashboard</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Manage Events</span>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Table Container -->
            <div class="table-container">
                <div class="table-header">
                    <h3>All Events (<?php echo mysqli_num_rows($events); ?>)</h3>
                    <div class="table-actions">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search events...">
                        </div>
                        <a href="add_event.php" class="btn-primary">
                            <i class="fas fa-plus"></i> Add New Event
                        </a>
                    </div>
                </div>

                <?php if (mysqli_num_rows($events) > 0): ?>
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Event Details</th>
                                <th style="width: 120px;">Category</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($e = mysqli_fetch_assoc($events)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($e['image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($e['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($e['title']); ?>" 
                                                 class="event-image"
                                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjYwIiByeD0iOCIgZmlsbD0iI0Y4RjlGQSIvPgo8cGF0aCBkPSJNMzAgMjBDMzMuMzEzNyAyMCAzNiAxNy4zMTM3IDM2IDE0QzM2IDEwLjY4NjMgMzMuMzEzNyA4IDMwIDhDMjYuNjg2MyA4IDI0IDEwLjY4NjMgMjQgMTRDMjQgMTcuMzEzNyAyNi42ODYzIDIwIDMwIDIwWiIgZmlsbD0iI0RERERERCIvPgo8cGF0aCBkPSJNMTggNDJWMzZDMjAgMzYgMjIgMzQgMjQgMzRIMzZDMzggMzQgNDAgMzYgNDAgMzhWNDJIMThaIiBmaWxsPSIjREREREREIi8+Cjwvc3ZnPgo='">
                                        <?php else: ?>
                                            <div style="width:60px;height:60px;background:#f8f9fa;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ddd;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="event-title"><?php echo htmlspecialchars($e['title']); ?></div>
                                        <div class="event-description"><?php echo htmlspecialchars($e['description']); ?></div>
                                    </td>
                                    <td>
                                        <span class="event-category"><?php echo htmlspecialchars($e['catname']); ?></span>
                                    </td>
                                    <td>
                                        <div class="event-date"><?php echo date('M j, Y', strtotime($e['event_date'])); ?></div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_event.php?id=<?php echo $e['id']; ?>" class="btn btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-delete" onclick="confirmDelete(<?php echo $e['id']; ?>, '<?php echo htmlspecialchars(addslashes($e['title'])); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Events Found</h3>
                        <p>Get started by creating your first campus event.</p>
                        <a href="add_event.php" class="btn-primary">
                            <i class="fas fa-plus"></i> Add New Event
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the event "<strong id="eventTitle"></strong>"?</p>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-confirm">
                    <i class="fas fa-trash"></i> Delete Event
                </a>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.events-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Modal functions
        function confirmDelete(eventId, eventTitle) {
            document.getElementById('eventTitle').textContent = eventTitle;
            document.getElementById('confirmDelete').href = 'delete_event.php?id=' + eventId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Auto-hide success message after 5 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                setTimeout(() => {
                    successAlert.style.display = 'none';
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>