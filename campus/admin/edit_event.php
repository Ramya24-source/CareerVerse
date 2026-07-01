<?php 
include 'includes/auth_check.php'; 
include 'includes/db.php';

$success = "";
$error = "";

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch event details
$event_query = mysqli_query($conn, "SELECT e.*, c.name AS catname FROM events e 
JOIN categories c ON e.category_id = c.id WHERE e.id = $event_id");
$event = mysqli_fetch_assoc($event_query);

// If event doesn't exist, redirect
if (!$event) {
    header("Location: manage_events.php?error=Event not found");
    exit;
}

// Fetch categories for dropdown
$cats = mysqli_query($conn, "SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $link = mysqli_real_escape_string($conn, $_POST['registration_link']);

    // Handle image upload if new image is provided
    $imgName = $event['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imgName = time() . '_' . basename($_FILES['image']['name']);
        $imgTmp = $_FILES['image']['tmp_name'];
        $target = "uploads/" . $imgName;
        
        // Check if uploads directory exists, create if not
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        
        // Validate image file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($imgTmp);
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($imgTmp, $target)) {
                // Delete old image if it exists and is different from new one
                if (!empty($event['image']) && $event['image'] != $imgName && file_exists("uploads/" . $event['image'])) {
                    unlink("uploads/" . $event['image']);
                }
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        } else {
            $error = "Invalid image format. Please upload JPEG, PNG, GIF, or WebP images.";
        }
    }

    if (empty($error)) {
        // Remove updated_at from the query since it doesn't exist in your table
        $query = "UPDATE events SET 
                  category_id = '$cat', 
                  title = '$title', 
                  description = '$desc', 
                  event_date = '$date', 
                  registration_link = '$link', 
                  image = '$imgName'
                  WHERE id = $event_id";
        
        if (mysqli_query($conn, $query)) {
            $success = "Event updated successfully!";
            // Refresh event data
            $event_query = mysqli_query($conn, "SELECT e.*, c.name AS catname FROM events e 
            JOIN categories c ON e.category_id = c.id WHERE e.id = $event_id");
            $event = mysqli_fetch_assoc($event_query);
        } else {
            $error = "Error updating event: " . mysqli_error($conn);
        }
    }
}

// Reset categories pointer for the form
mysqli_data_seek($cats, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin Panel</title>
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

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 25px;
            text-align: center;
        }

        .form-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #666;
            font-size: 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .form-label .required {
            color: var(--warning);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background-color: white;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 45px;
        }

        /* File Upload Styling */
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .file-upload-label i {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .file-upload-label span {
            color: #666;
            font-size: 15px;
        }

        .file-upload-label .file-name {
            margin-top: 10px;
            font-weight: 500;
            color: var(--dark);
        }

        .file-preview {
            margin-top: 15px;
            text-align: center;
        }

        .file-preview img {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .current-image {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex: 1;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        /* Event Info Card */
        .event-info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
        }

        .event-info-card h4 {
            color: var(--dark);
            margin-bottom: 10px;
            font-size: 16px;
        }

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            font-size: 14px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: #666;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--dark);
            font-weight: 500;
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
            
            .form-container {
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
                <h1 class="page-title">Edit Event</h1>
                <div class="breadcrumb">
                    <a href="index.php">Dashboard</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="manage_events.php">Manage Events</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Edit Event</span>
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <div class="form-header">
                    <h2>Edit Event: <?php echo htmlspecialchars($event['title']); ?></h2>
                    <p>Update the event details below</p>
                </div>

                <!-- Event Information Card -->
                <div class="event-info-card">
                    <h4>Event Information</h4>
                    <div class="event-info-grid">
                        <div class="info-item">
                            <span class="info-label">Created Date</span>
                            <span class="info-value">
                                <?php 
                                if (isset($event['created_at'])) {
                                    echo date('M j, Y g:i A', strtotime($event['created_at']));
                                } else {
                                    echo 'Not available';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Category</span>
                            <span class="info-value"><?php echo htmlspecialchars($event['catname']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Event Form -->
                <form method="POST" enctype="multipart/form-data" id="eventForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category" class="form-label">Category <span class="required">*</span></label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php while($c = mysqli_fetch_assoc($cats)): ?>
                                    <option value="<?php echo $c['id']; ?>" 
                                        <?php echo ($event['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="event_date" class="form-label">Event Date <span class="required">*</span></label>
                            <input type="date" id="event_date" name="event_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($event['event_date']); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="title" class="form-label">Event Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   placeholder="Enter event title" 
                                   value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="description" class="form-label">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-control" 
                                      placeholder="Enter event description" 
                                      required><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="registration_link" class="form-label">Registration Link</label>
                            <input type="url" id="registration_link" name="registration_link" class="form-control" 
                                   placeholder="https://example.com/register" 
                                   value="<?php echo htmlspecialchars($event['registration_link']); ?>">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Event Image</label>
                            <?php if (!empty($event['image'])): ?>
                                <div class="file-preview">
                                    <img src="uploads/<?php echo htmlspecialchars($event['image']); ?>" 
                                         alt="Current event image" 
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0Cb3g9IjAgMCAzMDAgMjAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgcng9IjgiIGZpbGw9IiNGNEY5RkEiLz4KPHBhdGggZD0iTTE1MCA3MEMxNjYuNTQ4IDcwIDE4MCA1Ni41NDc3IDE4MCA0MEMxODAgMjMuNDUyMyAxNjYuNTQ4IDEwIDE1MCAxMEMxMzMuNDUyIDEwIDEyMCAyMy40NTIzIDEyMCA0MEMxMjAgNTYuNTQ3NyAxMzMuNDUyIDcwIDE1MCA3MFoiIGZpbGw9IiNEREREREQiLz4KPHBhdGggZD0iTTkwIDE1MFYxMjBDMTAwIDEyMCAxMTAgMTEwIDEyMCAxMTBIMTgwQzE5MCAxMTAgMjAwIDEyMCAyMDAgMTMwVjE1MEg5MFoiIGZpbGw9IiNEREREREIiLz4KPC9zdmc+Cg=='">
                                </div>
                                <div class="current-image">
                                    <i class="fas fa-info-circle"></i> Current image: <?php echo htmlspecialchars($event['image']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="file-upload" style="margin-top: 15px;">
                                <input type="file" id="image" name="image" class="file-upload-input" accept="image/*">
                                <label for="image" class="file-upload-label" id="fileUploadLabel">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click to upload new event image (optional)</span>
                                    <span class="file-name" id="fileName">No file chosen</span>
                                </label>
                            </div>
                            <div class="file-preview" id="filePreview"></div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="manage_events.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Events
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('eventForm');
            const imageInput = document.getElementById('image');
            const fileUploadLabel = document.getElementById('fileUploadLabel');
            const fileName = document.getElementById('fileName');
            const filePreview = document.getElementById('filePreview');
            const submitBtn = document.getElementById('submitBtn');
            const eventDate = document.getElementById('event_date');

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            eventDate.min = today;

            // File upload preview
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileName.textContent = file.name;
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a valid image file (JPEG, PNG, GIF, or WebP).');
                        this.value = '';
                        fileName.textContent = 'No file chosen';
                        filePreview.innerHTML = '';
                        return;
                    }
                    
                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image size should be less than 5MB.');
                        this.value = '';
                        fileName.textContent = 'No file chosen';
                        filePreview.innerHTML = '';
                        return;
                    }
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    filePreview.innerHTML = '';
                    fileName.textContent = 'No file chosen';
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating Event...';
                submitBtn.disabled = true;
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

            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
    </script>
</body>
</html>