<footer id="footer" class="color color-quaternary">
                <div class="container">
                    <div class="row">
                        <div class="footer-ribbon">
                            <span><a href="<?=site_url();?>ro/contact">Contacteaza-ne</a></span>
                        </div>



                        <div class="col-md-5" style="margin-bottom: 0px">
                            <h4 class="mb-none">Contact Rapid</h4>
                            <p>Ne poti contacta imediat prin intermediul formularului de mai jos.</p>
                            
                            <form id="contactForm" action="" style="display: none">
                                <input type="hidden" value="Contact Form" name="subject" id="subject">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <label>Nume *</label>
                                            <input type="text" value="" data-msg-required="Introduce-ti numele dvs." maxlength="100" class="form-control" name="name" id="name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Email *</label>
                                            <input type="email" value="" data-msg-required="Introduce-ti adresa dvs. de mail" data-msg-email="Please enter a valid email address." maxlength="100" class="form-control" name="email" id="email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <label>Mesaj *</label>
                                            <textarea maxlength="5000" data-msg-required="Introduce-ti mesajul dorit" rows="2" class="form-control" name="message" id="message" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="submit" value="Trimite" class="btn btn-primary mb-xl" data-loading-text="Loading...">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="alert alert-success hidden" id="contactSuccess">
                                            Mesajul s-a transmis cu success
                                        </div>

                                        <div class="alert alert-danger hidden" id="contactError">
                                            Eroare la transmiterea mesajului, va rugam reincercati.
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>



                        <div class="col-md-3" style="margin-bottom: 0px">
                            <h4>Program de lucru</h4>
                                 <ul class="list list-icons list-dark mt-xlg">
                                    <li><i class="fa fa-clock-o" style="color: white"></i> Luni - Vineri: 10<sup>00</sup> - 18<sup>00</sup></li>
                                    <li><i class="fa fa-clock-o" style="color: white"></i> Sambata - Duminica - Inchis</li>
                                </ul>
                        </div>                  
                        <div class="col-md-4" style="margin-bottom: 0px">
                            <div class="contact-details" style="display: none">
                                <h4>Adresa de contact</h4>
                                <ul class="contact">
                                    <li><p><i class="fa fa-map-marker"></i> <strong>Adresa:</strong> Prl. Tunari, Nr.1,Bl.Fb28, Slatina, jud. OLT</p></li>
                                    <li><p><i class="fa fa-phone"></i> <strong>Telefon:</strong> +4 0765-317-755</p></li>
                                    <li><p><i class="fa fa-envelope"></i> <strong>Email:</strong> <a href="contact@aplicatieweb.ro">contact@aplicatieweb.ro</a></p></li>
                                </ul>
                            </div>
                        </div>                        
                    </div>
                </div>
                <div class="footer-copyright" style="margin-top: 0px">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-1">
                                <a href="index.html" class="logo">
                                    <img alt="aplicatieweb.ro" class="img-responsive" src="<?=site_url();?>img/logo.png">
                                </a>
                            </div>
                            <div class="col-md-7">
                                <p>© Copyright <?=date("Y")?>. Toate drepturile rezervate. <strong> Site realizat de <a href='<?=site_url();?>'>aplicatieweb</a></strong></p> 
                            </div>
                            <div class="col-md-4">
                                <nav id="sub-menu">
                                    <ul>
                                        <li><a href="http://www.anpc.gov.ro">ANPC</a></li>
                                        <li><a href="sitemap.html">Sitemap</a></li>
                                        <li><a href="<?=site_url();?>/ro/contact">Contact</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Vendor -->
        <script src="<?=site_url();?>/vendor/jquery/jquery.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.appear/jquery.appear.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.easing/jquery.easing.js"></script>
        <script src="<?=site_url();?>/vendor/jquery-cookie/jquery-cookie.js"></script>
        <script src="<?=site_url();?>/vendor/bootstrap/js/bootstrap.js"></script>
        <script src="<?=site_url();?>/vendor/common/common.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.validation/jquery.validation.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.stellar/jquery.stellar.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.easy-pie-chart/jquery.easy-pie-chart.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.gmap/jquery.gmap.js"></script>
        <script src="<?=site_url();?>/vendor/jquery.lazyload/jquery.lazyload.js"></script>
        <script src="<?=site_url();?>/vendor/isotope/jquery.isotope.js"></script>
        <script src="<?=site_url();?>/vendor/owl.carousel/owl.carousel.js"></script>
        <script src="<?=site_url();?>/vendor/magnific-popup/jquery.magnific-popup.js"></script>
        <script src="<?=site_url();?>/vendor/vide/vide.js"></script>
        
        <!-- Theme Base, Components and Settings -->
        <script src="<?php echo site_url(); ?>js/theme.js"></script>
        
        <!-- Current Page Vendor and Views -->
        <script src="<?=site_url();?>/vendor/rs-plugin/js/jquery.themepunch.tools.min.js"></script>
        <script src="<?=site_url();?>/vendor/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
        <script src="<?=site_url();?>/vendor/circle-flip-slideshow/js/jquery.flipshow.js"></script>
        <script src="<?=site_url();?>/js/views/view.home.js"></script>
        
        <!-- Theme Custom -->
        <script src="<?=site_url();?>/js/custom.js"></script>
        
        <!-- Theme Initialization Files -->
        <script src="<?=site_url();?>/js/theme.init.js"></script>

        <!-- Google Analytics: Change UA-XXXXX-X to be your site's ID. Go to http://www.google.com/analytics/ for more information.
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        
            ga('create', 'UA-12345678-1', 'auto');
            ga('send', 'pageview');
        </script>
         -->

    </body>
</html>