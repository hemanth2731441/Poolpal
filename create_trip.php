<?php
include 'header.php';
include 'db.php';

// Check if user is logged in as a driver
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Create New Trip</h5>
                    <form action="process_trip.php" method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Vehicle Type Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="vehicle_type">Vehicle Type</label>
                                    <select class="form-control" id="vehicle_type" name="vehicle_type" required>
                                        <option value="">Select Vehicle Type</option>
                                        <optgroup label="Car Options">
                                            <option value="taxi_car">Taxi Car</option>
                                            <option value="carpooling">Carpooling</option>
                                        </optgroup>
                                        <optgroup label="Goods Vehicle">
                                            <option value="7ft_vehicle">7ft Vehicle</option>
                                            <option value="8ft_vehicle">8ft Vehicle</option>
                                            <option value="3wheeler_cargo">3 Wheeler Cargo</option>
                                            <option value="tata_407">Tata 407</option>
                                        </optgroup>
                                        <optgroup label="Bike">
                                            <option value="bike">Bike</option>
                                        </optgroup>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a vehicle type.
                                    </div>
                                </div>
                            </div>

                            <!-- Cities -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="departure_city">From</label>
                                    <input type="text" class="form-control" id="departure_city" name="departure_city" required>
                                    <div class="invalid-feedback">
                                        Please enter departure city.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="destination_city">To</label>
                                    <input type="text" class="form-control" id="destination_city" name="destination_city" required>
                                    <div class="invalid-feedback">
                                        Please enter destination city.
                                    </div>
                                </div>
                            </div>

                            <!-- Dates and Times -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="departure_date">Departure Date</label>
                                    <input type="date" class="form-control" id="departure_date" name="departure_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">
                                        Please select departure date.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="departure_time">Departure Time</label>
                                    <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                                    <div class="invalid-feedback">
                                        Please select departure time.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="arrival_date">Arrival Date</label>
                                    <input type="date" class="form-control" id="arrival_date" name="arrival_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">
                                        Please select arrival date.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="arrival_time">Arrival Time</label>
                                    <input type="time" class="form-control" id="arrival_time" name="arrival_time" required>
                                    <div class="invalid-feedback">
                                        Please select arrival time.
                                    </div>
                                </div>
                            </div>

                            <!-- Trip Details -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="seats">Available Seats</label>
                                    <input type="number" class="form-control" id="seats" name="seats" min="1" required>
                                    <div class="invalid-feedback">
                                        Please enter number of available seats.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price">Price per Seat (₹)</label>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                                    <div class="invalid-feedback">
                                        Please enter price per seat.
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Options -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="luggage_space">Luggage Space</label>
                                    <select class="form-control" id="luggage_space" name="luggage_space" required>
                                        <option value="">Select Luggage Space</option>
                                        <option value="Small">Small</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Large">Large</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select luggage space.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Additional Features</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="has_ac" name="has_ac">
                                        <label class="form-check-label" for="has_ac">AC Available</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="allow_smoking" name="allow_smoking">
                                        <label class="form-check-label" for="allow_smoking">Smoking Allowed</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="pets_allowed" name="pets_allowed">
                                        <label class="form-check-label" for="pets_allowed">Pets Allowed</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="notes">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Trip</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Date validation
document.getElementById('departure_date').addEventListener('change', function() {
    document.getElementById('arrival_date').min = this.value;
});
</script>

<?php include 'footer.php'; ?> 