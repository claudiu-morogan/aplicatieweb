<div role="main" class="main">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading heading-border heading-middle-border">
                    <h2>Formular de comanda</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <form method="POST" action="<?=site_url()?>ro/comanda">
                <div class="form-horizontal form-bordered">
                    <!-- PACHET SERVICII -->
                    <div class="form-group">
                        <label class="col-md-3 control-label">Pachet selectat</label>
                        <div class="col-md-6">
                            <select name="pachet" id="pachet" class="form-control populate">
                                <option value="site-prezentare-basic"        <?php echo ($tip_site == 'site-prezentare-basic') ? 'selected' : false; ?>> Site de prezentare - BASIC 99 &euro;</option>
                                <option value="site-prezentare-standard"     <?php echo ($tip_site == 'site-prezentare-standard') ? 'selected' : false; ?> > Site de prezentare - STANDARD 150 &euro;</option>
                                <option value="site-prezentare-professional" <?php echo ($tip_site == 'site-prezentare-professional') ? 'selected' : false; ?>> Site de prezentare - PROFESSIONAL 300 &euro;</option>
                                <option value="site-prezentare-ultimate"     <?php echo ($tip_site == 'site-prezentare-ultimate') ? 'selected' : false; ?>> Site de prezentare - ULTIMATE 500 &euro;</option>

                                <option value="magazin-basic"        <?php echo ($tip_site == 'magazin-basic') ? 'selected' : false; ?>> Magazin virtual - BASIC 399 &euro;</option>
                                <option value="magazin-standard"     <?php echo ($tip_site == 'magazin-standard') ? 'selected' : false; ?>> Magazin virtual - STANDARD  449 &euro;</option>
                                <option value="magazin-professional" <?php echo ($tip_site == 'magazin-professional') ? 'selected' : false; ?>> Magazin virtual - PROFESSIONAL 600 &euro;</option>
                                <option value="magazin-ultimate"     <?php echo ($tip_site == 'magazin-ultimate') ? 'selected' : false; ?>> Magazin virtual - ULTIMATE 900 &euro;</option>

                                <option value="mentenanta_web"     <?php echo ($tip_site == 'mentenanta_web') ? 'selected' : false; ?>> Mentenanta WEB</option>
                                <option value="reparatie_pc"       <?php echo ($tip_site == 'reparatie_pc') ? 'selected' : false; ?>> Reparatie PC</option>
                                <option value="alte_servicii"       <?php echo ($tip_site == 'alte_servicii') ? 'selected' : false; ?>> Reparatie PC</option>
                            </select>
                        </div>
                    </div>
                    <!-- NUME SI PRENUME -->
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-5 col-md-offset-1">
                                <label>Nume *</label>
                                <input type="text" value="" data-msg-required="Introduce-ti numele dvs." maxlength="100" class="form-control" name="nume" id="nume" required>
                            </div>
                            <div class="col-md-5">
                                <label>Prenume *</label>
                                <input type="text" value="" data-msg-required="Introduce-ti prenumele dvs." maxlength="100" class="form-control" name="prenume" id="prenume" required>
                            </div>
                        </div>
                    </div>
                    <!-- TELEFON SI EMAIL -->
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-5 col-md-offset-1">
                                <label>Telefon *</label>
                                <input type="text" value="" data-msg-required="Introduce-ti numarul dvs. de telefon" maxlength="100" class="form-control" name="telefon" id="telefon" required>
                            </div>
                            <div class="col-md-5">
                                <label>Email *</label>
                                <input type="text" value="" data-msg-required="Introduce-ti adresa dvs. de email" data-msg-email="Eroare! Introduce-ti o adresa de mail valida" maxlength="100" class="form-control" name="email" id="email" required>
                            </div>
                        </div>
                    </div>
                    <!-- SUBIECT -->
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <label>Subiect *</label>
                                <input type="text" value="" data-msg-required="Subiect necesar" maxlength="100" class="form-control" name="subiect" id="subiect" required>
                            </div>                            
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                            <label>Detalii despre proiect</label>
                            <textarea maxlength="5000" data-msg-required="Introduce-ti continutul mesajului" rows="10" class="form-control" name="detalii_proiect" id="detalii_proiect"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-11 col-md-offset-1">
                            <input type="submit" value="Trimite" class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if($subject) : ?>
    <script type="text/javascript">
        setTimeout(function(){
            var incarcaSubiect = (function(){
                $('#subiect').val('<?=$subject;?>');    
            });
            $(document).ready(incarcaSubiect);
        }, 500);
    </script>
<?php endif; ?>