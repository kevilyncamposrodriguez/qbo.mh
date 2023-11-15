<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Sincronizador</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />

       <!-- ================== BEGIN BASE CSS STYLE ================== -->
        <link href="public/css/transparent/app.min.css" rel="stylesheet" />
        <!-- ================== END BASE CSS STYLE ================== -->

        <!-- ================== BEGIN PAGE LEVEL CSS STYLE ================== -->
        <link href="public/plugins/jvectormap-next/jquery-jvectormap.css" rel="stylesheet" />
        <link href="public/plugins/bootstrap-calendar/css/bootstrap_calendar.css" rel="stylesheet" />
        <link href="public/plugins/gritter/css/jquery.gritter.css" rel="stylesheet" />
        <link href="public/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
        <link href="public/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
        <link href="public/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet" />
	<link href="public/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" />
	<link href="public/plugins/@danielfarrell/bootstrap-combobox/css/bootstrap-combobox.css" rel="stylesheet" />
	<link href="public/plugins/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet" />
        <!-- ================== END PAGE LEVEL CSS STYLE ================== -->
        <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
	<link href="public/plugins/smartwizard/dist/css/smart_wizard.css" rel="stylesheet" />
	<!-- ================== END PAGE LEVEL STYLE ================== -->
        <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
	<link href="public/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
	<link href="public/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
	<link href="public/plugins/datatables.net-select-bs4/css/select.bootstrap4.min.css" rel="stylesheet" />
	<!-- ================== END PAGE LEVEL STYLE ================== -->
	<link href="public/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet" />
	<link href="public/plugins/dropzone/dist/min/dropzone.min.css" rel="stylesheet" />
        <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
	
	
	<link href="public/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />
	
        <link href="public/css/transparent/invoice-print.css" rel="stylesheet" />
	<!-- ================== END PAGE LEVEL STYLE ================== -->
        <!-- FAVICONS -->
        <link rel="shortcut icon" href="public/img/logito.png" type="image/x-icon">
        <link rel="icon" href="public/img/logito.png" type="image/x-icon">
    </head>
    <body >
        <!-- begin page-cover -->
        <div class="page-cover"></div>
        <!-- end page-cover -->

        <!-- begin #page-loader -->
        <div id="page-loader" class="fade show"><span class="spinner"></span></div>
        <!-- end #page-loader -->

        <!-- begin #page-container -->
        <div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
            <!-- begin #header -->
            <div id="header" class="header navbar-default">
                <!-- begin navbar-header -->
                <div class="navbar-header">
                    <a href="index.php" class="navbar-brand"><span>
                            <div class="icon">
                                <img src="public/img/logo.png" width="80" height="80" alt="Sincronizador QBO-MH"/>

                            </div>
                        </span> <b>Sincronizador</b></a>
                    <button type="button" class="navbar-toggle" data-click="top-menu-toggled">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
               
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown navbar-user">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="d-none d-md-inline">Usuario: <?php echo " ".$_SESSION['username']; ?></span> 
                        </a>
                    </li>
                </ul>
                <!-- end header navigation right -->
            </div>
            <!-- end #header -->

            <!-- begin #top-menu -->
            <div id="top-menu" class="top-menu ">
                <!-- begin top-menu nav -->
                <ul class="nav">
                    <li>
                        <a href="index.php">
                            <i class="fa fa-home fa-fw"></i>
                            <span>Inicio </span> 
                        </a>
                    </li>
                    <li class="has-sub">
                        <a href="javascript:;">
                            <i class="fa fa-book fa-fw"></i>
                            <span>Comprobantes</span>
                            <b class="caret"></b>
                        </a>
                        <ul class="sub-menu">
                            <li><a href="?controller=Expenses&action=index">Sin procesar</a></li>
                            <li><a href="?controller=Expenses&action=all">Procesados</a></li>
                        </ul>
                    </li>
                    
                    <!--<li>
                        <a href="widget.html">
                            <i class="fa fa-file"></i>
                            <span>Facturas de compra </span> 
                        </a>
                    </li>-->
                    <li class="has-sub">
                        <a href="javascript:;">
                            <i class="fa fa-upload fa-fw"></i>
                            <span>Importar</span>
                            <b class="caret"></b>
                        </a>
                        <ul class="sub-menu">
                            <li><a href="?controller=Invoice&action=import">Facturas Eléctronicas</a></li>
                            <li><a href="?controller=CreditNote&action=import">Notas de Crédito Electronicas</a></li>
                            <li><a href="?controller=Check&action=importChecks">Importar cheques</a></li>
                            <?php 
                                if($_SESSION['username']=='3102755692'){
                                    echo '<li><a href="?controller=Expenses&action=importPayments">Importar pagos</a></li>';
                                    
                                } 
                            ?>
                        </ul>
                    </li>
                    
                    <li>
                        <a href="?controller=InvoicePurchase&action=index">
                            <i class="fa fa-file"></i>
                            <span>Factura de Compra </span> 
                        </a>
                    </li>
                    <li>
                        <a href="#config-Modal"  data-toggle="modal">
                            <i class="fa fa-cogs"></i>
                            <span>Configuración </span> 
                        </a>
                    </li>
                    <?php 
                    if($_SESSION['username']=='3101747416'){
                        echo '<li>
                           <a href="?controller=Default&action=client">
                                <i class="fa fa-sign-out-alt fa-fw"></i>
                                <span>Sesiones </span> 
                            </a>
                        </li>';
                    }
                    ?>
                    <li>
                        <a href="?controller=Default&action=logout">
                            <i class="fa fa-sign-out-alt fa-fw"></i>
                            <span>Salir </span> 
                        </a>
                    </li>
                   
                      
                </ul>
               
                <!-- end top-menu nav -->
            </div>
            <!-- end #top-menu -->

            <!-- begin #content -->
            <div id="content" class="content hidden-print">

