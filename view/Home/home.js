function init() {
    //ESCUCHAR SI EL BOTON GUARDAR HACE SUBMIT PARA EJECUTAR LA FUNCION guardaryeditar()
    $("#consul_form").on("submit", function(e) {
        guardar(e);
    });
    
}
//TOMA EL VALOR EMITIDO POR EL HEADER
var usu_id = $('#user_idx').val();

$(document).ready(function() {

});

function guardar(e) {
    e.preventDefault();

    var formData = new FormData($("#consul_form")[0]);
    
    formData.append("usu_id", usu_id);

    if ($("#cons_nom").val() == " ") {
        console.log("Hay campos vacios");
        //swal("¡Advertencia!", "No escribiste ningún título", "warning");
    } else {
        console.log($('#cons_nom').val());

        $('#btnguardar').prop("disabled",true);
        $('#btnguardar').html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: "../../controller/consulta.php?op=insert",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data){
                $('#cons_nom').val('');
                
                swal("¡Listo!", "Has creado una consulta.", "success");
                console.log(data);

                $('#btnguardar').prop("disabled",false);
                $('#btnguardar').html('<i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar');   
            }
        });
    }
}

init();