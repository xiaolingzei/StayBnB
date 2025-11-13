<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; padding: 40px 0; }
        
        .payment-container { max-width: 900px; margin: 0 auto; }
        
        .payment-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        
        .payment-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 40px; text-align: center; }
        
        .payment-body { padding: 40px; }
        
        .payment-method-btn { padding: 20px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all 0.3s; margin-bottom: 15px; background: white; }
        
        .payment-method-btn:hover { border-color: #0a53fe; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(10, 83, 254, 0.2); }
        
        .payment-method-btn.active { border-color: #0a53fe; background: #eff6ff; }
        
        .payment-method-btn img { height: 40px; object-fit: contain; }
        
        .payment-form { display: none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 12px; }
        
        .payment-form.active { display: block; }
        
        .order-summary { background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 30px; }
        
        .btn-pay { background: #10b981; border: none; padding: 15px; font-size: 1.1rem; font-weight: 600; }
        
        .btn-pay:hover { background: #059669; }
        
        .secure-badge { text-align: center; color: #6b7280; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <div class="payment-header">
                <i class="fas fa-lock fa-3x mb-3"></i>
                <h2>Secure Payment</h2>
                <p class="mb-0">Complete your booking payment</p>
            </div>
            
            <div class="payment-body">
                <!-- Order Summary -->
                <div class="order-summary">
                    <h5 class="mb-3">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Hotel:</span>
                        <strong id="hotelName">Loading...</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Booking Reference:</span>
                        <strong id="bookingRef">Loading...</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Check-in Date:</span>
                        <strong id="checkinDate">Loading...</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Check-out Date:</span>
                        <strong id="checkoutDate">Loading...</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Number of Nights:</span>
                        <strong id="numNights">Loading...</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="h5 mb-0">Total Amount:</span>
                        <span class="h5 mb-0 text-primary" id="totalAmount">₱0</span>
                    </div>
                </div>

                <h5 class="mb-3">Select Payment Method</h5>
                
                <!-- Payment Methods -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="payment-method-btn" onclick="selectPayment('gcash')">
                            <div class="text-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/GCash_Logo.svg/512px-GCash_Logo.svg.png" alt="GCash">
                                <p class="mb-0 mt-2 small fw-semibold">GCash</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="payment-method-btn" onclick="selectPayment('maya')">
                            <div class="text-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Maya_%28payment_service%29_logo.svg/512px-Maya_%28payment_service%29_logo.svg.png" alt="Maya">
                                <p class="mb-0 mt-2 small fw-semibold">Maya</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="payment-method-btn" onclick="selectPayment('card')">
                            <div class="text-center">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                                <p class="mb-0 mt-2 small fw-semibold">Credit/Debit Card</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="payment-method-btn" onclick="selectPayment('cash')">
                            <div class="text-center">
                                <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                <p class="mb-0 mt-2 small fw-semibold">Pay at Hotel</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="paymentForm">
                    <input type="hidden" id="bookingId" name="booking_id">
                    <input type="hidden" id="paymentAmount" name="amount">
                    <input type="hidden" id="paymentMethod" name="payment_method">

                    <!-- GCash Form -->
                    <div id="gcashForm" class="payment-form">
                        <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>GCash Payment</h6>
                        <div class="mb-3">
                            <label class="form-label">GCash Mobile Number *</label>
                            <input type="tel" class="form-control" name="gcash_number" 
                                   placeholder="09171234567" pattern="09[0-9]{9}" maxlength="11">
                            <small class="text-muted">Enter your 11-digit GCash number</small>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You will receive a GCash payment request on your mobile number.
                        </div>
                    </div>

                    <!-- Maya Form -->
                    <div id="mayaForm" class="payment-form">
                        <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>Maya Payment</h6>
                        <div class="mb-3">
                            <label class="form-label">Maya Mobile Number *</label>
                            <input type="tel" class="form-control" name="maya_number" 
                                   placeholder="09171234567" pattern="09[0-9]{9}" maxlength="11">
                            <small class="text-muted">Enter your 11-digit Maya number</small>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You will receive a Maya payment request on your mobile number.
                        </div>
                    </div>

                    <!-- Card Form -->
                    <div id="cardForm" class="payment-form">
                        <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>Card Payment</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Card Number *</label>
                                <input type="text" class="form-control" name="card_number" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" 
                                       onkeyup="formatCardNumber(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Cardholder Name *</label>
                                <input type="text" class="form-control" name="card_name" 
                                       placeholder="JUAN DELA CRUZ">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Expiry Date *</label>
                                <input type="text" class="form-control" name="card_expiry" 
                                       placeholder="MM/YY" maxlength="5" 
                                       onkeyup="formatExpiry(this)">
                            </div>
                            <div class="col-6">
                                <label class="form-label">CVV *</label>
                                <input type="text" class="form-control" name="card_cvv" 
                                       placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-shield-alt me-2"></i>
                            Your card information is encrypted and secure.
                        </div>
                    </div>

                    <!-- Cash Payment Form -->
                    <div id="cashForm" class="payment-form">
                        <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>Pay at Hotel</h6>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Payment at Hotel Counter</strong>
                            <p class="mb-0 mt-2">You can pay the full amount when you arrive at the hotel. Please bring cash or ask the hotel about their accepted payment methods.</p>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Your booking will be marked as "Pending Payment" until you complete payment at the hotel.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div id="paymentButtonArea" style="display: none;">
                        <button type="submit" class="btn btn-success btn-pay w-100" id="payButton">
                            <i class="fas fa-lock me-2"></i>Complete Payment
                        </button>
                        <div class="secure-badge">
                            <i class="fas fa-shield-alt me-1"></i>
                            <small>Secured by 256-bit SSL encryption</small>
                        </div>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="dashboard.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Cancel and go back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedMethod = null;
        let bookingData = null;

        // Get booking ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const bookingId = urlParams.get('booking_id');

        // Load booking details
        async function loadBookingDetails() {
            if (!bookingId) {
                alert('Invalid booking ID');
                window.location.href = 'dashboard.php';
                return;
            }

            try {
                const response = await fetch(`get-booking-details.php?booking_id=${bookingId}`);
                const data = await response.json();

                if (data.success) {
                    bookingData = data.booking;
                    document.getElementById('bookingId').value = bookingData.booking_id;
                    document.getElementById('paymentAmount').value = bookingData.total_amount;
                    document.getElementById('hotelName').textContent = bookingData.hotel_name;
                    document.getElementById('bookingRef').textContent = bookingData.booking_ref;
                    document.getElementById('checkinDate').textContent = new Date(bookingData.checkin_date).toLocaleDateString();
                    document.getElementById('checkoutDate').textContent = new Date(bookingData.checkout_date).toLocaleDateString();
                    document.getElementById('numNights').textContent = bookingData.num_nights;
                    document.getElementById('totalAmount').textContent = '₱' + parseFloat(bookingData.total_amount).toLocaleString();
                } else {
                    alert(data.message || 'Failed to load booking details');
                    window.location.href = 'dashboard.php';
                }
            } catch (error) {
                console.error('Error loading booking:', error);
                alert('Failed to load booking details');
            }
        }

        loadBookingDetails();

        function selectPayment(method) {
            selectedMethod = method;
            document.getElementById('paymentMethod').value = method;

            // Remove active class from all buttons
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to selected button
            event.currentTarget.classList.add('active');

            // Hide all forms
            document.querySelectorAll('.payment-form').forEach(form => {
                form.classList.remove('active');
            });

            // Show selected form
            document.getElementById(method + 'Form').classList.add('active');
            document.getElementById('paymentButtonArea').style.display = 'block';

            // Update button text
            const buttonTexts = {
                'gcash': 'Pay with GCash',
                'maya': 'Pay with Maya',
                'card': 'Pay with Card',
                'cash': 'Confirm Booking (Pay Later)'
            };
            document.getElementById('payButton').innerHTML = `<i class="fas fa-lock me-2"></i>${buttonTexts[method]}`;
        }

        function formatCardNumber(input) {
            let value = input.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            input.value = formattedValue;
        }

        function formatExpiry(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 2) {
                input.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                input.value = value;
            }
        }

        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!selectedMethod) {
                alert('Please select a payment method');
                return;
            }

            const formData = new FormData(this);
            const button = document.getElementById('payButton');
            const originalText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            try {
                const response = await fetch('process-payment.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Payment successful!');
                    window.location.href = `booking-confirmation.php?ref=${data.booking_ref}`;
                } else {
                    alert(data.message || 'Payment failed. Please try again.');
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Payment error:', error);
                alert('An error occurred. Please try again.');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>