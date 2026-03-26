<?php
include 'header.php';
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the selected vehicle type from URL parameter
$vehicle_type = isset($_GET['type']) ? $_GET['type'] : '';

// Vehicle categories and their options
$vehicle_categories = [
    'car' => [
        'taxi_car' => 'Taxi Car',
        'carpooling' => 'Carpooling'
    ],
    'goods' => [
        '7ft_vehicle' => '7ft Vehicle',
        '8ft_vehicle' => '8ft Vehicle',
        '3wheeler_cargo' => '3 Wheeler Cargo',
        'tata_407' => 'Tata 407'
    ],
    'bike' => [
        'bike' => 'Bike'
    ]
];

// Get the category of the selected vehicle type
$selected_category = '';
foreach ($vehicle_categories as $category => $types) {
    if (array_key_exists($vehicle_type, $types)) {
        $selected_category = $category;
        break;
    }
}
?>

<div class="container mt-4">
    <!-- Vehicle Type Selection -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Select Vehicle Type</h5>
                    <div class="d-flex justify-content-start flex-wrap gap-3">
                        <!-- Car Options -->
                        <div class="dropdown">
                            <button class="btn <?php echo $selected_category === 'car' ? 'btn-primary active' : 'btn-outline-primary'; ?> dropdown-toggle" type="button" id="carDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Car Options
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="carDropdown">
                                <?php foreach ($vehicle_categories['car'] as $value => $label): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $vehicle_type === $value ? 'active' : ''; ?>" 
                                           href="?type=<?php echo $value; ?>">
                                            <?php echo $label; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Goods Vehicle Options -->
                        <div class="dropdown">
                            <button class="btn <?php echo $selected_category === 'goods' ? 'btn-success active' : 'btn-outline-success'; ?> dropdown-toggle" type="button" id="goodsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Goods Vehicle
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="goodsDropdown">
                                <?php foreach ($vehicle_categories['goods'] as $value => $label): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $vehicle_type === $value ? 'active' : ''; ?>" 
                                           href="?type=<?php echo $value; ?>">
                                            <?php echo $label; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Bike Option -->
                        <div>
                            <a href="?type=bike" 
                               class="btn <?php echo $vehicle_type === 'bike' ? 'btn-danger active' : 'btn-outline-danger'; ?>">
                                Bike
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <?php if ($vehicle_type): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            Available <?php 
                                foreach ($vehicle_categories as $types) {
                                    if (isset($types[$vehicle_type])) {
                                        echo $types[$vehicle_type];
                                        break;
                                    }
                                }
                            ?> Rides
                        </h5>
                        <?php
                        try {
                            // Fetch trips based on vehicle type
                            $sql = "SELECT * FROM trips WHERE vehicle_type = ? AND departure_date >= CURDATE() ORDER BY departure_date, departure_time";
                            $stmt = $conn->prepare($sql);
                            
                            if (!$stmt) {
                                throw new Exception("Failed to prepare statement: " . $conn->error);
                            }
                            
                            $stmt->bind_param("s", $vehicle_type);
                            
                            if (!$stmt->execute()) {
                                throw new Exception("Failed to execute query: " . $stmt->error);
                            }
                            
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0):
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Price</th>
                                                <th>Available Seats</th>
                                                <th>Vehicle Details</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($trip = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($trip['departure_city']); ?></td>
                                                    <td><?php echo htmlspecialchars($trip['destination_city']); ?></td>
                                                    <td><?php echo date('d M Y', strtotime($trip['departure_date'])); ?></td>
                                                    <td><?php echo date('h:i A', strtotime($trip['departure_time'])); ?></td>
                                                    <td>₹<?php echo number_format($trip['price']); ?></td>
                                                    <td><?php echo $trip['seats']; ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($trip['vehicle_number']); ?>
                                                        <?php if ($trip['has_ac']): ?>
                                                            <span class="badge bg-info ms-1">AC</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="book_trip.php?trip_id=<?php echo $trip['id']; ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            Book Now
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No rides available for this vehicle type at the moment.
                                </div>
                            <?php endif;
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add custom CSS for active states -->
<style>
.dropdown-item.active {
    background-color: #0d6efd;
    color: white;
}
.btn.active {
    border-width: 2px;
}
</style>

<?php include 'footer.php'; ?> 