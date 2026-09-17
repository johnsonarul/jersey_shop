<?php include 'header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="font-weight-bold" style="color: var(--primary-color);">Contact Us</h2>
        <p class="lead" style="color: #cccccc;">Have a question? We'd love to hear from you.</p>
    </div>
    
    <div class="row justify-content-center">
        <!-- Address Card -->
        <div class="col-md-5 mb-4">
            <div class="p-4 rounded bg-card border border-secondary h-100 text-center hover-shadow">
                <i class="fas fa-map-marker-alt mb-3 mt-2" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h4 class="font-weight-bold text-white mb-3">Address</h4>
                <p style="color: #cccccc; font-size: 1.1rem; line-height: 1.8;">
                    Micheal Palayam,<br>
                    Dindigul,<br>
                    Tamilnadu,<br>
                    India
                </p>
            </div>
        </div>
        
        <!-- Contact Card -->
        <div class="col-md-5 mb-4">
            <div class="p-4 rounded bg-card border border-secondary h-100 text-center hover-shadow">
                <i class="fas fa-headset mb-3 mt-2" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h4 class="font-weight-bold text-white mb-4">Get in Touch</h4>
                
                <div class="d-grid gap-3" style="max-width: 250px; margin: 0 auto;">
                    <a href="tel:+919791649247" class="btn btn-outline-light d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-phone-alt"></i> +91 9791649247
                    </a>
                    
                    <a href="mailto:JR@jersey.com" class="btn btn-outline-light d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-envelope"></i> JR@jersey.com
                    </a>
                    
                    <a href="https://wa.me/917397131930" target="_blank" class="btn d-flex align-items-center justify-content-center gap-2" style="background-color: #25D366; color: white; border-radius: 30px; font-weight: 600; padding: 12px 20px; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> WhatsApp Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
