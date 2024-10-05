
<?php
// Check if the form has been submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect student registration form data with fallback for undefined keys
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $number = $_POST['number'] ?? null;
    $uname = $_POST['uname'] ?? null;
    $degree = $_POST['degree'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $cgpa = $_POST['cgpa'] ?? null;
    $address = $_POST['address'] ?? null;
    $resume = $_POST['resume'] ?? null;
    $linkedin = $_POST['linkedin'] ?? null;
    $github = $_POST['github'] ?? null;
    $marksheet10 = $_POST['marksheet10'] ?? null;
    $marksheet12 = $_POST['marksheet12'] ?? null;
    $certificate = $_POST['certificate'] ?? null;

    // Database connection
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASSWORD", "pranjali");
    define("DB_DATABASE", "studentdb");

    // Attempt to connect to MySQL database
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

    // Check if the connection was successful
    if (!$conn) {
        die('Connection Failed: ' . mysqli_connect_error());
    }

    // Prepare an SQL statement to prevent SQL injection for student registration
    $stmt = $conn->prepare("INSERT INTO studentdetails (name, number, uname, degree, semester, cgpa, resume, linkedin, github, address, email, marksheet10, marksheet12, certificate)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Adjust bind_param according to your actual parameter types
    $stmt->bind_param("ssssiissssssss", $name, $number, $uname, $degree, $semester, $cgpa, $resume, $linkedin, $github, $address, $email, $marksheet10, $marksheet12, $certificate);

    // Execute the query for student registration
    if ($stmt->execute()) {
        echo "Registration Successful.<br>";
        // Get the last inserted student ID for attendance
        $student_id = $conn->insert_id;

        // Now insert attendance record
        $class_date = $_POST['class_date'] ?? date('Y-m-d'); // Assuming current date if not provided
        $status = $_POST['status'] ?? 'Present'; // Default status
        $remarks = $_POST['remarks'] ?? ''; // Optional remarks

        // Prepare an SQL statement for attendance
        $attendance_stmt = $conn->prepare("INSERT INTO attendance (student_id, class_date, status, remarks) VALUES (?, ?, ?, ?)");
        $attendance_stmt->bind_param("isss", $student_id, $class_date, $status, $remarks);

        // Execute the query for attendance
        if ($attendance_stmt->execute()) {
            echo "Attendance recorded successfully.";
        } else {
            echo "Error recording attendance: " . $attendance_stmt->error;
        }

        // Close the attendance statement
        $attendance_stmt->close();

    } else {
        echo "Error: " . $stmt->error; // Use $stmt->error instead of $conn->error for prepared statements
    }

    // Close the student registration statement
    $stmt->close();

    // Handle file upload
    if (isset($_FILES["fileToUpload"])) {
        $target_dir = "newImages/";  // Folder where the image will get stored
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a real image
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check === false) {
            echo "\r\n File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "\r\n Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size (limit to 500KB)
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            echo "\r\n Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            echo "\r\n Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Attempt to upload the file if there were no errors
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";
            } else {
                echo "\r\n Sorry, there was an error uploading your file.";
            }
        } else {
            echo "\r\n Sorry, your file was not uploaded.";
        }
    } else {
        echo "No file uploaded.";
    }

    // Run the Python script to add new encodings
    $command = escapeshellcmd('python Encodings.py');
    $output = shell_exec($command);

    // Redirect the user after successful registration
    echo '<script>alert("Registration and attendance recorded successfully"); window.location="scanFace.html";</script>';
    exit();
} else {
    echo "No form data submitted.";
}
?>
