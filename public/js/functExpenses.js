function search1(){
     var idcard=document.getElementById("searchIdCard").value;
     if(idcard != ""){
         $.ajax({
        url: '?controller=Customer&action=searchById',
        type: 'GET',
        dataType: 'json',
        data: "idCard="+idcard,
        success: function (datas) {
            
            if (datas!= null) {
                var id = "01";
                if(datas[0].CompanyName.indexOf('R') != -1){
                    id = "03";
                }else if(datas[0].CompanyName.indexOf('N') != -1){
                    id = "04";
                }else{
                   if(datas[0].CompanyName.length < 10){
                        id="01";
                    }else if(datas[0].CompanyName.length == 12){
                        id="02";
                    }else{
                        id="02";
                    } 
                }
                document.getElementById("typeIdCard").value = id;
                
                if(datas[0].CompanyName!= null){
                    document.getElementById("idCardE").value = datas[0].CompanyName;
                }
                if(datas[0].DisplayName!= null){
                document.getElementById("nameE").value = datas[0].DisplayName;
                }
                if(datas[0].PrimaryEmailAddr!= null){
                document.getElementById("emailE").value = datas[0].PrimaryEmailAddr.Address;
                }
                if(datas[0].PrimaryPhone!= null){
                document.getElementById("phoneE").value = datas[0].PrimaryPhone.FreeFormNumber;
                }
                if(datas[0].CurrencyRef != null){
                document.getElementById("currencyDoc").value = datas[0].CurrencyRef;
                }
            } else {
                 alert("No se encontraron datos");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
             alert("Error al encontrar datos");
        }
    });
     }else{
         alert("favor ingresar un valor");
     }
}
function addRow() {
    var myModal = $('#invoice-Modal');
    var cabys = $('#cabys', myModal).val();
    var product = $('#detail', myModal).val();
    var qty = $('#qty', myModal).val();
    var sku = $('#sku', myModal).val();
    var price = $('#unid', myModal).val();
    var tax = $('#tax', myModal).val();
    var discount = $('#discount', myModal).val();
    tax = ((qty*price-discount)*tax)/100; 
    var total = (qty*price)+(tax)-discount;
    total=parseFloat(total.toFixed(6));
    
    var subtotal=0;
    var impuestos = 0;
    var descuentos = 0;
    var totalFact = 0;
    if(cabys.length == 13){
    
    var fila="<tr id='L"+$('#DetalleServicio tr').length+"'><td>"+$('#DetalleServicio tr').length+"</td><td>"+cabys+"</td><td>"+product+"</td><td>"+sku+"</td><td>"+qty+"</td><td>"+price+"</td><td>"+tax+"</td><td>"+discount+"</td><td>"+total+"</td><td>"+"<a class = 'btn btn-xs btn-default'title = 'Eliminar factura' id='deletedProduct' onclick='deleteRow("+$('#DetalleServicio tr').length+")'><i id='deletedRow' class = 'fa fa-times txt-color-blue' ></i></a>"+"</td></tr>";
    
    
    var btn = document.createElement("TR");
   	btn.innerHTML=fila;
    document.getElementById("DetalleServicio").appendChild(btn);
    
    $('#detail', myModal).val("");
    $('#qty', myModal).val(1);
     $('#cabys', myModal).val("");
    $('#sku', myModal).val("Sp");
    $('#price', myModal).val(0);
    $('#tax', myModal).val(0);
    $('#discount', myModal).val(0);
    
    $('#totalDoc').text('');
    $('#tImpuesto').text('');
    $('#tDescuento').text('');
    $('#subtotal').text('');
    
    var filas = $("#DetalleServicio").find("tr");
    
    for(i=1; i<filas.length; i++){ //Recorre las filas 1 a 1
		var celdas = $(filas[i]).find("td");
		subtotal = subtotal +($(celdas[4]).text()*$(celdas[5]).text());
		subtotal = parseFloat(subtotal);
		impuestos = parseFloat(impuestos) +(1*$(celdas[6]).text());
		
		descuentos = parseFloat(descuentos) +parseFloat($(celdas[7]).text());
		totalFact = parseFloat(totalFact)+parseFloat($(celdas[8]).text());
    }
    
    
    $('#totalDoc').append(totalFact.toFixed(2));
    $('#tImpuesto').append(impuestos.toFixed(2));
    $('#tDescuento').append(descuentos.toFixed(2));
    $('#subtotal').append(subtotal.toFixed(2));
    }else{
        alert("El codigo CAByS debe de contener 13 digitos");
    }
}
function deleteRow(row) {
    var filas = $("#DetalleServicio").find("tr");
    filas[row].remove();
    filas = $("#DetalleServicio").find("tr");
    var subtotal=0;
    var impuestos = 0;
    var descuentos = 0;
    var totalFact = 0;
    $('#totalDoc').text('');
    $('#tImpuesto').text('');
    $('#tDescuento').text('');
    $('#subtotal').text('');
    
    for(i=1; i<filas.length; i++){ //Recorre las filas 1 a 1
		var celdas = $(filas[i]).find("td");
		subtotal = subtotal +($(celdas[4]).text()*$(celdas[5]).text());
		subtotal = parseFloat(subtotal);
		impuestos = parseFloat(impuestos) +($(celdas[4]).text()*$(celdas[6]).text());
		
		descuentos = parseFloat(descuentos) +parseFloat($(celdas[7]).text());
		totalFact = parseFloat(totalFact)+parseFloat($(celdas[8]).text());
    }
    
    
    $('#totalDoc').text(totalFact.toFixed(2));
    $('#tImpuesto').text(impuestos.toFixed(2));
    $('#tDescuento').text(descuentos.toFixed(2));
    $('#subtotal').text(subtotal.toFixed(2));
}
function proccess() {
    
    
  //datos documento
  var tipoDocumento=8;
  //var tipoCambio = $('#tipoCambio').val();
  var consecutive = $('#consecutivo').html();
  var clave = $('#claveDoc').html();
  var nRefDoc = $('#nRefDoc').val();
  var typeDocRef = $('#typeDocRef').val();
  var fReferencia = $('#fReferencia').val();
  var codRef = $('#codRef').val();
  var razon = $('#razon').val();
  var tCambio = $('#tCambio').val();
  
  if(nRefDoc == "" || fReferencia == "" || razon == "" ){
      alert("Favor llenar los campos de referencia");
      return 0;
  }
  if(tCambio == "" || tCambio==0){
      alert("El Tipo de cambio debe de ser mayor a cero ");
      return 0;
  }
  
  //datos emisor
  var typeIdCard = $('#typeIdCard').val();
  var nameE = $('#nameE').val();
  var idCardE = $('#idCardE').val();
  var emailE = $('#emailE').val();
  var phoneE = $('#phoneE').val();
  var payE = $('#payE').val();
  var typePay = $('#typePay').val();
  var credit = $('#credit').val();
  var currency = $('#currencyDoc').val();
  
  
  
  if(nameE == "" || idCardE == "" || emailE == "" || credit == "" || currency == ""){
      alert("Llenar todos los datos del emisor");
      return 0;
  }
 
  //variables de detalle  
  var filas = $("#DetalleServicio").find("tr");
  var details = Array();
  var linea=0;
  var n = 0;
  var cabys=0;
  var description=0;
  var sku=0;
  var qty =0;
  var price = 0;
  var tax = 0;
  var discount = 0;
    if(filas.length > 1){
      //datos de detalle
      for(i=1; i<filas.length; i++){ //Recorre las filas 1 a 1
		var celdas = $(filas[i]).find("td");
		n = $(celdas[0]).text();
		cabys = $(celdas[1]).text();
		description = $(celdas[2]).text();
		sku = $(celdas[3]).text();
		qty = $(celdas[4]).text();
		price = $(celdas[5]).text();
		tax = $(celdas[6]).text();
		discount = $(celdas[7]).text();
		total = $(celdas[8]).text();
		
		linea = new Array(n,cabys,description,sku,qty,price,tax,discount,total);
		details.push(linea);
    }
    }
    if(details.length <1){
      alert("No hay lineas ingresadas");
      return 0;
  }
    //datos finales del doc
    var subtotal = $('#subtotal').html();
    var descuentos = $('#tDescuento').html();
    var impuestos = $('#tImpuesto').html();
    var total = $('#totalDoc').html();
    //envio de datos
    $.ajax({
        url: '?controller=InvoicePurchase&action=proccess',
        type: 'POST',
        data: {'details': details,'emailE': emailE,'tipoDocumento':tipoDocumento, 'consecutive':consecutive,'clave':clave,'typeIdCard':typeIdCard ,'idCardE':idCardE ,'nameE':nameE,'phoneE':phoneE,'payE':payE ,'typePay':typePay ,'credit':credit ,'currency':currency,'subtotal':subtotal,'descuentos':descuentos,'impuestos':impuestos,'total':total ,'nRefDoc':nRefDoc,'tCambio':tCambio,'typeDocRef':typeDocRef,'fReferencia':fReferencia,'codRef':codRef,'razon':razon},
        success: function (data) {
                alert(data);
                window.location.href = "https://www.contafast.net/sincronizador/?controller=InvoicePurchase&action=index";
        },
        error: function (request, status, error) {
            alert("Error al cargar datos, favor intente de nuevo. "+error);
        }
    });
 
}


