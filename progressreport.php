<?php session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Progress Report</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
      <link rel="stylesheet" href="progressreport.css?v=<?php echo filemtime('progressreport.css'); ?>">

</head>

<body>
    <?php include 'housekeeping_navbar.php'; ?>

    <div class="main p-3">
        <div class="text-center">
            <h1 class="mb-4">Progress Report</h1>
            
            <div id="message" class="alert" style="display:none;"></div>

            <div class="container py-2">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <i class="lni lni-clipboard me-2"></i>
                                <span>Submit Progress Report</span>
                            </div>
                            <div class="card-body p-4">
                                <form id="reportForm" action="submit_report.php" method="POST" enctype="multipart/form-data">
                                    <!-- Image Capture Section -->
                                    <div class="form-group mb-4">
                                        <label for="reportImage" class="section-title">
                                            <i class="lni lni-camera"></i> Capture Image
                                        </label>
                                        <div class="text-center">
                                            <label for="reportImage" class="btn btn-capture">
                                                <i class="lni lni-camera"></i> Capture Image
                                            </label>
                                            <input type="file" 
                                                class="form-control" 
                                                id="reportImage" 
                                                name="reportImage" 
                                                accept="image/*" 
                                                capture="camera" 
                                                style="display: none;" 
                                                onchange="validateCapture(event)">
                                            <p class="text-muted mt-2 small">Click the button above to capture an image using your camera</p>
                                        </div>
                                    </div>

                                    <!-- Image Preview Section -->
                                    <div id="imagePreviewContainer" class="mb-4 text-center" style="display: none;">
                                        <h6 class="mb-3" style="color: var(--maroon);">Image Preview:</h6>
                                        <img id="imagePreview" src="/placeholder.svg" alt="Captured Image" class="img-fluid">
                                    </div>

                                    <!-- Location Selection Section -->
                                    <div class="form-group mb-4">
                                        <label for="updateLocation" class="section-title">
                                            <i class="lni lni-map-marker"></i> Select Location
                                        </label>
                                        <select class="form-control" id="updateLocation" name="location">
                                            <option value="">Select Location</option>
                                        </select>
                                    </div>

                                    <!-- Description Section -->
                                    <div class="form-group mb-4">
                                        <label for="reportDescription" class="section-title">
                                            <i class="lni lni-text-format"></i> Description
                                        </label>
                                        <textarea class="form-control" id="reportDescription" name="reportDescription" rows="4" placeholder="Enter a brief description of your progress..."></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="lni lni-upload me-2"></i> Submit Report
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>

    <script>
        const logoutLink = document.getElementById('logout-link');
        logoutLink.addEventListener('click', function(event) {
            event.preventDefault();
            const confirmLogout = confirm('Are you sure you want to log out?');
            if (confirmLogout) {
                window.location.href = logoutLink.href;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageElement = document.getElementById('message');

            function getQueryParams() {
                const params = new URLSearchParams(window.location.search);
                return {
                    message: params.get('message'),
                    status: params.get('status')
                };
            }

            const {
                message,
                status
            } = getQueryParams();
            if (message) {
                messageElement.textContent = message;
                messageElement.className = 'alert alert-' + status;
                messageElement.style.display = 'block';

                setTimeout(() => {
                    messageElement.style.display = 'none';
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 5000);
            } else {
                messageElement.style.display = 'none';
            }

            const fileInput = document.getElementById('reportImage');
            const fileNameInput = document.getElementById('imageFileName');
            if (fileInput && fileNameInput) {
                fileInput.addEventListener('change', function() {
                    const fileName = fileInput.files[0] ? fileInput.files[0].name : 'No file chosen';
                    fileNameInput.value = fileName;
                });
            }
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locationSelect = document.getElementById('updateLocation');

            fetch('get_locations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        data.locations.forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.location_name;
                            option.textContent = location.location_name;
                            locationSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No locations available';
                        locationSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching locations:', error);
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Error fetching locations';
                    locationSelect.appendChild(option);
                });
        });
    </script>
    
    <script>
        // Function to reset the image preview container
        function resetImagePreview() {
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const imagePreview = document.getElementById('imagePreview');
            
            // Reset the preview image source and hide the container
            imagePreview.src = '';
            imagePreviewContainer.style.display = 'none';
        }

        // Function to handle the image capture and preview
        function validateCapture(event) {
            const fileInput = event.target;
            const file = fileInput.files[0];

            // Check if a file was selected
            if (!file) {
                return;
            }

            // Reset the preview for the latest image
            resetImagePreview();

            // Preview the captured image
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const imagePreview = document.getElementById('imagePreview');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('form').addEventListener('submit', function (event) {
                const description = document.getElementById('reportDescription').value.trim();
                const fileInput = document.getElementById('reportImage');
                const location = document.getElementById('updateLocation').value;

                if (!description || !fileInput.files.length || !location) {
                    alert("Please fill in all required fields before submitting.");
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
