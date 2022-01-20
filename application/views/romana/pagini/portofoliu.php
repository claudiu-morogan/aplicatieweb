<div role="main" class="main">

                <section class="page-header">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="breadcrumb">
                                    <li><a href="<?=site_url();?>">Home</a></li>
                                    <li class="active">Portofoliu</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h1>Proiectele noastre</h1>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="container">

                    <h2>Portofoliu</h2>

                    <ul class="nav nav-pills sort-source" data-sort-id="portfolio" data-option-key="filter">
                        <li data-option-value="*" class="active"><a href="#">Toate</a></li>
                        <li data-option-value=".site-prezentare"><a href="#">Site-uri de prezentare</a></li>
                        <li data-option-value=".magazin-virtual"><a href="#">Magazine Virtuale</a></li>
                        <li data-option-value=".aplicatie-web"><a href="#">Aplicatii Web</a></li>
                    </ul>

                    <hr>

                    <div class="row">

                        <ul class="portfolio-list sort-destination" data-sort-id="portfolio">                            

                            <?php foreach($projects as $proiect) : ?>

                                <li class="col-md-4 col-sm-6 col-xs-12 isotope-item <?=$proiect->filtru?>">
                                    <div class="portfolio-item">
                                        <a href="<?php echo ($proiect->link != 'not') ? $proiect->link : '';?>" target="_blank">
                                            <span class="thumb-info">
                                                <span class="thumb-info-wrapper">
                                                    <img src="<?=$proiect->imagine;?>" class="img-responsive" alt="">
                                                    <span class="thumb-info-title">
                                                        <span class="thumb-info-inner"><?=$proiect->nume?></span>
                                                        <span class="thumb-info-type"><?=$proiect->tip?></span>
                                                    </span>
                                                    <span class="thumb-info-action">
                                                        <span class="thumb-info-action-icon"><i class="fa fa-link"></i></span>
                                                    </span>
                                                </span>
                                            </span>
                                        </a>
                                    </div>
                                </li>

                            <?php endforeach; ?>                            
                        </ul>
                    </div>

                </div>

            </div>