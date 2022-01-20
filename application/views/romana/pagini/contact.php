<div role="main" class="main">

                <section class="page-header">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="breadcrumb">
                                    <li><a href="<?=site_url();?>">Home</a></li>
                                    <li class="active">Contact</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h1>Date de contact</h1>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Google Maps - Go to the bottom of the page to change settings and map location. -->
                <div id="map" class="google-map"></div>

                <div class="container">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="alert alert-success hidden" id="contactSuccess">
                                <strong>Success!</strong> Your message has been sent to us.
                            </div>

                            <div class="alert alert-danger hidden" id="contactError">
                                <strong>Error!</strong> There was an error sending your message.
                            </div>

                            <h2 class="mb-sm mt-sm"><strong>Contact</strong> - raspuns din partea noastra in maxim 24 de ore</h2>
                            <form id="contactForm" method="POST" action="<?=site_url();?>ro/contact_form">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <label>Nume *</label>
                                            <input type="text" value="" data-msg-required="Introduce-ti numele dvs." maxlength="100" class="form-control" name="name" id="name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Adresa de email *</label>
                                            <input type="email" value="" data-msg-required="Introduce-ti adresa dvs. de email" data-msg-email="Eroare! Introduce-ti o adresa de mail valida" maxlength="100" class="form-control" name="email" id="email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <label>Subiect *</label>
                                            <input type="text" value="" data-msg-required="Va rugam introduce-ti subiectul mesajului" maxlength="100" class="form-control" name="subject" id="subject" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <label>Mesaj *</label>
                                            <textarea maxlength="5000" data-msg-required="Introduce-ti continutul mesajului" rows="10" class="form-control" name="message" id="message" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <input type="submit" value="Trimite" class="btn btn-primary btn-lg mb-xlg">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">

                            <h4 class="heading-primary mt-lg"><strong>Contacteaza-ne</strong></h4>
                            <p>
                                Nu ezitati sa ne contactati pentru a primi o oferta personalizata sau pentru a afla cum puteti beneficia de experienta noastra in domeniu.
                               

                            </p>

                            <hr>

                            <h4 class="heading-primary">Adresa <strong>Noastra</strong></h4>
                            <ul class="list list-icons list-icons-style-3 mt-xlg">
                                <li><i class="fa fa-map-marker"></i> <strong>Adresa:</strong> jud. Olt, oras Slatina, Prl.Tunari nr.1 bl.FB28 sc.b et.4 ap.10</li>
                                <li><i class="fa fa-phone"></i> <strong>Telefon:</strong> <a href="tel:+400765317755"> +40 0765 317 755</a></li>
                                <li><i class="fa fa-envelope"></i> <strong>Email:</strong> <a href="mailto:office@aplicatieweb.ro">office@aplicatieweb.ro</a></li>
                            </ul>

                            <hr>

                            <h4 class="heading-primary"><strong>Program</strong></h4>
                            <ul class="list list-icons list-dark mt-xlg">
                                <li><i class="fa fa-clock-o"></i> Luni - Vineri: 10<sup>00</sup> - 18<sup>00</sup></li>
                                <li><i class="fa fa-clock-o"></i> Sambata - Duminica - Inchis</li>
                            </ul>

                        </div>

                    </div>

                </div>

            </div>

            <script type="text/javascript">
                var map;
                // Coordonatele pentru adresa unde arata gmaps
                var coords = {lat: 44.429299, lng: 24.382035};

                function initMap() {
                    // Initializare harta
                    map = new google.maps.Map(document.getElementById('map'), {
                      center: coords,
                      zoom: 16
                    });
                    // Initializare marker
                    var marker = new google.maps.Marker({
                        position: coords,
                        map: map,
                        title: 'AplicatieWeb.Ro'
                    });
                }
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD2pDRSWN1HJZTzuV0isUSb3tbuDeHAoDU&callback=initMap" async defer></script>