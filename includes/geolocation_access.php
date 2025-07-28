<?php
/**
 * Geolocation Access Control System
 * Single-file implementation with all features
 */

// ==============================================
// CONFIGURATION SECTION - EDIT THESE VALUES
// ==============================================
$allowedLocations = [
    [
        'latitude' => 6.920857,    // Example: New York
        'longitude' => 80.091139,
        'name' => 'Office NAF'
    ],
    [
        'latitude' => 34.0522,   // Example: Los Angeles
        'longitude' => -118.2437,
        'name' => 'op1'
    ],
    [
        'latitude' => 51.5074,    // Example: London
        'longitude' => -0.1278,
        'name' => 'op3'
    ]
];

define('ALLOWED_RADIUS', 200); // Access radius in meters
define('COOKIE_EXPIRY', 60);   // Cookie expiry in seconds (10 minute)

// ==============================================
// CORE FUNCTIONS - DON'T MODIFY BELOW THIS POINT
// ==============================================

/**
 * Calculate distance between two coordinates using Haversine formula
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Earth's radius in meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

/**
 * Check if current location is within any allowed location
 */
function isLocationAllowed($currentLat, $currentLon) {
    global $allowedLocations;
    
    foreach ($allowedLocations as $location) {
        $distance = calculateDistance(
            $currentLat, 
            $currentLon, 
            $location['latitude'], 
            $location['longitude']
        );
        
        if ($distance <= ALLOWED_RADIUS) {
            return true;
        }
    }
    
    return false;
}

/**
 * Handle the geolocation check
 */
function checkGeolocationAccess() {
    
    // Check if location data was posted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latitude']) && isset($_POST['longitude'])) {
        $currentLat = floatval($_POST['latitude']);
        $currentLon = floatval($_POST['longitude']);
        
        if (isLocationAllowed($currentLat, $currentLon)) {

            // Location is allowed - set a cookie with timestamp
            setcookie('geo_access', 'granted', time() + COOKIE_EXPIRY, '/');
            return true;
        } else {
            // Location not allowed
            setcookie('geo_access', 'denied', time() + COOKIE_EXPIRY, '/');
            return false;
        }
    }
    
    // Check if access was previously granted via cookie
    if (isset($_COOKIE['geo_access']) && $_COOKIE['geo_access'] === 'granted') {
        return true;
    }
    
    return false;
}

// ==============================================
// MAIN EXECUTION - HANDLE ACCESS CONTROL
// ==============================================

// If this is a POST request with coordinates, just process it and exit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latitude'])) {
    header('Content-Type: application/json');
    echo json_encode(['access' => checkGeolocationAccess()]);
    exit();
}

// Check access for normal page requests
if (!checkGeolocationAccess()) {
    // Show access denied page with location information
    showAccessDenied();
    exit();
}

// ==============================================
// ACCESS GRANTED - YOUR PROTECTED CONTENT WOULD GO HERE
// ==============================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Protected Content</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: #28a745; padding: 20px; background-color: #f8f9fa; border-radius: 5px; }
    </style>
</head>
<body>
    <!-- Your protected content would go here -->
    
    <?php includeGeolocationScript(); ?>
</body>
</html>

<?php
// ==============================================
// FUNCTION DEFINITIONS
// ==============================================

/**
 * Show the access denied page with location information
 */
function showAccessDenied() {
    header("HTTP/1.1 403 Forbidden");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #d9534f; }
        .location-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px auto;
            max-width: 400px;
        }
        .coordinates {
            font-family: monospace;
            font-size: 1.1em;
        }
        .allowed-locations {
            margin: 30px auto;
            max-width: 500px;
            text-align: left;
        }
        .location {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Access Denied</h1>
    <p>You are not within the allowed geographic location to access this content.</p>
    
    <div class="location-info">
        <h3>Your Current Location</h3>
        <div id="currentLocation">
            <p>Loading your coordinates...</p>
        </div>
    </div>
    
    <div class="allowed-locations">
        <h3>Authorized Locations</h3>
        <?php
        global $allowedLocations;
        foreach ($allowedLocations as $loc) {
            echo '<div class="location">';
            echo '<strong>' . htmlspecialchars($loc['name']) . '</strong><br>';
            echo 'Latitude: <span class="coordinates">' . $loc['latitude'] . '</span>, ';
            echo 'Longitude: <span class="coordinates">' . $loc['longitude'] . '</span>';
            echo '</div>';
        }
        ?>
    </div>
    
    <p>Please move to an authorized location (within <?php echo ALLOWED_RADIUS; ?> meters) and refresh the page.</p>
    <button onclick="window.location.reload()">Retry</button>

    <script>
        // Function to get high-accuracy location
        function showCurrentLocation() {
            if (navigator.geolocation) {
                console.log("User's current location" + navigator.geolocation);
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude.toFixed(8);
                        const lng = position.coords.longitude.toFixed(8);
                        const accuracy = position.coords.accuracy.toFixed(1);
                        document.getElementById('currentLocation').innerHTML = `
                            <p>Latitude: <span class="coordinates">${lat}</span></p>
                            <p>Longitude: <span class="coordinates">${lng}</span></p>
                            <p>Accuracy: <span class="coordinates">${accuracy} meters</span></p>
                        `;
                        
                        // Automatically verify with server
                        verifyLocation(position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        let errorMessage = "Unable to retrieve your location: ";
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += "You denied the request for geolocation. Please allow location access.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += "Location information is unavailable. Ensure GPS is enabled.";
                                break;
                            case error.TIMEOUT:
                                errorMessage += "The request to get location timed out. Try again.";
                                break;
                            case error.UNKNOWN_ERROR:
                                errorMessage += "An unknown error occurred.";
                                break;
                        }
                        document.getElementById('currentLocation').innerHTML = 
                            `<p style="color: #dc3545;">${errorMessage}</p>`;
                    },
                    { 
                        enableHighAccuracy: true, 
                        timeout: 10000, 
                        maximumAge: 0 
                    }
                );
            } else {
                document.getElementById('currentLocation').innerHTML = 
                    "<p>Geolocation is not supported by your browser.</p>";
            }
        }

        // Verify location with server
        function verifyLocation(latitude, longitude) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `latitude=${latitude}&longitude=${longitude}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.access) {
                    // If access is granted, reload the page
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Start monitoring when page loads
        document.addEventListener('DOMContentLoaded', function() {
            showCurrentLocation();
            // Set up periodic checking every 30 seconds
            setInterval(showCurrentLocation, 30000);
        });
    </script>
</body>
</html>
<?php
}

/**
 * Include the geolocation monitoring script
 */
function includeGeolocationScript() {
?>
<script>
    // Continuous geolocation monitoring with high accuracy
    function monitorGeolocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    verifyLocation(position.coords.latitude, position.coords.longitude);
                },
                function(error) {
                    console.error("Geolocation error:", error);
                    handleLocationError();
                },
                { 
                    enableHighAccuracy: true, 
                    timeout: 10000, 
                    maximumAge: 0 
                }
            );
        }
    }

    function verifyLocation(latitude, longitude) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `latitude=${latitude}&longitude=${longitude}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.access) {
                handleLocationError();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            handleLocationError();
        });
    }

    function handleLocationError() {
        // Redirect to denied page if not already there
        if (!window.location.href.includes('denied')) {
            window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'denied=1';
        }
    }

    // Start monitoring when page loads and every 30 seconds
    document.addEventListener('DOMContentLoaded', function() {
        monitorGeolocation();
        setInterval(monitorGeolocation, 30000);
    });
</script>
<?php
}
?>