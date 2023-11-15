
  <div class="col-xs-12 col-sm-5 col-md-5 col-lg-6">
            <ul id="sparks" class="">
                <li class="sparks-info">
                    <h5> Compra $ <span class="txt-color-greenDark"><i class="fa fa-institution"></i>₡<?php 
                                            if(isset($_SESSION['sale'])){
                                                echo $_SESSION['sale'];
                                            }else{
                                                echo "N/R";
                                            }?></span></h5>
                    
                </li>
                <li class="sparks-info">
                    <h5> Venta $ <span class="txt-color-purple"><i class="fa fa-institution"></i>₡<?php 
                                            if(isset($_SESSION['purchase'])){
                                                echo $_SESSION['purchase'];
                                            }else{
                                                echo "N/R";
                                            }?></span></h5>
                    
                </li>
            </ul>
        </div>